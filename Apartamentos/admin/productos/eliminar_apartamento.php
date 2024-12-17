<?php
// Inicia la sesión y verifica si el usuario es administrador
include('../../php/conecta.inc');
include('../seguridad.inc');
// Verificar si se recibió el ID del apartamento
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die('ID de apartamento no válido.');
}

$id_apartamento = (int)$_GET['id'];

try {
    // Eliminar las imágenes asociadas al apartamento
    $stmtFotos = $pdoAdmin->prepare('SELECT url_foto FROM FotosApartamentos WHERE id_apartamento = ?');
    $stmtFotos->execute([$id_apartamento]);
    $imagenes = $stmtFotos->fetchAll(PDO::FETCH_ASSOC);

    foreach ($imagenes as $imagen) {
        $rutaImagen = '../../photo/' . $imagen['url_foto'];
        if (file_exists($rutaImagen)) {
            unlink($rutaImagen); // Eliminar la imagen del sistema de archivos
        }
    }

    // Eliminar las imágenes de la base de datos
    $stmtDeleteFotos = $pdoAdmin->prepare('DELETE FROM FotosApartamentos WHERE id_apartamento = ?');
    $stmtDeleteFotos->execute([$id_apartamento]);

    // Eliminar el apartamento de la base de datos
    $stmtDeleteApartamento = $pdoAdmin->prepare('DELETE FROM Apartamentos WHERE id_apartamento = ?');
    $stmtDeleteApartamento->execute([$id_apartamento]);

    // Redirigir de nuevo a la página de administración con un mensaje de éxito
    header('Location: panel.php?mensaje=' . urlencode('Apartamento eliminado correctamente.'));
    exit;

} catch (Exception $e) {
    // Mostrar mensaje de error si algo sale mal
    die('Error al eliminar el apartamento: ' . $e->getMessage());
}
