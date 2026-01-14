<?php

/**Cargamos fichero auth.php con require_once x si se pide 
 * otra vez no se cargue dos veces
 */
require_once __DIR__ . '/auth.php';
/**Cargamos clase gestion acceso a bbdd */
require_once __DIR__ . '/base-code/data-access/CalendarDataAccess.php';
/**Cargamos clase que representa al evento */
require_once __DIR__ . '/base-code/entities/Event.php';

/**Si el usuario no esta logeado redirjo a index */
requerir_autenticacion();

/**Ruta completa bbdd */
$ruta_bd = __DIR__ . '/base-code/calendar.db';
/**Objeto de acceso a bbdd */
$acceso_datos = new CalendarDataAccess($ruta_bd);

/**Obtencion del id del usuario logueado desde la seseion */
$id_usuario =$_SESSION['id_usuario'];

/**variables form para rellenar y si hay errores volver a mostrarlo */
$titulo ='';
$descripcion='';
$fecha_inicio='';
$fecha_fin='';

/**Array errores de validacion */
$errores=[];

/**Si el formulario de ha enciado por POST procesarremos los datos */
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    /**Recoleccion y saneamiento de los datos del form */
    $titulo=trim($_POST['titulo'] ?? '');
    $descripcion=trim($_POST['descripcion'] ?? '');
    $fecha_inicio= $_POST['fecha_inicio'] ?? '';
    $fecha_fin= $_POST['fecha_fin'] ?? '';

    /**Validacion basica lado servidor */

    if($titulo === ''){
        $errores[]='El titulo del evento es obligatorio.';
    }
    if($descripcion ===''){
        $errores ='La descripcion del evento es obligatoria.';
    }
    if($fecha_inicio === ''){
        $errores = 'La fecha y hora de inicio son obligatorias.';
    }
    if($fecha_fin === ''){
        $errores[] ='La fecha y hora de fin son obligartorias.';
    }

    /**Si existen ambas fechas, comprobamos que inicio < fin */
    if($fecha_inicio !== '' && $fecha_fin !== ''){
        if(strtotime($fecha_inicio) >= strtotime($fecha_fin)){
            $errores[]='La fecha de inicio debe ser anterior a la de fin';
        }
    }

    /**Si no tenemos errores, creamos el evento y lo guardamos en la BD */
    if(empty($errores)){
        /**Creo objeto evente con los atributos en el mismo orden que el constructor */
        $evento = new Event(
            $id_usuario,
            $titulo, 
            $descripcion,
            $fecha_inicio,
            $fecha_fin, 
            null //if null lo asigana la bd
        );
        /**Llamo a metodo createEven de CalendarDataAccess que inserta el evento en su tabla correspond
         * Devuleve true si todo va bien si no false         */
        $creado_ok = $acceso_datos -> createEvent($evento);

        if($creado_ok){
            redirigir('events.php');
        }else{
            $errores[]= 'Se ha producido un error al guardar el evento en la base de datos.';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo evento - Calendario</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <!-- Barra superior sencilla -->
    <nav class="navbar navbar-dark bg-success mb-4">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">
                <i class="fas fa-calendar-plus"></i> Nuevo evento
            </span>
            <a href="events.php" class="btn btn-outline-light btn-sm">
                Volver a mis eventos
            </a>
        </div>
    </nav>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <!-- Card con el formulario -->
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Crear nuevo evento</h5>
                    </div>
                    <div class="card-body">
                        <!-- Mostrar errores si los hay -->
                        <?php if(!empty($errores)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach($errores as $mensaje_error): ?>
                                        <li><?= htmlspecialchars($mensaje_error, ENT_QUOTES, 'UTF-8') ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <!-- Formulario de creacion de evento -->
                        <form method="post" action="new-event.php">
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

                            <!-- Boton GUARDAR -->
                            <button type="submit" class="btn btn-success">
                                Guardar evento
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

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
