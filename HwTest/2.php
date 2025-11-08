<?php

declare(strict_types=1);
$in = @fopen('2.txt', 'rb');
if ($in === false) {
    $in = STDIN;
}
$N = (int)trim(fgets($in));
$expected = 1;   // 下一次要移除的编号
$ans = 0;        // 触发排序的次数（答案）

// “未排序区段”的两端容器
$headStack = []; // 头插 -> 栈（LIFO）
$tailQueue = []; // 尾插 -> 队列（用递增下标模拟出队）
$qHead = 0;      // 队首下标
$tracking = false; // 是否处于“未排序区段（自上次排序后发生过插入）”
$residue = 0;      // “有序残余”的数量（上次排序后还没移除的元素个数）
$alive = 0;        // 当前序列里还活着的元素数量（统计用）

$total = $N * 2;
for ($i = 0; $i < $total; ++$i) {
    $line = trim(fgets($in));
    if ($line === 'remove') {
        // 自上次排序后只有“有序残余”
        if (!$tracking) {
            // 有残余就直接吃掉，无需排序
            if ($residue > 0) {
                --$residue;
                --$alive;
            }
            ++$expected;
            continue;
        }
        // tracking == true，有“未排序区段”的元素
        if (!empty($headStack)) {
            $top = end($headStack);
            if ($top === $expected) {
                array_pop($headStack);
                --$alive;
                ++$expected;
            } else {
                // 触发一次“排序”：计数 +1，并把剩余元素都视作“有序残余”
                ++$ans;
                // 本次 remove 把 expected 移除了
                --$alive;
                ++$expected;
                // 现在 alive 就等于“排序后剩余的数量”
                $residue = $alive;
                // 清空“未排序区段”，退出 tracking
                $headStack = [];
                $tailQueue = [];
                $qHead = 0;
                $tracking = false;
            }
        } elseif ($residue > 0) {
            // 队首实际上来自“有序残余”，一定匹配
            --$residue;
            --$alive;
            ++$expected;
        } elseif ($qHead < count($tailQueue) && $tailQueue[$qHead] === $expected) {
            ++$qHead;
            --$alive;
            ++$expected;
            // 可选：空间回收
            if ($qHead > 1024 && $qHead * 2 > count($tailQueue)) {
                // 紧凑化队列
                $tailQueue = array_slice($tailQueue, $qHead);
                $qHead = 0;
            }
        } else {
            // 队首不是期望值 -> 触发排序
            ++$ans;
            --$alive;
            ++$expected;
            $residue = $alive; // 排序后剩余这么多
            $headStack = [];
            $tailQueue = [];
            $qHead = 0;
            $tracking = false;
        }
        continue;
    }
    // 处理一次插入： "head add x" 或 "tail add x"
    [$direction, $add, $num] = explode(' ', $line);
    $x = (int)$num;
    ++$alive;
    if (!$tracking) {
        // 从“纯残余态”进入一个新的“未排序区段”
        $tracking = true;
        // headStack / tailQueue 已经在上次排序时被清空
    }
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
