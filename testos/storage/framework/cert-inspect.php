<?php
foreach (glob(__DIR__ . '/../../public/images/certificate/*.png') as $f) {
    $i = getimagesize($f);
    echo substr(md5_file($f), 0, 8), ' ', $i[0], 'x', $i[1], ' ', basename($f), PHP_EOL;
}
