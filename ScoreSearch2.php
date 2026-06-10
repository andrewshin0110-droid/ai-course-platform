<?php
session_start();

if (!isset($_SESSION['user'])) {
    echo "使用者尚未登錄";
    exit();
}

$user = $_SESSION['user'];
$student_id = $user['學號'];
$student_major = $user['學系名稱'];
  

// 數據庫連接設置
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "course"; 

// 創建連接
$conn = new mysqli($servername, $username, $password, $dbname);

// 檢查連接
if ($conn->connect_error) {
    die("連接失敗: " . $conn->connect_error);
}

////////////////////////門檻查詢////////////////////////////////

$sql2 = "
SELECT
    x.學系名稱 AS 學系名稱,
    x.年度 AS 年度,
    x.通過學分 AS 通過學分,
    x.必修學分 AS 必修學分,
    x.選修學分 AS 選修學分,
    x.領域需求 AS 領域需求,
    x.領域學分 AS 領域學分,
    x.聯絡資訊 AS 聯絡資訊
FROM
    學系 x
WHERE
    x.學系名稱 = ?
";

$stmt2 = $conn->prepare($sql2);
$stmt2->bind_param("s", $student_major);
$stmt2->execute();
$result2 = $stmt2->get_result();

////////////////////////該學系之必修 選修及格線////////////////////////////////

$sql_major_requirements = "SELECT 必修學分, 選修學分 FROM 學系 WHERE 學系名稱 = ?";
$stmt_major = $conn->prepare($sql_major_requirements);
$stmt_major->bind_param("s", $student_major);
$stmt_major->execute();
$result_major = $stmt_major->get_result();

if ($result_major->num_rows == 1) {
    $row = $result_major->fetch_assoc();
    $required_credits_target = $row['必修學分']; // 学系的必修学分要求
    $elective_credits_target = $row['選修學分']; // 学系的选修学分要求
} else {
    echo "找不到該學生的學系數據";
    exit();
}

/////////////////////////////該學生必修 選修學分各自總和//////////////////////////////

$sql_required = "SELECT SUM(課程.學分) AS 必修總學分 
                 FROM 修課 
                 JOIN 開課 ON 修課.開課序號 = 開課.開課序號 
                 JOIN 課程 ON 開課.科目序號 = 課程.科目序號
                 WHERE 修課.學號 = ? 
                 AND 課程.必選修通識 = '必修' 
                  AND (修課.成績 >= 60 OR 修課.成績 IS NULL OR TRIM(修課.成績) = '')
                 AND 課程.領域 = '系定必修'";

$stmt_required = $conn->prepare($sql_required);
$stmt_required->bind_param("s", $student_id);
$stmt_required->execute();
$result_required = $stmt_required->get_result();
$required_total = $result_required->fetch_assoc()['必修總學分'];

// 查询选修课程学分总和
$sql_elective = "SELECT SUM(課程.學分) AS 選修總學分 
                 FROM 修課 
                 JOIN 開課 ON 修課.開課序號 = 開課.開課序號 
                 JOIN 課程 ON 開課.科目序號 = 課程.科目序號
                 WHERE 修課.學號 = ? AND 課程.必選修通識 = '選修' AND 修課.成績 >= 60";
$stmt_elective = $conn->prepare($sql_elective);
$stmt_elective->bind_param("s", $student_id);
$stmt_elective->execute();
$result_elective = $stmt_elective->get_result();
$elective_total = $result_elective->fetch_assoc()['選修總學分'];

////////////////////////////計算學分差距////////////////////////////////////

$required_credits_left = max(0, $required_credits_target - ($required_total !== null ? $required_total : 0));
$elective_credits_left = max(0, $elective_credits_target - ($elective_total !== null ? $elective_total : 0));

///////////////////////////英檢/////////////////////////////

$sql_TOEIC = "SELECT 英文檢定 FROM 學生
              WHERE 學號 = ?
             ";
             $stmt = $conn->prepare($sql_TOEIC);
             $stmt->bind_param("s", $student_id); // 確保 $學號 已正確賦值
             $stmt->execute();
             $result = $stmt->get_result();

///////////////////////////////系必修/////////////////////////////////////////

$sql3 = "
SELECT 
   c.課程名稱, 
    c.學分, 
    c.必選修通識, 
    c.領域, 
    COALESCE(r.成績, '未提供') AS 成績,
    CASE 
        WHEN r.成績 IS NULL AND r.備註 = '通過' THEN '通過'  -- 當成績為空且備註為'通過'時顯示通過
        WHEN r.成績 IS NULL THEN '未修過'  -- 當成績為空時顯示未修過
        WHEN r.成績 < 60 THEN '未通過'  -- 當成績小於60時顯示未通過
        ELSE '通過'  -- 否則顯示通過
    END AS 狀態
FROM 修課 r
INNER JOIN 開課 k ON r.開課序號 = k.開課序號
INNER JOIN 課程 c ON k.科目序號 = c.科目序號
WHERE 
     c.必選修通識 = '必修'
    AND c.領域 = '系定必修'
    AND r.學號 = ?
ORDER BY c.課程名稱 ASC";



