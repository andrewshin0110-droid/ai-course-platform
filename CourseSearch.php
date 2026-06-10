<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>課程查詢</title>
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel="stylesheet"/>
  <link rel="stylesheet" href="Home.css">
  <link rel="stylesheet" href="CourseSearch.css">
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
            <div class="text">課程查詢</div>
        </div>
        <header>
            <nav class="navigation1">
                <a href="">
                    <i class='bx bx-phone'></i>
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
                    <option value="1">一</option>
                    <option value="2">二</option>
                    <option value="3">三</option>
                    <option value="4">四</option>
                    <option value="5">五</option>
                    <option value="6">六</option>
                    <option value="7">日</option>
                </select>
            </div>

            <input type="submit" value="搜尋" class="btn-cs">
        </form>

    </section>

   

    <script src="Home.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>
</html>