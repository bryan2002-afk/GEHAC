document.addEventListener("DOMContentLoaded",()=>{

    /* =====================================
       SIDEBAR
    ===================================== */

    const toggleBtn=document.getElementById("toggle-btn");
    const sidebar=document.getElementById("sidebar");
    const contenido=document.getElementById("contenido");


    /* Recuperar estado guardado */
    const sidebarEstado=localStorage.getItem("sidebar");

    if(sidebarEstado==="collapsed"){

        sidebar.classList.add("collapsed");
        contenido.classList.add("collapsed");

    }


    /* Abrir/cerrar sidebar */
    toggleBtn.addEventListener("click",()=>{

        sidebar.classList.toggle("collapsed");

        contenido.classList.toggle("collapsed");


        /* Guardar estado */
        if(sidebar.classList.contains("collapsed")){

            localStorage.setItem(
                "sidebar",
                "collapsed"
            );

        }else{

            localStorage.setItem(
                "sidebar",
                "expanded"
            );

        }

    });


    /* =====================================
       SUBMENU
    ===================================== */

    const submenu=document.querySelector(".submenu");
    const submenuToggle=document.querySelector(".submenu-toggle");

    submenuToggle.addEventListener("click",(e)=>{

        e.preventDefault();
        e.stopPropagation();

        submenu.classList.toggle("open");

    });


    /* =====================================
       CERRAR SUBMENU
    ===================================== */

    document.addEventListener("click",(e)=>{

        if(!submenu.contains(e.target)){

            submenu.classList.remove("open");

        }

    });

});