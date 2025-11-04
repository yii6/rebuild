<?php

declare(strict_types=1);
function play(array $a, array $b): array
{
    if ($a['power'] > $b['power']) {
        return [$a, $b];
    }
    if ($a['power'] < $b['power']) {
        return [$b, $a];
    }
    if ($a['id'] < $b['id']) {
        return [$a, $b];
    }
    return [$b, $a];
}

function finishTop3(array $cur): void
{
    $size = count($cur);
    if ($size === 4) {
        // 命名方便理解
        $A = $cur[0];
        $B = $cur[1];
        $C = $cur[2];
        $D = $cur[3];
        // 半决赛1: A vs B
        [$w1, $l1] = play($A, $B);
        // 半决赛2: C vs D
        [$w2, $l2] = play($C, $D);
        // 决赛: w1 vs w2  -> 冠军/亚军
        [$champ, $runner] = play($w1, $w2);
        // 季军战: l1 vs l2 -> 季军
        [$third, $dummy] = play($l1, $l2);
        echo $champ['id'], ' ', $runner['id'], ' ', $third['id'], PHP_EOL;
        return;
    }
    if ($size === 3) {
        // 约定顺序：0 vs 1 打半决赛，2 轮空进决赛
        $A = $cur[0];
        $B = $cur[1];
        $C = $cur[2];
        // 半决赛: A vs B
        [$w1, $l1] = play($A, $B);
        // 决赛: w1 vs C
        [$champ, $runner] = play($w1, $C);
        // 季军是半决赛的输家 l1
        echo $champ['id'], ' ', $runner['id'], ' ', $l1['id'], PHP_EOL;
    }
}

$in = @fopen('in.txt', 'rb');
if ($in === false) {
    $in = STDIN;
}

$buf = trim(fgets($in));
$parts = preg_split('/\s+/', $buf);

$cur = [];
for ($i = 0, $N = count($parts); $i < $N; ++$i) {
    $cur[] = [
        'id' => $i,
        'power' => (int)$parts[$i],
    ];
}

while (count($cur) > 4) {
    $next = [];
    $size = count($cur);
    for ($i = 0; $i < $size; $i += 2) {
        if ($i + 1 < $size) {
            // 两两对打
            [$winner, $loser] = play($cur[$i], $cur[$i + 1]);
            $next[] = $winner;
        } else {
            // 轮空直接晋级
            $next[] = $cur[$i];
        }
    }
    $cur = $next;
}
finishTop3($cur);
