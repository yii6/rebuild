<?php
mb_internal_encoding("UTF-8");
$in = @fopen("in.txt", "r");
if ($in === false) {
    $in = STDIN;
}

// 读第一串
$line = '';
while (!feof($in)) {
    $line = trim(fgets($in));
    if ($line !== '') break;
}
$A = $line;

// 读第二串
$line = '';
while (!feof($in)) {
    $line = trim(fgets($in));
    if ($line !== '') break;
}
$B = $line;

// 读后续相似行
$similarLines = [];
while (!feof($in)) {
    $l = trim(fgets($in));
    if ($l === '') continue;
    $similarLines[] = $l;
}
if ($in !== STDIN) {
    fclose($in);
}

/********************
 * 2. 并查集实现 (Union-Find)
 ********************/
$parent = []; // string -> string (its parent rep)

// make/find/union
function uf_make($x, &$parent) {
    if (!isset($parent[$x])) {
        $parent[$x] = $x;
    }
}
function uf_find($x, &$parent) {
    if (!isset($parent[$x])) {
        return null; // not in UF at all
    }
    if ($parent[$x] !== $x) {
        $parent[$x] = uf_find($parent[$x], $parent);
    }
    return $parent[$x];
}
function uf_union($a, $b, &$parent) {
    uf_make($a, $parent);
    uf_make($b, $parent);
    $ra = uf_find($a, $parent);
    $rb = uf_find($b, $parent);
    if ($ra !== $rb) {
        // 简单按rb挂ra
        $parent[$rb] = $ra;
    }
}

// 存放噪音模式
$noisePatterns = []; // e.g. ["(***)", ...]

// 解析相似规则行
foreach ($similarLines as $line) {
    // 拆成词（按空白分开）
    $parts = preg_split('/\s+/', $line);
    if (!$parts || count($parts) === 0) continue;

    $hasNoise = false;
    foreach ($parts as $tok) {
        if (strpos($tok, '***') !== false) {
            $hasNoise = true;
        }
    }

    if ($hasNoise) {
        // 这一行是噪音模式行（通常就一个，比如 "(***)")
        // 允许多个的话也收集多个
        foreach ($parts as $tok) {
            if ($tok === '') continue;
            if (strpos($tok, '***') !== false) {
                $noisePatterns[] = $tok;
            }
        }
    } else {
        // 普通相似词，把它们union到同一类
        $first = $parts[0];
        uf_make($first, $parent);
        for ($i = 1; $i < count($parts); $i++) {
            uf_union($first, $parts[$i], $parent);
        }
    }
}

// 去重噪音模式
$noisePatterns = array_values(array_unique($noisePatterns));

/**
 * 辅助：判断两个片段是否等价
 * 规则：
 *   - 完全相同 直接OK
 *   - 否则，如果两个都在并查集中且同一个代表元 也OK
 */
function is_equivalent($s1, $s2, &$parent) {
    if ($s1 === $s2) return true;
    $r1 = uf_find($s1, $parent);
    $r2 = uf_find($s2, $parent);
    if ($r1 !== null && $r2 !== null && $r1 === $r2) {
        return true;
    }
    return false;
}

/********************
 * 3. 构建“候选词库”和“前缀匹配列表”
 *
 * 为什么？
 * 我们需要在字符串A的某个位置i，尝试匹配一段词 x；
 * 在字符串B的某个位置j，尝试匹配一段词 y；
 * 如果 x ~ y，就可以一起前进。
 *
 * 候选词来源：
 *   - 所有出现在相似行里的非噪音词（可能是多字符，比如"论语"、"三字经"、"de"、"的"）
 *   - 每个字符串的每个单字本身（保证至少可以逐字比）
 *
 * 我们会对 A、B 分别预处理：prefixTokensA[i] = 从i开头能取哪些词
 * 同理 prefixTokensB[j]
 ********************/

function mb_strlen_u($s) { return mb_strlen($s, 'UTF-8'); }
function mb_substr_u($s, $start, $len = null) {
    if ($len === null) {
        return mb_substr($s, $start, null, 'UTF-8');
    }
    return mb_substr($s, $start, $len, 'UTF-8');
}

