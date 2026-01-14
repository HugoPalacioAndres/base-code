<?php
/**Cargamos sistema de autenticacion */
require_once __DIR__ . '/auth.php';
/**Clase de acceso a datos del calendario */
require_once __DIR__ . '/base-code/data-access/CalendarDataAccess.php';
/**Carga de la clase que represe nta a un evento */
require_once __DIR__ . '/base-code/entities/Event.php';

/**Comprobacion de que el usuario esta autenticado  
 * si no se le redigira al index.php
 */
requerir_autenticacion();

/**Crear objeto de acceso a la bbdd
 * __DIR__ devulce la ruta absoluta a este archivo
 * añado la ruta relatuva al fichero de bd calendar.db
 */
$ruta_bd = __DIR__ .'/base-code/calendar.db';
/**Cremaos objeto para hablar con la BBDD */
$acceso_datos = new CalendarDataAccess($ruta_bd);
/**obteneer id del usu logueado desde la sesion */
$id_usuario = $_SESSION['id_usuario'];
/**Preparo array de errores y variable para guardar el evento  */
$errores = [];
$evento = null;

/**Recupero el id del evento desde la query string (?id=123)
 * usao el operador ?? para dar nill si no viene el parametro
 */
$id_evento = $_GET['id']?? null;

/**Validacion ide del evento (debe venir informado y compuesto solo por digitos (ctype_digit)) */
if ($id_evento === null || !ctype_digit($id_evento)){
    /**Si no viene el is o no es numero valido añado mensaje a array erroes */
    $errores[] ='No se puede acceder al evento porque el identificador no es valido.';

}else{
    /**Convierto id a entero por seguridad */
    $id_evento =(int)$id_evento;
    /**Intento recuperar el evento desde la base de datos 
     * utilizando el metodo getEventById de CalendarDataAccess
     */
    $evento = $acceso_datos->getEventById($id_evento);

    /**si no se ha encontrado ningun evento con ese ID, mostrare error */
    if ($evento === null){
        $errores[]='No se puede acceder al evento por que no existe.';
    }else{
        /**Comprobar que el evento pertenece al usuario logueado
         * utilizare  getUserId() metodo de la clase event
         */
        if($evento -> getUserId() !== $id_usuario){
            /**El evento no es del usuario actual, no permito borrarlo */
            $errores[] ='No tiene permisos para eliminar este evento.';
            /**Por seguridad "olvidamos" el evento para que no se muestre */
            $evento = null;
        }
    }
}

/**Proceso eliminar evento si el evento es valido (no es null)
 * y la peticion llega por POST
 */
if ($evento !== null && $_SERVER['REQUEST_METHOD'] === 'POST') {
    /** Vamos a distinguir qué botón ha pulsado el usuario:
     *  - name="accion" value="si" → Sí, eliminar
     *  - name="accion" value="no" → No, volver al listado
     */
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'si') {
        /** Llamada al método deleteEvent del acceso a datos, este método
         *  recibe el ID del evento a borrar
         */
        $ok = $acceso_datos->deleteEvent($id_evento);

        /** Si todo es correcto redirijo a listado de eventos */
        if ($ok) {
            redirigir('events.php');
            exit; /** Aseguro que el script termina después de redirección */
        } else {
            /** Si falla algo en la BD añadimos mensaje de error */
            $errores[] = 'Se ha producido un error al eliminar el evento de la base de datos.';
        }
    } else {
        /** El usuario ha pulsado "No, volver al listado":
         *  no borramos nada, simplemente redirigimos
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
    <title>Eliminar evento - Calendario</title>
    <!-- Incluimos Bootstrap 5 desde CDN para estilos rápidos y consistentes -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <!-- Barra de navegación superior en rojo (bg-danger) para reforzar la acción peligrosa -->
    <nav class="navbar navbar-dark bg-danger mb-4">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">
                <!-- Icono de calendario tachado (necesitarías Font Awesome si quieres que se vea) -->
                <i class="fas fa-calendar-times"></i> Eliminar evento
            </span>
            <!-- Enlace para volver al listado de eventos -->
            <a href="events.php" class="btn btn-outline-light btn-sm">
                Volver al listado
            </a>
        </div>
    </nav>

    <div class="container">
        <?php if ($evento === null): ?>
            <!--
                Caso 1: No se puede acceder al evento
                - ID inválido
                - Evento no existe
                - Evento no pertenece al usuario
            -->
            <div class="alert alert-danger">
                <?php foreach ($errores as $mensaje_error): ?>
                    <!-- Mostramos cada error escapando HTML por seguridad -->
                    <p class="mb-0"><?= htmlspecialchars($mensaje_error, ENT_QUOTES, 'UTF-8') ?></p>
                <?php endforeach; ?>
            </div>
            <!-- Botón para volver al listado de eventos -->
            <a href="events.php" class="btn btn-primary">Volver al listado de eventos</a>
        <?php else: ?>
            <!--
                Caso 2: Evento válido y del usuario
                Mostramos una tarjeta con los datos del evento y pedimos confirmación
            -->
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <!-- Card de Bootstrap con borde rojo para indicar peligro -->
                    <div class="card border-danger">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0">Confirmar eliminación</h5>
                        </div>
                        <div class="card-body">
                            <p>Vas a eliminar el siguiente evento:</p>

                            <!-- Lista con la información principal del evento -->
                            <ul>
                                <li><strong>Título:</strong> <?= htmlspecialchars($evento->getTitle(), ENT_QUOTES, 'UTF-8') ?></li>
                                <li><strong>Descripción:</strong> <?= htmlspecialchars($evento->getDescription(), ENT_QUOTES, 'UTF-8') ?></li>
                                <li><strong>Inicio:</strong> <?= htmlspecialchars($evento->getStartDate(), ENT_QUOTES, 'UTF-8') ?></li>
                                <li><strong>Fin:</strong> <?= htmlspecialchars($evento->getEndDate(), ENT_QUOTES, 'UTF-8') ?></li>
                            </ul>

                            <!-- Si hubiera errores al intentar eliminar, se muestran aquí -->
                            <?php if (!empty($errores)): ?>
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        <?php foreach ($errores as $mensaje_error): ?>
                                            <li><?= htmlspecialchars($mensaje_error, ENT_QUOTES, 'UTF-8') ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                            <!--
                                Formulario de confirmación
                                - method="post": para que el borrado se haga vía POST
                                - action mantiene el id en la URL (delete-event.php?id=...)
                            -->
                            <form method="post" action="delete-event.php?id=<?= $id_evento ?>">
                                <!--
                                    Mensaje de aviso: acción destructiva
                                    Podrías añadir aquí un input hidden "confirmar" si quisieras
                                    más comprobaciones en el servidor.
                                -->
                                <p class="text-danger">Esta acción no se puede deshacer.</p>

                                <!-- Botón principal para confirmar eliminación -->
                                <button type="submit" name="accion" value="si" class="btn btn-danger">
                                    Sí, eliminar evento
                                </button>

                                <!-- Botón para cancelar y volver al listado -->
                                <button type="submit" name="accion" value="no" class="btn btn-secondary ms-2">
                                    No, volver al listado
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Script de Bootstrap para componentes (modales, etc.), no estrictamente necesario aquí pero habitual -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>