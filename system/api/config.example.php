<?php
/**
 * Front Agent Configuration — copy to config.php and fill in values
 * from your Whoopix CMS site (Admin → site / sync credentials).
 *
 * This file is the bridge between your local/tenant site and cms.whoopix.ai.
 * Never commit real secrets.
 */

declare(strict_types=1);

// Must match the sync client registered for your license in Whoopix CMS.
define('SYNC_CLIENT_ID', getenv('SYNC_CLIENT_ID') ?: 'your-client-id');
define('SYNC_CLIENT_SECRET', getenv('SYNC_CLIENT_SECRET') ?: 'your-shared-secret-hex');
define('SYNC_PROTOCOL', 'cms-sync-v1');
define('SYNC_MAX_AGE', 300);

// Local MySQL used by this tenant front (CMS reads/writes through the agent).
define('AGENT_DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('AGENT_DB_USER', getenv('DB_USER') ?: 'whoopix_tenant');
define('AGENT_DB_PASS', getenv('DB_PASS') ?: 'change-me');
define('AGENT_DB_NAME', getenv('DB_NAME') ?: 'whoopix_tenant');

define(
    'UPLOAD_IMAGES_PATH',
    getenv('UPLOAD_IMAGES_PATH') ?: (realpath(__DIR__ . '/../../files/upload') ?: (__DIR__ . '/../../files/upload'))
);
define(
    'UPLOAD_TEMPLATES_PATH',
    getenv('UPLOAD_TEMPLATES_PATH') ?: (realpath(__DIR__ . '/../../templates') ?: (__DIR__ . '/../../templates'))
);
define('UPLOAD_IMAGES_EXTENSIONS', getenv('UPLOAD_IMAGES_EXTENSIONS') ?: 'jpg,jpeg,png,gif,webp,svg');
define('UPLOAD_TEMPLATES_EXTENSIONS', getenv('UPLOAD_TEMPLATES_EXTENSIONS') ?: 'php');
define(
    'UPLOAD_VIDEOS_PATH',
    getenv('UPLOAD_VIDEOS_PATH') ?: (realpath(__DIR__ . '/../../files/videos') ?: (__DIR__ . '/../../files/videos'))
);
define('UPLOAD_VIDEOS_EXTENSIONS', getenv('UPLOAD_VIDEOS_EXTENSIONS') ?: 'mp4,webm,mov');
define('UPLOAD_MAX_SIZE_KB', (int) (getenv('UPLOAD_MAX_SIZE_KB') ?: 5120));
define('UPLOAD_VIDEOS_MAX_SIZE_KB', (int) (getenv('UPLOAD_VIDEOS_MAX_SIZE_KB') ?: 524288));
