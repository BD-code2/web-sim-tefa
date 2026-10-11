<?php
$dir = __DIR__ . '/../../public/images/certificate/';
$files = glob($dir . '*.png');
sort($files);
$cellW = 500; $cellH = 420; $cols = 3; $rows = 2;
$sheet = imagecreatetruecolor($cellW * $cols, $cellH * $rows);
$white = imagecolorallocate($sheet, 255, 255, 255);
$black = imagecolorallocate($sheet, 0, 0, 0);
$red = imagecolorallocate($sheet, 220, 0, 0);
imagefill($sheet, 0, 0, $white);
foreach ($files as $idx => $f) {
    $src = imagecreatefrompng($f);
    $sw = imagesx($src); $sh = imagesy($src);
    $scale = min(($cellW - 20) / $sw, ($cellH - 50) / $sh);
    $dw = (int)($sw * $scale); $dh = (int)($sh * $scale);
    $col = $idx % $cols; $row = intdiv($idx, $cols);
    $x = $col * $cellW + (int)(($cellW - $dw) / 2);
    $y = $row * $cellH + 40;
    // checkerboard-ish light bg to reveal transparency
    $bg = imagecolorallocate($sheet, 235, 235, 235);
    imagefilledrectangle($sheet, $col * $cellW + 5, $row * $cellH + 5, ($col + 1) * $cellW - 5, ($row + 1) * $cellH - 5, $bg);
    imagecopyresampled($sheet, $src, $x, $y, 0, 0, $dw, $dh, $sw, $sh);
    $label = chr(65 + $idx) . ' :: ' . basename($f);
    imagestring($sheet, 5, $col * $cellW + 10, $row * $cellH + 12, $label, $black);
    imagestring($sheet, 5, $col * $cellW + 10, $row * $cellH + 25, "$sw x $sh", $red);
    imagedestroy($src);
}
$out = __DIR__ . '/cert-contact-sheet.png';
imagepng($sheet, $out);
echo "OK $out\n";
