<?php

declare(strict_types=1);
function wrongInput($handle)
{
    echo '-1', PHP_EOL;
    if ($handle !== STDIN) {
        fclose($handle);
    }
    exit;
}

$in = @fopen('in.txt', 'rb');
if ($in === false) {
    $in = STDIN;
}

// 1. 读取第一行 (M,N)
$firstLine = trim(fgets($in));
[$mStr, $nStr] = explode(',', $firstLine);

$M = (int) $mStr;
$N = (int) $nStr;

if ($M < 3 || $M > 10 || $N < 3 || $N > 100) {
    wrongInput($in);
}

// 2. 初始化选手信息
$players = [];
for ($i = 0; $i < $N; ++$i) {
    $players[$i] = [
        'id' => $i + 1,
        'total' => 0,
        'cnt' => array_fill(1, 10, 0),  // 各分数出现次数
    ];
}

for ($judge = 0; $judge < $M; ++$judge) {
    $line = trim(fgets($in));
    $parts = explode(',', $line);
    if (count($parts) !== $N) {
        wrongInput($in);
    }
    // 逐个分数检查并计入
    for ($i = 0; $i < $N; ++$i) {
        $scoreStr = trim($parts[$i]);
        $score = (int) $scoreStr;
        if ($score < 1 || $score > 10) {
            wrongInput($in);
        }
        $players[$i]['total'] += $score;
        ++$players[$i]['cnt'][$score];
    }
}

// 4. 排序
usort($players, function ($a, $b) {
    if ($a['total'] !== $b['total']) {
        return $b['total'] <=> $a['total'];
    }
    // 再比高分次数，从10分往1分
    for ($score = 10; $score >= 1; --$score) {
        if ($a['cnt'][$score] !== $b['cnt'][$score]) {
            return $b['cnt'][$score] <=> $a['cnt'][$score];
        }
    }
});

$top3 = [
    $players[0]['id'],
    $players[1]['id'],
    $players[2]['id'],
];

echo implode(',', $top3), PHP_EOL;

if ($in !== STDIN) {
    fclose($in);
}
