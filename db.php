<?php
// 数据库连接配置
$servername = "localhost";
$username = "webuser";
$password = "0000";
$dbname = "DynamicSite";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    // 设置 PDO 错误模式为异常
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // 确保使用 UTF-8 字符集
    $conn->exec("SET NAMES 'utf8'");
} catch (PDOException $e) {
    echo "连接失败: " . $e->getMessage();
    die();
}
?>
