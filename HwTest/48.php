<?php

declare(strict_types=1);

$in = fopen('in.txt', 'r');
$out = fopen('out.txt', 'w');
$n = 0;
fscanf($in, '%d', $n);

$grid = [];
$momX = $momY = $babyX = $babyY = -1;

for ($i = 0; $i < $n; ++$i) {
    $line = trim(fgets($in));
    $vals = explode(' ', $line);
    $row = [];
    for ($j = 0; $j < $n; ++$j) {
        $v = (int)$vals[$j];
        $row[$j] = $v;
        if ($v === -3) {
            $momX = $i;
            $momY = $j;
        } elseif ($v === -2) {
            $babyX = $i;
            $babyY = $j;
        }
    }
    $grid[$i] = $row;
}

// dist[x][y] = 最短步数，初始化为一个很大的数
$INF = 3000;
$dist = array_fill(0, $n, array_fill(0, $n, $INF));

// candy[x][y] = 在该最短步数下，最多能拿到的糖果数
$NEG_INF = -1;
$candy = array_fill(0, $n, array_fill(0, $n, $NEG_INF));

// 队列 (用两个数组加 head 指针实现标准 BFS)
$qx = [];
$qy = [];
$head = 0;
$tail = 0;

// 起点：妈妈位置
$dist[$momX][$momY] = 0;
$candy[$momX][$momY] = 0;
$qx[$tail] = $momX;
$qy[$tail] = $momY;
++$tail;

// 四个方向
$dirs = [
    [1, 0],
    [-1, 0],
    [0, 1],
    [0, -1],
];

// -------- BFS 主循环 --------
while ($head < $tail) {
    $x = $qx[$head];
    $y = $qy[$head];
    ++$head;

    $currDist = $dist[$x][$y];
    $currCandy = $candy[$x][$y];

    foreach ($dirs as $d) {
        $nx = $x + $d[0];
        $ny = $y + $d[1];

        // 边界检查
        if ($nx < 0 || $nx >= $n || $ny < 0 || $ny >= $n) {
            continue;
        }

        $cellVal = $grid[$nx][$ny];

        // 障碍不能走
        if ($cellVal === -1) {
            continue;
        }

        // 走过去的步数
        $nd = $currDist + 1;

        // 新的糖果数累加：
        // 只有 >=0 的格子才有糖果
        // 妈妈(-3)和宝宝(-2)都不是糖
        $gain = 0;
        if ($cellVal >= 0) {
            $gain = $cellVal;
        }
        $nc = $currCandy + $gain;

        // 如果我们发现了一条更短的路到 (nx,ny)
        if ($nd < $dist[$nx][$ny]) {
            $dist[$nx][$ny] = $nd;
            $candy[$nx][$ny] = $nc;

            $qx[$tail] = $nx;
            $qy[$tail] = $ny;
            ++$tail;
        } // 或者步数相同，但是糖果更多
        elseif ($nd == $dist[$nx][$ny] && $nc > $candy[$nx][$ny]) {
            $candy[$nx][$ny] = $nc;
            // 仍然可以入队，因为虽然距离没变，但收益提高了，
            // 邻居有可能因为这条更高收益路径继续提升后续节点的收益
            $qx[$tail] = $nx;
            $qy[$tail] = $ny;
            ++$tail;
        }
    }
}

if ($dist[$babyX][$babyY] === $INF) {
    // 到不了宝宝
    echo '-1';
} else {
    // 最短路径中能拿到的最多糖果
    echo $candy[$babyX][$babyY];
}

// 关闭文件
fclose($in);
fclose($out);
