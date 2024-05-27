<?php
session_start();

if (!isset($_SESSION['email']) || $_SESSION['user_type'] !== 'user') {
    header("Location: login.php");
    exit;
}

// 数据库连接
include 'db.php';

// 处理信息发送
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['message'])) {
    $user_id = $_SESSION['user_id'];
    $message = $_POST['message'];

    $stmt = $conn->prepare("INSERT INTO messages (user_id, message, status) VALUES (:user_id, :message, 'pending')");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':message', $message);

    if ($stmt->execute()) {
        echo "<script type='text/javascript'>alert('信息已发送，等待审核。');</script>";
    } else {
        echo "<script type='text/javascript'>alert('发送失败，请重试。');</script>";
    }
}

// 处理信息删除
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];
    $stmt = $conn->prepare("DELETE FROM messages WHERE id = :id AND user_id = :user_id");
    $stmt->bindParam(':id', $delete_id);
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
}

// 从数据库获取用户和管理员发送的消息
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("
    SELECT m.id, m.message, m.status, u.username, u.user_type 
    FROM messages m
    JOIN users u ON m.user_id = u.id
    WHERE m.user_id = :user_id OR u.user_type = 'admin'
    ORDER BY m.created_at DESC
");
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

function translateStatus($status) {
    switch ($status) {
        case 'pending':
            return '待审核';
        case 'approved':
            return '已通过';
        case 'rejected':
            return '已拒绝';
        default:
            return $status;
    }
}

function translateUserType($user_type) {
    switch ($user_type) {
        case 'user':
            return '普通用户';
        case 'admin':
            return '管理员';
        default:
            return $user_type;
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>用户消息</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8 mt-3">
            <div class="d-flex justify-content-between">
                <h2>用户消息</h2>
                <button onclick="location.href='logout.php'" class="btn btn-danger">注销</button>
            </div>
            <div class="mt-4">
                <form method="post" action="user_messages.php">
                    <div class="form-group">
                        <textarea name="message" class="form-control" rows="3" required placeholder="输入您的信息..."></textarea>
                    </div>
                    <div class="form-group mt-2">
                        <input type="submit" class="btn btn-primary" value="发送">
                    </div>
                </form>
            </div>
            <h3 class="mt-4">已发送的消息</h3>
            <?php
            if ($messages) {
                foreach ($messages as $message) {
                    echo "<div class='d-flex justify-content-between align-items-center mb-3 p-3 border rounded'>";
                    echo "<div>";
                    echo "<p><span class='h5'>用户: {$message['username']}</span><br>消息: {$message['message']}</p>";
                    echo "</div>";
                    echo "<div class='text-right'>";
                    echo "<p><strong>状态:</strong> " . translateStatus($message['status']) . "</p>";
                    if ($message['username'] === $_SESSION['username']) {
                        echo "<form method='post' action='user_messages.php' class='d-inline'>
                    <input type='hidden' name='delete_id' value='{$message['id']}'>
                    <input type='submit' value='删除' class='btn btn-secondary btn-sm'>
                  </form>";
                    }
                    echo "</div>";
                    echo "</div>";
                }
            } else {
                echo "<p>没有消息。</p>";
            }
            ?>
        </div>
    </div>
</div>
</body>
</html>
