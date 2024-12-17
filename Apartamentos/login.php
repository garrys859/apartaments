<?php
include('admin/error.inc');
// Inicia la sesión para manejar las variables de sesión
session_start();

// Incluye la conexión a la base de datos
include('php/conecta.inc');

// Verifica si la conexión está funcionando
if (!isset($pdoAdmin)) {
    die('Error: La conexión a la base de datos no está disponible.');
}

// Verifica si el formulario fue enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = $_POST['correo'] ?? '';
    $contrasena = $_POST['contrasena'] ?? '';
    
    // Verifica que los campos no estén vacíos
    if (!empty($correo) && !empty($contrasena)) {
        try {
            // Prepara la consulta SQL para evitar inyecciones SQL
            $stmt = $pdoAdmin->prepare('SELECT * FROM Usuarios WHERE correo_electronico = ?');
            $stmt->execute([$correo]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verifica si se encontró el usuario y si la contraseña coincide
            if ($usuario && password_verify($contrasena, $usuario['contrasena'])) {
                // Almacena los datos de sesión
                $_SESSION['id_usuario'] = $usuario['id_usuario'];
                $_SESSION['nombre'] = $usuario['nombre'];
                $_SESSION['correo'] = $usuario['correo_electronico'];
                $_SESSION['administrador'] = (bool) $usuario['administrador'];
                
                // Redirige según el tipo de usuario
                if ($_SESSION['administrador']) {
                    header('Location: admin/index.php');
                } else {
                    header('Location: index.php');
                }
                exit;
            } else {
                $error = 'Correo o contraseña incorrectos.';
            }
        } catch (Exception $e) {
            $error = 'Error al conectar con la base de datos: ' . $e->getMessage();
        }
    } else {
        $error = 'Por favor, complete todos los campos.';
    }
}
?>

<!doctype html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Iniciar sesión</title>
        <link rel="stylesheet" href="css/miestilo.css">
    </head>
    <body>
        <div class="login-container">
            <h1>Iniciar sesión</h1>
            <?php if (!empty($error)) : ?>
                <p class="error"> <?php echo $error; ?> </p>
            <?php endif; ?>
            <form action="login.php" method="post">
                <label for="correo">Correo electrónico</label>
                <input type="email" name="correo" id="correo" required>
                
                <label for="contrasena">Contraseña</label>
                <input type="password" name="contrasena" id="contrasena" required>
                
                <button type="submit">Iniciar sesión</button>
            </form>
        </div>
    </body>
</html>
