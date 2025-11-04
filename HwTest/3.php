<?php

declare(strict_types=1);
function buildFreeNis(array $ni, int $n): array
{
    $levels = [];
    $sizes = [];
    for ($i = 0; $i <= $n; ++$i) {
        $cnt = $ni[$i] ?? 0;
        if ($cnt > 0) {
            $size = 1 << $i;
            $levels[$size] = $cnt;
            $sizes[] = $size;
        }
    }
    sort($sizes); // 升序
    return [$levels, $sizes];
}

/** 找到当前最大有货尺寸（从大到小） */
function maxSize(array $stock, array $sizesDesc): int
{
    foreach ($sizesDesc as $s) {
        if (($stock[$s] ?? 0) > 0) {
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

/**
 * 判定是否可为 k 个用户各提供至少 d 带宽
 *   1) 找最小覆盖 s >= need，用 1 块结束该用户
 *   2) 取当前最大尺寸 s_max 做整份匹配：t = min(floor(need/s_max), 库存),把 need 压一次，回到 1)
 *    重复直到 need<=0 或库存耗尽
 */
function canServeUsers(int $k, array $freeNis, array $sizesAsc, int $d): bool
{
    $sizesDesc = array_reverse($sizesAsc);
    for ($user = 0; $user < $k; ++$user) {
        $need = $d;
        while ($need > 0) {
            // 1) 最小覆盖（>= need）
            $cover = minCover($freeNis, $sizesAsc, $need);
            if ($cover > 0) {
                --$freeNis[$cover];
                break;
            }
            // 2) 用最大块压缩 need
            $maxSize = maxSize($freeNis, $sizesDesc);
            if ($maxSize === 0) {
                return false;
            }
            $q = $freeNis[$maxSize];
            $t = intdiv($need, $maxSize);
            if ($t > $q) {
                $t = $q;
            }
            $freeNis[$maxSize] -= $t;
            $need -= $t * $maxSize;
        }
    }
    return true;
}

// ------------------ 输入（文件流优先，失败回落 STDIN） ------------------
$in = @fopen('in.txt', 'rb');
if ($in === false) {
    $in = STDIN;
}
while ($n = fgets($in)) {
    $n = (int) trim($n);
    $line = trim(fgets($in));
    $Ni = array_map('intval', preg_split('/\s+/', $line));
    $D = (int) trim(fgets($in));

    [$freeNis, $sizesAsc] = buildFreeNis($Ni, $n);

    // 统计“块总数”和“带宽总和”
    $totalBlocks = 0;
    $totalBandwidth = 0;
    foreach ($freeNis as $size => $qty) {
        $totalBlocks += $qty;
        $totalBandwidth += $size * $qty;
    }

    if ($D <= 0) {
        echo $totalBlocks, "\n";
        if ($in !== STDIN) {
            fclose($in);
        }
        continue;
    }

    $maxPossibleUsers = min($totalBlocks, intdiv($totalBandwidth, $D));
    // 二分最大可服务用户数（上中位收敛）
    $lo = 0;
    $hi = $maxPossibleUsers;
    while ($lo < $hi) {
        $mid = intdiv($lo + $hi + 1, 2);
        if (canServeUsers($mid, $freeNis, $sizesAsc, $D)) {
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