$stmt3 = $conn->prepare($sql3);
$stmt3->bind_param("s", $student_id); // 確保 $學號 已正確賦值
$stmt3->execute();
$result3 = $stmt3->get_result();

//////////////////////////////////////////////////////////////////////////////////////

$sql_compare = "
SELECT 
    c.課程名稱
FROM 課程 c
WHERE 
    c.必選修通識 = '必修'
    AND c.領域 = '系定必修'
    AND NOT EXISTS (
        SELECT 1
        FROM 修課 r
        INNER JOIN 開課 k ON r.開課序號 = k.開課序號
        INNER JOIN 課程 c2 ON k.科目序號 = c2.科目序號
        WHERE 
            c2.課程名稱 = c.課程名稱 -- 按課程名稱進行比較
            AND r.學號 = ? -- 指定學生學號
    )
ORDER BY c.課程名稱 ASC";
$stmt_compare = $conn->prepare($sql_compare);
if ($stmt_compare === false) {
    echo "SQL Error: " . $conn->error;
    exit;
}

// 綁定參數
$stmt_compare->bind_param("s", $student_id);

// 執行查詢
$stmt_compare->execute();

// 獲取結果
$result_compare = $stmt_compare->get_result();


//////////////////////////////////系選修////////////////////////////////////////////////

$sql4 = "
SELECT 
   c.課程名稱, 
   c.學分, 
   c.必選修通識, 
   c.領域, 
   COALESCE(r.成績, '未修過') AS 成績,
   CASE 
       WHEN r.成績 IS NULL AND r.備註 = '通過' THEN '通過'  -- 當成績為空且備註為'通過'時顯示通過
       WHEN r.成績 IS NULL THEN '未修過'  -- 當成績為空時顯示未修過
       WHEN r.成績 < 60 THEN '未通過'  -- 當成績小於60時顯示未通過
       ELSE '通過'  -- 否則顯示通過
   END AS 狀態
FROM 修課 r
INNER JOIN 開課 k ON r.開課序號 = k.開課序號
INNER JOIN 課程 c ON k.科目序號 = c.科目序號
WHERE 
   c.必選修通識 = '選修'  -- 正確的表別名是 c
   AND r.學號 = ?  -- 在這裡使用學號
ORDER BY 
   k.學年學期 ASC  -- 正確的表別名是 k

";

$stmt4 = $conn->prepare($sql4);
$stmt4->bind_param("s", $student_id); // 確保 $學號 已正確賦值
$stmt4->execute();
$result4 = $stmt4->get_result();

////////////////////////通識課程-社會核心////////////////////////////////

$sql5 = "
SELECT 
   c.課程名稱, 
   c.學分, 
   c.必選修通識, 
   c.領域, 
   COALESCE(r.成績, '未修過') AS 成績,
   CASE 
       WHEN r.成績 IS NULL AND r.備註 = '通過' THEN '通過'  -- 當成績為空且備註為'通過'時顯示通過
       WHEN r.成績 IS NULL THEN '未修過'  -- 當成績為空時顯示未修過
       WHEN r.成績 < 60 THEN '未通過'  -- 當成績小於60時顯示未通過
       ELSE '通過'  -- 否則顯示通過
   END AS 狀態
FROM 修課 r
INNER JOIN 開課 k ON r.開課序號 = k.開課序號
INNER JOIN 課程 c ON k.科目序號 = c.科目序號
WHERE 
   c.必選修通識 = '核心課程'  -- 使用正確的表別名 c
   AND c.領域 = '公民與社會探究'  -- 使用正確的表別名 c
   AND r.學號 = ?  -- 在這裡使用學號
ORDER BY 
   k.學年學期 ASC;  -- 使用正確的表別名 k

";

$stmt5 = $conn->prepare($sql5);
$stmt5->bind_param("s", $student_id); // 確保 $學號 已正確賦值
$stmt5->execute();
$result5 = $stmt5->get_result();

///////////////////////////通識課程-人文核心/////////////////////////////////////////////
$sql13 = "
SELECT 
   c.課程名稱, 
   c.學分, 
   c.必選修通識, 
   c.領域, 
   COALESCE(r.成績, '未修過') AS 成績,
   CASE 
       WHEN r.成績 IS NULL AND r.備註 = '通過' THEN '通過'  -- 當成績為空且備註為'通過'時顯示通過
       WHEN r.成績 IS NULL THEN '未修過'  -- 當成績為空時顯示未修過
       WHEN r.成績 < 60 THEN '未通過'  -- 當成績小於60時顯示未通過
       ELSE '通過'  -- 否則顯示通過
   END AS 狀態
FROM 修課 r
INNER JOIN 開課 k ON r.開課序號 = k.開課序號
INNER JOIN 課程 c ON k.科目序號 = c.科目序號
WHERE 
   c.必選修通識 = '核心課程'  -- 使用正確的表別名 c
   AND c.領域 = '藝術與人文思維'  -- 使用正確的表別名 c
   AND r.學號 = ?  -- 在這裡使用學號
ORDER BY 
   k.學年學期 ASC;  -- 使用正確的表別名 k

";

$stmt13 = $conn->prepare($sql13);
$stmt13->bind_param("s", $student_id); // 確保 $學號 已正確賦值
$stmt13->execute();
$result13 = $stmt13->get_result();

