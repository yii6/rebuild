<?php

declare(strict_types=1);
$in = @fopen('in.txt', 'r');
if ($in === false) {
    // 如果 in.txt 不存在，就用标准输入，方便调试
    $in = STDIN;
}

$n = 0;
fscanf($in, '%d', $n);

$f = array_fill(0, $n + 1, 0);
$prefix = array_fill(0, $n + 1, 0);   // prefix[x] = f(1)+...+f(x)

$f[1] = 1;
$prefix[1] = 1;

// 4. DP 计算从 2 到 n
for ($x = 2; $x <= $n; ++$x) {
    $half = floor($x / 2);
    $f[$x] = 1 + $prefix[$half];
    $prefix[$x] = $prefix[$x - 1] + $f[$x];
}
echo $f[$n], PHP_EOL;

fclose($in);
