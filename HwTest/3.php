<?php

declare(strict_types=1);
function buildStock(array $ni, int $n): array
{
    $stock = [];
    $sizes = [];
    for ($i = 0; $i <= $n; ++$i) {
        $cnt = $ni[$i] ?? 0;
        if ($cnt > 0) {
            // 使用 ** 避免位移在极端 32 位平台溢出
            $size = 2 ** $i;
            $stock[$size] = ($stock[$size] ?? 0) + $cnt;
            $sizes[] = $size;
        }
    }
    sort($sizes);
    $sizesDesc = array_reverse($sizes);
    return [$stock, $sizes, $sizesDesc];
}

/** 从大到小找到：<= need 的最大尺寸（存在且有货时返回，否则 0） */
function maxLE(array $stock, array $sizesDesc, int $need): int
{
    foreach ($sizesDesc as $s) {
        if ($s <= $need && ($stock[$s] ?? 0) > 0) {
            return $s;
        }
    }
    return 0;
}

/** 从小到大找到第一块 s >= need 的最小覆盖（允许等于） */
function minCover(array $stock, array $sizesAsc, int $need): int
{
    foreach ($sizesAsc as $s) {
        if (($stock[$s] ?? 0) > 0 && $s >= $need) {
            return $s;
        }
    }
    return 0;
}

/** 为单个用户分配 ≥d 带宽；按引用消耗库存 */
function serveOneUser(array &$stock, array $sizesAsc, array $sizesDesc, int $d): bool
{
    $need = $d;
    while ($need > 0) {
        $s = maxLE($stock, $sizesDesc, $need);
        if ($s > 0) {
            $q = $stock[$s];
            $t = intdiv($need, $s); // s<=need, t>=1
            if ($t > $q) {
                $t = $q;
            }
            $stock[$s] -= $t;
            $need -= $t * $s;
            continue;
        }
        $cover = minCover($stock, $sizesAsc, $need);
        if ($cover > 0) {
            --$stock[$cover];
            break;
        }
        return false;
    }
    return true;
}

/** 判定是否可为 k 个用户各提供至少 d 带宽 */
function canServeUsers(int $k, array $stock, array $sizesAsc, array $sizesDesc, int $d): bool
{
    for ($user = 0; $user < $k; ++$user) {
        if (!serveOneUser($stock, $sizesAsc, $sizesDesc, $d)
        ) {
            return false;
        }
    }
    return true;
}

// ------------------ 输入（文件流优先；3 行一组） ------------------
$in = @fopen('3.txt', 'rb');
if ($in === false) {
    $in = STDIN;
}

while (($lineN = fgets($in)) !== false) {
    $n = (int)trim($lineN);
    $lineNi = fgets($in);
    if ($lineNi === false) {
        break;
    }
    $Ni = array_map('intval', preg_split('/\s+/', trim($lineNi)));
    $lineD = fgets($in);
    if ($lineD === false) {
        break;
    }
    $D = (int)trim($lineD);
    [$stock, $sizesAsc, $sizesDesc] = buildStock($Ni, $n);
    // 块总数 & 带宽总和
    $totalBlocks = 0;
    $totalBandwidth = 0;
    foreach ($stock as $size => $qty) {
        $totalBlocks += $qty;
        $totalBandwidth += $size * $qty;
    }
    if ($D <= 0) {
        echo $totalBlocks, "\n";
        continue;
    }
    $maxPossibleUsers = min($totalBlocks, intdiv($totalBandwidth, $D));
    // 二分最大可服务用户数（上中位收敛）
    $lo = 0;
    $hi = $maxPossibleUsers;
    while ($lo < $hi) {
        $mid = intdiv($lo + $hi + 1, 2);
        if (canServeUsers($mid, $stock, $sizesAsc, $sizesDesc, $D)) {
            $lo = $mid;
        } else {
            $hi = $mid - 1;
        }
    }
    echo $lo, "\n";
}

if ($in !== STDIN) {
    fclose($in);
}
