<?php

declare(strict_types=1);

$in = fopen('in.txt', 'r');

// 1. 读取 n 和 m
$n = 0;
$m = 0;
fscanf($in, '%d %d', $n, $m);

// 2. 读取科目名称行
$subjectsLine = trim(fgets($in));
$subjects = explode(' ', $subjectsLine);

// 建一个 map: 科目名 => 下标
$subjectIndex = [];
for ($i = 0; $i < $m; ++$i) {
    $subjectIndex[$subjects[$i]] = $i;
}

// 3. 读取 n 个学生信息
$students = [];
for ($i = 0; $i < $n; ++$i) {
    $line = trim(fgets($in));
    $parts = explode(' ', $line);

    $name = $parts[0];
    $scores = [];
    $total = 0;
    for ($j = 0; $j < $m; ++$j) {
        $score = (int) $parts[$j + 1];
        $scores[$j] = $score;
        $total += $score;
    }

    $students[] = [
        'name' => $name,
        'scores' => $scores,
        'total' => $total,
    ];
}

// 4. 读取最后一行：排序用的科目名称
$sortSubject = trim(fgets($in));

// 5. 确定排序依据
// 如果科目存在，就用该科目的分数；否则按总分
$useSubjectIndex = null;
if (isset($subjectIndex[$sortSubject])) {
    $useSubjectIndex = $subjectIndex[$sortSubject]; // 某一科
} else {
    $useSubjectIndex = -1; // 用 -1 表示“按总分”
}

// 6. 排序
usort($students, function ($a, $b) use ($useSubjectIndex) {
    // 先取比较分数
    if ($useSubjectIndex === -1) {
        // 按总分
        $scoreA = $a['total'];
        $scoreB = $b['total'];
    } else {
        // 指定科目
        $scoreA = $a['scores'][$useSubjectIndex];
        $scoreB = $b['scores'][$useSubjectIndex];
    }

    // 第一关键字：分数，降序（高分在前）
    if ($scoreA !== $scoreB) {
        return $scoreB - $scoreA; // 大的在前
    }

    // 第二关键字：姓名，字典序升序（小的在前）
    return strcmp($a['name'], $b['name']);
});

// 7. 输出排序后的姓名列表
$resultNames = [];
foreach ($students as $stu) {
    $resultNames[] = $stu['name'];
}
echo implode(' ', $resultNames);

fclose($in);
