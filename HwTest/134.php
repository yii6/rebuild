<?php

declare(strict_types=1);
$in = @fopen('in.txt', 'rb');
if ($in === false) {
    $in = STDIN;
}
$N = (int) trim(fgets($in));
$dp = array_fill(0, $N + 1, 0);
// 初始化边界
$dp[0] = 1;
if ($N >= 1) {
    $dp[1] = 1;
}
if ($N >= 2) {
    $dp[2] = 1;
}
if ($N >= 3) {
    $dp[3] = 2;
}

for ($i = 4; $i <= $N; ++$i) {
    $dp[$i] = $dp[$i - 1] + $dp[$i - 3];
}

echo $dp[$N], PHP_EOL;

// 关闭文件句柄（如果不是 STDIN）
if ($in !== STDIN) {
    fclose($in);
}
