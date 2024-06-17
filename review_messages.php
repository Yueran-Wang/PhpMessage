<?php
session_start();

if (!isset($_SESSION['email']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// 从数据库获取待审核消息的示例代码
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['message_id']) && isset($_POST['action'])) {
    $message_id = $_POST['message_id'];
    $action = $_POST['action'];

    $new_status = $action === 'approve' ? 'approved' : 'rejected';
    $stmt = $conn->prepare("UPDATE messages SET status = :status WHERE id = :id");
    $stmt->bindParam(':status', $new_status);
    $stmt->bindParam(':id', $message_id);
    $stmt->execute();
}

$stmt = $conn->prepare("SELECT * FROM messages WHERE status = 'pending'");
$stmt->execute();
$pending_messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>审核消息</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex justify-content-between">
                <h2>审核信息</h2>
                <button onclick="location.href='index.php'" class="btn btn-primary">返回主页</button>
            </div>
            <div class="mt-4">
                <?php
                if ($pending_messages) {
                    foreach ($pending_messages as $message) {
                        echo "<div class='d-flex justify-content-between align-items-center mb-3 p-3 border rounded'>";
                        echo "<div>";
                        echo "<p>{$message['message']}</p>";
                        echo "</div>";
                        echo "<div class='text-right'>";
                        echo "<form method='post' action='review_messages.php' class='d-inline'>
                                <input type='hidden' name='message_id' value='{$message['id']}'>
                                <button type='submit' name='action' value='approve' class='btn btn-success btn-sm'>通过</button>
                                <button type='submit' name='action' value='reject' class='btn btn-danger btn-sm ms-2'>拒绝</button>
                              </form>";
                        echo "</div>";
                        echo "</div>";
                    }
                } else {
                    echo "<p class='text-muted'>没有待审核的消息。</p>";
                }
                ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>
