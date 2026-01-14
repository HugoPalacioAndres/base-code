<?php
/**Cargo sistema de autenticacion y utilidades de sesion */
require_once __DIR__ . '/auth.php';
/**Comprobacion de usuario autenticado, si no redireccion a index.php */
requerir_autenticacion();
/**Array de errores por si hubiera algo raro, no deberia pasar nada pero por sia caso */
$errores =[];

/**Procesar form si llega por POST */
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    /**Distincion de boton pulsado:
     * si, desconectar o no, volver al listado
     */
    $accion = $_POST['accion'] ?? '';
    if($accion === 'si'){
        /** aqui se cierra la sesion del user:
         * Vacio el array $_SESSION
         * Destruir cookie de sesion
         * Llamar a session_destroy()
         * En uth.php se puede meter un funcion tipo cerrar_sesion()
         */

        cerrar_sesion(); /**OJO echo en sevilla a falta de comprobacion de nombre en auth.php */

        redirigir('index.php');
        exit;
    }else{
        /**Ususario pulso No, volverl al listado, no cerramos sesion
         * y redirijp a events.php
         */
        redirigir('events.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <!-- Hacemos la página responsive -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Desconectar - Calendario</title>
    <!-- Bootstrap 5 desde CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <!-- Barra de navegación superior -->
    <nav class="navbar navbar-dark bg-secondary mb-4">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">
                <i class="fas fa-sign-out-alt"></i> Desconectar
            </span>
            <a href="events.php" class="btn btn-outline-light btn-sm">
                Volver a mis eventos
            </a>
        </div>
    </nav>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card border-secondary">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">Confirmar desconexión</h5>
                    </div>
                    <div class="card-body">
                        <p>¿Seguro que desea desconectar de la aplicación?</p>

                        <?php if (!empty($errores)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errores as $mensaje_error): ?>
                                        <li><?= htmlspecialchars($mensaje_error, ENT_QUOTES, 'UTF-8') ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form method="post" action="logout.php">
                            <!-- Botón "Sí, desconectar" -->
                            <button type="submit" name="accion" value="si" class="btn btn-danger">
                                Sí, desconectar
                            </button>
                            <!-- Botón "No, volver al listado" -->
                            <button type="submit" name="accion" value="no" class="btn btn-secondary ms-2">
                                No, volver al listado de eventos
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JS de Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>