<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config.php';

use App\Core\Database;
use App\Repository\MemberRepository;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * PHP 8.2+ deprecates dynamic properties, so we use a typed subclass
 * instead of attaching ->errType to the base Exception.
 */
final class FbLoginException extends Exception
{
    public function __construct(
        string $message,
        public readonly ?string $errType = null,
    ) {
        parent::__construct($message);
    }
}

/**
 * Facebook login handler.
 *
 * facebook/graph-sdk was abandoned by Meta; this implementation talks
 * to Graph API directly via Guzzle. App id / secret live here to
 * mirror the previous file's layout — consider moving to db.php if
 * you centralise secrets.
 */

const FB_APP_ID      = '';
const FB_APP_SECRET  = '';
const FB_GRAPH_BASE  = 'https://graph.facebook.com/v20.0';

/** @throws Exception */
function fb_graph_get(Client $http, string $path, string $token): array
{
    try {
        $res = $http->get(FB_GRAPH_BASE . $path, [
            'query' => ['access_token' => $token],
            'http_errors' => true,
            'timeout' => 10,
        ]);
    } catch (GuzzleException $e) {
        throw new Exception('Graph returned an error: ' . $e->getMessage(), 0, $e);
    }
    $decoded = json_decode((string) $res->getBody(), true);
    return is_array($decoded) ? $decoded : [];
}

try {
    $payload = json_decode(file_get_contents('php://input') ?: 'null');
    $accessToken = (string) ($payload->accessToken ?? '');
    if ($accessToken === '') {
        throw new Exception('Missing accessToken');
    }

    $http = new Client();

    // 1. Check email permission
    $perms = fb_graph_get($http, '/me/permissions', $accessToken)['data'] ?? [];
    $emailPerm = null;
    foreach ($perms as $p) {
        if (($p['permission'] ?? null) === 'email') {
            $emailPerm = $p;
            break;
        }
    }

    if ($emailPerm === null) {
        throw new FbLoginException(
            'No email permission included at all in this access token',
            'ERR_NO_EMAIL_PERMISSION',
        );
    }
    if (($emailPerm['status'] ?? '') === 'declined') {
        throw new FbLoginException(
            'Email permission declined in this access token',
            'ERR_EMAIL_PERMISSION_DECLINED',
        );
    }

    // 2. Fetch profile
    $fbUser = (object) fb_graph_get($http, '/me?fields=id,name,email', $accessToken);
    if (empty($fbUser->email)) {
        throw new Exception('Facebook profile did not include an email');
    }

    // 3. Upsert member
    $members = new MemberRepository();
    $user = $members->findByEmail((string) $fbUser->email);

    if ($user !== null) {
        $members->updateById((int) $user->id, ['fb_uid' => (string) $fbUser->id]);
    } else {
        $imgFilename = 'fb_img_' . $fbUser->id . '.jpg';
        $http->request(
            'GET',
            'https://graph.facebook.com/' . rawurlencode((string) $fbUser->id) . '/picture',
            [
                'query' => ['type' => 'large'],
                'sink'  => fopen(__DIR__ . '/../../files/user_pic/' . $imgFilename, 'w'),
                'timeout' => 15,
            ],
        );

        $newId = $members->create([
            'fb_uid'   => (string) $fbUser->id,
            'email'    => (string) $fbUser->email,
            'username' => (string) $fbUser->name,
            'pass'     => '--dummy-password--' . bin2hex(random_bytes(22)),
            'name'     => (string) $fbUser->name,
            'pic'      => $imgFilename,
            'stat'     => 1,
        ]);
        $user = $members->find((int) $newId);
    }

    $_SESSION['user'] = $user->id . '_' . $user->pass;

    echo json_encode(['isSuccessful' => true, 'memberId' => $user->id]);
} catch (Throwable $e) {
    header(($_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.1') . ' 500 Internal Server Error', true, 500);
    echo json_encode([
        'isSuccessful' => false,
        'error' => [
            'message' => $e->getMessage(),
            'type'    => $e instanceof FbLoginException ? $e->errType : null,
        ],
    ]);
}
