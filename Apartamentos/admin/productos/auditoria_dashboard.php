<?php
include('../../php/conecta.inc');

try {
    $stmt = $pdoAdmin->prepare('SELECT * FROM Auditoria ORDER BY fecha_accion DESC');
    $stmt->execute();
    $auditorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die('Error al obtener la auditoría: ' . $e->getMessage());
}
?>
<!doctype html>
<html lang="es">
<head>
    <title>Auditoría</title>
</head>
<body>
    <h1>Registro de Auditoría</h1>

    <table border="1">
        <thead>
            <tr>
                <th>Usuario</th>
                <th>Tabla</th>
                <th>Acción</th>
                <th>Datos Anteriores</th>
                <th>Datos Nuevos</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($auditorias as $registro): ?>
                <tr>
                    <td><?= $registro['usuario'] ?></td>
                    <td><?= $registro['tabla'] ?></td>
                    <td><?= $registro['accion'] ?></td>
                    <td><?= $registro['datos_anteriores'] ?></td>
                    <td><?= $registro['datos_nuevos'] ?></td>
                    <td><?= $registro['fecha_accion'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <a href="../index.php">Volver al Panel</a>
</body>
</html>