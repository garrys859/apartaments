<?php 
include 'php/cabecera.inc';
include 'php/conecta.inc';

// Función para sanitizar datos
function limpiar($datos) {
    $datos = trim($datos);
    $datos = stripslashes($datos);
    $datos = htmlspecialchars($datos, ENT_QUOTES, 'UTF-8');
    return $datos;
}

// Función para validar la existencia de imágenes
function getImagePath($imagePath, $default = 'default.jpg') {
    return file_exists("photo/" . $imagePath) ? $imagePath : $default;
}

// Consulta SQL: Obtener los apartamentos y su foto de portada
$query = $pdoAdmin->query("
    SELECT 
        ap.id_apartamento, 
        ap.titulo, 
        ap.descripcion, 
        ap.precio, 
        (SELECT fa2.url_foto 
         FROM fotosapartamentos fa2 
         WHERE fa2.id_apartamento = ap.id_apartamento 
         ORDER BY fa2.portada DESC, fa2.id_foto ASC 
         LIMIT 1) AS portada
    FROM apartamentos ap 
    WHERE ap.disponibilidad = 1
")->fetchAll(PDO::FETCH_ASSOC);

// Mostrar apartamentos
if (empty($query)) {
    echo "<p>No hay apartamentos disponibles en este momento.</p>";
} else {
    foreach ($query as $row) {
        // Sanitizar datos
        $id = limpiar($row['id_apartamento']);
        $titulo = limpiar($row['titulo']);
        $descripcion = limpiar($row['descripcion']);
        $precio = limpiar($row['precio']);
        $portada = getImagePath($row['portada']);

        // Generar el HTML de cada apartamento
        echo "<div class='apartamento-card'>";
        // Título con enlace
        echo "<h3><a href='detalle_apartamento.php?id=$id'>$titulo</a></h3>";
        // Imagen con enlace
        echo "<a href='detalle_apartamento.php?id=$id'>
                 <img src='photo/$portada' alt='$titulo' width='200px'>
              </a>";
        // Descripción y precio
        echo "<p>" . nl2br($descripcion) . "</p>";
        echo "<p><strong>Precio:</strong> $precio €</p>";
        echo "</div>";
    }
}

echo "<br>";
$pdoAdmin = null;

include 'php/piedepagina.inc';
?>
