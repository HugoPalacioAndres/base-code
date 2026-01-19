<?php
    /**Cargamos nuestro fichero auth.php con require_once por si se pide otra vez no se cargue dos veces */
    require_once __DIR__ . '/auth.php';
    /**Cargamos clase que gestiona el acceso a la bbdd */
    require_once __DIR__ . '/base-code/data-access/CalendarDataAccess.php';
    /**Cargamos clase que representa al evento */
    require_once __DIR__ . '/base-code/entities/Event.php';
    
    /**Requerimos autenticacion si no esta logueado redirige a index.php 
     * esta funcion esta en auth.php
     * comprueba si existe $_SESSION['id_usuario']
     * si no existe redirige a index.php
     */
    requerir_autenticacion();

    /**Creamos la ruta completa a la base de datos
     * __DIR__ es la ruta actual del directorio donde se ejecuta el script
     * concatenamos con la ruta relativa a la bd
     */
    $ruta_bd = __DIR__ . '/base-code/calendar.db';
    
    /**Creamos un objeto CalendarDataAccess 
     * este objeto nos permite hacer consultas a la base de datos
     * le pasamos la ruta de la bd como parametro
     */
    $acceso_datos = new CalendarDataAccess($ruta_bd);

    /**Obtenemos el id del usuario que esta logueado desde la sesion
     * $_SESSION['id_usuario'] se guardó cuando hizo login en index.php
     */
    $id_usuario = $_SESSION['id_usuario'];
    
    /**Obtenemos el nombre del usuario que esta logueado desde la sesion
     * lo usaremos para mostrar "Bienvenido, [nombre]"
     */
    $nombre_usuario = $_SESSION['nombre_usuario'];

    /**Obtenemos TODOS los eventos del usuario logueado
     * llamamos a metodo getEventsByUserId de CalendarDataAccess
     * le pasamos el id del usuario logueado
     * devuelve un array de objetos Event (puede estar vacio si no tiene eventos)
     */
    $eventos = $acceso_datos->getEventsByUserId($id_usuario);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Eventos - Calendario</title>
    
    <!-- Cargamos Bootstrap 5 desde CDN (Content Delivery Network)
         Bootstrap proporciona estilos CSS profesionales listos para usar
         no necesitamos escribir CSS nosotros
    -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Cargamos Font Awesome desde CDN
         Font Awesome proporciona iconos vectoriales (plus, edit, trash, etc)
         los usamos con <i class="fas fa-[nombre-icono]"></i>
    -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">
    <!-- NAVBAR (Header con navegacion)
         navbar-dark = fondo oscuro
         bg-success = fondo verde de Bootstrap
         mb-4 = margen abajo (margin-bottom)
    -->
    <nav class="navbar navbar-dark bg-success mb-4">
        <div class="container-fluid">
            <!-- Logo/Titulo con icono -->
            <span class="navbar-brand mb-0 h1">
                <i class="fas fa-calendar-alt"></i> Mi Calendario
            </span>
            
            <!-- Contenedor derecha: nombre usuario y boton logout
                 d-flex = display flexbox (alinea elementos en fila)
                 gap-3 = espacio entre elementos
            -->
            <div class="d-flex align-items-center gap-3">
    <span class="text-white">
        Bienvenido, <strong><?= htmlspecialchars($nombre_usuario, ENT_QUOTES, 'UTF-8') ?></strong>
    </span>

    <!-- Enlace a perfil -->
    <a href="profile.php" class="btn btn-outline-light btn-sm">
        <i class="fas fa-user"></i> Mi perfil
    </a>

    <!-- Enlace a cambio de contraseña (opcional) -->
    <a href="change-password.php" class="btn btn-outline-light btn-sm">
        <i class="fas fa-key"></i>
    </a>

    <!-- Botón logout -->
    <a href="logout.php" class="btn btn-danger btn-sm">
        <i class="fas fa-sign-out-alt"></i> Cerrar sesión
    </a>
