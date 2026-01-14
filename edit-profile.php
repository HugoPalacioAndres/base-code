<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/basecode/data-access/CalendarAtaAccess.php';
require_once __DIR__ . '/base-code/entities/User.php';

/**Exigimos que autenticacion */
requerir_autenticacion();

/**Creamos acceso a la BD */
$ruta_bd = __DIR__ . '/base-code/calendar.db';
$acceso_datos = new CalendarDataAccess($ruta_bd);

/**Obtenemos id de usuario y cargamos objeto user */
$id_usuario = $_SESSION['id_usuario'];
$usuario = $acceso_datos -> getUserById($id_usuario);

if($usuario === null){
    /**Si el usuario no existe  se fuerza logout*/
    redirigir('logout.php');
    exit;
}

/**Variables para el formulario rellenas inicialmente con los datos actuales */
$email = $usuario -> getEmail();
$nombre = $usuario -> getFirstName();
$apellidos = $usuario -> getLastName();
$fecha_nac = $usuario -> getBirthDate();
$about = $usuario -> getAbout();

$errores = [];
$mensaje_exito='';

/**Procesar su el formulario llega por post */
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    /**recoger datos y hacer trim (quitar espacios) */
    $email = trim($_POST['email'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $apellidos = trim($_POST['apellidos'] ?? '');
    $fecha_nac = trim($_POST['fecha_nac'] ?? '');
    $about = trim($_POST['about'] ?? '');

    /**Validaciones */
    /**correo relleno y formato */
    if ($email == ''){
        $errores[] = 'El correo electronico es obligatorio.';
    }elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $errores[] = 'El formato del correo electronico no es valido.';
    }

    /**nombre obligatorio */
    if($nombre === ''){
        $errores[] = 'El nombre es obligatorio.';
    }

    /** */


}
