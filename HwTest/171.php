<?php

declare(strict_types=1);
$in = @fopen('in.txt', 'rb');
if ($in === false) {
    $in = STDIN;
}
$line = trim(fgets($in));
if ($in !== STDIN) {
    fclose($in);
}

preg_match_all('/\d+/', $line, $matches);
$nums = $matches[0];

$cnt = count($nums);
$K = (int) $nums[$cnt - 1];
$X = [];
for ($i = 0; $i < $cnt - 1; ++$i) {
    $X[] = (int) $nums[$i];
}

$n = count($X);

// 计算中位数
$sorted = $X;
sort($sorted); // 升序
$median = $sorted[intdiv($n, 2)]; // n/2 位置的元素，0-based

// 前缀和 prefix，使得 prefix[s] = X[0] + ... + X[s-1]
// prefix 长度 = n+1
$prefix = array_fill(0, $n + 1, 0);
for ($i = 1; $i <= $n; ++$i) {
    $prefix[$i] = $prefix[$i - 1] + $X[$i - 1];
}

// 遍历所有合法的起点 i：0 <= i <= n-K
$bestIdx = 0;
$bestDiff = null;

$limit = $n - $K;
for ($i = 0; $i <= $limit; ++$i) {
    // sum of X[i+1 ... i+K-1] = prefix[i+K] - prefix[i+1]
    // 注意当 K=1 时，这个区间是空的，我们要小心一下
    if ($K == 1) {
        $tailSum = 0;
    } else {
        $tailSum = $prefix[$i + $K] - $prefix[$i + 1];
    }

    $val = $X[$i] - $tailSum; // V(i)
    $diff = abs($val - $median);

    if ($bestDiff === null) {
        $bestDiff = $diff;
        $bestIdx = $i;
    } else {
        if ($diff < $bestDiff) {
            $bestDiff = $diff;
            $bestIdx = $i;
        } elseif ($diff == $bestDiff && $i > $bestIdx) {
            // 距离相等，取更大的 i
            $bestIdx = $i;
        }
    }
}

// 输出最终结果
echo $bestIdx, PHP_EOL;
