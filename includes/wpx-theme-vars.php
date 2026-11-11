<?php
/**
 * Per-tenant CSS variable overrides from onboarding preview theme.
 * Written by CMS onboarding (mod_settings.preview_theme_css) when the owner
 * arrives from the whoopix.ai brief → preview flow.
 */
if (!function_exists('wpx_render_preview_theme_css')) {
    function wpx_render_preview_theme_css(): void {
        static $done = false;
        if ($done) { return; }
        $done = true;

        $css = '';
        if (function_exists('db_query') && function_exists('db_fetch_array')) {
            $row = db_fetch_array(db_query(
                "SELECT setting_value FROM mod_settings
                 WHERE module = 'onboarding' AND setting_key = 'preview_theme_css'
                 LIMIT 1"
            ));
            if (!empty($row['setting_value'])) {
                $css = trim((string) $row['setting_value']);
            }
        }
        if ($css === '') { return; }
        echo '<style id="wpx-preview-theme">' . $css . '</style>' . "\n";
    }
}
wpx_render_preview_theme_css();
