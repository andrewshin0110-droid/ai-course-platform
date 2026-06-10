<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>首頁</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel="stylesheet"/>
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
                    <a href="/website/Home-unlogin.php" class="link">
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
                    <a href="/website/CourseSearch-unlogin.php" class="link" target="_blank">
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
                <button class="btnlogin-popup">登入</button>
            </nav>
        </header>

        <div class="wrapper">
            <span class="icon-close">
                <ion-icon name="close-outline"></ion-icon>
            </span>
            <div class="form-box login">
                <h2>登入</h2>
                <form action="login.php" method="post">
                    <div class="inputbox">
                        <ion-icon name="mail-outline"></ion-icon> 
                        <input type="text" name="username" required>
                        <label>學號</label>
                    </div>

                    <div class="inputbox">
                        <ion-icon name="lock-closed-outline"></ion-icon>
                        <input type="password" name="password" required>
                        <label>密碼</label>
                    </div>

                    <div class="rember-forget">
                        <label><input type="checkbox">記住我</label>
                        <a href="#">忘記密碼?</a>
                    </div>
                    <button type="submit" class="btn">登入</button>
                </form>
            </div>
        </div>

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
    </section>

    <script src="Home.js"></script>
    <script src="login.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>
</html>