///////////////////////////通識課程-多元課程/////////////////////////////////////////////
$sql14 = "
SELECT 
   c.課程名稱, 
   c.學分, 
   c.必選修通識, 
   c.領域, 
   COALESCE(r.成績, '未修過') AS 成績,
   CASE 
       WHEN r.成績 IS NULL AND r.備註 = '通過' THEN '通過'  -- 當成績為空且備註為'通過'時顯示通過
       WHEN r.成績 IS NULL THEN '未修過'  -- 當成績為空時顯示未修過
       WHEN r.成績 < 60 THEN '未通過'  -- 當成績小於60時顯示未通過
       ELSE '通過'  -- 否則顯示通過
   END AS 狀態
FROM 修課 r
INNER JOIN 開課 k ON r.開課序號 = k.開課序號
INNER JOIN 課程 c ON k.科目序號 = c.科目序號
WHERE 
   c.必選修通識 = '多元選修課程'  -- 使用正確的表別名 c
   AND r.學號 = ?  -- 在這裡使用學號
ORDER BY 
   k.學年學期 ASC;  -- 使用正確的表別名 k

";

$stmt14 = $conn->prepare($sql14);
$stmt14->bind_param("s", $student_id); // 確保 $學號 已正確賦值
$stmt14->execute();
$result14 = $stmt14->get_result();

////////////////////////////系必修學分//////////////////////////////////

$sql6 = "
SELECT 
    COUNT(DISTINCT 課程.科目序號) AS 修過的必修課程數量,  -- 已修的必修課程數量
    COALESCE(SUM(課程.學分), 0) AS 已修學分,  -- 已修必修學分總數
    學系.必修學分 AS 應修學分,  -- 應修的必修學分
    GREATEST(學系.必修學分 - COALESCE(SUM(課程.學分), 0), 0) AS 還差學分 
FROM 
    學生
JOIN 
    修課 ON 學生.學號 = 修課.學號
JOIN 
    開課 ON 修課.開課序號 = 開課.開課序號
JOIN 
    課程 ON 開課.科目序號 = 課程.科目序號
JOIN 
    學系 ON 學生.學系名稱 = 學系.學系名稱
WHERE 
    課程.必選修通識 = '必修' 
    AND 修課.學號 = ?  -- 在這裡使用學號
    AND 課程.領域 = '系定必修'
   AND (修課.成績 >= 60 OR 修課.備註 = '通過')  -- 成績大於等於 60，或者成績為 NULL 或 空
    
GROUP BY 
    學生.學號, 
    學生.姓名, 
    學系.必修學分
    ";


$stmt6 = $conn->prepare($sql6);
$stmt6->bind_param("s", $student_id); // 確保 $學號 已正確賦值
$stmt6->execute();
$result6 = $stmt6->get_result();

///////////////////////////////////////通識學分 -社會核心////////////////////////////////////////////////
$sql11 = "
SELECT 
    
    6 - COALESCE(SUM(課程.學分), 0) AS 還差學分  -- 還差的學分
FROM 
    學生
JOIN 
    修課 ON 學生.學號 = 修課.學號
JOIN 
    開課 ON 修課.開課序號 = 開課.開課序號
JOIN 
    課程 ON 開課.科目序號 = 課程.科目序號
JOIN 
    學系 ON 學生.學系名稱 = 學系.學系名稱
WHERE 
    課程.必選修通識 = '核心課程' 
    AND 領域 = '公民與社會探究'
    AND 修課.學號 = ?  -- 在這裡使用學號
    AND 修課.成績 >= 60 
GROUP BY 
    學生.學號, 
    學生.姓名, 
    學系.必修學分
    ";


$stmt11 = $conn->prepare($sql11);
$stmt11->bind_param("s", $student_id); // 確保 $學號 已正確賦值
$stmt11->execute();
$result11 = $stmt11->get_result();

///////////////////////////////////////通識學分－人文核心////////////////////////

$sql12 = "
SELECT 
    
    6 - COALESCE(SUM(課程.學分), 0) AS 還差學分  -- 還差的學分
FROM 
    學生
JOIN 
    修課 ON 學生.學號 = 修課.學號
JOIN 
    開課 ON 修課.開課序號 = 開課.開課序號
JOIN 
    課程 ON 開課.科目序號 = 課程.科目序號
JOIN 
    學系 ON 學生.學系名稱 = 學系.學系名稱
WHERE 
    課程.必選修通識 = '核心課程' 
    AND 領域 = '藝術與人文思維'
    AND 修課.學號 = ?  -- 在這裡使用學號
    AND 修課.成績 >= 60 
GROUP BY 
    學生.學號, 
    學生.姓名, 
    學系.必修學分
    ";


$stmt12 = $conn->prepare($sql12);
$stmt12->bind_param("s", $student_id); // 確保 $學號 已正確賦值
$stmt12->execute();
$result12 = $stmt12->get_result();

///////////////////////////////////////通識學分－多元課程////////////////////////

$sql15 = "
SELECT 
    
    10 - COALESCE(SUM(課程.學分), 0) AS 還差學分  -- 還差的學分
