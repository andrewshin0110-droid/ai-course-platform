// 選擇所有需要的元素
const commentModal = document.getElementById("commentModal"); // 提交評論彈窗
const allCommentsModal = document.getElementById("allCommentsModal"); // 查看所有評論彈窗
const commentButtons1 = document.querySelectorAll('.viewCommentsButton'); // 「評論」按鈕
const allCommentsButtons1 = document.querySelectorAll('.viewAllCommentsButton'); // 「查看所有評論」按鈕
const iconCloses = document.querySelectorAll('.icon-close'); // 關閉按鈕

function showSummaryButton(event) {
    // 获取当前按钮的课程 ID
    const courseId = event.target.getAttribute('data-course-id');

    // 根据课程 ID 找到对应的“總結”按钮
    const summaryButton = document.getElementById(`summary-${courseId}`);

    if (summaryButton) {
        // 显示“總結”按钮
        summaryButton.style.display = 'inline-block';
    }
}

// 點擊「評論」按鈕顯示提交評論的彈窗
commentButtons1.forEach(button => {
    button.addEventListener('click', () => {
        commentModal.style.transform = 'scale(1)'; // 顯示提交評論的彈窗
    });
});

// 點擊「查看所有評論」按鈕顯示所有評論的彈窗
allCommentsButtons1.forEach(button => {
    button.addEventListener('click', () => {
        allCommentsModal.style.transform = 'scale(1)'; // 顯示所有評論的彈窗
    });
});

// 點擊關閉按鈕隱藏彈窗
iconCloses.forEach(icon => {
    icon.addEventListener('click', () => {
        commentModal.style.transform = 'scale(0)'; // 隱藏提交評論彈窗
        allCommentsModal.style.transform = 'scale(0)'; // 隱藏所有評論彈窗
        document.getElementById('chatGPTSummary').innerHTML = ''; // 清空 ChatGPT 總結
        document.getElementById('modalComment').value = ''; // 清空輸入框
        document.getElementById('wordLimit').value = ''; // 清空輸入框
        const summaryDiv = document.getElementById("chatGPTSummary");
        summaryDiv.style.display = "none"; // 隱藏區塊
        const summaryButtons = document.querySelectorAll('.viewAllCommentsButton2');
    summaryButtons.forEach(button => {
        button.style.display = 'none';
    });

    });
});


// 切換不同區域的顯示
function showContent(wrapperId, contentId) {
    // 隱藏所有內容
    document.querySelectorAll(`#${wrapperId} .content`).forEach(content => {
        content.style.display = 'none';
    });

    // 顯示所選內容
    document.getElementById(contentId).style.display = 'block';

    // 更新按鈕樣式
    const buttons = document.querySelectorAll(`#${wrapperId} .search`);
    buttons.forEach(button => button.classList.remove('active')); // 清除其他按鈕的 active 樣式

    // 為當前按鈕添加 active 類
    const activeButton = Array.from(buttons).find(button =>
        button.textContent.trim() === (contentId === 'content1' ? '所有評論' : 'ChatGPT 來幫你')
    );
    if (activeButton) activeButton.classList.add('active');
}

// 彈窗彈出時的初始化
function openAllCommentsModal() {
    const allCommentsModal = document.getElementById("allCommentsModal");
    allCommentsModal.style.transform = 'scale(1)'; // 顯示彈窗
    showContent('wrapper1', 'content1'); // 默認顯示“所有評論”
}

// 添加關閉彈窗邏輯
function closeAllCommentsModal() {
    const allCommentsModal = document.getElementById("allCommentsModal");
    allCommentsModal.style.transform = 'scale(0)'; // 隱藏彈窗
    document.querySelectorAll('.search').forEach(button => button.classList.remove('active')); // 清除 active 樣式
}


// 預設顯示第一個區域
document.addEventListener("DOMContentLoaded", function() {
    document.getElementById('content1').style.display = 'block'; // 顯示所有評論內容
    document.getElementById('content2').style.display = 'none'; // 隱藏 ChatGPT 內容
});

// 獲取相關 DOM 元素

var commentButtons = document.getElementsByClassName("viewCommentsButton");
var allCommentsButtons = document.getElementsByClassName("viewAllCommentsButton");
var currentCourseId = null; // 用於存儲當前課程的 ID

// 關閉按鈕的邏輯
document.querySelectorAll('.close').forEach(function(closeBtn) {
    closeBtn.onclick = function() {
        commentModal.style.display = "none";
        allCommentsModal.style.display = "none";
    };
});

// 點擊「評論」按鈕時顯示評論彈窗
for (var i = 0; i < commentButtons.length; i++) {
    commentButtons[i].addEventListener("click", function() {
        currentCourseId = this.getAttribute("data-course-id");
        commentModal.style.display = "block"; // 顯示評論彈窗
        allCommentsModal.style.display = "none"; // 確保查看評論彈窗關閉
    });
}

