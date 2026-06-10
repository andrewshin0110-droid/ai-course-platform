<?php
session_start();

// 檢查是否已登入
if (!isset($_SESSION['user'])) {
    echo "使用者尚未登錄";
    exit();
}

$user = $_SESSION['user'];
$student_id = $user['學號']; // 從 session 中獲取學號
$student_major = $user['學系名稱'];

// 資料庫連接設置
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "course"; // 資料庫名稱

// 創建連接
$conn = new mysqli($servername, $username, $password, $dbname);

// 檢查連接
if ($conn->connect_error) {
    die("連接失敗: " . $conn->connect_error);
}

// 從修課資料表抓取資料
// 準備 SQL 查詢
$sql= "
  SELECT
    r.開課序號 AS 開課序號,
    c.課程名稱 AS 課程名稱,
    c.學分 AS 學分,
    r.成績 AS 成績,
    c.必選修通識 AS 必選修,
    c.領域 AS 領域,
    o.學年學期 AS 學年學期 -- 从开课表中抓取学年学期
  FROM
    修課 r
  JOIN
    開課 o ON r.開課序號 = o.開課序號 -- 关联修课和开课表
  JOIN
    課程 c ON o.科目序號 = c.科目序號 -- 关联开课和课程表
  WHERE
    r.學號 = ?  -- 使用学生的学号作为查询条件
  ORDER BY
    o.學年學期;  -- 按学年学期排序
  ";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param("s", $student_id); // 使用正確的變數綁定學號參數
  
  // 執行查詢
  $stmt->execute();
  
  // 獲取結果
  $result = $stmt->get_result();
  
  if ($result->num_rows > 0) {
    // 設定檔案下載的 Header
    header('Content-Type: text/plain; charset=utf-8');
    header('Content-Disposition: attachment; filename=student_courses.txt');

    // 打開輸出流
    $output = fopen('php://output', 'w');

    // 定義固定寬度的表頭格式
    $header = [
        "開課序號" => 12,
        "課程名稱" => 30,
        "學分" => 8,
        "成績" => 8,
        "必選修" => 10,
        "領域" => 15,
        "學年學期" => 10
    ];

    // 輸出表頭
    $header_line = '';
    foreach ($header as $title => $width) {
        $header_line .= str_pad($title, $width, ' ', STR_PAD_RIGHT); // 標題左對齊
    }
    fwrite($output, $header_line . "\n");

    // 輸出水平分隔線
    fwrite($output, str_repeat("=", 90) . "\n");

    // 記錄上一欄最大長度
    $last_max_length = 0;

    // 輸出每行資料
    while ($row = $result->fetch_assoc()) {
        // 先計算這一行資料的各欄位長度
        $row_lengths = [
            strlen($row['開課序號']),
            strlen($row['課程名稱']),
            strlen($row['學分']),
            strlen($row['成績']),
            strlen($row['必選修']),
            strlen($row['領域']),
            strlen($row['學年學期'])
        ];

        // 找出這一行資料最長的欄位長度（這裡使用課程名稱的長度來調整）
        $max_length = max($row_lengths); // 計算最大長度

        // 計算每一欄的起始位置，這邊根據前一欄的最大長度調整起始位置
        $start_positions = [
            $header['開課序號'], // 開課序號的起始位置
            $header['開課序號'] + 3 + $header['課程名稱'] + 3, // 課程名稱的起始位置
            $header['開課序號'] + 3 + $header['課程名稱'] + 3 + $header['學分'] + 3, // 學分的起始位置
            $header['開課序號'] + 3 + $header['課程名稱'] + 3 + $header['學分'] + 3 + $header['成績'] + 3, // 成績的起始位置
            $header['開課序號'] + 3 + $header['課程名稱'] + 3 + $header['學分'] + 3 + $header['成績'] + 3 + $header['必選修'] + 3, // 必選修的起始位置
            $header['開課序號'] + 3 + $header['課程名稱'] + 3 + $header['學分'] + 3 + $header['成績'] + 3 + $header['必選修'] + 3 + $header['領域'] + 3, // 領域的起始位置
            $header['開課序號'] + 3 + $header['課程名稱'] + 3 + $header['學分'] + 3 + $header['成績'] + 3 + $header['必選修'] + 3 + $header['領域'] + 3 + $header['學年學期'] + 3, // 學年學期的起始位置
        ];

        // 準備輸出這一行的資料
        $row_line = '';
        $row_line .= str_pad($row['開課序號'], $header['開課序號'], ' ', STR_PAD_RIGHT); // 開課序號左對齊
        $row_line .= str_pad(mb_strimwidth($row['課程名稱'], 0, 30, "...", "UTF-8"), $header['課程名稱'], ' ', STR_PAD_RIGHT); // 課程名稱左對齊
        $row_line .= str_pad($row['學分'], $header['學分'], ' ', STR_PAD_LEFT); // 學分右對齊
        $row_line .= str_pad($row['成績'], $header['成績'], ' ', STR_PAD_LEFT); // 成績右對齊
        $row_line .= str_pad($row['必選修'], $header['必選修'], ' ', STR_PAD_RIGHT); // 必選修左對齊
        $row_line .= str_pad($row['領域'], $header['領域'], ' ', STR_PAD_RIGHT); // 領域左對齊
        $row_line .= str_pad($row['學年學期'], $header['學年學期'], ' ', STR_PAD_LEFT); // 學年學期右對齊

        // 根據最大欄位長度調整起始位置並輸出
        fwrite($output, str_pad($row_line, $start_positions[count($start_positions)-1], ' ', STR_PAD_RIGHT) . "\n");
    }

    // 關閉輸出流
    fclose($output);
    exit;
} else {
    echo "沒有找到修課資料！";
}
  ?>