<?php

declare(strict_types=1);
$in = @fopen('in.txt', 'r');
if ($in === false) {
    $in = STDIN;
}

$line = trim(fgets($in));
[$Tstr, $Nstr] = preg_split('/\s+/', $line);
$T = (int) $Tstr;
$N = (int) $Nstr;

// 读取第2行: 路程段时间 (N+1 个整数)
$line = trim(fgets($in));
$parts = preg_split('/\s+/', $line);
$travelDays = 0;
for ($i = 0; $i < $N + 1; ++$i) {
    $travelDays += (int) $parts[$i];
}

// 还可以用来唱歌的天数上限
$C = $T - $travelDays;
if ($C <= 0) {
    // 没时间唱歌或刚好没有富余天数
    echo 0, PHP_EOL;
    if ($in !== STDIN) {
        fclose($in);
    }
    exit;
}

// 读取每个城市的 (M, D)
$cities = [];
for ($i = 0; $i < $N; ++$i) {
    $line = trim(fgets($in));
    [$Mstr, $Dstr] = preg_split('/\s+/', $line);
    $M = (int) $Mstr;
    $D = (int) $Dstr;
    $cities[] = [$M, $D];
}

if ($in !== STDIN) {
    fclose($in);
}
$cityProfitList = [];
foreach ($cities as $cd) {
    $M = $cd[0];
    $D = $cd[1];
    $daily = [];
    $cur = $M;
    $days = 0;
    // 最多不需要超过C天（唱超过总上限也没意义）
    while ($cur > 0 && $days < $C) {
        $daily[] = $cur;
        $cur -= $D;
        if ($cur < 0) {
            $cur = 0; // 收入不会低于0
        }
        ++$days;
    }

    $prefixProfit = [0];
    $sumVal = 0;
    $lenDaily = count($daily);
    for ($k = 0; $k < $lenDaily; ++$k) {
        $sumVal += $daily[$k];
        $prefixProfit[] = $sumVal;
    }
    $cityProfitList[] = $prefixProfit;
}

// ---------- 动态规划 ----------
//
// dp[d] = 已经处理前i个城市时，使用了正好d天唱歌所能获得的最大收益
// 初始化: dp[0]=0，其它=-1表示不可达
//
// 对每个城市i (顺序不可颠倒):
//   新建 newDp，全设为 -1
//   枚举当前dp[d]可达，然后尝试在这个城市唱k天
//   newDp[d+k] = max(newDp[d+k], dp[d] + profit_i(k))
//
// 最终答案是 max(dp[d]) for d=0..C

$dp = array_fill(0, $C + 1, -1);
$dp[0] = 0;

foreach ($cityProfitList as $prefixProfit) {
    $newDp = array_fill(0, $C + 1, -1);

    $maxStay = count($prefixProfit) - 1; // 该城市最多可唱的正收益天数
    // 但也不能超过C
    if ($maxStay > $C) {
        $maxStay = $C;
    }

    for ($used = 0; $used <= $C; ++$used) {
        if ($dp[$used] < 0) {
            continue;
        } // 这个状态不可达，跳过

        $baseIncome = $dp[$used];

        // 尝试在当前城市唱 $add 天 (0..maxStay)，
        // 前提是 used + add <= C
        for ($add = 0; $add <= $maxStay; ++$add) {
            $totalDays = $used + $add;
            if ($totalDays > $C) {
                break;
            }

            $gain = $prefixProfit[$add]; // 唱add天的收入
            $candidate = $baseIncome + $gain;

            if ($candidate > $newDp[$totalDays]) {
                $newDp[$totalDays] = $candidate;
            }
        }
    }

    $dp = $newDp;
}

$ans = 0;
for ($d = 0; $d <= $C; ++$d) {
    if ($dp[$d] > $ans) {
        $ans = $dp[$d];
    }
}

echo $ans, PHP_EOL;