// 收集所有非噪音的词(并查集里出现过的key)
$allTokensSet = [];
foreach ($parent as $tok => $_) {
    $allTokensSet[$tok] = true;
}

// 再加上 A、B 中的所有单字符，保证逐字可拆
$lenA = mb_strlen_u($A);
$lenB = mb_strlen_u($B);
for ($i = 0; $i < $lenA; $i++) {
    $ch = mb_substr_u($A, $i, 1);
    $allTokensSet[$ch] = true;
}
for ($j = 0; $j < $lenB; $j++) {
    $ch = mb_substr_u($B, $j, 1);
    $allTokensSet[$ch] = true;
}

// 变成数组
$allTokens = array_keys($allTokensSet);

// 为了匹配更贴近语义，我们希望优先匹配更长的词。
// 我们按长度降序排序
usort($allTokens, function($a, $b) {
    $la = mb_strlen($a, 'UTF-8');
    $lb = mb_strlen($b, 'UTF-8');
    if ($la === $lb) return 0;
    return ($la > $lb) ? -1 : 1; // 长的在前
});

// 预处理：对某个字符串 S，构造 prefixTokens[pos] = [ [tok=>..., len=>...], ... ]
function buildPrefixTokens($S, $allTokens) {
    $L = mb_strlen($S, 'UTF-8');
    $prefixTokens = array_fill(0, $L+1, []); // 最后位置L也留空数组

    foreach ($allTokens as $tok) {
        $tLen = mb_strlen($tok, 'UTF-8');
        if ($tLen === 0) continue;
        // 尝试放到所有可能起点
        for ($i = 0; $i + $tLen <= $L; $i++) {
            $substr = mb_substr($S, $i, $tLen, 'UTF-8');
            if ($substr === $tok) {
                // 避免重复加入同一个tok
                $prefixTokens[$i][] = ['tok'=>$tok, 'len'=>$tLen];
            }
        }
    }

    // 对每个位置，把列表按len降序（已经基本按全局顺序是降序，但做一下去重）
    for ($i = 0; $i <= $L; $i++) {
        // 去重based on tok+len
        $seen = [];
        $tmp = [];
        foreach ($prefixTokens[$i] as $item) {
            $key = $item['tok'].'#'.$item['len'];
            if (!isset($seen[$key])) {
                $seen[$key] = true;
                $tmp[] = $item;
            }
        }
        // 再次按长度降序
        usort($tmp, function($a,$b){
            if ($a['len'] === $b['len']) return 0;
            return ($a['len'] > $b['len']) ? -1 : 1;
        });
        $prefixTokens[$i] = $tmp;
    }

    return $prefixTokens;
}

$prefixA = buildPrefixTokens($A, $allTokens);
$prefixB = buildPrefixTokens($B, $allTokens);

/********************
 * 4. 噪音模式 -> 正则
 *   模式里 "***" 代表 ".*" (任意长度, 可为空)
 *   其他字符当字面量
 *   比如 "(***)" -> ^\(.*\)$
 ********************/

function noisePatternToRegex($pat) {
    // 我们把模式按 '***' 切开，每段进行 preg_quote
    $parts = explode('***', $pat);
    $regex = '/^';
    $cnt = count($parts);
    for ($i=0; $i<$cnt; $i++) {
        $regex .= preg_quote($parts[$i], '/');
        if ($i < $cnt-1) {
            $regex .= '.*';
        }
    }
    $regex .= '/u'; // unicode
    return $regex;
}

// 预编译所有噪音模式
$noiseRegs = []; // 每项: ['pat'=>原始模式, 're'=>编译后的regex]
foreach ($noisePatterns as $p) {
    $noiseRegs[] = [
        'pat' => $p,
        're'  => noisePatternToRegex($p),
    ];
}

