<?php

// Cambiar el estilo del formulario de inicio de sesión
function custom_login_styles() {
    ?>
    <style>
        /* Fondo del cuerpo */
        body.login {
            background-color: #f8f9fa; /* Color del fondo */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh; /* Ocupa todo el alto de la pantalla */
        }

        /* Quitar el margen de WordPress */
        body.login div#login {
            width: 100%;
            max-width: 400px; /* Ajusta el ancho del formulario */
            padding: 0;
        }

        .login h1 {
            margin: 0; /* Elimina el margen predeterminado */
            padding: 0;
        }

        .login h1 a {
            background-color: #0D457E; /* Fondo azul oscuro */
            background-image: url('<?php echo get_template_directory_uri(); ?>/images/headerEmmaus.png') !important;
            background-size: contain !important; /* Ajusta el tamaño del logo */
            background-repeat: no-repeat !important;
            background-position: center !important;
            width: 100% !important;
            height: 100px !important;
            border-top-left-radius: 10px; /* Bordes superiores redondeados */
            border-top-right-radius: 10px;
            border-bottom-left-radius: 10px; /* Quitar redondeado inferior */
            border-bottom-right-radius: 10px;
        }

        /* Tarjeta del formulario */
        #loginform {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        /* Campo de entrada */
        #loginform input[type="text"], 
        #loginform input[type="password"] {
            border: 1px solid #0D457E; /* Bordes azules */
            border-radius: 5px;
            padding: 10px;
            font-size: 14px;
            width: 100%; /* Ocupa todo el ancho del formulario */
            margin-bottom: 20px; /* Espacio entre los campos */
        }

        /* Botón de inicio de sesión */
        #wp-submit {
            background-color: #0D457E;
            color: #fff;
            border: none;
            border-radius: 5px;
            padding: 10px;
            font-size: 16px;
            width: 100%;
            text-transform: uppercase;
            cursor: pointer;
        }

        #wp-submit:hover {
            background-color: #07365A;
        }

        /* Texto de "Olvidaste tu contraseña" */
        .login #nav a {
            color: #0D457E;
            font-size: 14px;
            display: block;
            margin-top: 10px;
            text-align: center;
        }

        /* Logo adicional debajo del header */
        .custom-logo {
            text-align: center;
            margin-bottom: 20px;
        }

        .custom-logo img {
            max-width: 100%;
            height: auto;
            margin: 0 auto;
            display: block;
        }
        
        .custom-logo-container {
            background-color: #0D457E; /* Fondo azul oscuro */
            padding: 20px; /* Espaciado interno */
            border-bottom-left-radius: 10px; /* Bordes inferiores redondeados */
            border-bottom-right-radius: 10px;
            border-top-left-radius: 10px; /* Quitar redondeado superior */
            border-top-right-radius: 10px;
            display: flex;
            justify-content: center; /* Centrar horizontalmente el logo */
            align-items: center; /* Centrar verticalmente el logo */
            margin: 0; /* Eliminar cualquier margen */
        }

        /* Imagen del logo inferior */
        .custom-logo {
            max-width: 80%; /* Reducir el tamaño del logo a la mitad */
            height: auto; /* Mantener la proporción del logo */
        }
    </style>
    <?php
}
add_action('login_enqueue_scripts', 'custom_login_styles');

// Cambiar el título del logo en el formulario
function custom_login_logo_title() {
    return 'Emmaus Colombia';
}
add_filter('login_headertext', 'custom_login_logo_title');

// Cambiar la URL del logo
function custom_login_logo_url() {
    return home_url(); // Cambia esto si quieres una URL personalizada
}
add_filter('login_headerurl', 'custom_login_logo_url');

function custom_logo_in_form() {
    $logo_url = get_template_directory_uri() . '/images/logo.png';
    return '<div class="custom-logo-container">
                <img src="' . $logo_url . '" alt="Logo Genesis" class="custom-logo">
            </div>';
}
add_filter('login_message', 'custom_logo_in_form');

?>