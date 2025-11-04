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

/**
 * 判定是否可为 k 个用户各提供至少 d 带宽
 * 新策略：
 *   while need>0:
 *     1) 先用 “<=need 的最大块” 尽量压缩 need（整份匹配：t=min(floor(need/s),库存)）
 *     2) 若已经没有任何 <=need 的块，则找一块 “最小覆盖(>=need)” 直接结束该用户
 *     3) 两者都没有则失败.
 */
function canServeUsers(int $k, array $freeNis, array $sizesAsc, int $d): bool
{
    $sizesDesc = array_reverse($sizesAsc);
    for ($user = 0; $user < $k; ++$user) {
        if (!serveOneUser($freeNis, $sizesAsc, $sizesDesc, $d)) {
            return false;
        }
    }
    return true;
}

/** 拆分后的：为“单个用户”分配 d 带宽；会消耗库存（$stock 按引用传入） */
function serveOneUser(array &$stock, array $sizesAsc, array $sizesDesc, int $d): bool
{
    $need = $d;
    while ($need > 0) {
        // 1) 优先用 <=need 的最大块“凑近”
        $s = maxLE($stock, $sizesDesc, $need);
        if ($s > 0) {
            $q = $stock[$s];
            $t = intdiv($need, $s);         // 此时 s <= need，t >= 1
            if ($t > $q) {
                $t = $q;
            }
            $stock[$s] -= $t;
            $need -= $t * $s;
            if ($need <= 0) {
                break;
            }
            continue;                        // 继续用更小块凑
        }
        // 2) 没有 <=need 的块了，用一块最小覆盖 >=need 收尾
        $cover = minCover($stock, $sizesAsc, $need);
        if ($cover > 0) {
            --$stock[$cover];
            break;
        }
        return false;
    }
    return true;
}

// ------------------ 输入（文件流优先，失败回落 STDIN；3行一组，无空行） ------------------
$in = @fopen('in.txt', 'rb');
if ($in === false) {
    $in = STDIN;
}

while (($lineN = fgets($in)) !== false) {
    $n = (int)trim($lineN);
    $lineNi = trim(fgets($in));
    $Ni = array_map('intval', preg_split('/\s+/', $lineNi));
    $D = (int)trim(fgets($in));

    [$freeNis, $sizesAsc] = buildFreeNis($Ni, $n);

    // 块总数 & 带宽总和
    $totalBlocks = 0;
    $totalBandwidth = 0;
    foreach ($freeNis as $size => $qty) {
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
