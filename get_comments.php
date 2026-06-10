<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "course"; 

// 創建連接
$conn = new mysqli($servername, $username, $password, $dbname);

// 檢查連接
if ($conn->connect_error) {
    die("连接失败: " . $conn->connect_error);
}

// 確保 POST 請求中有课程ID
$course_id = !empty($_POST['course_id']) ? $_POST['course_id'] : null;

if ($course_id) {
    $stmt = $conn->prepare("SELECT 評論 FROM 課程評論 WHERE 開課序號 = ?");
    $stmt->bind_param("s", $course_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $comments = [];
    while ($row = $result->fetch_assoc()) {
        $comments[] = $row['評論'];
    }

    // 返回 JSON 格式的評論
    echo json_encode($comments);
}

// 關閉連接
$conn->close();


?>
