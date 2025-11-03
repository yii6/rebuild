<?php

declare(strict_types=1);
$in = @fopen('in.txt', 'r');
if ($in === false) {
    $in = STDIN;
}

// 读取起始下标 K
$K = (int) trim(fgets($in));

// 读取单词数量 N
$N = (int) trim(fgets($in));

// 读取 N 行单词
$words = [];
for ($i = 0; $i < $N; ++$i) {
    $w = trim(fgets($in));
    $words[] = $w;
}
if ($in !== STDIN) {
    fclose($in);
}

// 2. 初始化使用标记 & 结果
$used = array_fill(0, $N, false);
$used[$K] = true;

$result = $words[$K];
$current = $words[$K];

// 3. 迭代式接龙
while (true) {
    // 最后一个字符
    $need = substr($current, -1); // 比如 "word" -> "d"

    $bestIdx = -1;
    $bestLen = -1;
    $bestWord = null;

    // 遍历所有未使用的单词，找候选
    for ($i = 0; $i < $N; ++$i) {
        if ($used[$i]) {
            continue;
        }

        $w = $words[$i];
        if ($w === '') {
            continue; // 防御，题目说长度至少1，其实不会进来
        }

        // 必须以 $need 开头
        if ($w[0] !== $need) {
            continue;
        }

        $len = strlen($w);

        if ($len > $bestLen) {
            // 更长的，直接替换
            $bestLen = $len;
            $bestIdx = $i;
            $bestWord = $w;
        } elseif ($len === $bestLen && $bestLen !== -1) {
            // 长度相同，取字典序更小的
            // 字典序更小：$w < $bestWord
            if ($w < $bestWord) {
                $bestIdx = $i;
                $bestWord = $w;
            }
        }
    }

    // 没有可接的单词了，停止
    if ($bestIdx === -1) {
        break;
    }

    // 接上这个最佳单词
    $used[$bestIdx] = true;
    $result .= $bestWord;
    $current = $bestWord;
}

// 4. 输出拼接结果
echo $result, PHP_EOL;
