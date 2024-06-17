<?php
include 'db.php';

$error_message = '';
$success_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $user_type = $_POST['user_type'];
    $admin_password = $_POST['admin_password'];

    // 检查两次输入的密码是否一致
    if ($password !== $confirm_password) {
        $error_message = "两次输入的密码不一致。";
    } else {
        // 检查邮箱是否已经被注册过
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $email_count = $stmt->fetchColumn();

        // 检查用户名是否已经被注册过
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $username_count = $stmt->fetchColumn();

        if ($email_count > 0) {
            $error_message = "该邮箱已经被注册过。";
        } elseif ($username_count > 0) {
            $error_message = "该用户名已经被注册过。";
        } else {
            // 如果用户类型是管理员，则检查管理员密码
            if ($user_type == 'admin' && $admin_password !== 'admin_secret_password') {
                $error_message = "管理员密码错误。";
            } else {
                // 插入新用户
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (username, email, password, user_type) VALUES (:username, :email, :password, :user_type)");
                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':password', $hashed_password);
                $stmt->bindParam(':user_type', $user_type);

                if ($stmt->execute()) {
                    $success_message = "注册成功！";
                } else {
                    $error_message = "错误: " . $stmt->errorInfo()[2];
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>注册</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script type="text/javascript">
        function showMessage(message) {
            alert(message);
        }
    </script>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h2 class="mt-5">注册</h2>
            <?php
            if (!empty($error_message)) {
                echo "<script type='text/javascript'>showMessage('$error_message');</script>";
            }
            if (!empty($success_message)) {
                echo "<script type='text/javascript'>showMessage('$success_message');</script>";
            }
            ?>
            <form method="post" action="register.php">
                <div class="mb-3">
                    <label for="username" class="form-label">用户名</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">邮箱</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">密码</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">确认密码</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>
                <div class="mb-3">
                    <label for="user_type" class="form-label">用户类型</label>
                    <select class="form-select" id="user_type" name="user_type" onchange="checkUserType()">
                        <option value="user">普通用户</option>
                        <option value="admin">管理员</option>
                    </select>
                </div>
                <div class="mb-3" id="admin_password_div" style="display:none;">
                    <label for="admin_password" class="form-label">管理员密码</label>
                    <input type="password" class="form-control" id="admin_password" name="admin_password">
                </div>
                <button type="submit" class="btn btn-primary">注册</button>
            </form>
            <p class="mt-3">已经有账号了？<a href="login.php">登录</a></p>
        </div>
    </div>
</div>
<script type="text/javascript">
    function checkUserType() {
        var userType = document.getElementById('user_type').value;
        var adminPasswordDiv = document.getElementById('admin_password_div');
        if (userType === 'admin') {
            adminPasswordDiv.style.display = 'block';
        } else {
            adminPasswordDiv.style.display = 'none';
        }
    }
</script>
</body>
</html>
