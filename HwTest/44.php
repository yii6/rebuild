<?php

declare(strict_types=1);
$in = fopen('in.txt', 'r');

$scrambledStr = '';
$n = 0;
fscanf($in, '%s %d', $scrambledStr, $n);

function countDigits($str)
{
    $cnt = array_fill(0, 10, 0); // 下标0~9分别对应字符'0'~'9'
    $len = strlen($str);
    for ($i = 0; $i < $len; ++$i) {
        $ch = $str[$i];
        $idx = ord($ch) - ord('0'); // 将字符'0'~'9'转成0~9
        ++$cnt[$idx];
    }
    return $cnt;
}

$targetCount = countDigits($scrambledStr);
$targetLen = strlen($scrambledStr);

// 暴力枚举可能的起始数字 start
$upperStart = 1000 - $n + 1;
for ($start = 1; $start <= $upperStart; ++$start) {
    $end = $start + $n - 1;
    // 拼接从 $start 到 $end 的所有数字成一个字符串
    $concat = '';
    for ($num = $start; $num <= $end; ++$num) {
        $concat .= $num;
        // 如果长度已经超过目标长度，就不用继续了
        if (strlen($concat) > $targetLen) {
            break;
        }
    }

    // 长度不一致直接跳过
    if (strlen($concat) !== $targetLen) {
        continue;
    }
    $currCount = countDigits($concat);

    $match = true;
    for ($d = 0; $d < 10; ++$d) {
        if ($currCount[$d] !== $targetCount[$d]) {
            $match = false;
            break;
        }
    }
    if ($match) {
        echo $start;
        exit;
    }
}

// 关闭文件
fclose($in);
