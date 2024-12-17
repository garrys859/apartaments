<?php
// Inicia la sesión y verifica si el usuario es administrador
include('../../php/conecta.inc');
include('../seguridad.inc');

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die('ID de apartamento no válido.');
}

$id_apartamento = (int)$_GET['id'];
$apartamento = [];
$imagenes = [];

try {
    // Obtener los datos actuales del apartamento
    $stmt = $pdoAdmin->prepare('SELECT * FROM Apartamentos WHERE id_apartamento = ?');
    $stmt->execute([$id_apartamento]);
    $apartamento = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$apartamento) {
        die('Apartamento no encontrado.');
    }

    // Obtener las imágenes actuales del apartamento
    $stmtFotos = $pdoAdmin->prepare('SELECT id_foto, url_foto, portada FROM FotosApartamentos WHERE id_apartamento = ?');
    $stmtFotos->execute([$id_apartamento]);
    $imagenes = $stmtFotos->fetchAll(PDO::FETCH_ASSOC);

    // Verificar si se está procesando el formulario
    $accion = isset($_POST['accion']) ? $_POST['accion'] : '';

    // Acción de eliminar imagen
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $accion === 'eliminar_imagen') {
        $id_foto = (int)$_POST['eliminar_imagen'];

        $stmtImgPath = $pdoAdmin->prepare('SELECT url_foto FROM FotosApartamentos WHERE id_foto = ? AND id_apartamento = ?');
        $stmtImgPath->execute([$id_foto, $id_apartamento]);
        $imagen = $stmtImgPath->fetch(PDO::FETCH_ASSOC);

        if ($imagen) {
            $rutaImagen = '../../photo/' . $imagen['url_foto'];
            if (file_exists($rutaImagen)) {
                unlink($rutaImagen);
            }

            $stmtDeleteImg = $pdoAdmin->prepare('DELETE FROM FotosApartamentos WHERE id_foto = ?');
            $stmtDeleteImg->execute([$id_foto]);
            $mensaje = 'Imagen eliminada correctamente.';
        }
    }

    // Acción de establecer imagen de portada
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $accion === 'establecer_portada') {
        $id_foto = (int)$_POST['id_foto'];

        // Desmarcar la portada de todas las imágenes
        $stmtResetPortada = $pdoAdmin->prepare('UPDATE FotosApartamentos SET portada = 0 WHERE id_apartamento = ?');
        $stmtResetPortada->execute([$id_apartamento]);

        // Marcar la nueva imagen como portada
        $stmtSetPortada = $pdoAdmin->prepare('UPDATE FotosApartamentos SET portada = 1 WHERE id_foto = ?');
        $stmtSetPortada->execute([$id_foto]);

        $mensaje = 'Imagen de portada establecida correctamente.';
    }

    // Acción de actualizar el apartamento
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $accion === 'actualizar') {
        $titulo = htmlspecialchars(trim($_POST['titulo']));
        $direccion = htmlspecialchars(trim($_POST['direccion']));
        $ciudad = htmlspecialchars(trim($_POST['ciudad']));
        $pais = htmlspecialchars(trim($_POST['pais']));
        $descripcion = htmlspecialchars(trim($_POST['descripcion']));
        $precio = floatval($_POST['precio']);
        $disponibilidad = isset($_POST['disponibilidad']) ? 1 : 0;

        $stmtUpdate = $pdoAdmin->prepare('UPDATE Apartamentos SET titulo = ?, direccion = ?, ciudad = ?, pais = ?, descripcion = ?, precio = ?, disponibilidad = ? WHERE id_apartamento = ?');
        $stmtUpdate->execute([$titulo, $direccion, $ciudad, $pais, $descripcion, $precio, $disponibilidad, $id_apartamento]);

        if (isset($_FILES['imagenes']) && !empty($_FILES['imagenes']['name'][0])) {
            $carpetaDestino = '../../photo/';
            foreach ($_FILES['imagenes']['tmp_name'] as $index => $tmpName) {
                if (!empty($tmpName)) {
                    $nombreImagen = uniqid() . '_' . basename($_FILES['imagenes']['name'][$index]);
                    $rutaImagen = $carpetaDestino . $nombreImagen;
                    if (move_uploaded_file($tmpName, $rutaImagen)) {
                        $stmtImg = $pdoAdmin->prepare('INSERT INTO FotosApartamentos (id_apartamento, url_foto) VALUES (?, ?)');
                        $stmtImg->execute([$id_apartamento, $nombreImagen]);
                    }
                }
            }
        }

        header('Location: panel.php?mensaje=' . urlencode('Apartamento actualizado correctamente.'));
        exit;
    }
} catch (Exception $e) {
    $error = 'Error al procesar la solicitud: ' . $e->getMessage();
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Apartamento</title>
    <link rel="stylesheet" href="../../css/miestilo.css">
</head>
<body>
    <h1>Editar Apartamento</h1>

    <?php if (isset($mensaje)): ?>
        <p class="exito"><?php echo $mensaje; ?></p>
    <?php elseif (isset($error)): ?>
        <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="accion" value="actualizar">
    <label for="titulo">Título:</label>
    <input type="text" name="titulo" id="titulo" value="<?php echo htmlspecialchars($apartamento['titulo']); ?>" required>

    <label for="direccion">Dirección:</label>
    <input type="text" name="direccion" id="direccion" value="<?php echo htmlspecialchars($apartamento['direccion']); ?>" required>

    <label for="ciudad">Ciudad:</label>
    <input type="text" name="ciudad" id="ciudad" value="<?php echo htmlspecialchars($apartamento['ciudad']); ?>" required>

    <label for="pais">País:</label>
    <input type="text" name="pais" id="pais" value="<?php echo htmlspecialchars($apartamento['pais']); ?>" required>

    <label for="descripcion">Descripción:</label>
    <textarea name="descripcion" id="descripcion" required><?php echo htmlspecialchars($apartamento['descripcion']); ?></textarea>

    <label for="precio">Precio:</label>
    <input type="number" step="0.01" name="precio" id="precio" value="<?php echo htmlspecialchars($apartamento['precio']); ?>" required>

    <label for="imagenes">Subir Nuevas Imágenes (múltiples):</label>
    <input type="file" name="imagenes[]" id="imagenes" multiple accept="image/*">

    <label for="disponibilidad">
        <input type="checkbox" name="disponibilidad" id="disponibilidad" <?php echo $apartamento['disponibilidad'] ? 'checked' : ''; ?>> Disponible
    </label>

    <button type="submit">Actualizar Apartamento</button>
</form>

<div class="galeria">
    <?php foreach ($imagenes as $imagen): ?>
        <div>
            <img src="../../photo/<?php echo htmlspecialchars($imagen['url_foto']); ?>" width="100" alt="Imagen">
            
            <!-- Formulario para eliminar imagen -->
            <form method="POST" style="display:inline;">
                <input type="hidden" name="accion" value="eliminar_imagen">
                <input type="hidden" name="eliminar_imagen" value="<?php echo $imagen['id_foto']; ?>">
                <button type="submit" class="boton-eliminar">Eliminar</button>
            </form>

            <!-- Formulario para establecer portada -->
            <form method="POST" style="display:inline;">
                <input type="hidden" name="accion" value="establecer_portada">
                <input type="hidden" name="id_foto" value="<?php echo $imagen['id_foto']; ?>">
                <button type="submit" class="boton-portada" <?php echo $imagen['portada'] ? 'disabled' : ''; ?>>
                    <?php echo $imagen['portada'] ? 'Portada actual' : 'Establecer como portada'; ?>
                </button>
            </form>
        </div>
    <?php endforeach; ?>
</div>


    <a href="eliminar_apartamento.php?id=<?= $apartamento['id_apartamento'] ?>" class="boton-eliminar" onclick="return confirm('¿Estás seguro de que deseas eliminar este apartamento?');">Eliminar Apartamento</a>
    <a href="panel.php" class="boton">Volver al panel de administración</a>
    
</body>
</html>
