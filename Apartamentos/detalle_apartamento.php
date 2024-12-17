<?php
include 'php/conecta.inc';

// Verificar el ID del apartamento
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID de apartamento no válido.");
}

$id_apartamento = (int) $_GET['id'];

// Registrar la visita
try {
    $stmtVisita = $pdoAdmin->prepare('INSERT INTO VisitasApartamentos (id_apartamento) VALUES (?)');
    $stmtVisita->execute([$id_apartamento]);

    // Consultar la información del apartamento
    $stmtApartamento = $pdoAdmin->prepare('SELECT * FROM Apartamentos WHERE id_apartamento = ?');
    $stmtApartamento->execute([$id_apartamento]);
    $apartamento = $stmtApartamento->fetch(PDO::FETCH_ASSOC);

    // Consultar imágenes adicionales
    $stmtFotos = $pdoAdmin->prepare('SELECT url_foto FROM FotosApartamentos WHERE id_apartamento = ?');
    $stmtFotos->execute([$id_apartamento]);
    $fotos = $stmtFotos->fetchAll(PDO::FETCH_ASSOC);

    if (!$apartamento) {
        die("Apartamento no encontrado.");
    }
} catch (Exception $e) {
    die("Error al cargar la información del apartamento: " . $e->getMessage());
}
?>

<!doctype html>
<html lang="es">
    <head>
        <title><?= htmlspecialchars($apartamento['titulo']) ?></title>
        <link rel="stylesheet" href="css/miestilo.css">
        <meta charset="UTF-8">
    </head>
    <body>
        <h1><?= htmlspecialchars($apartamento['titulo']) ?></h1>
        <div class="galeria">
            <?php foreach ($fotos as $foto): ?>
                <img src="photo/<?= htmlspecialchars($foto['url_foto']) ?>" alt="Foto del apartamento" width="300px">
            <?php endforeach; ?>
        </div>


        <p><strong>Dirección:</strong></p>
        <p><?= nl2br(htmlspecialchars($apartamento['direccion'])) ?></p>
        <p><strong>Ciudad:</strong></p>
        <p><?= nl2br(htmlspecialchars($apartamento['ciudad'])) ?></p>

        <p><strong>Descripción:</strong></p>
        <p><?= nl2br(htmlspecialchars($apartamento['descripcion'])) ?></p>

        <p><strong>Precio:</strong> €<?= number_format($apartamento['precio'], 2) ?></p>

        <a href="index.php" class="boton">Volver al inicio</a>
    </body>
</html>
