<?php
/**Pagina para cambiar la contraseña de ususario conectado
 * Nesito:
 * Sesion inciada,
 * Cargar a usuario actual dede la BD con getUserById()
 * Muestra de form contra actual, nueva contra y repetir nueva contra
 * Validaciones contraseñas
 * Si todo esta correcto actualizo has en BD con updateUser()
 * Mostrar mensaje de exito o error y redireccion a events.php
 */
requiere_once __DIR__ . 'auth.php';
requier_once __DIR__ . '/base-code/data-access/CalendarDataAccess.php';
requier_once __DIR__ . '/base-code/entities/User.php';

/**Exijo que usuario este autenticado */
requerir_autenticacion();

/**Accesoa a BBDD */
$ruta_bd = __DIR__ . '/base-code/calendar.db';
$acceso_datos= new CalendarDataAccess($ruta_bd);

/**Obtencion del id del ussu logueado */
$id_usuario = $_SESSION['id_usuario'];

/**Recuperacion del usu actual desde la BD */
$usuario = $acceso_datos -> getUserById($id_usuario);/**Metodo de CalendarDataAccess */

/**Caso raro pero posible que haya sesion pero no usuario en la BD
 * se forzara el logout
 */
if($usuario === null ){
    redirigir('logout.php');
    exit
}

/**Variables para formulario y errores */
$errores=[]
$mensaje_exito='';
$contraseña_actual='';
$nueva_contraseña='';
$repetir_nueva_contraseña='';

/**Procesar form si llega por POST */
if($_SERVER['REQUEST_METHOD']==='POST'){
    /**Recogemos datos del form su no llegan, cadena vacia */
    $contraseña_actual=$_POST['password_actual'] ?? '';
    $nueva_contraseña=$_POST['password_nueva'] ?? '';
    $repetir_nueva_contraseña=$_POST['password_repetida'] ?? '';

    /**Validaciondes de presencia */
    if($contraseña_actual === ''){
        $errores[]='La contraseña actual es obligatoria.';
    }
    if($nueva_contraseña === ''){
        errores[]='La nueva contraseña es obligatoria.';
    }
    if($repetir_nueva_contraseña === ''){
        $errores[]='Debe repetir la nueva contraseña.';
    }

    /**Si hay errores de campos vacios no sigo con el resto */
    if(empty($errores)){
        /**En la bd se guarda el hash en la column password
         * la obtendo con el metodo getPaassword()*/
        $hash_bd = $usuario->getPassword();

        if(!password_verify($contraseña_actual, $hash_bd)){
            $errores[]='La contraseña actual no es correcta.';
        }
        /**Validacion nueva contra, minimo 8 char, una letra y un num */
        if(strlen($nueva_contraseña) < 8){
            $errores[] = 'La nueva contrasela debe tener al menos 8 caracteres.';
        }
        if(!preg_match('/[A-Za-z]/', $nueva_contraseña)){
            $errores[]='La nueva contraseña debe contenner por lo menos una letra.';
        }
        if(!preg_match('/[0-9]/', $nueva_contraseña)){
            $errores[] = 'Las nuevas contraseñas no coinciden.';
        }
        /**Comprobamos que la nueva contra coincida con la repetida */
        if(empty($errores)){
            /**Calcular hash de la nueva contraseña */
            $nuevo_hash = password_hash($nueva_contraseña, PASSWORD_DEFAULT);

            /**Actualizar el objeto usuario y guardarlo con updateUser() */
            $usuario -> setPassword($nuevo_hash);
            $ok = $acceso_datos -> updateUser($usuario);

            if($ok){
                /**Evento critico en session -> recomendable regenerar ID */
                session_regenerate_id(true);
                $mensaje_exito = 'Contrasela modificada correctamente.';

                $contraseña_actual = $nueva_contraseña = $repetir_nueva_contraseña = '';
            }else {
                errores[] ='Se ha producido un error al cactualizar la contraseña en la base de datos';
            }

        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <!-- Responsive -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar contraseña - Calendario</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-warning mb-4">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">
                <i class="fas fa-key"></i> Cambiar contraseña
            </span>
            <a href="events.php" class="btn btn-outline-light btn-sm">
                Volver a mis eventos
            </a>
        </div>
    </nav>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">Modificar contraseña</h5>
                    </div>
                    <div class="card-body">
                        <!-- Mensaje de éxito si la contraseña se ha cambiado -->
                        <?php if ($mensaje_exito !== ''): ?>
                            <div class="alert alert-success">
                                <?= htmlspecialchars($mensaje_exito, ENT_QUOTES, 'UTF-8') ?>
                                <div class="mt-2">
                                    <a href="events.php" class="btn btn-sm btn-success">Volver al listado de eventos</a>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Mostrar errores de validación si los hay -->
                        <?php if (!empty($errores)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errores as $mensaje_error): ?>
                                        <li><?= htmlspecialchars($mensaje_error, ENT_QUOTES, 'UTF-8') ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <!-- Formulario de cambio de contraseña -->
                        <form method="post" action="change-password.php">
                            <!-- Contraseña actual -->
                            <div class="mb-3">
                                <label for="password_actual" class="form-label">Contraseña actual</label>
                                <input
                                    type="password"
                                    class="form-control"
                                    id="password_actual"
                                    name="password_actual"
                                    required
                                >
                            </div>

                            <!-- Nueva contraseña -->
                            <div class="mb-3">
                                <label for="password_nueva" class="form-label">Nueva contraseña</label>
                                <input
                                    type="password"
                                    class="form-control"
                                    id="password_nueva"
                                    name="password_nueva"
                                    required
                                >
                                <div class="form-text">
                                    Mínimo 8 caracteres, con al menos una letra y un número.
                                </div>
                            </div>

                            <!-- Repetir nueva contraseña -->
                            <div class="mb-3">
                                <label for="password_repetida" class="form-label">Repetir nueva contraseña</label>
                                <input
                                    type="password"
                                    class="form-control"
                                    id="password_repetida"
                                    name="password_repetida"
                                    required
                                >
                            </div>

                            <!-- Botones -->
                            <button type="submit" class="btn btn-warning">
                                Cambiar contraseña
                            </button>
                            <a href="events.php" class="btn btn-secondary ms-2">
                                Cancelar
                            </a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JS Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>