</div>

        </div>
    </nav>

    <!-- CONTENEDOR PRINCIPAL
         container = ancho maximo y centrado
    -->
    <div class="container">
        <!-- BOTON PARA CREAR NUEVO EVENTO (ANTES de la tabla)
             btn = clase Bootstrap para botones
             btn-success = color verde
             mb-4 = margen abajo (spacing)
             href="new-event.php" = enlaza a pagina para crear nuevo evento
        -->
        <a href="new-event.php" class="btn btn-success mb-4">
            <i class="fas fa-plus"></i> Nuevo evento
        </a>

        <!-- CARD = contenedor con titulo y contenido (Bootstrap)
             mb-4 = margen abajo
        -->
        <div class="card mb-4">
            <!-- HEADER de la card con titulo
                 bg-success = fondo verde
                 text-white = texto blanco
            -->
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">
                    <i class="fas fa-list"></i> Mis eventos
                </h5>
            </div>
            
            <!-- BODY de la card = contenido principal -->
            <div class="card-body">
                <?php if(!empty($eventos)): ?>
                    <!-- SI HAY EVENTOS mostrar tabla
                         !empty() = devuelve true si el array NO esta vacio
                    -->
                    
                    <!-- table-responsive = hace la tabla responsive (se adapta a moviles)
                         sin esto en moviles se ve mal
                    -->
                    <div class="table-responsive">
                        <!-- TABLA
                             table = clase Bootstrap para tablas
                             table-hover = cambia color fila cuando pasas el raton
                             table-striped = alterna colores en filas (rayas)
                             align-middle = alinea contenido verticalmente al centro
                        -->
                        <table class="table table-hover table-striped align-middle">
                            <thead class="table-success">
                                <!-- FILA DE ENCABEZADOS -->
                                <tr>
                                    <th scope="col">Título</th>
                                    <th scope="col">Descripción</th>
                                    <th scope="col">Inicio</th>
                                    <th scope="col">Fin</th>
                                    <!-- text-center = centra el contenido -->
                                    <th scope="col" class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- RECORRER TODOS LOS EVENTOS del usuario
                                     foreach itera sobre cada evento en el array $eventos
                                     $evento es cada objeto Event en la iteracion
                                -->
                                <?php foreach($eventos as $evento): ?>
                                    <tr>
                                        <!-- Mostrar titulo del evento
                                             getTitle() es metodo de la clase Event
                                             htmlspecialchars() evita inyeccion de codigo malicioso
                                             ENT_QUOTES = escapa comillas simples y dobles
                                             UTF-8 = codificacion de caracteres
                                        -->
                                        <td><?= htmlspecialchars($evento->getTitle(), ENT_QUOTES, 'UTF-8') ?></td>
                                        
                                        <!-- Mostrar descripcion del evento -->
                                        <td><?= htmlspecialchars($evento->getDescription(), ENT_QUOTES, 'UTF-8') ?></td>
                                        
                                        <!-- Mostrar fecha inicio
                                             <small> = texto pequeño
                                             text-muted = color gris (menos destacado)
                                        -->
                                        <td>
                                            <small class="text-muted">
                                                <?= htmlspecialchars($evento->getStartDate(), ENT_QUOTES, 'UTF-8') ?>
                                            </small>
                                        </td>
                                        
                                        <!-- Mostrar fecha fin -->
                                        <td>
                                            <small class="text-muted">
                                                <?= htmlspecialchars($evento->getEndDate(), ENT_QUOTES, 'UTF-8') ?>
                                            </small>
                                        </td>
                                        
                                        <!-- BOTONES DE ACCIONES (editar y eliminar)
                                             text-center = centra los botones
                                        -->
                                        <td class="text-center">
                                            <!-- BOTON EDITAR
                                                 href="edit-event.php?id=<?= $evento->getId() ?>"
                                                 enlaza a edit-event.php pasando el id del evento en la URL
                                                 ?id=... esto es un parametro GET
                                                 btn btn-primary btn-sm = boton azul pequeño
                                                 aria-label = texto para lectores de pantalla (accesibilidad)
                                            -->
                                            <a href="edit-event.php?id=<?= $evento->getId() ?>" 
                                               class="btn btn-primary btn-sm"
                                               aria-label="Editar evento <?= htmlspecialchars($evento->getTitle(), ENT_QUOTES, 'UTF-8') ?>">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            <!-- BOTON ELIMINAR
                                                 href="delete-event.php?id=<?= $evento->getId() ?>"
                                                 enlaza a delete-event.php pasando el id del evento
                                                 btn btn-danger btn-sm = boton rojo pequeño
                                                 onclick="return confirm(...)" 
                                                   muestra dialogo pidiendo confirmacion
                                                   si usuario dice si (true) ejecuta el enlace
                                                   si usuario dice no (false) no hace nada
                                            -->
                                            <a href="delete-event.php?id=<?= $evento->getId() ?>" 
                                               class="btn btn-danger btn-sm"
                                               aria-label="Eliminar evento <?= htmlspecialchars($evento->getTitle(), ENT_QUOTES, 'UTF-8') ?>"
                                               onclick="return confirm('¿Estás seguro de que deseas eliminar este evento?');">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <!-- SI NO HAY EVENTOS mostrar mensaje
                         else = se ejecuta si !empty($eventos) es false
                         es decir si $eventos esta vacio
                    -->
                    <div class="text-center py-5 text-muted">
                        <!-- Icono grande de bandeja vacia -->
                        <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 20px; opacity: 0.5; display: block;"></i>
                        
                        <p>No tienes eventos aún.</p>
                        
                        <!-- Boton para crear primer evento -->
                        <a href="new-event.php" class="btn btn-success">
                            <i class="fas fa-plus"></i> Crear primer evento
                        </a>
                    </div>
                <?php endif; ?>
                <!-- CIERRA el if(!empty($eventos)) -->
            </div>
            <!-- CIERRA card-body -->
        </div>
        <!-- CIERRA card -->

        <!-- BOTON PARA CREAR NUEVO EVENTO (DESPUES de la tabla)
             solo se muestra si hay eventos (!empty($eventos))
             si no hay eventos se muestra el boton en el mensaje "No tienes eventos aun"
        -->
        <?php if(!empty($eventos)): ?>
            <div class="text-center mb-4">
                <a href="new-event.php" class="btn btn-success">
                    <i class="fas fa-plus"></i> Nuevo evento
                </a>
            </div>
        <?php endif; ?>
        <!-- CIERRA el if de boton flotante -->
    </div>
    <!-- CIERRA container -->

    <!-- Cargamos JavaScript de Bootstrap desde CDN
         esto permite que algunos componentes Bootstrap funcionen dinamicamente
         por ejemplo: menus desplegables, modales, etc
    -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
