<?php
session_start(); // 启动会话

$servername = "localhost";
$dbUsername = "root"; // 数据库用户名
$dbPassword = ""; // 数据库密码
$dbname = "course"; // 数据库名称

// 建立資料庫連接
$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbname);

// 檢查連接是否成功
if ($conn->connect_error) {
    die("連接資料庫失敗: " . $conn->connect_error);
}

// 確認表單資料已提交且相關字段存在
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['username']) && isset($_POST['password'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $conn->real_escape_string($_POST['password']); // 获取并清理密码输入

    // 使用準備好的語句防止SQL注入
    $stmt = $conn->prepare("SELECT * FROM `學生` WHERE `電子郵件` = ? AND `密碼` = ?");
    $stmt->bind_param("ss", $username, $password); // 绑定用户名和密码参数
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        // 获取用户数据
        $user = $result->fetch_assoc();

        // 将用户数据存储在会话中
        $_SESSION['user'] = $user; // 使用 'user' 键名来存储会话中的用户数据

        // 重定向到 home.php
        header("Location: Home.php");
        exit();
    } else {
        echo "登入失敗：帳號或密碼錯誤";
        echo '<button onclick="window.location.href=\'/website/Home.php\'">重新登入</button>';
    }

    $stmt->close();
} else {
    echo "請填寫用戶名和密碼";
}

$conn->close();
?>

