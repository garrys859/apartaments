<?php
// Inicia la sesión y verifica si el usuario es administrador
include('../../php/conecta.inc');
include('../seguridad.inc');

// Verificar si se recibió el ID del apartamento y el porcentaje de descuento
if (!isset($_GET['id']) || empty($_GET['id']) || !isset($_POST['descuento']) || empty($_POST['descuento'])) {
    die('ID de apartamento o porcentaje de descuento no válido.');
}

$id_apartamento = (int)$_GET['id'];
$porcentaje_descuento = floatval($_POST['descuento']);

// Validar que el porcentaje de descuento esté dentro de un rango aceptable (-100% a 100%)
if ($porcentaje_descuento < -100 || $porcentaje_descuento > 100) {
    die('El porcentaje de descuento debe estar entre -100 y 100.');
}

try {
    // Obtener el precio actual del apartamento
    $stmtPrecio = $pdoAdmin->prepare('SELECT precio FROM Apartamentos WHERE id_apartamento = ?');
    $stmtPrecio->execute([$id_apartamento]);
    $apartamento = $stmtPrecio->fetch(PDO::FETCH_ASSOC);

    if (!$apartamento) {
        die('Apartamento no encontrado.');
    }

    $precio_actual = floatval($apartamento['precio']);
    
    // Calcular el nuevo precio con el descuento aplicado
    $nuevo_precio = $precio_actual + ($precio_actual * ($porcentaje_descuento / 100)); // Permite aplicar descuentos o recargos
    $nuevo_precio = round($nuevo_precio, 2); // Redondear a 2 decimales

    // Actualizar el precio en la base de datos
    $stmtUpdate = $pdoAdmin->prepare('UPDATE Apartamentos SET precio = ? WHERE id_apartamento = ?');
    $stmtUpdate->execute([$nuevo_precio, $id_apartamento]);

    // Insertar el registro de auditoría
    $stmtAudit = $pdoAdmin->prepare('INSERT INTO Auditoria (id_apartamento, accion, precio_anterior, precio_nuevo, fecha_accion) VALUES (?, ?, ?, ?, NOW())');
    $stmtAudit->execute([$id_apartamento, 'Aplicación de descuento', $precio_actual, $nuevo_precio]);

    // Redirigir de nuevo a la página de administración con un mensaje de éxito
    header('Location: ../index.php?mensaje=' . urlencode('Descuento aplicado correctamente. Nuevo precio: ' . $nuevo_precio . ' €'));
    exit;

} catch (Exception $e) {
    // Mostrar mensaje de error si algo sale mal
    die('Error al aplicar el descuento: ' . $e->getMessage());
}
