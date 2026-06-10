<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$apiUrl = "https://api.openai.com/v1/chat/completions";
$apiKey = "";  // openAi API 密钥

// 獲取 POST 请求的數據
$inputData = json_decode(file_get_contents("php://input"), true);

$comments = isset($inputData['comments']) ? $inputData['comments'] : [];
$wordLimit = isset($inputData['word_limit']) ? intval($inputData['word_limit']) : 150;

// 檢查評論是否為空
if (empty($comments)) {
    echo json_encode(['summary' => '尚無評論', 'comments' => []]);
    exit;
}

// 準備消息請求
$messages = [
    ['role' => 'user', 'content' => '請用大約' . $wordLimit . '字總結以下評論：' . implode(' ', $comments)]
];

// 初始化 CURL 請求
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: " . "Bearer " . $apiKey,
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    "model" => "gpt-3.5-turbo",
    "messages" => $messages,
    "max_tokens" => $wordLimit * 4, // 將令牌數量設置為字數的4倍
    "temperature" => 0.7,
]));

// 執行請求
$response = curl_exec($ch);

// 檢查 CURL 是否返回錯誤
if (curl_errno($ch)) {
    $error_msg = curl_error($ch);
    echo json_encode(['error' => 'CURL 请求错误: ' . $error_msg]);
    curl_close($ch);
    exit;
}

curl_close($ch);

// 處理響應
$responseData = json_decode($response, true);

if (isset($responseData['error'])) {
    echo json_encode(['error' => 'API 錯誤: ' . $responseData['error']['message']]);
} else {
    $summary = $responseData['choices'][0]['message']['content'] ?? '未能獲取總結';
    echo json_encode(['summary' => $summary, 'comments' => $comments]);
}
?>
