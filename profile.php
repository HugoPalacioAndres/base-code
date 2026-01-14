<?php   
require_once __DIR__ .'/auth.php';
require_once __DIR__ .'/base-code/data-access/CalendarDataAccess.php';
require_once __DIR__ . '/base-code/entitites/User.php';
/**Ravision de autenticacion si no redirijo a index.php */
requerir_autenticacion();

/**Objeto acceso a la BD, usamos misma ruta qie el resto de paginas */
$ruta_bd = __DIR__ . '/base-code/calendar.db';
$acceso_datos = new CalendarDataAccess($ruta_bd);

/**Obtener id del usu log desde la sesion  */
$id_usuario = $_SESSION['id_usuario'];

/**Recuepero el objeto user desde la bd
 * usando metodo getUserById(int $user_id)
 */
if($ususario === null){
    /**Al igual que antes caso raro pero posible
     * La sesion dice que hay usario pero no existe en la BD
     * Lo mas seguro es mandar a logout o index
     */
    redirigir('logout.php');
    exit;
}
/**Extraer los datos que queremos mostrar del objeto USer
 * utilizo los getter de la clase User getEmail, getFirstName..
 */
$email = $usuario->getEmail();
$nombre = $usuario -> getFirstName();
$apellidos = $usuario -> getLAStName();
$fecha_nac = $usuario -> getBirthDate();
$about = $usuario -> getAbout();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <!-- Responsive -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi perfil - Calendario</title>
    <!-- Bootstrap 5 desde CDN para estilos -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <!-- Barra de navegación superior con enlace de vuelta a eventos -->
    <nav class="navbar navbar-dark bg-primary mb-4">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">
                <i class="fas fa-user"></i> Mi perfil
            </span>
            <a href="events.php" class="btn btn-outline-light btn-sm">
                Volver a mis eventos
            </a>
        </div>
    </nav>

    <div class="container">
        <!-- Centramos la tarjeta de perfil en pantallas grandes -->
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <!-- Tarjeta de Bootstrap con los datos del usuario -->
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Datos del usuario</h5>
                    </div>
                    <div class="card-body">
                        <!-- Email -->
                        <div class="mb-3">
                            <h6 class="fw-bold mb-1">Correo electrónico</h6>
                            <p class="mb-0">
                                <?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>
                            </p>
                        </div>

                        <!-- Nombre -->
                        <div class="mb-3">
                            <h6 class="fw-bold mb-1">Nombre</h6>
                            <p class="mb-0">
                                <?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?>
                            </p>
                        </div>

                        <!-- Apellidos -->
                        <div class="mb-3">
                            <h6 class="fw-bold mb-1">Apellidos</h6>
                            <p class="mb-0">
                                <?= htmlspecialchars($apellidos, ENT_QUOTES, 'UTF-8') ?>
                            </p>
                        </div>

                        <!-- Fecha de nacimiento -->
                        <div class="mb-3">
                            <h6 class="fw-bold mb-1">Fecha de nacimiento</h6>
                            <p class="mb-0">
                                <?= htmlspecialchars($fecha_nac, ENT_QUOTES, 'UTF-8') ?>
                            </p>
                        </div>

                        <!-- Acerca de mí -->
                        <div class="mb-3">
                            <h6 class="fw-bold mb-1">Acerca de mí</h6>
                            <p class="mb-0">
                                <!-- nl2br para respetar saltos de línea que haya escrito el usuario -->
                                <?= nl2br(htmlspecialchars($about, ENT_QUOTES, 'UTF-8')) ?>
                            </p>
                        </div>

                        <!-- Enlaces de acción -->
                        <div class="mt-4 d-flex flex-wrap gap-2">
                            <!-- Enlace para editar el perfil -->
                            <a href="edit-profile.php" class="btn btn-primary">
                                Editar perfil
                            </a>
                            <!-- Enlace para cambiar contraseña (opcional pero lógico) -->
                            <a href="change-password.php" class="btn btn-outline-secondary">
                                Cambiar contraseña
                            </a>
                            <!-- Enlace para volver al listado de eventos -->
                            <a href="events.php" class="btn btn-outline-dark">
                                Volver al listado de eventos
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JS de Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