FROM 
    學生
JOIN 
    修課 ON 學生.學號 = 修課.學號
JOIN 
    開課 ON 修課.開課序號 = 開課.開課序號
JOIN 
    課程 ON 開課.科目序號 = 課程.科目序號
JOIN 
    學系 ON 學生.學系名稱 = 學系.學系名稱
WHERE 
    課程.必選修通識 = '多元選修課程' 
    AND 修課.學號 = ?  -- 在這裡使用學號
    AND 修課.成績 >= 60 
GROUP BY 
    學生.學號, 
    學生.姓名, 
    學系.必修學分
    ";


$stmt15 = $conn->prepare($sql15);
$stmt15->bind_param("s", $student_id); // 確保 $學號 已正確賦值
$stmt15->execute();
$result15 = $stmt15->get_result();


///////////////////////////////////////////系選修學分//////////////////////////////////////

$sql7 = "
SELECT 
    COUNT(DISTINCT 課程.科目序號) AS 修過的選修課程數量,  -- 已修的必修課程數量
    COALESCE(SUM(課程.學分), 0) AS 已修學分,  -- 已修必修學分總數
    學系.選修學分 AS 應修學分,  -- 應修的必修學分
    GREATEST(學系.選修學分 - COALESCE(SUM(課程.學分), 0), 0) AS 還差學分 
FROM 
    學生
JOIN 
    修課 ON 學生.學號 = 修課.學號
JOIN 
    開課 ON 修課.開課序號 = 開課.開課序號
JOIN 
    課程 ON 開課.科目序號 = 課程.科目序號
JOIN 
    學系 ON 學生.學系名稱 = 學系.學系名稱
WHERE 
    課程.必選修通識 = '選修' 
    AND 修課.學號 = ?  -- 在這裡使用學號
    AND 修課.成績 >= 60  
GROUP BY 
    學生.學號, 
    學生.姓名, 
    學系.必修學分
    ";


$stmt7 = $conn->prepare($sql7);
$stmt7->bind_param("s", $student_id); // 確保 $學號 已正確賦值
$stmt7->execute();
$result7 = $stmt7->get_result();

///////////////////////////體育////////////////////////////////

$sql8 = "
SELECT 
   c.課程名稱, 
   c.學分, 
   c.必選修通識, 
   c.領域, 
   COALESCE(r.成績, '未修過') AS 成績,
   CASE 
       WHEN r.成績 IS NULL AND r.備註 = '通過' THEN '通過'  -- 當成績為空且備註為'通過'時顯示通過
       WHEN r.成績 IS NULL THEN '未修過'  -- 當成績為空時顯示未修過
       WHEN r.成績 < 60 THEN '未通過'  -- 當成績小於60時顯示未通過
       ELSE '通過'  -- 否則顯示通過
   END AS 狀態
FROM 修課 r
INNER JOIN 開課 k ON r.開課序號 = k.開課序號
INNER JOIN 課程 c ON k.科目序號 = c.科目序號
WHERE 
   c.必選修通識 = '必修'  -- 使用正確的表別名 c
   AND c.領域 = '體育'    -- 使用正確的表別名 c
   AND r.學號 = ?  -- 使用學號作為條件
ORDER BY 
   k.學年學期 ASC;  -- 使用正確的表別名 k

";

$stmt8= $conn->prepare($sql8);
$stmt8->bind_param("s", $student_id); // 確保 $學號 已正確賦值
$stmt8->execute();
$result8 = $stmt8->get_result();

//////////////////////國防/////////////////////////////////

$sql9 = "
SELECT 
   c.課程名稱, 
   c.學分, 
   c.必選修通識, 
   c.領域, 
   COALESCE(r.成績, '未修過') AS 成績,
   CASE 
       WHEN r.成績 IS NULL AND r.備註 = '通過' THEN '通過'  -- 當成績為空且備註為'通過'時顯示通過
       WHEN r.成績 IS NULL THEN '未修過'  -- 當成績為空時顯示未修過
       WHEN r.成績 < 60 THEN '未通過'  -- 當成績小於60時顯示未通過
       ELSE '通過'  -- 否則顯示通過
   END AS 狀態
FROM 修課 r
INNER JOIN 開課 k ON r.開課序號 = k.開課序號
INNER JOIN 課程 c ON k.科目序號 = c.科目序號
WHERE 
   c.必選修通識 = '必修'  -- 使用正確的表別名 c
   AND c.領域 = '國防'    -- 使用正確的表別名 c
   AND r.學號 = ?  -- 使用學號作為條件
ORDER BY 
   k.學年學期 ASC;  -- 使用正確的表別名 k

";

$stmt9 = $conn->prepare($sql9);
$stmt9->bind_param("s", $student_id); // 確保 $學號 已正確賦值
$stmt9->execute();
$result9 = $stmt9->get_result();

/////////////////////////英文////////////////////////////////

