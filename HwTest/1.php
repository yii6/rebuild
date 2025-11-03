<?php
$in = @fopen("in.txt", "r");
if ($in === false) {
    $in = STDIN;
}
$line = trim(fgets($in));
[$n, $T, $k, $str] = preg_split('/\s+/', $line);
$n = (int)$n;
$T = (int)$T;
$k = (int)$k;
$ans = 0;
$calories = explode(",", substr($str, 1, -1));

//是否是k个运动
function isK($str, $k)
{
    $n = strlen($str);
    $tmp = 0;
    $excises = [];
    for ($i = 0; $i < $n; $i++) {
        if ($str[$i] == '1') {
            $tmp++;
            $excises[] = $i;
        }
    }
    return $tmp == $k ? $excises : false;
}

function totalCalories($excises, $calories)
{
    $calorie = 0;
    foreach ($excises as $j) {
        $calorie += (int)$calories[$j];
    }
    return $calorie;
}

for ($i = 0; $i < 1 << $n; $i++) {
    $str = str_pad(decbin($i), $n, "0", STR_PAD_LEFT);
    if ($excises = isK($str, $k)) {
        $calorie = totalCalories($excises, $calories);
        if ($calorie == $T) {
            $ans++;
        }
    }
}
echo $ans;
if ($in !== STDIN) {
    fclose($in);
}
