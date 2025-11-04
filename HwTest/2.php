<?php

declare(strict_types=1);
$in = @fopen('in.txt', 'rb');
if ($in === false) {
    $in = STDIN;
}
$n = 0;
fscanf($in, '%d', $n);
$list = [];
$total = $n * 2;
$nextRemoveNo = 1;
$ans = 0;
while ($total--) {
    $line = trim(fgets($in));
    if ($line === 'remove') {
        if ($list[0] != $nextRemoveNo) {
            sort($list);
            ++$ans;
        }
        ++$nextRemoveNo;
        array_shift($list);
        continue;
    }
    [$direction, $add, $num] = explode(' ', $line);
    if ($direction === 'tail') {
        $list[] = (int)$num;
    } else {
        array_unshift($list, (int)$num);
    }
}
echo $ans;
if ($in !== STDIN) {
    fclose($in);
}
