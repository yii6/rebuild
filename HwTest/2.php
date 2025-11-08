<?php

declare(strict_types=1);

$in = @fopen('2.txt', 'rb');
if ($in === false) {
    $in = STDIN;
}
$N = (int)trim(fgets($in));
$expected = 1;        // 下一次要移除的编号
$ans = 0;             // 触发“排序”的次数
$residue = 0;         // 上次“排序”后剩余、尚未移除的有序残余个数

$headStack = [];      // 头插形成的栈（仅记录上次“排序”后的新插入）
$tailQueue = [];      // 尾插形成的队列
$qHead = 0;           // 队首指针（避免 array_shift 的 O(k)）

$total = $N * 2;
for ($i = 0; $i < $total; ++$i) {
    $line = trim(fgets($in));
    if ($line === 'remove') {
        // 是否存在“未排序区段”
        $hasPending = (!empty($headStack)) || ($qHead < count($tailQueue));
        if (!$hasPending) {
            --$residue;
            ++$expected;
            continue;
        }
        // 有未排序区段
        if (!empty($headStack)) {
            $top = end($headStack);
            if ($top === $expected) {
                array_pop($headStack);
                ++$expected;
                continue;
            }
        } else {
            // 无头插遮挡
            if ($residue > 0) {
                --$residue;
                ++$expected;
                continue;
            }
            if ($qHead < count($tailQueue) && $tailQueue[$qHead] === $expected) {
                ++$qHead;
                ++$expected;
                continue;
            }
        }
        // ——触发一次“排序”——
        ++$ans;
        // 本次 remove 会移除 $expected
        $pendingHead = count($headStack);
        $pendingTail = count($tailQueue) - $qHead;
        $totalAlive = $residue + $pendingHead + $pendingTail;
        // 排序后，剩余都视作有序残余
        $residue = $totalAlive - 1;
        // 清空未排序区段
        $headStack = [];
        $tailQueue = [];
        $qHead = 0;
        ++$expected;
        continue;
    }
    // 插入："head add x" / "tail add x"
    [$direction, $add, $num] = explode(' ', $line);
    $x = (int)$num;
    if ($direction === 'tail') {
        $tailQueue[] = $x;
    } else {
        $headStack[] = $x;
    }
}
echo $ans;
if ($in !== STDIN) {
    fclose($in);
}
