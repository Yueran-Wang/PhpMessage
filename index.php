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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
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

            <a href="logout.php" class="btn btn-danger mt-3">注销</a>
        </div>
    </div>
</div>

</body>
</html>
