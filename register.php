<?php
    /**Cargamos nustro fichero auten.php con require_once por si se pide otra vez no se cargue dos veces */
    require_once __DIR__ . '/auth.php';
    /**Cargamos clase que gestiona el acceso a la bbdd */
    require_once __DIR__ .'/base-code/data-access/CalendarDataAccess.php';
    /**Cargamos clase que representa al usuario */
    require_once __DIR__ .'/base-code/entities/User.php';
    /**Si el usuario esta logueado rederigimos a events funcion que esta en auten.php */
    redirigir_si_esta_autenticado();
    /**Aseguramos que sesion esta iniciada */
    iniciar_sesion_si_es_necesario();

    /**Inicializar variables vacias donde se guardara lo que el usuario introduzca en el formulario */
    $correo ='';
    $nombre='';
    $apellidos='';
    $fecha_nacimiento='';

    /**Array que acumulara los mensajes de error */
    $errores=[];

    /**Bandera (true/false) que utilizaremos para mostrar usuario creado correctamente  */
    $usuario_creado=false;

    /**Procesar formulario si fue enviado por POST */
    if($_SERVER['REQUEST_METHOD'] === 'POST'){ /**Variable global que dice que metodo se utilizo GET o POST $_SERVER['REQUEST_METHOD] */
        /**Recoger datos de formulario */
        $correo = trim($_POST['correo'] ?? '');
        $nombre = trim($_POST ['nombre'] ?? '');
        $apellidos = trim($_POST['apellidos'] ?? '');
        $fecha_nacimiento = trim($_POST ['fecha_nacimiento'] ?? '');

        /**Contraseñas no las trato con trim ya que los espacios pueden ser intencionados */
        $contrasena = ($_POST['contrasena'] ?? '');
        $repetir_contrasena =($_POST['repetir_contrasena'] ?? '');
    
        /**Validar datos siempre en el servidor por si hay  un usuario mal intencionado */

        if($correo === ''){
            $errores[] = 'El correo electronico es obligatorio.';
        }elseif (!filter_var ($correo, FILTER_VALIDATE_EMAIL)){ /**filter_var se utiliza para validar datos, FILTER_VAR_EMAIL validaa correo electronico */
            $errores[] = 'El formato de correo electronico no es valido.';
        }

        if($nombre ===''){
            $errores[] = 'El nombre es obligatorio.';
        }

        if($apellidos ===''){
           $errores[] = 'El apellido es obligatorio.';
        }

        if($fecha_nacimiento ===''){
            $errores[]= 'La fecha de nacimiento es obligatoria.';
        } 

    
        
        /**Podría validar que sea una fecha válida, pero con type="date" en HTML5, pero el navegador ya lo hace por nosotros */
        
        /**Validar contraseña */
        if ($contrasena === ''){
            $errores[] = "La contraseña es obligatoria.";
        }else {
            /**Si contra no esta vacia hacemos mas validaciones
             * con strlen() devuelve el numero de caracteres min 8
             */
            if(strlen($contrasena)<8){
                $errores[] = 'La contrasela debe tener al menos 8 caracteres.';
            }
            /**Comprobar que almenos tiene un numero 
             * con preg_match busca patrones (expreciones regulares)
             * [/'0-9'/] busca un digitp de 0 a 9 si no encuentra ninguno devuelve 0
             */
            if(!preg_match('/[0-9]/', $contrasena)){
                $errores[] = 'La contrasela debe contener minimo un numero.';
            }

            /**Comprobar que almenos tien una letra /[a-z,A-Z]/ */
            if(!preg_match('/[a-zA-Z]/', $contrasena)){
                $errores[]= 'La contraseña debe contener minimo una letra.';
            }
        }

            /**validar repetir contraseña */
            if($repetir_contrasena === ''){
                $errores[]= 'Debe repetir la contraseña.';
            }elseif($repetir_contrasena !== $contrasena){
                $errores[] = 'Las contraseñas no coinciden.';
            }

            /**Si no hay errores comprobar la bd y crear usuario 
             * comprobamos que el array errores este vacio
            */
            if(empty($errores)){
                /**Creamos ruta a la bd */
                $ruta_bd= __DIR__ . '/base-code/calendar.db';
                /**Creamos objeto CalendarDataAcces permite acceder a la bd */
                $acceso_datos = new CalendarDataAccess($ruta_bd);

                /**Comprobamos que el correo no existe 
                 * con metodo getUserByEmail de CalendarDataAcces
                 * devuelve user si existe o null si no existe
                 */
                $usuario_existente = $acceso_datos -> getUserByEmail($correo);

                if($usuario_existente !== null){
                    $errores[] = 'Ya existente un usuario registrado con ese correo electronico.';
                }else {
                    /**Como el correo no existe , podemos crear el usuario
                     * Creo el hash de la contraseña se guarda como una cadena que no se puede desencriptar
                     * pasword_hash recibe texto plano y devuelve un has 
                     * con el algoritmo PASSWORD_DEFAULT
                     */
                    $hash_contrasena = password_hash($contrasena, PASSWORD_DEFAULT);

                    /**Crear objeto User con sus parametros en ese orden */
                    $usuario = new User(
                        $correo,
                        $hash_contrasena,
                        $nombre,
                        $apellidos,
                        $fecha_nacimiento,
                        '',
                        null /**El id nul por que lo asignara la bd */

                    );

                    /**Guardar al usuario en la BD 
                     * Metodo createUser de CalendarDataAcces inserta el usuario
                     * en la BD
                     * devuelve tru se todo va bien false si hay un error
                    */
                    $creado_ok =$acceso_datos -> createUser($usuario);
                    if($creado_ok){
                        /**Usuario creado correctamente */
                        $usuario_creado = true;
                        /**Limpio campos para dejar formulario vacio*/
                        $correo='';
                        $nombre='';
                        $apellidos='';
                        $fecha_nacimiento ='';

                    }else{
                        $errores[]='Se ha producido un error al crear el usuario en la base de datos.';

                    }
                }
        }

    }
