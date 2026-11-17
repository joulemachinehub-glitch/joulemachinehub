<?php
// Joule Machine Hub - Live Image Rotation API
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/auth.php';

// Check admin auth
if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Admin authorization required. Please log in as joulemachinehub@gmail.com']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'POST method required']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: $_POST;

$imageRel = trim($data['image_path'] ?? '');
$direction = trim($data['direction'] ?? $data['angle'] ?? '90_cw');

if (empty($imageRel)) {
    echo json_encode(['success' => false, 'error' => 'No image path provided']);
    exit;
}

// Security: Prevent directory traversal, restrict strictly to assets/images/machines/
$imageRel = ltrim(str_replace('\\', '/', $imageRel), '/');
if (!str_starts_with($imageRel, 'assets/images/machines/')) {
    echo json_encode(['success' => false, 'error' => 'Invalid image directory']);
    exit;
}

$webRoot = realpath(__DIR__ . '/..');
$fullPath = $webRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $imageRel);

if (!file_exists($fullPath)) {
    echo json_encode(['success' => false, 'error' => 'Image file not found: ' . htmlspecialchars($imageRel)]);
    exit;
}

$ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
    echo json_encode(['success' => false, 'error' => 'Unsupported image format']);
    exit;
}

$rotated = false;

// 1. Try PHP GD if loaded (standard on Linux / Hostinger)
if (extension_loaded('gd')) {
    try {
        if ($ext === 'png') {
            $src = imagecreatefrompng($fullPath);
        } else {
            $src = imagecreatefromjpeg($fullPath);
        }

        if ($src) {
            // GD imagerotate: positive angle rotates counter-clockwise
            $gdDegrees = 270; // default 90 CW in GD is 270
            if ($direction === '90_ccw') {
                $gdDegrees = 90;
            } elseif ($direction === '180') {
                $gdDegrees = 180;
            } elseif ($direction === '90_cw') {
                $gdDegrees = 270;
            }

            $rot = imagerotate($src, $gdDegrees, 0);
            imagedestroy($src);

            if ($rot) {
                if ($ext === 'png') {
                    imagepng($rot, $fullPath);
                } else {
                    imagejpeg($rot, $fullPath, 88);
                }
                imagedestroy($rot);
                $rotated = true;
            }
        }
    } catch (Throwable $e) {
        error_log("GD rotate error: " . $e->getMessage());
    }
}

// 2. Windows fallback using PowerShell System.Drawing
if (!$rotated && PHP_OS_FAMILY === 'Windows') {
    $rotateFlip = 'Rotate90FlipNone';
    if ($direction === '90_ccw') {
        $rotateFlip = 'Rotate270FlipNone';
    } elseif ($direction === '180') {
        $rotateFlip = 'Rotate180FlipNone';
    }

    $psScript = "Add-Type -AssemblyName System.Drawing; " .
        "\$bytes = [System.IO.File]::ReadAllBytes('{$fullPath}'); " .
        "\$ms = New-Object System.IO.MemoryStream(\$bytes, \$false); " .
        "\$bmp = [System.Drawing.Bitmap]::FromStream(\$ms); " .
        "\$clone = [System.Drawing.Bitmap]\$bmp.Clone(); " .
        "\$bmp.Dispose(); \$ms.Dispose(); " .
        "\$clone.RotateFlip([System.Drawing.RotateFlipType]::{$rotateFlip}); " .
        "\$codecs = [System.Drawing.Imaging.ImageCodecInfo]::GetImageEncoders(); " .
        "\$jpegCodec = \$codecs | Where-Object { \$_.MimeType -eq 'image/jpeg' }; " .
        "\$ep = New-Object System.Drawing.Imaging.EncoderParameters(1); " .
        "\$ep.Param[0] = New-Object System.Drawing.Imaging.EncoderParameter([System.Drawing.Imaging.Encoder]::Quality, 88L); " .
        "\$outMs = New-Object System.IO.MemoryStream; " .
        "\$clone.Save(\$outMs, \$jpegCodec, \$ep); " .
        "\$newBytes = \$outMs.ToArray(); " .
        "\$outMs.Dispose(); \$clone.Dispose(); " .
        "[System.IO.File]::WriteAllBytes('{$fullPath}', \$newBytes);";

    $tempScript = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'rot_' . uniqid() . '.ps1';
    file_put_contents($tempScript, $psScript);
    exec("powershell -ExecutionPolicy Bypass -File \"{$tempScript}\" 2>&1", $output, $ret);
    @unlink($tempScript);
    if ($ret === 0) {
        $rotated = true;
    }
}

if ($rotated) {
    clearstatcache(true, $fullPath);
    $info = @getimagesize($fullPath);
    $w = $info ? $info[0] : 0;
    $h = $info ? $info[1] : 0;

    echo json_encode([
        'success' => true,
        'message' => 'Image rotated successfully',
        'image_path' => $imageRel,
        'dimensions' => "{$w}x{$h}",
        'cache_buster' => time()
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Image rotation failed. Please check file permissions.'
    ]);
}