$sql10 = "
SELECT 
   c.課程名稱, 
   c.學分, 
   c.必選修通識, 
   c.領域, 
   COALESCE(r.成績, '未修過') AS 成績,
   CASE 
       WHEN r.成績 IS NULL AND r.備註 = '通過' THEN '通過'  -- 當成績為空且備註為'通過'時顯示通過
       WHEN r.成績 IS NULL THEN '未修過'  -- 當成績為空時顯示未修過
       WHEN r.成績 < 60 THEN '未通過'  -- 當成績小於60時顯示未通過
       ELSE '通過'  -- 否則顯示通過
   END AS 狀態
FROM 修課 r
INNER JOIN 開課 k ON r.開課序號 = k.開課序號
INNER JOIN 課程 c ON k.科目序號 = c.科目序號
WHERE 
   c.必選修通識 = '英文'  -- 使用正確的表別名 c
   AND r.學號 = ?  -- 使用學號作為條件
ORDER BY 
   k.學年學期 ASC;  -- 使用正確的表別名 k

";

$stmt10 = $conn->prepare($sql10);
$stmt10->bind_param("s", $student_id); // 確保 $學號 已正確賦值
$stmt10->execute();
$result10 = $stmt10->get_result();

///////////////////////////深耕///////////////////////////////

$sql_hours = "
SELECT 
    學年學期,
    SUM(CASE WHEN 深耕學園.活動類別 = '藝文活動(藝術類)' THEN 深耕學園.認證時數 ELSE 0 END) AS 藝術類時數,
    SUM(CASE WHEN 深耕學園.活動類別 = '藝文活動(人文類)' THEN 深耕學園.認證時數 ELSE 0 END) AS 人文類時數
FROM 修課
JOIN 深耕學園 ON 修課.開課序號 = 深耕學園.開課序號
WHERE 修課.學號 = ? AND 修課.備註 LIKE '%深耕%'  
GROUP BY 學年學期;
";

$stmt_hours = $conn->prepare($sql_hours);
$stmt_hours->bind_param("s", $student_id);
$stmt_hours->execute();
$result_hours = $stmt_hours->get_result();


$sql_remaining_humanities = "
SELECT 
    8 - COALESCE(SUM(深耕學園.認證時數), 0) AS 剩餘人文深耕時數
FROM 修課
JOIN 深耕學園 ON 修課.開課序號 = 深耕學園.開課序號
WHERE 修課.學號 = ? 
  AND 修課.備註 = '人文深耕';
";

$stmt_remaining_humanities = $conn->prepare($sql_remaining_humanities);
$stmt_remaining_humanities->bind_param("s", $student_id);
$stmt_remaining_humanities->execute();
$result_remaining_humanities = $stmt_remaining_humanities->get_result();


$sql_remaining_arts = "
SELECT 
    8 - COALESCE(SUM(深耕學園.認證時數), 0) AS 剩餘藝術深耕時數
FROM 修課
JOIN 深耕學園 ON 修課.開課序號 = 深耕學園.開課序號
WHERE 修課.學號 = ? 
  AND 修課.備註 = '藝術深耕';
";

$stmt_remaining_arts = $conn->prepare($sql_remaining_arts);
$stmt_remaining_arts->bind_param("s", $student_id);
$stmt_remaining_arts->execute();
$result_remaining_arts = $stmt_remaining_arts->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>畢業資格查詢</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel="stylesheet"/>
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="ScoreSearch2.css">
</head>
<style>
    .styled-table .status-pass {
    background-color: #d4edda;
    color: #155724;
}

