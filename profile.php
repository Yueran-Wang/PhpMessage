<?php
session_start();

if (!isset($_SESSION['email']) || $_SESSION['user_type'] !== 'user') {
    header("Location: login.php");
    exit;
}

// 数据库连接
include 'db.php';

// 获取当前用户信息
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username, email FROM users WHERE id = :id");
$stmt->bindParam(':id', $user_id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // 验证当前密码
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = :id");
    $stmt->bindParam(':id', $user_id);
    $stmt->execute();
    $user_info = $stmt->fetch(PDO::FETCH_ASSOC);

    if (password_verify($current_password, $user_info['password'])) {
        if ($new_password === $confirm_password) {
            // 更新用户名和密码
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("UPDATE users SET username = :username, password = :password WHERE id = :id");
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':id', $user_id);

            if ($stmt->execute()) {
                echo "<script type='text/javascript'>alert('信息更新成功');</script>";
                // 更新会话中的用户名
                $_SESSION['username'] = $username;
            } else {
                echo "<script type='text/javascript'>alert('更新失败，请重试');</script>";
            }
        } else {
            echo "<script type='text/javascript'>alert('新密码和确认密码不匹配');</script>";
        }
    } else {
        echo "<script type='text/javascript'>alert('当前密码错误');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>个人管理</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 mt-3">
            <h2>个人管理</h2>
            <form method="post" action="profile.php">
                <div class="mb-3">
                    <label for="username" class="form-label">用户名</label>
                    <input type="text" class="form-control" id="username" name="username" value="<?php echo $user['username']; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="current_password" class="form-label">当前密码</label>
                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                </div>
                <div class="mb-3">
                    <label for="new_password" class="form-label">新密码</label>
                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                </div>
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">确认新密码</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit" class="btn btn-primary">更新信息</button>
            </form>
            <button onclick="location.href='logout.php'" class="btn btn-danger mt-3">注销</button>
            <button onclick="location.href='index.php'" class="btn btn-secondary mt-3">返回</button>
        </div>
    </div>
</div>
</body>
</html>
