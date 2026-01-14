<?php
/** Cargamos fichero auth.php, la clase de acceso a la BD y la clase Event */
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/base-code/data-access/CalendarDataAccess.php';
require_once __DIR__ . '/base-code/entities/Event.php';

/** Requerir autenticación: si no está logueado, redirige a index.php */
requerir_autenticacion();

/** Creamos la ruta completa a la BD
 * OJO: aquí es __DIR__ . '...' (con punto), no __DIR__ - '...'
 */
$ruta_bd = __DIR__ . '/base-code/calendar.db';

/** Creamos el objeto de acceso a datos */
$acceso_datos = new CalendarDataAccess($ruta_bd);

/** Obtenemos el id del usuario logueado desde la sesión */
$id_usuario = $_SESSION['id_usuario'];

/** Array de errores que podemos mostrar */
$errores = [];

/** Variable que guarda el propio evento que se va a editar */
$evento = null;

/** Comprobar que viene el id por query string (GET) */
$id_evento = $_GET['id'] ?? null;

if ($id_evento === null || !ctype_digit($id_evento)) {
    /** Si no viene id o no es un número no se puede continuar */
    $errores[] = 'No se puede acceder al evento porque el identificador no es válido.';
} else {
    /** Convierto a entero por seguridad */
    $id_evento = (int)$id_evento;

    /** Intento recuperar el evento de la BD */
    $evento = $acceso_datos->getEventById($id_evento);

    /** Si no existe, error */
    if ($evento === null) {
        $errores[] = 'No se puede acceder al evento porque no existe.';
    } else {
        /** Compruebo que el evento pertenece al usuario logueado */
        if ($evento->getUserId() !== $id_usuario) {
            $errores[] = 'No se puede acceder al evento porque no tiene permisos para verlo.';
            /** Por seguridad olvidamos el evento */
            $evento = null;
        }
    }
}

/** Variables del formulario (se rellenan con los datos actuales del evento) */
$titulo = '';
$descripcion = '';
$fecha_inicio = '';
$fecha_fin = '';

if ($evento !== null) {
    $titulo = $evento->getTitle();
    $descripcion = $evento->getDescription();
    $fecha_inicio = $evento->getStartDate();
    $fecha_fin = $evento->getEndDate();
}

/** Procesar formulario si se envía por POST */
if ($evento !== null && $_SERVER['REQUEST_METHOD'] === 'POST') {
    /** Recogemos y saneamos los datos que llegan del form */
    $titulo = trim($_POST['titulo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $fecha_inicio = $_POST['fecha_inicio'] ?? '';
    $fecha_fin = $_POST['fecha_fin'] ?? '';

    /** Limpiamos errores anteriores y validamos de nuevo */
    $errores = [];

    if ($titulo === '') {
        $errores[] = 'El título del evento es obligatorio.';
    }

    if ($descripcion === '') {
        $errores[] = 'La descripción del evento es obligatoria.';
    }

    if ($fecha_inicio === '') {
        $errores[] = 'La fecha y hora de inicio son obligatorias.';
    }

    if ($fecha_fin === '') {
        $errores[] = 'La fecha y hora de fin son obligatorias.';
    }

    /** Compruebo que el inicio < fin si ambas fechas existen */
    if ($fecha_inicio !== '' && $fecha_fin !== '') {
        if (strtotime($fecha_inicio) >= strtotime($fecha_fin)) {
            $errores[] = 'La fecha de inicio debe ser anterior a la fecha de fin.';
        }
    }

    /** Si no hay errores de validación se actualiza el evento */
    if (empty($errores)) {
        /** Creamos un nuevo objeto Event con los datos actualizados
         * OJO: mantenemos el mismo id de evento y el mismo userId
         */
        $evento_actualizado = new Event(
            $id_usuario,   // userId igual que antes
            $titulo,
            $descripcion,
            $fecha_inicio,
            $fecha_fin,
            $id_evento     // mismo id que estamos editando
        );

        /** Llamamos a updateEvent para guardar los cambios en la BD */
        $ok = $acceso_datos->updateEvent($evento_actualizado);

        if ($ok) {
            /** Si todo va bien, redirigimos al listado de eventos */
            redirigir('events.php');
        } else {
            $errores[] = 'Se ha producido un error al guardar los cambios en la base de datos.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar evento - Calendario</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-success mb-4">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">
                <i class="fas fa-calendar-check"></i> Editar evento
            </span>
            <a href="events.php" class="btn btn-outline-light btn-sm">
                Volver al listado
            </a>
        </div>
    </nav>

    <div class="container">
        <?php if ($evento === null): ?>
            <!-- Caso en que no se puede acceder al evento -->
            <div class="alert alert-danger">
                <?php foreach ($errores as $mensaje_error): ?>
                    <p class="mb-0"><?= htmlspecialchars($mensaje_error, ENT_QUOTES, 'UTF-8') ?></p>
                <?php endforeach; ?>
            </div>
            <a href="events.php" class="btn btn-primary">Volver al listado de eventos</a>
        <?php else: ?>
            <!-- Caso normal: evento existe y es del usuario -->
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">Modificar evento</h5>
                        </div>
                        <div class="card-body">
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

                            <!-- Formulario de edición -->
                            <form method="post" action="edit-event.php?id=<?= $id_evento ?>">
                                <!-- Campo TITULO -->
                                <div class="mb-3">
                                    <label for="titulo" class="form-label">Título</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        id="titulo"
                                        name="titulo"
                                        required
                                        value="<?= htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') ?>"
                                    >
                                </div>

                                <!-- Campo DESCRIPCION -->
                                <div class="mb-3">
                                    <label for="descripcion" class="form-label">Descripción</label>
                                    <textarea
                                        class="form-control"
                                        id="descripcion"
                                        name="descripcion"
                                        rows="4"
                                        required
                                    ><?= htmlspecialchars($descripcion, ENT_QUOTES, 'UTF-8') ?></textarea>
                                </div>

                                <!-- Campo FECHA INICIO -->
                                <div class="mb-3">
                                    <label for="fecha_inicio" class="form-label">Fecha y hora de inicio</label>
                                    <input
                                        type="datetime-local"
                                        class="form-control"
                                        id="fecha_inicio"
                                        name="fecha_inicio"
                                        required
                                        value="<?= htmlspecialchars($fecha_inicio, ENT_QUOTES, 'UTF-8') ?>"
                                    >
                                </div>

                                <!-- Campo FECHA FIN -->
                                <div class="mb-3">
                                    <label for="fecha_fin" class="form-label">Fecha y hora de fin</label>
                                    <input
                                        type="datetime-local"
                                        class="form-control"
                                        id="fecha_fin"
                                        name="fecha_fin"
                                        required
                                        value="<?= htmlspecialchars($fecha_fin, ENT_QUOTES, 'UTF-8') ?>"
                                    >
                                </div>

                                <!-- Botones -->
                                <button type="submit" class="btn btn-success">
                                    Guardar cambios
                                </button>
                                <a href="events.php" class="btn btn-secondary ms-2">
                                    Cancelar
                                </a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