// 提交評論
document.getElementById('submitModalComment').addEventListener('click', function() {
            var userComment = document.getElementById('modalComment').value;
            
            // 创建一个 XMLHttpRequest 对象
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'submit_comment.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            xhr.onload = function() {
            if (xhr.status === 200) {
                var response = xhr.responseText;

                // 根据服务器返回的内容判断是否显示警告
                if (response.includes("警告：")) {
                    alert(response); // 弹窗显示警告信息
                } else {
                    alert(response); // 显示评论提交成功信息
                }
            } else {
                alert('错误: 无法提交评论。');
            }
        };

            // 发送请求并传递课程ID和用户评论
            xhr.send('course_id=' + currentCourseId + '&comment=' + encodeURIComponent(userComment));
        });

// 點擊「查看所有評論」按鈕時顯示所有評論彈窗
for (var i = 0; i < allCommentsButtons.length; i++) {
    allCommentsButtons[i].addEventListener("click", function() {
        var courseId = this.getAttribute("data-course-id");
        currentCourseId = courseId;

        // 發送請求獲取評論
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "get_comments.php", true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.onload = function() {
            if (xhr.status == 200) {
                var comments = JSON.parse(xhr.responseText);
                document.getElementById('content1').innerHTML = ""; // 清空之前的評論
                if (comments.length > 0) {
                    comments.forEach(function(comment) {
                        var commentDiv = document.createElement("div");
                        commentDiv.textContent = comment;
                        document.getElementById('content1').appendChild(commentDiv);
                    });
                } else {
                    document.getElementById('content1').textContent = "尚無評論";
                }
                allCommentsModal.style.display = "block"; // 顯示所有評論彈窗
                commentModal.style.display = "none"; // 確保評論彈窗關閉
            }
        };
        xhr.send("course_id=" + currentCourseId);
    });
}


//chatgpt評論
function handleSummarize(event) {
    var wordLimit = document.getElementById('wordLimit').value;

    // 检查字数限制的有效性
    if (!wordLimit || wordLimit <= 0) {
        alert("請輸入一個有效的總結數字！");
        return;
    }

    // 获取课程ID
    var courseId = event.target.getAttribute("data-course-id");

    // 发送请求以获取评论
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "get_comments.php", true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr.onload = function() {
        if (xhr.status == 200) {
            var comments = JSON.parse(xhr.responseText);
            sendCommentsToChatGPT(comments, wordLimit);
        } else {
            console.error("獲取評論失敗，狀態碼:", xhr.status);
        }
    };
    xhr.send("course_id=" + courseId);
}

function sendCommentsToChatGPT(comments, wordLimit) {
    console.log("Sending comments to ChatGPT:", comments);
    console.log("Word Limit:", wordLimit);

     // 显示提示信息，告知用户正在生成总结
    document.getElementById('chatGPTSummary').innerHTML = '<p>正在生成總結，請稍後...</p>';
    document.getElementById('content2').style.display = 'block'; 
    document.getElementById('chatGPTSummary').style.display = 'block';


    var xhr = new XMLHttpRequest();
    xhr.open("POST", "chatgpt_integration.php", true);
    xhr.setRequestHeader("Content-type", "application/json");

    xhr.onload = function() {
        if (xhr.status === 200) {
            console.log("Full ChatGPT Response:", xhr.responseText);  // 检查完整响应
            try {
                var response = JSON.parse(xhr.responseText);
                console.log("Parsed ChatGPT Response:", response);

                if (response.error) {
                    document.getElementById('chatGPTSummary').textContent = "錯誤: " + response.error;
                } else {
                    if (response.summary === "尚無評論") {
                    // 如果 ChatGPT 返回尚无评论，则显示该消息
                    var summaryHtml = `<p>${response.summary}</p>`;
                    } else if (response.summary) {
                    // 如果有总结，则显示总结内容
                    var summaryHtml = `<h4>總結:</h4><p>${response.summary || "無總結內容"}</p>`;
                    }

                    // 更新 HTML 并显示
                    document.getElementById('chatGPTSummary').innerHTML = summaryHtml;
                    document.getElementById('chatGPTSummary').style.display = 'block'; // 确保显示区域
                }
            } catch (e) {
                console.error("解析響應時發生錯誤: " + e.message);
                document.getElementById('chatGPTSummary').textContent = "解析響應時發生錯誤";
            }
        } else {
            console.error("HTTP 錯誤: " + xhr.status);
            document.getElementById('chatGPTSummary').textContent = "HTTP 請求失敗，狀態碼: " + xhr.status;
        }
    };

    xhr.onerror = function() {
        console.error("請求失敗");
        document.getElementById('chatGPTSummary').textContent = "請求失敗，請稍後再試。";
    };

    // 增加日志以调试问题
    console.log("Sending data: ", JSON.stringify({ comments: comments, word_limit: wordLimit }));

    // 发送请求
    xhr.send(JSON.stringify({ comments: comments, word_limit: wordLimit }));
}