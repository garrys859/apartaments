<?php
// Incluye la seguridad para controlar acceso y la conexión a la base de datos
include('../../php/conecta.inc');
include('../seguridad.inc');
// Verifica si se recibió el ID del usuario
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: clientes.php?error=' . urlencode('ID no válido'));
    exit;
}

$id_usuario = (int) $_GET['id'];

try {
    // Consultar el rol actual del usuario
    $stmt = $pdoAdmin->prepare('SELECT administrador, correo_electronico FROM Usuarios WHERE id_usuario = ?');
    $stmt->execute([$id_usuario]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        // Verificar si el usuario es "Jorge" para evitar cambios en su rol
        if ($usuario['correo_electronico'] === 'Jorge@gmail.com') {
            header('Location: clientes.php?error=' . urlencode('No se puede cambiar el rol del administrador principal.'));
            exit;
        }

        // Cambiar el rol del usuario (si es cliente lo hacemos administrador y viceversa)
        $nuevo_rol = $usuario['administrador'] ? 0 : 1;
        $stmt_update = $pdoAdmin->prepare('UPDATE Usuarios SET administrador = ? WHERE id_usuario = ?');
        $stmt_update->execute([$nuevo_rol, $id_usuario]);
    } else {
        header('Location: clientes.php?error=' . urlencode('Usuario no encontrado.'));
        exit;
    }
} catch (Exception $e) {
    header('Location: clientes.php?error=' . urlencode('Error al cambiar el rol: ' . $e->getMessage()));
    exit;
}

header('Location: clientes.php');
exit;