document.addEventListener("DOMContentLoaded",()=>{


    /* =====================================
       SIDEBAR
    ===================================== */

    const toggleBtn=document.getElementById("toggle-btn");
    const sidebar=document.getElementById("sidebar");
    const contenido=document.querySelector(".contenido");


    /* Abrir/cerrar sidebar */
    toggleBtn.addEventListener("click",()=>{

        sidebar.classList.toggle("collapsed");

        contenido.classList.toggle("collapsed");

    });



    /* =====================================
       SUBMENÚ PRODUCTOS
    ===================================== */

    const submenu=document.querySelector(".submenu");
    const submenuToggle=document.querySelector(".submenu-toggle");


    /* Abrir/cerrar submenú */
    submenuToggle.addEventListener("click",(e)=>{

        /* Evita navegación */
        e.preventDefault();

        /* Evita propagación */
        e.stopPropagation();

        /* Agrega o quita clase */
        submenu.classList.toggle("open");

    });



    /* =====================================
       CERRAR MENÚ AL HACER CLICK FUERA
    ===================================== */

    document.addEventListener("click",(e)=>{

        /* Si el clic no ocurrió dentro */
        if(!submenu.contains(e.target)){

            submenu.classList.remove("open");

        }

    });


});


// Mostrar preview de imagen antes de subir
function previewImage(event) {
    const reader = new FileReader();

    reader.onload = function(){
        const output = document.getElementById('preview');
        output.src = reader.result;
        output.style.display = "block"; // 👈 mostrar la imagen
    };

    reader.readAsDataURL(event.target.files[0]);
}

    