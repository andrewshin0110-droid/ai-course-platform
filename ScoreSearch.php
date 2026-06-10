<?php

session_start();

if (!isset($_SESSION['user'])) {
    echo "使用者尚未登錄";
    exit();
}

$user = $_SESSION['user'];
?>


<?php
  $student_id = $user['學號'];
  $student_major = $user['學系名稱'];
  

// 数据库连接设置
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "course"; // 数据库名称

// 创建连接
$conn = new mysqli($servername, $username, $password, $dbname);

// 检查连接
if ($conn->connect_error) {
    die("連接失敗: " . $conn->connect_error);
}

//////////////////////////查詢修課紀錄////////////////////////////

$sql1 = "
  SELECT
    r.開課序號 AS 開課序號,
    c.課程名稱 AS 課程名稱,
    c.學分 AS 學分,
    r.成績 AS 成績,
    c.必選修通識 AS 必選修,
    c.年級 AS 年級,
    c.領域 AS 領域,
    o.學年學期 AS 學年學期, -- 从开课表中抓取学年学期
    r.備註
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

  $stmt1 = $conn->prepare($sql1);
  $stmt1->bind_param("s", $student_id);
  $stmt1->execute();
  $result1 = $stmt1->get_result();

?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>成績查詢</title>
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css'
  rel="stylesheet"/>
  <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
  <link rel="stylesheet" href="Home2.css">
  <link rel="stylesheet" href="ScoreSearch.css">
</head>
<body>
<div class="sidebar close ">
  <a href="#" class="logo-box">
    <i class='bx bx-menu'></i>
    <div class="logo-name">長庚大學ICGU</div>
  </a>
    <!--=================list=======================-->

    <ul class="sidebar-list">
    <!--================nonedropdownlist=====================-->
        <li>
            <div class="title">
              <a href="/website/Home.php" class="link">
                <i class='bx bxs-home'></i>
                <span class="name">首頁</span>
              </a>
            </div>

            <div class="submenu">
              <a href="#" class="link submenu-title">首頁</a>
            </div>
        </li>
        <!--=====================dropdownlist======================-->
        <li class="dropdown ">
          <div class="title">
            <a href="/website/CourseSearch.php" class="link" target="_blank">
              <i class='bx bx-book-open'></i>
              <span class="name">課程查詢</span>
            </a>
            <i class='bx bx-chevron-down'></i>
          </div>

          <div class="submenu">
            <a href="#" class="link submenu-title">課程查詢</a>
            <a href="#" class="link">系所必選修科目</a>
            <a href="#" class="link">課程預選</a>
            <a href="#" class="link">通識釋放名額</a>
          </div>
      </li>

      <li>
        <div class="title">
          <a href="" class="link">
            <i class='bx bx-list-check'></i>
            <span class="name">研究成果登錄</span>
          </a>
        </div>

        <div class="submenu">
          <a href="#" class="link submenu-title">研究成果登錄</a>
        </div>
    </li>

    <li>
      <div class="title">
        <a href="" class="link">
          <i class='bx bx-directions'></i>
          <span class="name">學務E點通</span>
        </a>
      </div>

      <div class="submenu">
        <a href="#" class="link submenu-title">學務E點通</a>
      </div>
   </li>

   <li class="dropdown ">
          <div class="title">
            <a href="#" class="link" >
              <i class='bx bx-book-open'></i>
              <span class="name">成績查詢</span>
            </a>
            <i class='bx bx-chevron-down'></i>
          </div>

          <div class="submenu">
            <a href="#" class="link submenu-title" >成績查詢</a>
            <a href="/website/ScoreSearch.php" class="link">在校成績查詢</a>
            <a href="/website/ScoreSearch2.php" class="link" target="_blank">畢業資格查詢</a>
          </div>
      </li>

  <li>
    <div class="title">
      <a href="" class="link">
        <i class='bx bx-printer'></i>
        <span class="name">單據列印</span>
      </a>
    </div>

    <div class="submenu">
      <a href="#" class="link submenu-title">單據列印</a>
    </div>
</li>

<li>
  <div class="title">
    <a href="" class="link">
      <i class='bx bx-file'></i>
      <span class="name">各項申請/查詢</span>
    </a>
  </div>

  <div class="submenu">
    <a href="#" class="link submenu-title">各項申請/查詢</a>
  </div>
</li>

<li>
  <div class="title">
    <a href="" class="link">
      <i class='bx bx-notepad'></i>
      <span class="name">個人資料</span>
    </a>
  </div>

  <div class="submenu">
    <a href="#" class="link submenu-title">個人資料</a>
  </div>
</li>

<li>
  <div class="title">
    <a href="/website/CourseForum.php" class="link" >
      <i class='bx bx-chat'></i>
      <span class="name">課程論壇</span>
    </a>
  </div>

  <div class="submenu">
    <a href="#" class="link submenu-title">課程論壇</a>
  </div>
</li>

<li>
  <div class="title">
    <a href="https://chatgpt.com/g/g-67443aced4b08191a9c72e36a1ee693a-chang-geng-da-xue-zi-guan-xi-zhi-hui-xuan-ke-zhu-shou" class="link"  target="_blank">
      <i class='bx bx-selection'></i>
      <span class="name">智能選課</span>
    </a>
  </div>

  <div class="submenu">
    <a href="#" class="link submenu-title">智能選課</a>
  </div>
</li>
    </ul>

</div>

<section class="home">
  <div class="toggle-sidebar">
    <div class="text">成績查詢</div>
  </div>
  <header>
    <nav class="navigation1">
      <a href="">
        <i class='bx bx-phone' ></i>
        <span>聯絡窗口</span>
      </a>
      <a href="">
        <i class='bx bx-globe' style="margin-left: 13px;"></i>
        </span>
        <span>中文</span>
      </a>
      <a href="logout.php">
          <i class='bx bx-log-out' style="margin-left: 13px;"></i>
          <span>登出</span>
        </a>
    </nav>
   </header>

  

   <div class="wrap-ss" id="wrapContainer" style="   padding: 30px;    box-sizing: border-box;">
   
  <?php 
  
    if ($result1->num_rows > 0) {
      echo "<table class='styled-table' id='courseTable'>";
        echo "<tr><th>學年學期</th><th>開課序號</th><th>課程名稱</th><th>學分</th><th>成績</th><th>必選修</th><th>領域</th><th>備註</th></tr>";
        while ($row = $result1->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row["學年學期"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["開課序號"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["課程名稱"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["學分"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["成績"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["必選修"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["領域"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["備註"]) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "没有找到修课记录";
    }

   
?>

<button id="downloadBtn" style="margin-top: 20px;">下載修課資料</button>
  
   </div>



   
</section>

<script>
    document.getElementById("downloadBtn").addEventListener("click", function() {
    window.location.href = '/website/download.php'; // 觸發檔案下載
    });
    </script>



   <script src="Home.js"></script>
   <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
   <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>
</html>