?> 

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de usuario</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        .contenedor {
            max-width: 500px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .errores {
            background-color: #ffcccc;
            color: #cc0000;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 3px;
            border-left: 4px solid #cc0000;
        }
        .errores ul {
            margin: 0;
            padding-left: 20px;
        }
        .exito {
            background-color: #ccffcc;
            color: #009900;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 3px;
            border-left: 4px solid #009900;
        }
        .campo {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="date"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 3px;
            box-sizing: border-box;
        }
        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus,
        input[type="date"]:focus {
            outline: none;
            border-color: #4CAF50;
            box-shadow: 0 0 5px rgba(76, 175, 80, 0.3);
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }
        button:hover {
            background-color: #45a049;
        }
        .enlaces {
            text-align: center;
            margin-top: 20px;
        }
        .enlaces a {
            color: #4CAF50;
            text-decoration: none;
        }
        .enlaces a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="contenedor">
        <h1>Registro de usuario</h1>

        <!-- Mostrar errores de validación si los hay -->
        <?php if (!empty($errores)): ?>
            <div class="errores">
                <ul>
                    <?php foreach ($errores as $mensaje_error): ?>
                        <!-- htmlspecialchars() evita inyección de código malicioso -->
                        <li><?= htmlspecialchars($mensaje_error, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Mostrar mensaje de éxito O formulario -->
        <?php if ($usuario_creado): ?>
            <div class="exito">
                <p><strong>¡Usuario creado correctamente!</strong></p>
                <p>Ahora puedes iniciar sesión con tu correo y contraseña.</p>
            </div>
            <div class="enlaces">
                <p><a href="index.php">← Volver a la página de login</a></p>
            </div>
        <?php else: ?>
            <form method="post" action="register.php">
                <!-- Campo CORREO -->
                <div class="campo">
                    <label for="correo">Correo electrónico:</label>
                    <input
                        type="email"
                        id="correo"
                        name="correo"
                        required
                        value="<?= htmlspecialchars($correo, ENT_QUOTES, 'UTF-8') ?>"
                    >
                </div>

                <!-- Campo NOMBRE -->
                <div class="campo">
                    <label for="nombre">Nombre:</label>
                    <input
                        type="text"
                        id="nombre"
                        name="nombre"
                        required
                        value="<?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?>"
                    >
                </div>

                <!-- Campo APELLIDOS -->
                <div class="campo">
                    <label for="apellidos">Apellidos:</label>
                    <input
                        type="text"
                        id="apellidos"
                        name="apellidos"
                        required
                        value="<?= htmlspecialchars($apellidos, ENT_QUOTES, 'UTF-8') ?>"
                    >
                </div>

                <!-- Campo FECHA DE NACIMIENTO -->
                <div class="campo">
                    <label for="fecha_nacimiento">Fecha de nacimiento:</label>
                    <input
                        type="date"
                        id="fecha_nacimiento"
                        name="fecha_nacimiento"
                        required
                        value="<?= htmlspecialchars($fecha_nacimiento, ENT_QUOTES, 'UTF-8') ?>"
                    >
                </div>

                <!-- Campo CONTRASEÑA -->
                <div class="campo">
                    <label for="contrasena">Contraseña:</label>
                    <input
                        type="password"
                        id="contrasena"
                        name="contrasena"
                        required
                    >
                </div>

                <!-- Campo REPETIR CONTRASEÑA -->
                <div class="campo">
                    <label for="repetir_contrasena">Repetir contraseña:</label>
                    <input
                        type="password"
                        id="repetir_contrasena"
                        name="repetir_contrasena"
                        required
                    >
                </div>

                <!-- Botón ENVIAR -->
                <div class="campo">
                    <button type="submit">Registrarse</button>
                </div>
            </form>

            <!-- Enlaces útiles -->
            <div class="enlaces">
                <p>¿Ya tienes cuenta? <a href="index.php">Inicia sesión aquí</a></p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
    




