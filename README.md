# PhpMessage

## 项目简介

PhpMessage 是一个用 PHP 编写的消息板项目，旨在提供一个简单易用的平台，用于发布和管理用户消息。该项目支持用户注册、登录、发布消息和查看消息。

## 功能特性

- 用户注册和登录
- 发布消息和查看消息
- 管理员可以审核和管理所有消息
- 使用 MySQL 数据库进行数据存储

## 安装

1. 克隆仓库到本地：
   ```bash
   git clone https://github.com/Yueran-Wang/PhpMessage.git
   ```

2. 导入数据库：
    - 使用 `db.sql` 文件在 MySQL 中创建数据库和表。

3. 配置数据库连接：
    - 在 `db.php` 文件中配置你的数据库连接信息。

4. 启动服务器：
    - 将项目放置于你的 PHP 服务器根目录下，并启动服务器。

## 文件结构

- `index.php`：主页，显示所有消息。
- `login.php`：用户登录页面。
- `register.php`：用户注册页面。
- `user_messages.php`：用户发布和查看自己的消息。
- `admin_messages.php`：管理员审核和管理消息的页面。
- `review_messages.php`：查看和审核用户消息。
- `db.php`：数据库连接文件。

## 使用说明

1. 注册新用户并登录。
2. 登录后可以发布新消息。
3. 管理员可以登录并审核所有用户的消息。

## 贡献

欢迎任何形式的贡献！如果你有任何建议或发现了 Bug，请提交一个 Issue 或创建一个 Pull Request。

## 许可证

本项目使用 MIT 许可证，详情请参阅 [LICENSE](LICENSE) 文件。