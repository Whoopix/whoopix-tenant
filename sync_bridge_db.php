<?php
/**
 * Resolve tenant DB credentials for sync-bridge scripts.
 * NEVER guess db_name from username slug — read system/api/config.php only.
 */
declare(strict_types=1);

if (!function_exists('wpx_sync_bridge_load_tenant_db')) {
    /**
     * @return array{client_id:string, db_name:string, db_user:string, db_pass:string, error:string}
     */
    function wpx_sync_bridge_load_tenant_db(string $tenantRoot): array {
        $out = array(
            'client_id'  => '',
            'db_name'    => '',
            'db_user'    => '',
            'db_pass'    => '',
            'license_id' => '',
            'error'      => '',
        );

        $configFile = rtrim($tenantRoot, '/\\') . '/system/api/config.php';
        if (!is_file($configFile)) {
            $out['error'] = 'missing system/api/config.php';
            return $out;
        }

        require $configFile;

        if (defined('SYNC_CLIENT_ID') && SYNC_CLIENT_ID !== '' && strpos((string) SYNC_CLIENT_ID, '{{') === false) {
            $out['client_id'] = (string) SYNC_CLIENT_ID;
        }
        if (defined('AGENT_DB_NAME') && AGENT_DB_NAME !== '' && strpos((string) AGENT_DB_NAME, '{{') === false) {
            $out['db_name'] = (string) AGENT_DB_NAME;
            $out['db_user'] = (defined('AGENT_DB_USER') && AGENT_DB_USER !== '' && strpos((string) AGENT_DB_USER, '{{') === false)
                ? (string) AGENT_DB_USER
                : $out['db_name'];
            $out['db_pass'] = (defined('AGENT_DB_PASS') && AGENT_DB_PASS !== '' && strpos((string) AGENT_DB_PASS, '{{') === false)
                ? (string) AGENT_DB_PASS
                : '';
        }

        if ($out['client_id'] === '') {
            $dirSlug = basename(rtrim($tenantRoot, '/\\'));
            if (!in_array($dirSlug, array('public_html', 'httpdocs', 'www', 'html'), true)) {
                $out['client_id'] = $dirSlug;
            }
        }

        if ($out['db_name'] === '' || $out['db_pass'] === '') {
            $out['error'] = 'tenant DB credentials not configured in system/api/config.php (re-run provision or repair_files)';
        }

        // License id from tenant config.php without a second require (api config already loaded above).
        $rootCfg = rtrim($tenantRoot, '/\\') . '/config.php';
        if (is_file($rootCfg)) {
            $cfgSrc = (string) file_get_contents($rootCfg);
            foreach (array('PROVISION_LICENSE_ID', 'LICENSE_ID', 'WHOPIX_LICENSE_ID') as $licConst) {
                if ($out['license_id'] !== '') {
                    break;
                }
                if (preg_match("/define\s*\(\s*'" . preg_quote($licConst, '/') . "'\s*,\s*'([^']+)'/", $cfgSrc, $m)) {
                    $lic = trim($m[1]);
                    if ($lic !== '' && strpos($lic, '{{') === false) {
                        $out['license_id'] = $lic;
                    }
                } elseif (preg_match("/define\s*\(\s*'" . preg_quote($licConst, '/') . "'\s*,\s*(\d+)/", $cfgSrc, $m)) {
                    $lic = trim($m[1]);
                    if ($lic !== '' && $lic !== '0') {
                        $out['license_id'] = $lic;
                    }
                }
            }
        }
        if ($out['license_id'] === '' && defined('SYNC_LICENSE_ID')) {
            $lic = trim((string) SYNC_LICENSE_ID);
            if ($lic !== '' && strpos($lic, '{{') === false) {
                $out['license_id'] = $lic;
            }
        }

        // Fallback: license id stored in tenant mod_settings during provision.
        if ($out['license_id'] === '' && $out['db_name'] !== '' && $out['db_pass'] !== '') {
            try {
                $pdo = new PDO(
                    'mysql:host=localhost;dbname=' . $out['db_name'] . ';charset=utf8mb4',
                    $out['db_user'],
                    $out['db_pass'],
                    array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
                );
                foreach (array('whoopix_license_id', 'license_id', 'provision_license_id') as $settingKey) {
                    $st = $pdo->prepare(
                        'SELECT `setting_value` FROM `mod_settings` WHERE `setting_key` = ? LIMIT 1'
                    );
                    $st->execute(array($settingKey));
                    $val = trim((string) $st->fetchColumn());
                    if ($val !== '' && ctype_digit($val)) {
                        $out['license_id'] = $val;
                        break;
                    }
                }
            } catch (Throwable $e) {
                /* ignore — optional fallback */
            }
        }

        return $out;
    }
}

