<?php

declare(strict_types=1);
$in = @fopen('in.txt', 'rb');
if ($in === false) {
    $in = STDIN;
}
$result = '';
$token = '';
$base = ord('a') - 1;
$line = trim(fgets($in));
$parts = explode(' ', $line);
foreach ($parts as $token) {
    // 去掉末尾的 '*'（如果有）
    $lastChar = $token[strlen($token) - 1];
    if ($lastChar === '*') {
        $token = substr($token, 0, -1);
    }
    $num = (int) $token;           // 比如 "20" -> 20
    $result .= chr($base + $num); // 1->a, 2->b, ...
}
echo $result, PHP_EOL;
if ($in !== STDIN) {
    fclose($in);
}
