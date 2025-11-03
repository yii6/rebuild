<?php

/**
 * Definition for a binary tree node.
 * class TreeNode {
 *     public $val = null;
 *     public $left = null;
 *     public $right = null;
 *     function __construct($val = 0, $left = null, $right = null) {
 *         $this->val = $val;
 *         $this->left = $left;
 *         $this->right = $right;
 *     }
 * }
 */
class Solution
{
    /**
     * @param TreeNode $root
     * @return Integer[][]
     */
    function levelOrder($root)
    {
// 如果树为空，返回空数组
        if ($root === null) {
            return [];
        }
// 初始化结果数组和队列（使用普通数组模拟队列）
        $result = [];
        $queue = [$root];  // 将根节点入队
// 遍历队列
        while (count($queue) > 0) {
            $levelSize = count($queue);  // 当前层的节点个数
            $levelValues = [];  // 当前层节点的值
// 遍历当前层的所有节点
            for ($i = 0; $i < $levelSize; $i++) {
// 出队
                $node = array_shift($queue);
// 将当前节点值添加到当前层的结果中
                $levelValues[] = $node->val;
// 如果左子节点存在，入队
                if ($node->left !== null) {
                    array_push($queue, $node->left);
                }
// 如果右子节点存在，入队
                if ($node->right !== null) {
                    array_push($queue, $node->right);
                }
            }
// 将当前层的值添加到最终结果中
            $result[] = $levelValues;
        }
        return $result;
    }
}

