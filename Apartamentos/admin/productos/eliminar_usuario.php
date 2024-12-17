<?php
// Incluye la conexión a la base de datos
include('../../php/conecta.inc');
// Verifica si el usuario ha iniciado sesión y si tiene el rol de 'administrador'
include('../seguridad.inc');

// Verifica si se recibió el ID del usuario
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: clientes.php?error=' . urlencode('ID no válido'));
    exit;
}

$id_usuario = (int) $_GET['id'];

try {
    // Consultar si el usuario es administrador
    $stmt = $pdoAdmin->prepare('SELECT administrador, correo_electronico FROM Usuarios WHERE id_usuario = ?');
    $stmt->execute([$id_usuario]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        // Verificar si el usuario es administrador y bloquear la eliminación
        if ($usuario['administrador'] == 1) {
            header('Location: clientes.php?error=' . urlencode('No se puede eliminar a un administrador.'));
            exit;
        }
        
        // Eliminar el usuario de la base de datos
        $stmt_delete = $pdoAdmin->prepare('DELETE FROM Usuarios WHERE id_usuario = ?');
        $stmt_delete->execute([$id_usuario]);
        
        header('Location: clientes.php?success=Usuario eliminado correctamente.');
    } else {
        header('Location: clientes.php?error=' . urlencode('Usuario no encontrado.'));
        exit;
    }
} catch (Exception $e) {
    header('Location: clientes.php?error=' . urlencode('Error al eliminar el usuario: ' . $e->getMessage()));
}
exit;
