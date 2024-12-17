<?php
include('php/conecta.inc');

try {
    $stmt = $pdoAdmin->query('SELECT id_usuario, contrasena FROM Usuarios');
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($usuarios as $usuario) {
        $hashed_password = password_hash($usuario['contrasena'], PASSWORD_DEFAULT);
        $update_stmt = $pdoAdmin->prepare('UPDATE Usuarios SET contrasena = ? WHERE id_usuario = ?');
        $update_stmt->execute([$hashed_password, $usuario['id_usuario']]);
    }
    
    echo "Todas las contraseñas han sido cifradas.";
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