.styled-table .status-fail {
    background-color: #f8d7da;
    color: #721c24;}


    .styled-table1 {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    font-size: 16px;
    border-radius: 12px;
    overflow: hidden;
}

.styled-table1 th, .styled-table1 td {
    border-bottom: 1px solid #ffcc80; /* 使用淺橘色邊框 */
    padding: 8px;
    text-align: left;
}

.styled-table1 th {
    background-color:  #ffb74d; /* 橘色標題背景 */
    color: white; /* 白色文字 */
    font-weight: bold;
}

.styled-table1 tr:nth-child(even) {
    background-color: #fff3e0; /* 淺橘色背景 */
}

.styled-table1 tr:hover {
    background-color: #ffe0b2; /* 橘色的 hover 效果 */
}

/* 額外設置圓角 */

.styled-table1 th:first-child {
    border-top-left-radius: 12px; /* 左上角圓角 */
}

.styled-table1 th:last-child {
    border-top-right-radius: 12px; /* 右上角圓角 */
}

.styled-table1 tr:last-child td:first-child {
    border-bottom-left-radius: 12px; /* 左下角圓角 */
}

.styled-table1 tr:last-child td:last-child {
    border-bottom-right-radius: 12px; /* 右下角圓角 */
}

</style>
<body>
    <section class="home">
        <div class="toggle-sidebar">
            <div class="text">畢業資格查詢</div>
        </div>

        <div class="wrap-ss">
            <?php
            if ($result2->num_rows > 0) {
                echo "<table class='styled-table' id='courseTable'>";
                echo "<tr><th>通過學分</th><th>必修學分</th><th>選修學分</th><th>領域需求</th><th>領域學分</th></tr>";
                while ($row = $result2->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row["通過學分"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["必修學分"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["選修學分"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["領域需求"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["領域學分"]) . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "没有找到修課紀錄";
            }
            ?>
        </div>

        <div class="wrap-ss1">
            <?php
            echo "<table class='styled-table' id='courseTable'>";
            echo "<tr><th></th><th>必修學分</th><th>選修學分</th></tr>";
            echo "<tr>";
            echo "<td>已得學分</td>";
            echo "<td>" . ($required_total !== null ? $required_total : 0) . "</td>";
            echo "<td>" . ($elective_total !== null ? $elective_total : 0) . "</td>";
            echo "</tr>";
            echo "<tr>";
            echo "<td>尚缺學分</td>";
            echo "<td>" . $required_credits_left . "</td>";
            echo "<td>" . $elective_credits_left . "</td>";
            echo "</tr>";
            if ($result->num_rows > 0) {
                
                $row = $result->fetch_assoc();
                
                $toeic_result = (strtolower($row['英文檢定']) === 'yes') ? "通過" : "未通過";
                
                echo "<tr>";
                echo "<td>英文檢定</td>";
                echo "<td colspan='2'>" . $toeic_result . "</td>"; 
                echo "</tr>";
            } else {
                echo "<tr>";
                echo "<td>英文檢定</td>";
                echo "<td colspan='2'>No TOEIC score found</td>"; 
                echo "</tr>";
            }
            
            echo "</table>";
            ?>
        </div>

        <div class="wrap-ss2">
            <div id="wrapper2" class="wrapper-ss" style="height: 400px;">
                <div id="button-container2">
                    <span style="font-size: 25px; font-weight: 900;">畢業資格</span>
                    <button class="search" onclick="showContent('wrapper2', 'content3')">系必修</button>
                    <button class="search" onclick="showContent('wrapper2', 'content4')">系選修</button>
                    <button class="search" onclick="showContent('wrapper2', 'content5')">通識課程</button>
                    <button class="search" onclick="showContent('wrapper2', 'content6')">軍訓課程</button>
                    <button class="search" onclick="showContent('wrapper2', 'content7')">體育課程</button>
                    <button class="search" onclick="showContent('wrapper2', 'content8')">英文課程</button>
                    <button class="search" onclick="showContent('wrapper2', 'content9')">深耕學園</button>
                </div>

                <div id="content3" class="content" style="overflow: auto; padding-left:90px; margin-top: 30px; margin-bottom: 10px;">
                    <?php
                    if ($result6->num_rows > 0) {
                        echo "<table class='styled-table' id='courseTable' style='background-color: #d4f4dd;'>";
                        echo "<tr><th>已修科目數</th><th>已修學分</th><th>應修學分</th><th>缺修學分</th></tr>";

                        // 輸出查詢結果
                        while ($row = $result6->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row["修過的必修課程數量"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["已修學分"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["應修學分"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["還差學分"]) . "</td>";
                            echo "</tr>";
                        }
                        echo "</table>";
                    } else {
                        echo "沒有找到結果";
                    }

                    if ($result_compare->num_rows > 0) {
                        echo "<table class='styled-table1' id='courseTable'>";
                        echo "<tr><th>未修科目</th></tr>";
                        while ($row = $result_compare->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row["課程名稱"]) . "</td>";
                            echo "</tr>";
                        }
                        echo "</table>";
                    }

                    if ($result3->num_rows > 0) {
                        echo "<table class='styled-table' id='courseTable'>";
                        echo "<tr><th>已修科目</th><th>學分</th><th>成績</th><th>領域</th><th>備註</th></tr>";  // 增加備註標題
                        while ($row = $result3->fetch_assoc()) {
                            // 判斷成績狀態的顏色
                            $statusClass = $row["狀態"] === "未通過" ? "status-fail" : "status-pass";
                    
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row["課程名稱"] ) . "</td>";
                            echo "<td>" . htmlspecialchars($row["學分"] ) . "</td>";
                            echo "<td>" . htmlspecialchars($row["成績"] ) . "</td>";
                            echo "<td>" . htmlspecialchars($row["領域"] ) . "</td>";
                            echo "<td class='$statusClass'>" . htmlspecialchars($row["狀態"]) . "</td>";
                            echo "</tr>";
                        }
                        echo "</table>";
                    } else {
                        echo "没有找到修課紀錄";
                    }

                   
                    ?>
                </div>

                <div id="content4" class="content" style="overflow: auto; padding-left:90px; margin-top: 30px; margin-bottom: 10px;">
                    <?php
                    if ($result7->num_rows > 0) {
                        echo "<table class='styled-table' id='courseTable'>";
                        echo "<tr><th>已修科目數</th><th>已修學分</th><th>應修學分</th><th>缺修學分</th></tr>";

                        // 輸出查詢結果
                        while ($row = $result7->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row["修過的選修課程數量"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["已修學分"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["應修學分"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["還差學分"]) . "</td>";
                            echo "</tr>";
                        }
                        echo "</table>";
                    } else {
                        echo "沒有找到結果";
                    }

                    if ($result4->num_rows > 0) {
                        echo "<table class='styled-table' id='courseTable'>";
                        echo "<tr><th>科目名稱</th><th>學分</th><th>成績</th><th>領域</th><th>備註</th></tr>";  // 增加備註標題
                        while ($row = $result4->fetch_assoc()) {
                            $statusClass = $row["狀態"] === "未通過" ? "status-fail" : "status-pass";
                    
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row["課程名稱"] ) . "</td>";
                            echo "<td>" . htmlspecialchars($row["學分"] ) . "</td>";
                            echo "<td>" . htmlspecialchars($row["成績"] ) . "</td>";
                            echo "<td>" . htmlspecialchars($row["領域"] ) . "</td>";
                            echo "<td class='$statusClass'>" . htmlspecialchars($row["狀態"]) . "</td>";
                            echo "</tr>";
                        }
                        echo "</table>";
                        
                    } else {
                        echo "尚無修課紀錄";
                    }
                    ?>
                </div>

                <div id="content5" class="content" style="overflow: auto; padding-left:90px; margin-top: 30px; margin-bottom: 10px;">
                    <?php
                    $noResultCount = 0; // 初始化技術變量
                    $totalQueries = 6; // 總共的查詢數量

                    if ($result11->num_rows > 0) {
                        echo "<table class='styled-table' id='courseTable'>";
                        echo "<tr><th>通識領域</th><th>應修學分</th><th>缺修學分</th></tr>";
                        while ($row = $result11->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>基礎社會科學</td>";
                            echo "<td>6</td>";
                            echo "<td>" . htmlspecialchars($row["還差學分"]) . "</td>";
                            echo "</tr>";
                        }
                        echo "</table>";
                    } else {
                        $noResultCount++;
                    }

                    if ($result5->num_rows > 0) {
                        echo "<table class='styled-table' id='courseTable'>";
                        echo "<tr><th>科目名稱</th><th>學分</th><th>成績</th><th>領域</th><th>備註</th></tr>";
                        while ($row = $result5->fetch_assoc()) {
                            $statusClass = $row["狀態"] === "未通過" ? "status-fail" : "status-pass";
                    
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row["課程名稱"] ) . "</td>";
                            echo "<td>" . htmlspecialchars($row["學分"] ) . "</td>";
                            echo "<td>" . htmlspecialchars($row["成績"] ) . "</td>";
                            echo "<td>" . htmlspecialchars($row["領域"] ) . "</td>";
                            echo "<td class='$statusClass'>" . htmlspecialchars($row["狀態"]) . "</td>";
                            echo "</tr>";
                        }
                        echo "</table>";
                    } else {
                        $noResultCount++;
                    }

                    if ($result12->num_rows > 0) {
                        echo "<table class='styled-table' id='courseTable'>";
                        echo "<tr><th>通識領域</th><th>應修學分</th><th>缺修學分</th></tr>";
                        while ($row = $result12->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>基礎人文科學</td>";
                            echo "<td>6</td>";
                            echo "<td>" . htmlspecialchars($row["還差學分"]) . "</td>";
                            echo "</tr>";
                        }
                        echo "</table>";
                    } else {
                        $noResultCount++;
                    }

                    if ($result13->num_rows > 0) {
                        echo "<table class='styled-table' id='courseTable'>";
                        echo "<tr><th>科目名稱</th><th>學分</th><th>成績</th><th>領域</th><th>備註</th></tr>";
                        while ($row = $result13->fetch_assoc()) {
                            $statusClass = $row["狀態"] === "未通過" ? "status-fail" : "status-pass";
                    
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row["課程名稱"] ) . "</td>";
                            echo "<td>" . htmlspecialchars($row["學分"] ) . "</td>";
                            echo "<td>" . htmlspecialchars($row["成績"] ) . "</td>";
                            echo "<td>" . htmlspecialchars($row["領域"] ) . "</td>";
                            echo "<td class='$statusClass'>" . htmlspecialchars($row["狀態"]) . "</td>";
                            echo "</tr>";
                        }
                        echo "</table>";
                    } else {
                        $noResultCount++;
                    }

                    if ($result15->num_rows > 0) {
                        echo "<table class='styled-table' id='courseTable'>";
                        echo "<tr><th>通識領域</th><th>應修學分</th><th>缺修學分</th></tr>";
                        while ($row = $result15->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>多元課程</td>";
                            echo "<td>10</td>";
                            echo "<td>" . htmlspecialchars($row["還差學分"]) . "</td>";
                            echo "</tr>";
                        }
                        echo "</table>";
                    } else {
                        $noResultCount++;
                    }

                    if ($result14->num_rows > 0) {
                        echo "<table class='styled-table' id='courseTable'>";
                        echo "<tr><th>科目名稱</th><th>學分</th><th>成績</th><th>領域</th><th>備註</th></tr>";
                        while ($row = $result14->fetch_assoc()) {
                            $statusClass = $row["狀態"] === "未通過" ? "status-fail" : "status-pass";
                    
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row["課程名稱"] ) . "</td>";
                            echo "<td>" . htmlspecialchars($row["學分"] ) . "</td>";
                            echo "<td>" . htmlspecialchars($row["成績"] ) . "</td>";
                            echo "<td>" . htmlspecialchars($row["領域"] ) . "</td>";
                            echo "<td class='$statusClass'>" . htmlspecialchars($row["狀態"]) . "</td>";
                            echo "</tr>";
                        }
                        echo "</table>";
                    } else {
                        $noResultCount++;
                    }

                    // 檢查是否所有查詢都没有结果
                    if ($noResultCount === $totalQueries) {
                        echo "<p>尚無修課紀錄</p>";
                    }
                    ?>
                </div>

                <div id="content6" class="content" style="overflow: auto; padding-left:90px; margin-top: 30px; margin-bottom: 10px;">
                    <?php
                    if ($result9->num_rows > 0) {
                        echo "<table class='styled-table' id='courseTable'>";
                        echo "<tr><th>科目名稱</th><th>學分</th><th>成績</th><th>領域</th><th>備註</th></tr>";  // 增加備註標題
                        while ($row = $result9->fetch_assoc()) {
                            $statusClass = $row["狀態"] === "未通過" ? "status-fail" : "status-pass";
                    
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row["課程名稱"] ) . "</td>";
                            echo "<td>" . htmlspecialchars($row["學分"] ) . "</td>";
                            echo "<td>" . htmlspecialchars($row["成績"] ) . "</td>";
                            echo "<td>" . htmlspecialchars($row["領域"] ) . "</td>";
                            echo "<td class='$statusClass'>" . htmlspecialchars($row["狀態"]) . "</td>";
                            echo "</tr>";
                        }
                        echo "</table>";
                    } else {
                        echo "尚無修課紀錄";
                    }
                    ?>
                </div>

                <div id="content7" class="content" style="overflow: auto; padding-left:90px; margin-top: 30px; margin-bottom: 10px;">
                    <?php
                    if ($result8->num_rows > 0) {
                        echo "<table class='styled-table' id='courseTable'>";
                        echo "<tr><th>科目名稱</th><th>學分</th><th>成績</th><th>領域</th><th>備註</th></tr>";  // 增加備註標題
                        while ($row = $result8->fetch_assoc()) {
                            $statusClass = $row["狀態"] === "未通過" ? "status-fail" : "status-pass";
                    
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row["課程名稱"] ) . "</td>";
                            echo "<td>" . htmlspecialchars($row["學分"] ) . "</td>";
                            echo "<td>" . htmlspecialchars($row["成績"] ) . "</td>";
                            echo "<td>" . htmlspecialchars($row["領域"] ) . "</td>";
                            echo "<td class='$statusClass'>" . htmlspecialchars($row["狀態"]) . "</td>";
                            echo "</tr>";
                        }
                        echo "</table>";
                    } else {
                        echo "尚無修課紀錄";
                    }
                    ?>
                </div>

                <div id="content8" class="content" style="overflow: auto; padding-left:90px; margin-top: 30px; margin-bottom: 10px;">
                    <?php
                    if ($result10->num_rows > 0) {
                        echo "<table class='styled-table' id='courseTable'>";
                        echo "<tr><th>科目名稱</th><th>學分</th><th>成績</th><th>領域</th><th>備註</th></tr>";  // 增加備註標題
                        while ($row = $result10->fetch_assoc()) {
                            $statusClass = $row["狀態"] === "未通過" ? "status-fail" : "status-pass";
                    
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row["課程名稱"] ) . "</td>";
                            echo "<td>" . htmlspecialchars($row["學分"] ) . "</td>";
                            echo "<td>" . htmlspecialchars($row["成績"] ) . "</td>";
                            echo "<td>" . htmlspecialchars($row["領域"] ) . "</td>";
                            echo "<td class='$statusClass'>" . htmlspecialchars($row["狀態"]) . "</td>";
                            echo "</tr>";
                        }
                        echo "</table>";
                    } else {
                        echo "尚無修課紀錄";
                    }
                    ?>
                </div>

                <div id="content9" class="content"  style="overflow: auto; padding-left:90px; margin-top: 30px; margin-bottom: 10px;">
                    <?php
                    if ($result_hours->num_rows > 0) {
                        echo "<h3>活動時數</h3>";
                        echo "<table class='styled-table' id='courseTable'>
                                <tr>
                                    <th>學年學期</th>
                                    <th>藝術類時數</th>
                                    <th>人文類時數</th>
                                </tr>";
                        while($row = $result_hours->fetch_assoc()) {
                            echo "<tr>
                                    <td>" . $row["學年學期"] . "</td>
                                    <td>" . $row["藝術類時數"] . "</td>
                                    <td>" . $row["人文類時數"] . "</td>
                                  </tr>";
                        }
                        echo "</table><br>";
                    } else {
                        echo "尚無活動時數資料。";
                    }

                    if ($result_remaining_humanities->num_rows > 0) {
                        $row = $result_remaining_humanities->fetch_assoc();
                        echo "剩餘人文深耕時數: " . $row["剩餘人文深耕時數"] . "<br>";
                    } else {
                        echo "尚無人文深耕時數資料。";
                    }

                    if ($result_remaining_arts->num_rows > 0) {
                        $row = $result_remaining_arts->fetch_assoc();
                        echo "剩餘藝術深耕時數: " . $row["剩餘藝術深耕時數"] . "<br>";
                    } else {
                        echo "尚無藝術深耕時數資料。";
                    }
                    ?>
                </div> 
            </div>
        </div>
    </section>


    
    <script src="ScoreSearch.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>
</html>
