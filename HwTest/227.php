<?php

declare(strict_types=1);
$in = @fopen('in.txt', 'r');
if ($in === false) {
    $in = STDIN;
}
$haystack = trim(fgets($in));
$needle = trim(fgets($in));

if ($in !== STDIN) {
    fclose($in);
}

// 2. 定义一个函数：判断从$start开始是否完整匹配整个$needle
function matchAt($hay, $needle, $start)
{
    $hLen = strlen($hay);
    $pLen = strlen($needle);

    $hi = $start; // pointer in haystack
    $pi = 0;      // pointer in needle

    while ($pi < $pLen) {
        if ($hi >= $hLen) {
            // haystack 已经没字符可匹配了
            return false;
        }

        $ch = $needle[$pi];

        if ($ch !== '[') {
            // 普通字符：必须严格相等
            if ($hay[$hi] !== $ch) {
                return false;
            }
            ++$hi;
            ++$pi;
        } else {
            // ch == '[': 我们需要一直找到下一个 ']'
            ++$pi; // 跳过 '['
            $setChars = [];

            // 收集候选字符，直到 ']'
            while ($pi < $pLen && $needle[$pi] !== ']') {
                $setChars[] = $needle[$pi];
                ++$pi;
            }

            // 如果没遇到']'就到头了，按输入保证这种不会发生（题目保证成对出现）
            if ($pi >= $pLen) {
                return false;
            }

            // needle[$pi] 此时应当是 ']'
            // 检查 hay[$hi] 是否在 setChars 中
            $targetChar = $hay[$hi];
            $ok = false;
            foreach ($setChars as $cand) {
                if ($cand === $targetChar) {
                    $ok = true;
                    break;
                }
            }
            if (! $ok) {
                return false;
            }

            // 成功匹配这一组，前进
            ++$hi;
            ++$pi; // 跳过 ']'
        }
    }

    // 如果我们成功跑完needle，说明匹配成功
    return true;
}

// 3. 依次尝试每个起点
$hLen = strlen($haystack);
$answer = -1;
for ($start = 0; $start < $hLen; ++$start) {
    if (matchAt($haystack, $needle, $start)) {
        $answer = $start;
        break;
    }
}

// 4. 输出答案
echo $answer, PHP_EOL;
