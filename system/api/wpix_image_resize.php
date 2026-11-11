<?php
/**
 * Resize raster images in place (GD). Used by GrapesJS template uploads and tenant sync.
 */
declare(strict_types=1);

if (!defined('WPX_IMAGE_MAX_WIDTH')) {
    define('WPX_IMAGE_MAX_WIDTH', 1200);
}
if (!defined('WPX_JPEG_QUALITY')) {
    define('WPX_JPEG_QUALITY', 85);
}

/**
 * @return array{ok:bool,resized:bool,width:int,height:int,error?:string}
 */
function wpix_image_resize_file(string $path, int $maxWidth = WPX_IMAGE_MAX_WIDTH, int $jpegQuality = WPX_JPEG_QUALITY): array
{
    if ($maxWidth < 1) {
        return ['ok' => false, 'resized' => false, 'width' => 0, 'height' => 0, 'error' => 'invalid_max_width'];
    }

    if (!is_file($path) || !is_readable($path) || !is_writable($path)) {
        return ['ok' => false, 'resized' => false, 'width' => 0, 'height' => 0, 'error' => 'missing_or_not_writable'];
    }

    if (!function_exists('imagecreatetruecolor')) {
        return ['ok' => true, 'resized' => false, 'width' => 0, 'height' => 0, 'error' => 'gd_unavailable'];
    }

    $info = @getimagesize($path);
    if ($info === false) {
        return ['ok' => true, 'resized' => false, 'width' => 0, 'height' => 0, 'error' => 'not_image'];
    }

    $width  = (int) $info[0];
    $height = (int) $info[1];
    $type   = (int) $info[2];

    if ($width <= $maxWidth) {
        return ['ok' => true, 'resized' => false, 'width' => $width, 'height' => $height];
    }

    $src = wpix_image_create_from_type($path, $type);
    if ($src === false) {
        return ['ok' => true, 'resized' => false, 'width' => $width, 'height' => $height, 'error' => 'decode_failed'];
    }

    if ($type === IMAGETYPE_JPEG) {
        $src = wpix_image_apply_exif_orientation($src, $path, $width, $height);
        $width  = imagesx($src);
        $height = imagesy($src);
        if ($width <= $maxWidth) {
            imagedestroy($src);
            return ['ok' => true, 'resized' => false, 'width' => $width, 'height' => $height];
        }
    }

    $newWidth  = $maxWidth;
    $newHeight = max(1, (int) round($height * ($maxWidth / $width)));

    $dst = imagecreatetruecolor($newWidth, $newHeight);
    if ($dst === false) {
        imagedestroy($src);
        return ['ok' => false, 'resized' => false, 'width' => $width, 'height' => $height, 'error' => 'canvas_failed'];
    }

    if (in_array($type, [IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP], true)) {
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
        if ($transparent !== false) {
            imagefilledrectangle($dst, 0, 0, $newWidth, $newHeight, $transparent);
        }
    }

    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    imagedestroy($src);

    $saved = wpix_image_save_by_type($dst, $path, $type, $jpegQuality);
    imagedestroy($dst);

    if (!$saved) {
        return ['ok' => false, 'resized' => false, 'width' => $width, 'height' => $height, 'error' => 'save_failed'];
    }

    return ['ok' => true, 'resized' => true, 'width' => $newWidth, 'height' => $newHeight];
}

/** @return resource|false */
function wpix_image_create_from_type(string $path, int $type)
{
    switch ($type) {
        case IMAGETYPE_JPEG:
            return @imagecreatefromjpeg($path) ?: false;
        case IMAGETYPE_PNG:
            return @imagecreatefrompng($path) ?: false;
        case IMAGETYPE_GIF:
            return @imagecreatefromgif($path) ?: false;
        case IMAGETYPE_WEBP:
            return function_exists('imagecreatefromwebp') ? (@imagecreatefromwebp($path) ?: false) : false;
        default:
            return false;
    }
}

/** @param resource $img */
function wpix_image_save_by_type($img, string $path, int $type, int $jpegQuality): bool
{
    $jpegQuality = max(1, min(100, $jpegQuality));

    switch ($type) {
        case IMAGETYPE_JPEG:
            return @imagejpeg($img, $path, $jpegQuality);
        case IMAGETYPE_PNG:
            return @imagepng($img, $path, 6);
        case IMAGETYPE_GIF:
            return @imagegif($img, $path);
        case IMAGETYPE_WEBP:
            return function_exists('imagewebp') ? @imagewebp($img, $path, $jpegQuality) : false;
        default:
            return false;
    }
}

/**
 * Apply EXIF orientation for JPEG sources.
 *
 * @param resource $img
 * @return resource
 */
function wpix_image_apply_exif_orientation($img, string $path, int $width, int $height)
{
    if (!function_exists('exif_read_data')) {
        return $img;
    }

    $exif = @exif_read_data($path);
    if (!is_array($exif) || empty($exif['Orientation'])) {
        return $img;
    }

    $orientation = (int) $exif['Orientation'];
    switch ($orientation) {
        case 2:
            imageflip($img, IMG_FLIP_HORIZONTAL);
            break;
        case 3:
            $img = imagerotate($img, 180, 0) ?: $img;
            break;
        case 4:
            imageflip($img, IMG_FLIP_VERTICAL);
            break;
        case 5:
            $img = imagerotate($img, -90, 0) ?: $img;
            imageflip($img, IMG_FLIP_HORIZONTAL);
            break;
        case 6:
            $img = imagerotate($img, -90, 0) ?: $img;
            break;
        case 7:
            $img = imagerotate($img, 90, 0) ?: $img;
            imageflip($img, IMG_FLIP_HORIZONTAL);
            break;
        case 8:
            $img = imagerotate($img, 90, 0) ?: $img;
            break;
    }

    return $img;
}

/**
 * Normalize a single PHP $_FILES entry (single or multi) into a flat list.
 *
 * @return list<array{name:string,type:string,tmp_name:string,error:int,size:int}>
 */
function wpix_collect_uploaded_files(string $field): array
{
    if (empty($_FILES[$field])) {
        return [];
    }

    $file = $_FILES[$field];
    if (!is_array($file['name'])) {
        return [[
            'name'     => (string) $file['name'],
            'type'     => (string) ($file['type'] ?? ''),
            'tmp_name' => (string) $file['tmp_name'],
            'error'    => (int) $file['error'],
            'size'     => (int) $file['size'],
        ]];
    }

    $out = [];
    foreach ($file['name'] as $i => $name) {
        $out[] = [
            'name'     => (string) $name,
            'type'     => (string) ($file['type'][$i] ?? ''),
            'tmp_name' => (string) ($file['tmp_name'][$i] ?? ''),
            'error'    => (int) ($file['error'][$i] ?? UPLOAD_ERR_NO_FILE),
            'size'     => (int) ($file['size'][$i] ?? 0),
        ];
    }

    return $out;
}
