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
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_message_id'])) {
    $delete_message_id = $_POST['delete_message_id'];
    $stmt = $conn->prepare("DELETE FROM messages WHERE id = :id AND user_id = :user_id");
    $stmt->bindParam(':id', $delete_message_id);
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
}

// 处理回复删除
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_reply_id'])) {
    $delete_reply_id = $_POST['delete_reply_id'];
    $stmt = $conn->prepare("DELETE FROM replies WHERE id = :id AND user_id = :user_id");
    $stmt->bindParam(':id', $delete_reply_id);
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
}

// 处理回复发送
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reply']) && isset($_POST['message_id'])) {
    $user_id = $_SESSION['user_id'];
    $message_id = $_POST['message_id'];
    $reply = $_POST['reply'];

    $stmt = $conn->prepare("INSERT INTO replies (message_id, user_id, reply) VALUES (:message_id, :user_id, :reply)");
    $stmt->bindParam(':message_id', $message_id);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':reply', $reply);

    if ($stmt->execute()) {
        echo "<script type='text/javascript'>alert('回复已发送。');</script>";
    } else {
        echo "<script type='text/javascript'>alert('回复发送失败，请重试。');</script>";
    }
}

// 从数据库获取所有审核通过的消息
$stmt = $conn->prepare("
    SELECT m.id, m.message, m.status, u.username, u.user_type 
    FROM messages m
    JOIN users u ON m.user_id = u.id
    WHERE m.status = 'approved'
    ORDER BY m.created_at DESC
");
$stmt->execute();
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 获取所有回复
$replyStmt = $conn->prepare("
    SELECT r.id, r.message_id, r.reply, r.created_at, u.username 
    FROM replies r
    JOIN users u ON r.user_id = u.id
");
$replyStmt->execute();
$replies = $replyStmt->fetchAll(PDO::FETCH_ASSOC);

// Organize replies by message_id
$repliesByMessage = [];
foreach ($replies as $reply) {
    $repliesByMessage[$reply['message_id']][] = $reply;
}

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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
                    echo "<div class='mb-3 p-3 border rounded'>";
                    echo "<div class='d-flex justify-content-between align-items-center'>";
                    echo "<div>";
                    echo "<p><span class='h5'>用户: {$message['username']}</span><br>消息: {$message['message']}</p>";
                    echo "</div>";
                    echo "<div class='text-right'>";
                    echo "<p><strong>状态:</strong> " . translateStatus($message['status']) . "</p>";
                    echo "<button class='btn btn-secondary btn-sm' data-bs-toggle='modal' data-bs-target='#replyModal' data-message-id='{$message['id']}'>回复</button>";
                    if ($message['username'] === $_SESSION['username']) {
                        echo "<form method='post' action='user_messages.php' class='d-inline'>
                            <input type='hidden' name='delete_message_id' value='{$message['id']}'>
                            <input type='submit' value='删除' class='btn btn-danger btn-sm'>
                          </form>";
                    }
                    echo "</div>";
                    echo "</div>";

                    // Display replies
                    if (isset($repliesByMessage[$message['id']])) {
                        echo "<div class='mt-3'>";
                        foreach ($repliesByMessage[$message['id']] as $reply) {
                            echo "<div class='border rounded p-2 mb-2 d-flex justify-content-between'>";
                            echo "<p><strong>{$reply['username']}:</strong> {$reply['reply']}</p>";
                            if ($reply['username'] === $_SESSION['username']) {
                                echo "<form method='post' action='user_messages.php' class='d-inline'>
                                    <input type='hidden' name='delete_reply_id' value='{$reply['id']}'>
                                    <input type='submit' value='删除' class='btn btn-danger btn-sm'>
                                  </form>";
                            }
                            echo "</div>";
                        }
                        echo "</div>";
                    }

                    echo "</div>";
                }
            } else {
                echo "<p>没有消息。</p>";
            }
            ?>
        </div>
    </div>
</div>

<!-- Modal for reply -->
<div class="modal fade" id="replyModal" tabindex="-1" aria-labelledby="replyModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="user_messages.php">
                <div class="modal-header">
                    <h5 class="modal-title" id="replyModalLabel">回复消息</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <textarea name="reply" class="form-control" rows="3" required placeholder="输入您的回复..."></textarea>
                        <input type="hidden" name="message_id" id="modalMessageId">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">关闭</button>
                    <input type="submit" class="btn btn-primary" value="发送回复">
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    var replyModal = document.getElementById('replyModal');
    replyModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var messageId = button.getAttribute('data-message-id');
        var modalMessageIdInput = replyModal.querySelector('#modalMessageId');
        modalMessageIdInput.value = messageId;
    });
</script>

</body>
</html>
