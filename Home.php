<?php
session_start(); // 启动会话

// 检查用户是否登录
if (!isset($_SESSION['user'])) {
    header("Location: Home-unlogin.php"); // 未登录则重定向到登录页面
    exit();
}

// 获取用户信息
$user = $_SESSION['user'];
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>首頁</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel="stylesheet" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="Home.css">
</head>
<body>
    <div class="sidebar close">
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
            <div class="text">歡迎使用ICGU</div>
        </div>
        <header>
            <nav class="navigation1">
                <a href="">
                    <i class='bx bx-phone'></i>
                    <span>聯絡窗口</span>
                </a>
                <a href="">
                    <i class='bx bx-globe' style="margin-left: 13px;"></i>
                    <span>中文</span>
                </a>
                <a href="logout.php">
                    <i class='bx bx-log-out' style="margin-left: 13px;"></i>
                    <span>登出</span>
                </a>
            </nav>
        </header>

        <div class="wrapper1">
            <div style="font-size: 25px; font-weight: 800; position: relative; bottom: 35%; right: -5%;">注意事項</div>
        </div>

        <div class="wrapper2">
            <p>
                <strong style="font-size: 25px">登入說明</strong> <br />
                <strong>輸入本校Microsoft365帳號<br />
                學生：學號@cgu.edu.tw<br />
                教職員：員工編號@cgu.edu.tw<br />
                Microsoft365 密碼為單一登入密碼</strong>
            </p>
        </div>

        <div class="wrapper3">
            <p>
                <strong style="font-size: 20px">學生資訊</strong> <br />
                姓名: <?php echo htmlspecialchars($user['姓名']); ?> <br />
                學系: <?php echo htmlspecialchars($user['學系名稱']); ?> <br />
                學號: <?php echo htmlspecialchars($user['學號']); ?>
            </p>
        </div>
    </section>

    <script src="Home.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>
</html>
