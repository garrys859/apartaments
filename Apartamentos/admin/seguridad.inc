<?php
session_start();
// Verifica si el usuario ha iniciado sesión
if (!isset($_SESSION['id_usuario'])) {
    $login_path = dirname(__FILE__) . '../login.php';
    header('Location: ' . $login_path);
    exit;
}

try {
    // Consulta el rol actual del usuario
    $stmt = $pdoAdmin->prepare('SELECT administrador FROM Usuarios WHERE id_usuario = ?');
    $stmt->execute([$_SESSION['id_usuario']]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verifica si sigue siendo administrador
    if (!$usuario || $usuario['administrador'] == 0) {
        session_destroy();
        header('Location: ../../login.php?error=' . urlencode('Su sesión ha expirado o su acceso fue revocado.'));
        exit;
    }

    // Actualiza la variable de sesión
    $_SESSION['administrador'] = true;
} catch (Exception $e) {
    header('Location: ../../login.php?error=' . urlencode('Error de autenticación.'));
    exit;
}
?>
