<?php
/**
 * Thumbnail generator za slike sa očuvanjem proporcija i podrškom za JPEG, PNG i WEBP formate.
 * @param string $sourcePath
 * @param string $destPath
 * @param string $extension
 * @param int $targetWidth
 * @return bool 
 */
function generateThumbnail($sourcePath, $destPath, $extension, $targetWidth = 400) {
    list($origWidth, $origHeight) = getimagesize($sourcePath);
    if (!$origWidth || !$origHeight) return false;

    $targetHeight = round($targetWidth * ($origHeight / $origWidth));

    switch ($extension) {
        case 'jpeg':
        case 'jpg':  $srcImage = imagecreatefromjpeg($sourcePath); break;
        case 'png':  $srcImage = imagecreatefrompng($sourcePath);  break;
        case 'webp': $srcImage = imagecreatefromwebp($sourcePath); break;
        default:     return false;
    }

    if (!$srcImage) return false;

    $thumbImage = imagecreatetruecolor($targetWidth, $targetHeight);

    if ($extension === 'png' || $extension === 'webp') {
        imagealphablending($thumbImage, false);
        imagesavealpha($thumbImage, true);
    }

    imagecopyresampled($thumbImage, $srcImage, 0, 0, 0, 0, $targetWidth, $targetHeight, $origWidth, $origHeight);

    switch ($extension) {
        case 'jpeg':
        case 'jpg':  $result = imagejpeg($thumbImage, $destPath, 85); break;
        case 'png':  $result = imagepng($thumbImage, $destPath, 6);   break;
        case 'webp': $result = imagewebp($thumbImage, $destPath, 80); break;
        default:     $result = false;
    }

    imagedestroy($srcImage);
    imagedestroy($thumbImage);
    return $result;
}