<?php
// 获取当前目录下的所有YAML文件
$files = glob("*.yaml");

foreach ($files as $file) {
    // 读取YAML文件内容
    $yamlContent = file_get_contents($file);

    // 解析YAML数据
    $data = yaml_parse($yamlContent);

    if ($data === false) {
        echo "无法解析 YAML 文件: $file\n";
        continue;
    }

    // 检查是否存在服务器条目并且"type"字段为"vless"
    if (isset($data['servers']) && is_array($data['servers'])) {
        $filteredServers = array_filter($data['servers'], function ($server) {
            return !(isset($server['type']) && $server['type'] === 'vless');
        });

        // 更新服务器数据
        $data['servers'] = array_values($filteredServers);

        // 将更新后的数据转回YAML格式
        $updatedYamlContent = yaml_emit($data);

        // 写回原文件
        file_put_contents($file, $updatedYamlContent);

        echo "已从文件 $file 中移除类型为 'vless' 的服务器。\n";
    } else {
        echo "文件 $file 中没有服务器数据。\n";
    }
}

echo "处理完成。\n";
