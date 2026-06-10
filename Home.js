const listitem = document.querySelectorAll(".sidebar-list li");

listitem.forEach(item=>{
    item.addEventListener("click" , () =>{
        let isActive = item.classList.contains("active");

        listitem.forEach((el) =>{
            el.classList.remove("active");
        });

        if(isActive)item.classList.remove("active")
            else item.classList.add("active")
    });
});

const logo = document.querySelector(".logo-box");
const sidebar = document.querySelector(".sidebar");

logo.addEventListener("click", () => {
    sidebar.classList.toggle("close");
});