// 给定字符串S和起点pos，尝试匹配噪音段；返回所有可行的(新位置endPos, 模式pat)
function noiseConsumeAt($S, $pos, $noiseRegs) {
    $res = [];
    $sub = mb_substr_u($S, $pos); // 从pos到结尾
    foreach ($noiseRegs as $rule) {
        if (preg_match($rule['re'], $sub, $m)) {
            // m[0]是匹配到的整段（可能是空、也可能很长）
            $consumedChars = mb_strlen_u($m[0]);
            $res[] = [
                'end' => $pos + $consumedChars,
                'pat' => $rule['pat'],
            ];
        }
    }
    return $res;
}

/********************
 * 5. 相似性判定 (布尔)
 *    dfsSimilar(i,j):  是否能让A[i:]和B[j:]对齐掉
 ********************/
$memoSim = []; // "i,j" => true/false

function dfsSimilar($i, $j, $A, $B, $prefixA, $prefixB, $noiseRegs, &$parent, &$memoSim) {
    $key = $i.','.$j;
    if (isset($memoSim[$key])) {
        return $memoSim[$key];
    }

    $lenA = mb_strlen_u($A);
    $lenB = mb_strlen_u($B);

    // 终止条件：都到结尾
    if ($i === $lenA && $j === $lenB) {
        $memoSim[$key] = true;
        return true;
    }

    // 尝试普通等价匹配（前提：都还有剩余）
    if ($i < $lenA && $j < $lenB) {
        foreach ($prefixA[$i] as $candA) {
            foreach ($prefixB[$j] as $candB) {
                if (is_equivalent($candA['tok'], $candB['tok'], $parent)) {
                    $ni = $i + $candA['len'];
                    $nj = $j + $candB['len'];
                    if (dfsSimilar($ni, $nj, $A, $B, $prefixA, $prefixB, $noiseRegs, $parent, $memoSim)) {
                        $memoSim[$key] = true;
                        return true;
                    }
                }
            }
        }
    }

    // 尝试A消费一段噪音（B不动）
    if ($i < $lenA && !empty($noiseRegs)) {
        $optsA = noiseConsumeAt($A, $i, $noiseRegs);
        foreach ($optsA as $opt) {
            $ni = $opt['end'];
            if ($ni > $i) { // 必须前进，避免死循环
                if (dfsSimilar($ni, $j, $A, $B, $prefixA, $prefixB, $noiseRegs, $parent, $memoSim)) {
                    $memoSim[$key] = true;
                    return true;
                }
            } else {
                // 如果噪音能匹配空串，那么我们也能"删掉空串"
                // 这个其实不推进i会死循环，所以我们只允许真正消耗字符的情况
            }
        }
    }

    // 尝试B消费一段噪音（A不动）
    if ($j < $lenB && !empty($noiseRegs)) {
        $optsB = noiseConsumeAt($B, $j, $noiseRegs);
        foreach ($optsB as $opt) {
            $nj = $opt['end'];
            if ($nj > $j) {
                if (dfsSimilar($i, $nj, $A, $B, $prefixA, $prefixB, $noiseRegs, $parent, $memoSim)) {
                    $memoSim[$key] = true;
                    return true;
                }
            }
        }
    }

    $memoSim[$key] = false;
    return false;
}

/********************
 * 6. 如果相似，还要收集差异信息
 *    dfsCollect(i,j): 返回一个数组，列出差异片段
 *    - 等价但字符串不同："片段A 片段B"
 *    - 噪音段："(***)" 这样的模式
 *    如果无解，返回null
 ********************/
$memoCollect = []; // "i,j" => null | array of diffs

