<?php

declare(strict_types=1);
function buildFreeNis(array $ni, int $n): array
{
    $levels = [];
    for ($i = 0; $i <= $n; ++$i) {
        $cnt = $ni[$i] ?? 0;
        if ($cnt > 0) {
            $levels[1 << $i] = $cnt;
        }
    }
    return $levels;
}

/**
 * 生成库存尺寸键的升序/降序数组（只做一次）.
 * @return array{0: int[], 1: int[]} [$sizesAsc, $sizesDesc]
 */
function buildOrders(array $stock): array
{
    $sizesAsc = array_keys($stock);
    sort($sizesAsc, SORT_NUMERIC);
    $sizesDesc = array_reverse($sizesAsc);
    return [$sizesAsc, $sizesDesc];
}

/** 是否还有库存 */
function hasStock(array $stock): bool
{
    foreach ($stock as $q) {
        if ($q > 0) {
            return true;
        }
    }
    return false;
}

/** 找到当前最大有货尺寸（按降序列表第一个有货的） */
function maxSize(array $stock, array $sizesDesc): ?int
{
    foreach ($sizesDesc as $s) {
        if (($stock[$s] ?? 0) > 0) {
            return $s;
        }
    }
    return null;
}

/** 从小到大找到第一块 s > need 的最小覆盖块 */
function minCover(array $stock, array $sizesAsc, int $need): ?int
{
    foreach ($sizesAsc as $s) {
        if (($stock[$s] ?? 0) > 0 && $s > $need) {
            return $s;
        }
    }
    return null;
}

/**
 * 判定是否可为 k 个用户各提供至少 d 带宽（你的“最大超配”策略）.
 *
 * 对每个用户：
 *   A) 取当前最大尺寸 s_max 做整份匹配：t = min(floor(need/s_max), 库存)
 *   B) 若 need>0（且自然有 need < s_max），从小到大找第一块 s>need 的最小覆盖，若找到则取 1 块结束该用户
 *   C) 若没有 s>need，则再取 1 块 s_max 把 need 再压一次，回到 B)
 *   重复直到 need<=0 或库存耗尽
 */
function canServeUsers(int $k, array $freeNis, int $d): bool
{
    if (empty($freeNis)) {
        return false;
    }
    if ($d <= 0) {
        $total = 0;
        foreach ($freeNis as $q) {
            $total += $q;
        }
        return $total >= $k;
    }

    // 一次性构建升/降序尺寸列表
    [$sizesAsc, $sizesDesc] = buildOrders($freeNis);

    for ($user = 0; $user < $k; ++$user) {
        $need = $d;

        while ($need > 0) {
            if (! hasStock($freeNis)) {
                return false;
            }
            // A) 用当前最大尺寸整份匹配
            $smax = maxSize($freeNis, $sizesDesc);
            if ($smax === null) {
                return false;
            } // 无货
            if ($need >= $smax) {
                $q = $freeNis[$smax];
                $t = intdiv($need, $smax);
                if ($t > $q) {
                    $t = $q;
                }
                if ($t > 0) {
                    $freeNis[$smax] -= $t;
                    $need -= $t * $smax;
                    if ($need <= 0) {
                        break;
                    } // 该用户满足
                }
            }

            // B) 最小覆盖：找最小 s>need
            $cover = minCover($freeNis, $sizesAsc, $need);
            if ($cover !== null) {
                --$freeNis[$cover];
                break;
            }

            // C) 没有 s>need：按你的策略，再用最大尺寸压一口，再回到 B)
            $smax = maxSize($freeNis, $sizesDesc);
            if ($smax === null || $freeNis[$smax] <= 0) {
                return false;
            }
            --$freeNis[$smax];
            $need -= $smax;         // 继续循环，下一轮再尝试“最小覆盖”
        }
    }

    return true;
}

// ------------------ 输入（文件流优先，失败回落 STDIN） ------------------
$in = @fopen('in.txt', 'rb');
if ($in === false) {
    $in = STDIN;
}

$n = (int) trim(fgets($in));
$line = trim(fgets($in));
$Ni = array_map('intval', preg_split('/\s+/', $line));
$D = (int) trim(fgets($in));

$freeNis = buildFreeNis($Ni, $n);

// 统计“块总数”和“带宽总和”
$totalBlocks = 0;
$totalBandwidth = 0;
foreach ($freeNis as $size => $qty) {
    $totalBlocks += $qty;
    $totalBandwidth += $size * $qty;
}

// D<=0：每块至少能“服务”一个用户（超配与否都不影响），上界即为总块数
if ($D <= 0) {
    echo $totalBlocks, "\n";
    if ($in !== STDIN) {
        fclose($in);
    }
    exit;
}

// 粗上界：带宽总和 / D
$maxPossibleUsers = intdiv($totalBandwidth, $D);

// 二分最大可服务用户数（上中位收敛）
$lo = 0;
$hi = $maxPossibleUsers;
while ($lo < $hi) {
    $mid = intdiv($lo + $hi + 1, 2);
    if (canServeUsers($mid, $freeNis, $D)) {
        $lo = $mid;
    } else {
        $hi = $mid - 1;
    }
}

echo $lo, "\n";

if ($in !== STDIN) {
    fclose($in);
}
