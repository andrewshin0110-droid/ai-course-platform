<?php
session_start();

if (!isset($_SESSION['user'])) {
    echo "使用者尚未登錄";
    exit();
}

$user = $_SESSION['user'];

  $student_id = $user['學號'];
  $student_major = $user['學系名稱'];


$servername = "localhost";
$username = "root";
$password = "";
$dbname = "course"; // 数据库名称

// 创建连接
$conn = new mysqli($servername, $username, $password, $dbname);

// 检查连接
if ($conn->connect_error) {
    die("连接失败: " . $conn->connect_error);
}

// 获取用户输入的课程名称
$course_name = isset($_POST['course_name']) ? $conn->real_escape_string($_POST['course_name']) : '';

// SQL 查询
$sql = "
    SELECT 
        o.開課序號, o.教師統編, o.科目序號, 
        c.課程名稱, t.教師名稱
    FROM 
        `開課` o
    LEFT JOIN 
        `課程` c ON o.科目序號 = c.科目序號
    LEFT JOIN
        `教師` t ON o.教師統編 = t.教師編號
    WHERE 1=1
";

// 如果有搜索关键字，则使用 LIKE 进行模糊匹配
if (!empty($course_name)) {
    $sql .= " AND c.課程名稱 LIKE '%$course_name%'";
}

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>課程論壇</title>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="Home.css">
    <link rel="stylesheet" href="CourseForum.css"> <!-- 引入自定義的 CSS 文件 -->
</head>
<body>
<div class="sidebar close">
    <a href="#" class="logo-box">
        <i class='bx bx-menu'></i>
        <div class="logo-name">長庚大學ICGU</div>
    </a>

    <ul class="sidebar-list">
        <li>
            <div class="title">
                <a href="/website/Home.php" class="link">
                    <i class='bx bxs-home'></i>
                    <span class="name">首頁</span>
                </a>
            </div>
        </li>

        <!-- 課程查詢 -->
        <li class="dropdown">
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

        <li class="dropdown">
            <div class="title">
                <a href="#" class="link">
                    <i class='bx bx-book-open'></i>
                    <span class="name">成績查詢</span>
                </a>
                <i class='bx bx-chevron-down'></i>
            </div>
            <div class="submenu">
                <a href="#" class="link submenu-title">成績查詢</a>
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
                <a href="/website/CourseForum.php" class="link">
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
        <div class="text">課程論壇</div>
    </div>

    <header>
        <nav class="navigation1">
            <a href="#"><i class='bx bx-phone'></i><span>聯絡窗口</span></a>
            <a href="#"><i class='bx bx-globe' style="margin-left: 13px;"></i><span>中文</span></a>
            <a href="logout.php"><i class='bx bx-log-out' style="margin-left: 13px;"></i><span>登出</span></a>
        </nav>
    </header>

    <!-- 表單 -->
    <form id="courseForm" method="POST" action="fetch_courses.php" target="_blank">
        <div class="custom-select">
            <label for="course_name">課程名稱</label>
            <input type="text" id="course_name" name="course_name" placeholder="輸入課程名稱...">
        </div>
        <button type="submit" class="btn-cf">搜尋</button>
    </form>

    <!-- 使用說明 -->
    <div class="wrap-cf">
        <p>
            <strong style="font-size: 25px">使用說明</strong> <br>
            <strong>step1: 在課程搜尋欄中輸入課程名稱<br>
            step2: <br>
            step3: <br>
            step4:</strong>
        </p>
    </div>
</section>

<script src="Home.js"></script>
<script src="CourseForum.js"></script>
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>
</html>