function dfsCollect($i, $j, $A, $B, $prefixA, $prefixB, $noiseRegs, &$parent, &$memoCollect) {
    $key = $i.','.$j;
    if (array_key_exists($key, $memoCollect)) {
        return $memoCollect[$key]; // could be null or array
    }

    $lenA = mb_strlen_u($A);
    $lenB = mb_strlen_u($B);

    // 成功到达结尾
    if ($i === $lenA && $j === $lenB) {
        $memoCollect[$key] = [];
        return [];
    }

    // 1. 尝试等价匹配
    if ($i < $lenA && $j < $lenB) {
        // 优先尝试"完全相同"的片段，确保不必要的差异不会被记录
        foreach ($prefixA[$i] as $candA) {
            foreach ($prefixB[$j] as $candB) {
                if ($candA['tok'] === $candB['tok']) {
                    $ni = $i + $candA['len'];
                    $nj = $j + $candB['len'];
                    $rest = dfsCollect($ni, $nj, $A, $B, $prefixA, $prefixB, $noiseRegs, $parent, $memoCollect);
                    if ($rest !== null) {
                        $memoCollect[$key] = $rest;
                        return $rest;
                    }
                }
            }
        }
        // 再尝试"等价但不同字符串"的片段
        foreach ($prefixA[$i] as $candA) {
            foreach ($prefixB[$j] as $candB) {
                if ($candA['tok'] !== $candB['tok'] &&
                    is_equivalent($candA['tok'], $candB['tok'], $parent))
                {
                    $ni = $i + $candA['len'];
                    $nj = $j + $candB['len'];
                    $rest = dfsCollect($ni, $nj, $A, $B, $prefixA, $prefixB, $noiseRegs, $parent, $memoCollect);
                    if ($rest !== null) {
                        // 记录差异 (candA tok vs candB tok)
                        $pair = $candA['tok'].' '.$candB['tok'];
                        $rest2 = $rest;
                        $rest2[] = $pair;
                        $memoCollect[$key] = $rest2;
                        return $rest2;
                    }
                }
            }
        }
    }

    // 2. A 吃噪音 (B不动)
    if ($i < $lenA && !empty($noiseRegs)) {
        $optsA = noiseConsumeAt($A, $i, $noiseRegs);
        foreach ($optsA as $opt) {
            $ni = $opt['end'];
            if ($ni > $i) {
                $rest = dfsCollect($ni, $j, $A, $B, $prefixA, $prefixB, $noiseRegs, $parent, $memoCollect);
                if ($rest !== null) {
                    $rest2 = $rest;
                    $rest2[] = $opt['pat']; // 记录噪音模式
                    $memoCollect[$key] = $rest2;
                    return $rest2;
                }
            }
        }
    }

    // 3. B 吃噪音 (A不动)
    if ($j < $lenB && !empty($noiseRegs)) {
        $optsB = noiseConsumeAt($B, $j, $noiseRegs);
        foreach ($optsB as $opt) {
            $nj = $opt['end'];
            if ($nj > $j) {
                $rest = dfsCollect($i, $nj, $A, $B, $prefixA, $prefixB, $noiseRegs, $parent, $memoCollect);
                if ($rest !== null) {
                    $rest2 = $rest;
                    $rest2[] = $opt['pat'];
                    $memoCollect[$key] = $rest2;
                    return $rest2;
                }
            }
        }
    }

    $memoCollect[$key] = null;
    return null;
}

/********************
 * 7. 主逻辑：判断相似性
 ********************/
$isSim = dfsSimilar(0, 0, $A, $B, $prefixA, $prefixB, $noiseRegs, $parent, $memoSim);

if ($isSim) {
    // 收集差异
    $diffList = dfsCollect(0, 0, $A, $B, $prefixA, $prefixB, $noiseRegs, $parent, $memoCollect);
    if ($diffList === null) {
        $diffList = [];
    }

    // 去重并保持顺序（最先出现的留着）
    $seen = [];
    $uniq = [];
    foreach ($diffList as $d) {
        if (!isset($seen[$d])) {
            $seen[$d] = true;
            $uniq[] = $d;
        }
    }

    // 输出
    echo "True", PHP_EOL;
    foreach ($uniq as $d) {
        echo $d, PHP_EOL;
    }
} else {
    // 不相似时，输出第一个分歧点之后的尾巴
    // 按字面字符对比（不考虑相似映射），和题目示例保持一致
    $lenMin = min($lenA, $lenB);
    $p = 0;
    while ($p < $lenMin) {
        $ca = mb_substr_u($A, $p, 1);
        $cb = mb_substr_u($B, $p, 1);
        if ($ca !== $cb) {
            break;
        }
        $p++;
    }

    $tailA = mb_substr_u($A, $p);
    $tailB = mb_substr_u($B, $p);

    echo "False", PHP_EOL;
    echo $tailA, " ", $tailB, PHP_EOL;
}
