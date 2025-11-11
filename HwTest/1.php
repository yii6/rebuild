<?php

declare(strict_types=1);

$in = @fopen('1.txt', 'rb');
if ($in === false) {
    $in = STDIN;
}
$line = trim(fgets($in));
[$n, $T, $k, $str] = preg_split('/\s+/', $line);
$n = (int)$n;
$T = (int)$T;
$k = (int)$k;
$calories = explode(',', substr($str, 1, -1));
$calories = array_map('intval', $calories);

function subsetSumsByCountDP(array $arr): array
{
    $m = count($arr);
    // 初始化：选 0 个时，和为 0 的方案有 1 种
    $buckets = array_fill(0, $m + 1, []);
    $buckets[0][0] = 1;
    $maxCnt = 0; // 当前已处理元素能达到的最大 cnt
    foreach ($arr as $v) {
        // 倒序遍历 cnt，避免本轮更新污染下一轮读取
        for ($cnt = $maxCnt; $cnt >= 0; --$cnt) {
            foreach ($buckets[$cnt] as $sum => $freq) {
                $ns = $sum + $v;
                $buckets[$cnt + 1][$ns] = ($buckets[$cnt + 1][$ns] ?? 0) + $freq;
            }
        }
        ++$maxCnt;
    }
    return $buckets;
}

// 折半
$mid = intdiv($n, 2);
$left = array_slice($calories, 0, $mid);
$right = array_slice($calories, $mid);

// 两边都预处理为：选中个数 => (sum => 方案数)
$leftBuckets = subsetSumsByCountDP($left);
$rightBuckets = subsetSumsByCountDP($right);

// 统计答案
$ans = 0;
$maxL = count($left);
$maxR = count($right);

// 枚举左边选中个数 cntL，右边需要选 needCnt = k - cntL
for ($cntL = 0; $cntL <= min($k, $maxL); ++$cntL) {
    $needCnt = $k - $cntL;
    if ($needCnt > $maxR) {
        continue;
    }
    if (empty($leftBuckets[$cntL]) || empty($rightBuckets[$needCnt])) {
        continue;
    }
    $leftMap = $leftBuckets[$cntL];
    $rightMap = $rightBuckets[$needCnt];
    // 遍历左边的每个 sumL，查右边 (T - sumL)
    foreach ($leftMap as $sumL => $freqL) {
        $needSum = $T - $sumL;
        if (isset($rightMap[$needSum])) {
            $ans += $freqL * $rightMap[$needSum];
        }
    }
}
echo $ans;
if ($in !== STDIN) {
    fclose($in);
}
