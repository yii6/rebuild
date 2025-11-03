<?php
$in = @fopen("in.txt", 'rb');
if ($in === false) {
    $in = STDIN;
}

// 1. 读取 N 和 M
$line = trim(fgets($in));
list($Nstr, $Mstr) = preg_split('/\s+/', $line);
$N = (int)$Nstr;
$M = (int)$Mstr;

// 2. 读取矩阵
$grid = [];
for ($r = 0; $r < $N; $r++) {
    $rowLine = trim(fgets($in));
    $parts = preg_split('/\s+/', $rowLine);
    $row = [];
    for ($c = 0; $c < $M; $c++) {
        $row[$c] = (int)$parts[$c];
    }
    $grid[$r] = $row;
}
if ($in !== STDIN) {
    fclose($in);
}

// 3. 准备 visited 标记
$visited = [];
for ($i = 0; $i < $N; $i++) {
    $visited[$i] = array_fill(0, $M, false);
}

// 8个方向
$dirs = [
    [-1, -1], [-1, 0], [-1, 1],
    [0, -1], [0, 1],
    [1, -1], [1, 0], [1, 1],
];

$clicks = 0;
for ($i = 0; $i < $N; $i++) {
    for ($j = 0; $j < $M; $j++) {

        if ($grid[$i][$j] === 1 && !$visited[$i][$j]) {
            // 发现一个新连通块
            $clicks++;

            // 迭代式DFS/BFS，这里用栈
            $stack = [[$i, $j]];
            $visited[$i][$j] = true;

            while (!empty($stack)) {
                [$cr, $cc] = array_pop($stack);

                // 遍历8方向
                foreach ($dirs as $d) {
                    $nr = $cr + $d[0];
                    $nc = $cc + $d[1];

                    if ($nr < 0 || $nr >= $N || $nc < 0 || $nc >= $M) {
                        continue;
                    }
                    if ($grid[$nr][$nc] === 1 && !$visited[$nr][$nc]) {
                        $visited[$nr][$nc] = true;
                        $stack[] = [$nr, $nc];
                    }
                }
            }
        }
    }
}
echo $clicks, PHP_EOL;
