<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/base-code/data-access/CalendarDataAccess.php';
require_once __DIR__ . '/base-code/entities/User.php';

redirigir_si_esta_autenticado();
iniciar_sesion_si_es_necesario();

/**Inicializar variables vacias donde se guarda lo introducido por el usuario */
$correo ='';
$contrasena='';

/**Array acumulador errores */
$errores=[];

/**Bandera credenciales invalidas */
$login_error = false;

/**Procesa si formulario se envio por POST */
if($_SERVER['REQUEST_METHOD']==='POST'){
    $correo =trim($_POST['correo'] ?? '');
    $contrasena= ($_POST['contrasena'] ?? '');

    if($correo === ''){
        $errores[] = 'El correo electronico es obligatorio.';
    }elseif(!filter_var($correo, FILTER_VALIDATE_EMAIL)){
        $errores[]='El formato del correo electronico no es valido.';
    }
    if($contrasena ===''){
        $errores[]='La contraseña es obligatoria.';
    }

    /**Si no hay problemas d validacion comprobar en bd */
    if(empty($errores)){
        /**Ruta a la BD */
        $ruta_bd = __DIR__ . '/base-code/calendar.db';
        /**Objeto CalendarDataAcces permitre acceder a BD */
        $acceso_datos = new CalendarDataAccess($ruta_bd);

        /**Buscamos usuario x correo
         * con metodo getUserByEmail de CalendarDataAccess
         * devvulve user si existe o nul si no existe
         */
        $usuario = $acceso_datos ->getUserByEmail($correo);

        /**Comprobamos que el usuario existe */
        if($usuario === null){
            /**El correo no existe enla bd */
            $login_error = true;
        }else{
            /**ususairo existe comprobamos contraseña 
             * pasword_verify comapara textoplano con el hash
             * devuelve true si son iguales false si no*/
            if(\password_verify($contrasena, $usuario->getPassword())){

                /**Contraseña correcta guardamos los datos en sesion */
                $_SESSION['id_usuario'] = $usuario->getId();
                $_SESSION['correo_usuario'] = $usuario->getEmail();
                $_SESSION['nombre_usuario'] = $usuario->getFirstName();

                /**Redirigir a events */
                redirigir('events.php');
            }else{
                $login_error=true;
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
    <title>Login - Calendario</title>
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
        .error-login {
            background-color: #ffcccc;
            color: #cc0000;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 3px;
            border-left: 4px solid #cc0000;
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
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 3px;
            box-sizing: border-box;
        }
        input[type="email"]:focus,
        input[type="password"]:focus {
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
        <h1>Iniciar sesión</h1>

        <!-- Mostrar error si las credenciales son invalidas -->
        <?php if($login_error): ?>
            <div class="error-login">
                <p><strong>Error de autenticación</strong></p>
                <p>El correo o la contraseña son incorrectos.</p>
            </div>
        <?php endif; ?>

        <!-- Mostrar errores de validacion si los hay -->
        <?php if(!empty($errores)): ?>
            <div class="errores">
                <ul>
                    <?php foreach($errores as $mensaje_error): ?>
                        <!-- htmlspecialchars() evita inyección de código malicioso -->
                        <li><?= htmlspecialchars($mensaje_error, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Formulario de login -->
        <form method="post" action="index.php">
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

            <!-- Botón ENVIAR -->
            <div class="campo">
                <button type="submit">Iniciar sesión</button>
            </div>
        </form>

        <!-- Enlaces utiles -->
        <div class="enlaces">
            <p>¿No tienes cuenta? <a href="register.php">Regístrate aquí</a></p>
        </div>
    </div>
</body>
</html>
