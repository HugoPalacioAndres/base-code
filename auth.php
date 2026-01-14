<?php

/**Comprueba si la inicion esta iniciada si no lo esta la inicia */
function iniciar_sesion_si_es_necesario(){
    if(session_status() === PHP_SESSION_NONE){
        session_start();
    }
}
/** Redige el nav de usuario a otra pagina  utilizando la cabecera location */
function redirigir($url){
    header('Location: ' . $url);
    exit;
}

/**Comprueba si el usuario tiene inciada sesion si la tiene se le envia a events si no no hace nada */
function redirigir_si_esta_autenticado(){
    iniciar_sesion_si_es_necesario();
    if(isset($_SESSION['id_usuario'])){
        redirigir('events.php');
    }
}

/**comprueba si el usuario tiene iniciada sesion  si no lo tiene redirige aindex si la tiene no hace nada */
function requerir_autenticacion(){
    iniciar_sesion_si_es_necesario();
    if(!isset($_SESSION['id_usuario'])){
        redirigir('index.php');
    }
}

/**Cierra la sesion del usuario de forma segura */
function cerrar_sesion():void{
    iniciar_sesion_si_es_necesario();

    /**Vaciado de datos de secion */
    $_SESSION = [];

    /**Destruir cookie de sesion si existe */
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie (
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }
    session_destroy();
}
