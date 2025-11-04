<?php

declare(strict_types=1);

$in = fopen('in.txt', 'r');
// 读取两行输入
$line1 = trim(fgets($in)); // 第一行：任务提交时刻+执行时间
$line2 = trim(fgets($in)); // 第二行：队列长度、执行者数量

// 解析第一行，得到任务数组
$parts1 = explode(' ', $line1);
$taskCountPairs = count($parts1);
$tasks = [];
for ($i = 0; $i < $taskCountPairs; $i += 2) {
    $t = (int)$parts1[$i];
    $dur = (int)$parts1[$i + 1];
    $tasks[] = [
        't' => $t,
        'dur' => $dur,
    ];
}

// 解析第二行
[$queueCapStr, $workerNumStr] = explode(' ', $line2);
$queueCap = (int)$queueCapStr;   // 工作队列最大长度 Q
$workerNum = (int)$workerNumStr; // 执行者数量 M

// 初始化
$N = count($tasks);
$idx = 0; // 下一个还未提交的任务索引
$INF = 1 << 30;

// worker状态：-1 表示空闲，否则表示完成时刻
$workers = array_fill(0, $workerNum, -1);

// 等待队列（FIFO），仅存每个任务的执行时长dur
$queue = [];

// 用于结果统计
$lastFinishTime = 0; // 所有任务里最晚的完成时刻
$droppedCount = 0;   // 被丢弃的任务数量

/**
 * 把队列里的任务分配给空闲worker
 * - 在同一时刻 $time
 * - 按worker编号从小到大
 * - worker一旦接到任务，忙到 $time + dur.
 * @param mixed $workers
 * @param mixed $queue
 * @param mixed $time
 * @param mixed $lastFinishTime
 */
function assignTasks(&$workers, &$queue, $time, &$lastFinishTime)
{
    // 找出当前空闲的worker（busy_until == -1）
    $idle = [];
    foreach ($workers as $wi => $finishTime) {
        if ($finishTime === -1) {
            $idle[] = $wi;
        }
    }

    // 编号小的优先
    sort($idle, SORT_NUMERIC);

    // 让他们按顺序从队列头拿任务
    $idleIdx = 0;
    while (!empty($queue) && $idleIdx < count($idle)) {
        $w = $idle[$idleIdx];
        ++$idleIdx;

        // 从队列头取最老任务
        $dur = array_shift($queue);

        // 该worker开始执行，结束时间为 time + dur
        $workers[$w] = $time + $dur;

        if ($workers[$w] > $lastFinishTime) {
            $lastFinishTime = $workers[$w];
        }
    }
}

/**
 * 获取下一个完成任务的最早时刻
 * 如果所有worker都空闲，则返回 $INF.
 * @param mixed $workers
 * @param mixed $INF
 */
function getNextCompletionTime($workers, $INF)
{
    $next = $INF;
    foreach ($workers as $finishTime) {
        if ($finishTime !== -1 && $finishTime < $next) {
            $next = $finishTime;
        }
    }
    return $next;
}

// 主循环：不断推进到下一个“关键时刻”
while (true) {
    // 结束条件：
    // 1. 所有任务都提交了 ($idx >= $N)
    // 2. 队列为空
    // 3. 所有worker都空闲
    $allIdle = true;
    foreach ($workers as $wt) {
        if ($wt !== -1) {
            $allIdle = false;
            break;
        }
    }
    if ($idx >= $N && empty($queue) && $allIdle) {
        break;
    }

    // 下一个任务提交时刻
    $nextSubmitTime = ($idx < $N) ? $tasks[$idx]['t'] : $INF;
    // 下一个worker完成时刻
    $nextCompletionTime = getNextCompletionTime($workers, $INF);

    // 选择下一个时间点（可能是提交，也可能是完成，或者两者一样）
    $currentTime = min($nextSubmitTime, $nextCompletionTime);

    // 步骤A：在 currentTime 完成的worker全部变空闲
    for ($w = 0; $w < $workerNum; ++$w) {
        if ($workers[$w] === $currentTime) {
            $workers[$w] = -1; // 空闲
        }
    }

    // 步骤B：先分配队列中的任务给空闲worker
    assignTasks($workers, $queue, $currentTime, $lastFinishTime);

    // 步骤C：处理在 currentTime 提交的新任务（最多一个，因为提交时间互不重复）
    if ($idx < $N && $tasks[$idx]['t'] === $currentTime) {
        $durNew = $tasks[$idx]['dur'];

        // 入队列，如果队列满则丢掉最老的然后再加入
        if (count($queue) < $queueCap) {
            $queue[] = $durNew;
        } else {
            // 队列已满 -> 丢弃最老的
            // 注意：题目默认队列最大长度>0，否则这里要特殊处理
            if ($queueCap > 0) {
                array_shift($queue); // 丢掉队首
                ++$droppedCount;
                $queue[] = $durNew;  // 把新任务排到队尾
            }
        }
        ++$idx; // 下一个待提交任务
    }

    // 步骤D：再分配一次（因为这个时刻可能因为新任务入队后还有空闲worker）
    assignTasks($workers, $queue, $currentTime, $lastFinishTime);
}

echo $lastFinishTime . ' ' . $droppedCount;

fclose($in);
