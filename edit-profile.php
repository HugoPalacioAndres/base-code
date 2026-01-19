<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/base-code/data-access/CalendarDataAccess.php';
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

    /** Apellidos obligatorios*/
    if ($apellidos === '') {
        $errores[]='Los apellidos son pbligatorios.';
    }

    /**Fecha de nacimiento obligatoria y vaalida */
    if ($fecha_nac === '') {
        $errores[] ='La fecha de nacimiento es obligatoria.';
    }else {
       $fecha_valida   = DateTime::createFromFormat('Y-m-d', $fecha_nac);
$errores_fecha  = DateTime::getLastErrors();

if ($fecha_valida === false
    || $errores_fecha['warning_count'] > 0
    || $errores_fecha['error_count'] > 0) {

            $errores[] = 'La fecha de nacimiento no es valida. Usa el formato AAAA-MM-DD.';
        }
    }

    /**COmoribacion de que el email no lo use otro usuario */
    if(empty($errores)) {
        $usuario_con_email = $acceso_datos -> getUserByEmail($email);

        if($usuario_con_email !== null && $usuario_con_email ->  getId() !== $usuario -> getId()) {
            $errores[] = 'Ya existe un usario regustrado con ese correo electronico.';
        }
    }

    /**Si no hay errores actializamos el objeto y la bd */
    if(empty($errores)) {
        $usuario -> setEmail($email);
        $usuario -> setFirstName($nombre);
        $usuario -> setLastName($apellidos);
        $usuario -> setBirthDate($fecha_nac);
        $usuario -> setAbout($about);
        /**goardar en base de datos */
        $ok = $acceso_datos -> updateUser($usuario);

        if ($ok)  {
            $mensaje_exito = 'Perfil actualizado correctamente.';
            redirigir('profile.php');
            exit;
        } else {
            $errores[] = 'Se ha producido un error al acrualizar el perfil en la base de datos.';
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
    <title>Editar perfil - Calendario</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <!-- Navbar superior -->
    <nav class="navbar navbar-dark bg-primary mb-4">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">
                <i class="fas fa-user-edit"></i> Editar perfil
            </span>
            <a href="profile.php" class="btn btn-outline-light btn-sm">
                Volver a mi perfil
            </a>
        </div>
    </nav>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Modificar datos del usuario</h5>
                    </div>
                    <div class="card-body">
                        <!-- Mensaje de éxito -->
                        <?php if ($mensaje_exito !== ''): ?>
                            <div class="alert alert-success">
                                <?= htmlspecialchars($mensaje_exito, ENT_QUOTES, 'UTF-8') ?>
                                <div class="mt-2">
                                    <a href="profile.php" class="btn btn-sm btn-success">
                                        Ir a mi perfil
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Errores -->
                        <?php if (!empty($errores)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errores as $mensaje_error): ?>
                                        <li><?= htmlspecialchars($mensaje_error, ENT_QUOTES, 'UTF-8') ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <!-- Formulario -->
                        <form method="post" action="edit-profile.php">
                            <!-- Email -->
                            <div class="mb-3">
                                <label for="email" class="form-label">Correo electrónico</label>
                                <input
                                    type="email"
                                    class="form-control"
                                    id="email"
                                    name="email"
                                    required
                                    value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"
                                >
                            </div>

                            <!-- Nombre -->
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="nombre"
                                    name="nombre"
                                    required
                                    value="<?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?>"
                                >
                            </div>

                            <!-- Apellidos -->
                            <div class="mb-3">
                                <label for="apellidos" class="form-label">Apellidos</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="apellidos"
                                    name="apellidos"
                                    required
                                    value="<?= htmlspecialchars($apellidos, ENT_QUOTES, 'UTF-8') ?>"
                                >
                            </div>

                            <!-- Fecha de nacimiento -->
                            <div class="mb-3">
                                <label for="fecha_nac" class="form-label">Fecha de nacimiento</label>
                                <input
                                    type="date"
                                    class="form-control"
                                    id="fecha_nac"
                                    name="fecha_nac"
                                    required
                                    value="<?= htmlspecialchars($fecha_nac, ENT_QUOTES, 'UTF-8') ?>"
                                >
                                <div class="form-text">
                                    Usa el formato AAAA-MM-DD.
                                </div>
                            </div>

                            <!-- Acerca de mí -->
                            <div class="mb-3">
                                <label for="about" class="form-label">Acerca de mí</label>
                                <textarea
                                    class="form-control"
                                    id="about"
                                    name="about"
                                    rows="4"
                                ><?= htmlspecialchars($about, ENT_QUOTES, 'UTF-8') ?></textarea>
                            </div>

                            <!-- Botones -->
                            <button type="submit" class="btn btn-primary">
                                Guardar cambios
                            </button>
                            <a href="profile.php" class="btn btn-secondary ms-2">
                                Cancelar
                            </a>
                            <a href="events.php" class="btn btn-outline-dark ms-2">
                                Volver al listado de eventos
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