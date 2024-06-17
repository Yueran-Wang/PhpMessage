<?php
session_start();

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}

$user_type = $_SESSION['user_type'];
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>主页</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h1 class="mt-5">欢迎, <?php echo $_SESSION['username']; ?>!</h1>

            <?php if ($user_type == 'user'): ?>
                <a href="user_messages.php" class="btn btn-primary mt-3">查看消息</a>
            <?php elseif ($user_type == 'admin'): ?>
                <a href="admin_messages.php" class="btn btn-primary mt-3">管理员消息</a>
                <a href="review_messages.php" class="btn btn-secondary mt-3">审核消息</a>
            <?php endif; ?>

            <a href="profile.php" class="btn btn-success mt-3">我的</a>
            <a href="logout.php" class="btn btn-danger mt-3">注销</a>
        </div>
    </div>
</div>

</body>
</html>
