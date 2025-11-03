<?php

declare(strict_types=1);

$in = fopen('in.txt', 'r');
if ($in === false) {
    $in = STDIN;
}

$n = 0;
fscanf($in, '%d', $n);

$lamps = [];
for ($i = 0; $i < $n; ++$i) {
    $id = $x1 = $y1 = $x2 = $y2 = 0;
    fscanf($in, '%d %d %d %d %d', $id, $x1, $y1, $x2, $y2);

    $cy = ($y1 + $y2) / 2.0;
    $lamps[] = [
        'id' => $id,
        'x1' => $x1,
        'y1' => $y1,
        'x2' => $x2,
        'y2' => $y2,
        'cy' => $cy,
    ];
}

$height = $lamps[0]['y2'] - $lamps[0]['y1'];
$radius = $height / 2.0;

// 2. 先按 cy 升序，再按 x1 升序，确保“更靠上更靠左”的灯靠前
usort($lamps, function ($a, $b) {
    if ($a['cy'] == $b['cy']) {
        return $a['x1'] <=> $b['x1'];
    }
    return $a['cy'] <=> $b['cy'];
});

// 3. 分行 + 行内排序
$used = array_fill(0, $n, false);
$resultIds = [];

for ($i = 0; $i < $n; ++$i) {
    if ($used[$i]) {
        continue;
    }

    // 当前还没用的最靠上的灯 -> 作为本行的基准
    $baseCy = $lamps[$i]['cy'];

    // 收集本行
    $rowIdxList = [];
    for ($j = $i; $j < $n; ++$j) {
        if ($used[$j]) {
            continue;
        }
        if (abs($lamps[$j]['cy'] - $baseCy) <= $radius) {
            $rowIdxList[] = $j;
            $used[$j] = true;
        }
    }

    // 这一行按 x1 升序，如果 x1 相同按 id
    usort($rowIdxList, function ($ia, $ib) use ($lamps) {
        $A = $lamps[$ia];
        $B = $lamps[$ib];
        if ($A['x1'] == $B['x1']) {
            return $A['id'] <=> $B['id'];
        }
        return $A['x1'] <=> $B['x1'];
    });

    // 把行内的灯id按顺序放到结果
    foreach ($rowIdxList as $idx) {
        $resultIds[] = $lamps[$idx]['id'];
    }
}

// 4. 输出结果（到标准输出或者你想写到文件也行）
echo implode(' ', $resultIds), PHP_EOL;

fclose($in);
