<?php

declare(strict_types=1);
$in = @fopen('in.txt', 'rb');
if ($in === false) {
    $in = STDIN;
}
$line = trim(fgets($in));
// 2. 清洗：仅保留字母，转小写
$clean = strtolower(preg_replace('/[^a-z]/i', '', $line));
// 如果清洗后为空，按语义输出空串（或仅换行）
if ($clean === '') {
    echo PHP_EOL;
    exit;
}

$len = strlen($clean);

// 3. 预统计剩余频次 remaining[26]
$remaining = array_fill(0, 26, 0);
for ($i = 0; $i < $len; ++$i) {
    ++$remaining[ord($clean[$i]) - 97];
}

$tokens = [];
for ($i = 0; $i < $len;) {
    $ch = $clean[$i];
    $idx = ord($ch) - 97;

    // 找最大连续段 [i .. j-1]
    $j = $i + 1;
    while ($j < $len && $clean[$j] === $ch) {
        ++$j;
    }
    $runLen = $j - $i;

    if ($runLen > 1) {
        // 连续字符：输出 ch + runLen
        $tokens[] = ['ch' => $ch, 'num' => $runLen];
        // 扣减剩余频次
        $remaining[$idx] -= $runLen;
        $i = $j; // 跳过整段
    } else {
        // 非连续：输出 ch + (后续次数) = remaining[idx] - 1
        $later = $remaining[$idx] - 1;
        if ($later < 0) {
            $later = 0;
        }
        $tokens[] = ['ch' => $ch, 'num' => $later];
        // 扣减当前这一个
        --$remaining[$idx];
        ++$i;
    }
}

// 5. 排序：数字降序；若相同，字母升序
usort($tokens, function ($a, $b) {
    if ($a['num'] !== $b['num']) {
        return $b['num'] <=> $a['num']; // 数字大的在前
    }
    return $a['ch'] <=> $b['ch'];       // 字母小的在前
});

// 6. 拼接输出
$out = '';
foreach ($tokens as $t) {
    $out .= $t['ch'] . $t['num'];
}
echo $out, PHP_EOL;
