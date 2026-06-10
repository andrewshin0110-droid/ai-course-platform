<?php
// Perspective API 密鑰
define('PERSPECTIVE_API_KEY', '');

// 數據庫連接
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'course');

// 創建連接
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// 檢查連接
if ($conn->connect_error) {
    die("連接失敗: " . $conn->connect_error);
}

// 從請求中獲取數據
$course_id = isset($_POST['course_id']) ? trim($_POST['course_id']) : '';
$comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';

// 輸入驗證
if (!empty($course_id) && !empty($comment)) {
    // 調用 Perspective API 檢查評論的惡毒性
    $toxicityScore = checkToxicity($comment);
    
    // 檢查惡毒性評分
    if ($toxicityScore >= 0.1) {
        echo "警告：您的評論內容過於激進，無法提交！"; // 返回警告信息
    } else {
        // 通過檢查 插入評論至資料庫
        $stmt = $conn->prepare("INSERT INTO 課程評論 (開課序號, 評論) VALUES (?, ?)");
        if ($stmt) {
            $stmt->bind_param("ss", $course_id, $comment);
            
            if ($stmt->execute()) {
                echo "評論提交成功"; // 返回成功信息
            } else {
                echo "評論提交失敗: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "準備語句失敗: " . $conn->error;
        }
    }
} else {
    echo "無效的输入"; // 返回無效输入信息
}

// 關閉連接
$conn->close();

// 函数：調用 Perspective API 檢查評論毒性
function checkToxicity($text) {
    $url = 'https://commentanalyzer.googleapis.com/v1alpha1/comments:analyze?key=' . PERSPECTIVE_API_KEY;

    $data = [
        'comment' => ['text' => $text],
        'languages' => ['zh'],  // 設定語言為中文
        'requestedAttributes' => ['TOXICITY' => new stdClass()]
    ];

    // 初始化 cURL
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    // 發送請求並獲取響應
    $response = curl_exec($ch);
    curl_close($ch);

    // 解析 API 響應
    $result = json_decode($response, true);

    // 檢查並返回評論惡毒性評分
    if (isset($result['attributeScores']['TOXICITY']['summaryScore']['value'])) {
        return $result['attributeScores']['TOXICITY']['summaryScore']['value'];
    } else {
        return 0; // 如果沒有返回毒性評分，默認返回 0
    }
}
?>


