<?php
// Inicia la sesión y verifica si el usuario es administrador
include('../../php/conecta.inc');
include('../seguridad.inc');

// Procesar el formulario al enviarlo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = htmlspecialchars(trim($_POST['titulo']));
    $direccion = isset($_POST['direccion']) ? htmlspecialchars(trim($_POST['direccion'])) : '';
    $ciudad = isset($_POST['ciudad']) ? htmlspecialchars(trim($_POST['ciudad'])) : '';
    $pais = isset($_POST['pais']) ? htmlspecialchars(trim($_POST['pais'])) : '';
    $descripcion = htmlspecialchars(trim($_POST['descripcion']));
    $precio = floatval($_POST['precio']);
    $disponibilidad = isset($_POST['disponibilidad']) ? 1 : 0;

    try {
        // Validar campos obligatorios
        if (empty($direccion) || empty($ciudad) || empty($pais)) {
            throw new Exception("Los campos Dirección, Ciudad y País son obligatorios.");
        }

        // Insertar el apartamento en la tabla Apartamentos
        $stmt = $pdoAdmin->prepare('INSERT INTO Apartamentos (titulo, direccion, ciudad, pais, descripcion, precio, disponibilidad) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$titulo, $direccion, $ciudad, $pais, $descripcion, $precio, $disponibilidad]);
        $id_apartamento = $pdoAdmin->lastInsertId();

        // Subida de imágenes
        if (!empty($_FILES['imagenes']['name'][0])) {
            $carpetaDestino = '../../photo/';
            foreach ($_FILES['imagenes']['tmp_name'] as $index => $tmpName) {
                $nombreImagen = uniqid() . '_' . basename($_FILES['imagenes']['name'][$index]);
                $rutaImagen = $carpetaDestino . $nombreImagen;

                if (move_uploaded_file($tmpName, $rutaImagen)) {
                    // Insertar la ruta de la imagen en la base de datos
                    $stmtImg = $pdoAdmin->prepare('INSERT INTO FotosApartamentos (id_apartamento, url_foto) VALUES (?, ?)');
                    $stmtImg->execute([$id_apartamento, $nombreImagen]);
                }
            }
        }

        $mensaje = 'Apartamento creado correctamente';
    } catch (Exception $e) {
        $error = 'Error al crear el apartamento: ' . $e->getMessage();
    }
}
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Apartamento</title>
    <link rel="stylesheet" href="../../css/miestilo.css">
</head>
<body>
    <h1>Crear Nuevo Apartamento</h1>

    <?php if (isset($mensaje)): ?>
        <p class="exito"><?php echo $mensaje; ?></p>
    <?php elseif (isset($error)): ?>
        <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <label for="titulo">Título:</label>
        <input type="text" name="titulo" id="titulo" required>

        <label for="direccion">Dirección:</label>
        <input type="text" name="direccion" id="direccion" required>

        <label for="ciudad">Ciudad:</label>
        <input type="text" name="ciudad" id="ciudad" required>

        <label for="pais">País:</label>
        <input type="text" name="pais" id="pais" required>

        <label for="descripcion">Descripción:</label>
        <textarea name="descripcion" id="descripcion" required></textarea>

        <label for="precio">Precio:</label>
        <input type="number" step="0.01" name="precio" id="precio" required>

        <label for="disponibilidad">
            <input type="checkbox" name="disponibilidad" id="disponibilidad"> Disponible
        </label>

        <label for="imagenes">Subir Imágenes (múltiples):</label>
        <input type="file" name="imagenes[]" id="imagenes" multiple accept="image/*">

        <button type="submit">Crear Apartamento</button>
    </form>

    <a href="../index.php" class="boton">Volver al panel de administración</a>
</body>
</html>