if (!function_exists('wpx_sync_bridge_relink_via_provision')) {
    /**
     * Resolve custom domain from hoops registry and relink docroot.
     *
     * @return array{ok:bool,skipped?:bool,domain?:string,reason?:string,http?:int}
     */
    function wpx_sync_bridge_relink_via_provision(string $clientId, string $licenseId): array {
        $token = getenv('PROVISION_SHARED_SECRET');
        if ($token === false || $token === '') {
            $token = 'whpx_prov_5e8a1c7b9d2f4a6e';
        }
        $url = 'https://hoops.site/provision.php?action=relink_domain&token=' . rawurlencode((string) $token)
            . '&username=' . rawurlencode($clientId)
            . '&license_id=' . rawurlencode($licenseId);
        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT        => 120,
            CURLOPT_SSL_VERIFYPEER => false,
        ));
        $raw  = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $body = json_decode((string) $raw, true);
        if (is_array($body) && !empty($body['skipped'])) {
            return array(
                'ok'      => true,
                'skipped' => true,
                'reason'  => (string) ($body['reason'] ?? 'not_custom_domain'),
                'http'    => $code,
            );
        }
        $ok = ($code >= 200 && $code < 300 && is_array($body) && !empty($body['ok']));
        return array(
            'ok'      => $ok,
            'skipped' => false,
            'domain'  => is_array($body) ? (string) ($body['domain'] ?? '') : '',
            'http'    => $code,
            'via'     => 'relink_domain',
        );
    }
}

if (!function_exists('wpx_sync_bridge_relink_custom_domain')) {
    /**
     * After bridge apply, refresh custom-domain docroot symlink via hoops.site provision.
     *
     * @return array{ok:bool,skipped?:bool,domain?:string,reason?:string,http?:int}
     */
    function wpx_sync_bridge_relink_custom_domain(string $clientId, string $licenseId, ?PDO $pdo = null): array {
        $clientId = trim($clientId);
        if ($clientId === '') {
            return array('ok' => false, 'skipped' => true, 'reason' => 'no_client');
        }

        // Lab (client / license 3453219): never run domain_relink after apply.
        // provision link_custom_domain() does a full recursive copy from /user/{client}/
        // onto the custom-domain docroot, which can overwrite repaired Lab
        // includes/header.php+footer.php with contaminated Site chrome that still
        // lives under /user/YOUR_CLIENT_ID/. Apply already writes directly to client.whoopix.ai.
        if ($clientId === 'client' || $licenseId === '3453219') {
            return array(
                'ok'         => true,
                'skipped'    => true,
                'reason'     => 'shai_lab_protect_no_relink_overwrite',
                'client_id'  => $clientId,
                'license_id' => $licenseId,
            );
        }

        $domain = '';
        $host = strtolower(preg_replace('~:\d+$~', '', (string) ($_SERVER['HTTP_HOST'] ?? '')));
        if ($host !== '' && !preg_match('~^(?:www\.)?hoops\.site$~i', $host) && !preg_match('~\.hoops\.site$~i', $host)) {
            $domain = preg_replace('~^www\.~i', '', $host);
        }

        if ($domain === '' && $pdo instanceof PDO) {
            try {
                $cols = $pdo->query('SHOW COLUMNS FROM `config_site`')->fetchAll(PDO::FETCH_COLUMN);
                if (in_array('site_url', $cols, true)) {
                    $row = $pdo->query('SELECT `site_url` FROM `config_site` LIMIT 1')->fetch(PDO::FETCH_ASSOC);
                    $siteUrl = trim((string) ($row['site_url'] ?? ''));
                    if ($siteUrl !== '') {
                        $parsed = parse_url($siteUrl, PHP_URL_HOST);
                        if (is_string($parsed) && $parsed !== '') {
                            $domain = preg_replace('~^www\.~i', '', strtolower($parsed));
                        }
                    }
                }
            } catch (Throwable $e) { /* config_site lookup is best-effort */ }
        }

        if ($domain === ''
            || preg_match('~^(?:www\.)?hoops\.site$~i', $domain)
            || preg_match('~\.hoops\.site$~i', $domain)) {
            return wpx_sync_bridge_relink_via_provision($clientId, $licenseId);
        }

        $token = getenv('PROVISION_SHARED_SECRET');
        if ($token === false || $token === '') {
            $token = 'whpx_prov_5e8a1c7b9d2f4a6e';
        }
        $url = 'https://hoops.site/provision.php?action=link_domain&token=' . rawurlencode((string) $token)
            . '&username=' . rawurlencode($clientId)
            . '&domain=' . rawurlencode($domain)
            . '&license_id=' . rawurlencode($licenseId);

        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT        => 120,
            CURLOPT_SSL_VERIFYPEER => false,
        ));
        $raw  = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $body = json_decode((string) $raw, true);
        $ok   = ($code >= 200 && $code < 300 && is_array($body) && !empty($body['ok']));

        return array(
            'ok'      => $ok,
            'skipped' => false,
            'domain'  => $domain,
            'http'    => $code,
        );
    }
}
