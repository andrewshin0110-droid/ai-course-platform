<?php
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

// 獲取表單提交的數據
$course_id = !empty($_POST['course_id']) ? $_POST['course_id'] : null;
$teacher_id = !empty($_POST['teacher_id']) ? $_POST['teacher_id'] : null;
$subject_id = !empty($_POST['subject_id']) ? $_POST['subject_id'] : null;
$start_time = !empty($_POST['start_time']) && $_POST['start_time'] != '0' ? $_POST['start_time'] : null;
$end_time = !empty($_POST['end_time']) && $_POST['end_time'] != '0' ? $_POST['end_time'] : null;
$week_day = !empty($_POST['week_day']) && $_POST['week_day'] != '0' ? $_POST['week_day'] : null;

// 映射時間值到實際時間
$time_map_start = [
    '1' => '08:10',
    '2' => '09:10',
    '3' => '10:10',
    '4' => '11:10',
    '5' => '12:10',
    '6' => '13:10',
    '7' => '14:10',
    '8' => '15:10',
    '9' => '16:10',
    '10' => '17:10',
    '11' => '18:10',
    '12' => '19:10',
    '13' => '20:10',
    '14' => '21:10'
];

$time_map_end = [
    '1' => '09:00',
    '2' => '10:00',
    '3' => '11:00',
    '4' => '12:00',
    '5' => '13:00',
    '6' => '14:00',
    '7' => '15:00',
    '8' => '16:00',
    '9' => '17:00',
    '10' => '18:00',
    '11' => '19:00',
    '12' => '20:00',
    '13' => '21:00',
    '14' => '22:00'
];

// 動態SQL查詢
$sql = "
    SELECT 
        o.開課序號, o.教師統編, o.科目序號, 
        CONCAT(o.開始, ' - ', o.結束) AS 上課時間,
        c.課程名稱, c.學分, c.年級, c.`必選修通識`, c.領域,
        t.教師名稱, t.教師職稱, t.教師電子郵件
    FROM 
        `開課` o
    LEFT JOIN 
        `課程` c ON o.科目序號 = c.科目序號
    LEFT JOIN
        `教師` t ON o.教師統編 = t.教師編號
    WHERE 1=1
";

// 添加條件
if ($course_id) {
    $sql .= " AND o.開課序號 = '" . $conn->real_escape_string($course_id) . "'";
}

if ($teacher_id) {
    $sql .= " AND o.教師統編 = '" . $conn->real_escape_string($teacher_id) . "'";
}

if ($subject_id) {
    $sql .= " AND o.科目序號 = '" . $conn->real_escape_string($subject_id) . "'";
}

// 處理時間範圍
$start_time_str = $start_time ? $time_map_start[$start_time] : null;
$end_time_str = $end_time ? $time_map_end[$end_time] : null;

// 確保課程的時間符合選擇的時間範圍
if ($start_time_str && $end_time_str) {
    $sql .= " AND o.開始 >= '$start_time_str' AND o.結束 <= '$end_time_str'";
} elseif ($start_time_str) {
    $sql .= " AND o.開始 >= '$start_time_str'";
} elseif ($end_time_str) {
    $sql .= " AND o.結束 <= '$end_time_str'";
}

if ($week_day) {
    $week_days_map = ['1' => '星期一', '2' => '星期二', '3' => '星期三', '4' => '星期四', '5' => '星期五', '6' => '星期六', '7' => '星期日'];
    $week_day_str = $week_days_map[$week_day];
    $sql .= " AND o.星期 = '$week_day_str'";
}

// 執行查询
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>課程查詢</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel="stylesheet"/>
    <link rel="stylesheet" href="Search.css">
</head>
<body>
    
<section class="home">
  <div class="toggle-sidebar">
    <div class="text">課程查詢</div>
  </div>

  <form action="Search.php" method="POST" class="form-container">
    <div class="inputbox-cs">
      <input type="text" id="course_id" name="course_id">
      <label for="course_id">開課序號:</label>
    </div>

    <div class="inputbox-cs">
      <input type="text" id="teacher_id" name="teacher_id">
      <label for="teacher_id">教師編號:</label>
    </div>

    <div class="inputbox-cs">
      <input type="text" id="subject_id" name="subject_id">
      <label for="subject_id">科目序號:</label>
    </div>

    <div class="custom-select">
      <label>上課時間(開始時間)</label>
      <select name="start_time" class="style">
        <option value="0">-請選擇-</option>
        <option value="1">(8:00)</option>
        <option value="2">(9:00)</option>
        <option value="3">(10:00)</option>
        <option value="4">(11:00)</option>
        <option value="5">(12:00)</option>
        <option value="6">(13:00)</option>
        <option value="7">(14:00)</option>
        <option value="8">(15:00)</option>
        <option value="9">(16:00)</option>
        <option value="10">(17:00)</option>
        <option value="11">(18:00)</option>
        <option value="12">(19:00)</option>
        <option value="13">(20:00)</option>
        <option value="14">(21:00)</option>
      </select>
    </div>

    <div class="custom-select">
      <label>上課時間(結束時間)</label>
      <select name="end_time" class="style">
        <option value="0">-請選擇-</option>
        <option value="1">(9:00)</option>
        <option value="2">(10:00)</option>
        <option value="3">(11:00)</option>
        <option value="4">(12:00)</option>
        <option value="5">(13:00)</option>
        <option value="6">(14:00)</option>
        <option value="7">(15:00)</option>
        <option value="8">(16:00)</option>
        <option value="9">(17:00)</option>
        <option value="10">(18:00)</option>
        <option value="11">(19:00)</option>
        <option value="12">(20:00)</option>
        <option value="13">(21:00)</option>
        <option value="14">(22:00)</option>
      </select>
    </div>

    <div class="custom-select">
      <label>上課時間(星期)</label>
      <select name="week_day" class="style">
        <option value="0">-請選擇-</option>
        <option value="1">星期一</option>
        <option value="2">星期二</option>
        <option value="3">星期三</option>
        <option value="4">星期四</option>
        <option value="5">星期五</option>
        <option value="6">星期六</option>
        <option value="7">星期日</option>
      </select>
    </div>

    <input type="submit" value="搜尋" class="btn-cs">
  </form>

  <div class="wrap-cs" id="wrapContainer">
    <?php
    if ($result->num_rows > 0) {
      // 输出查詢结果
      echo "<table class='styled-table' id='courseTable'>";
      echo "<tr><th>開課序號</th><th>教師編號</th><th>科目序號</th><th>上課時間</th><th>課程名稱</th><th>學分</th><th>年級</th><th>必選修通識</th><th>領域</th><th>教師名稱</th><th>教師電子郵件</th></tr>";
      while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row["開課序號"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["教師統編"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["科目序號"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["上課時間"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["課程名稱"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["學分"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["年級"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["必選修通識"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["領域"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["教師名稱"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["教師電子郵件"]) . "</td>";
        echo "</tr>";
      }
      echo "</table>";
    } else {
      echo "未找到相關課程";
    }
    // 關閉數據庫連接
    $conn->close();
    ?>
  </div>

  <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
  <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</section>

</body>
</html>





