// scripts.js
function showContent(wrapperId, contentId) {
    // Hide all content elements in the wrapper
    const wrapper = document.getElementById(wrapperId);
    const contents = wrapper.querySelectorAll('.content');
    contents.forEach(content => {
        content.style.display = 'none';
    });

    // Show the selected content
    const contentToShow = document.getElementById(contentId);
    if (contentToShow) {
        contentToShow.style.display = 'block';
    }
}

 // JavaScript 动态调整 padding-top
 



    // 更新按鈕樣式
  