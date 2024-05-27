<?php
session_start();

if (!isset($_SESSION['email']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit;
}

include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['message'])) {
    $user_id = $_SESSION['user_id'];
    $message = $_POST['message'];

    $stmt = $conn->prepare("INSERT INTO messages (user_id, message, status) VALUES (:user_id, :message, 'approved')");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':message', $message);

    if ($stmt->execute()) {
        echo "<script>alert('信息已发送。');</script>";
    } else {
        echo "<script>alert('发送失败，请重试。');</script>";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];
    $stmt = $conn->prepare("DELETE FROM messages WHERE id = :id");
    $stmt->bindParam(':id', $delete_id);
    $stmt->execute();
}

$stmt = $conn->prepare("SELECT m.id, m.message, m.status, u.username FROM messages m JOIN users u ON m.user_id = u.id ORDER BY m.created_at DESC");
$stmt->execute();
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>管理员消息</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex justify-content-between">
                <h2>管理员消息</h2>
                <button onclick="location.href='index.php'" class="btn btn-primary">返回主页</button>
            </div>
            <div class="mt-4">
                <form method="post" action="admin_messages.php">
                    <div class="form-group">
                        <textarea name="message" class="form-control" rows="3" required placeholder="输入您的信息..."></textarea>
                    </div>
                    <div class="form-group mt-2">
                        <button type="submit" class="btn btn-primary">发送</button>
                    </div>
                </form>
            </div>
            <h3 class="mt-4">已发送的消息</h3>
            <?php
            if ($messages) {
                foreach ($messages as $message) {
                    echo "<div class='d-flex justify-content-between align-items-center mb-3 p-3 border rounded'>";
                    echo "<div>";
                    echo "<p><span class='h5'>{$message['username']}</span><br>{$message['message']}</p>";
                    echo "</div>";
                    echo "<div class='text-right'>";
                    echo "<p class='text-muted'><strong>状态:</strong> " . ($message['status'] == 'pending' ? '待审核' : ($message['status'] == 'approved' ? '已通过' : '已拒绝')) . "</p>";
                    echo "<form method='post' action='admin_messages.php' class='d-inline'>
                            <input type='hidden' name='delete_id' value='{$message['id']}'>
                            <button type='submit' class='btn btn-secondary btn-sm'>删除</button>
                          </form>";
                    echo "</div>";
                    echo "</div>";
                }
            } else {
                echo "<p class='text-muted'>没有消息。</p>";
            }
            ?>
        </div>
    </div>
</div>
</body>
</html>
