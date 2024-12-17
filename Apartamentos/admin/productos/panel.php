<?php
include('../error.inc');
// Incluye la conexión a la base de datos
include('../../php/conecta.inc');
// Verifica si el usuario ha iniciado sesión y si tiene el rol de 'administrador'
include('../seguridad.inc');
?>

<!doctype html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Gestionar Apartamentos</title>
        <link rel="stylesheet" href="../css/miestilo.css">
    </head>
    <body>
        <div class="admin-container">
            <header>
                <h1>Gestor de Apartamentos</h1>
                <nav>
                    <a href="../index.php">Volver al Panel</a>
                    <a href="../../logout.php">Cerrar sesión</a>
                </nav>
            </header>

            <section>
                <h2>Apartamentos</h2>
                <a href="crear_apartamento.php" class="boton">Nuevo Apartamento</a>
                <table border="1">
                    <thead>
                        <tr>
                            <th>Imagen</th>
                            <th>Título</th>
                            <th>Descripción</th>
                            <th>Precio</th>
                            <th>Disponibilidad</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        try {
                            $stmt = $pdoAdmin->query("
    SELECT 
        ap.id_apartamento, 
        ap.titulo, 
        ap.descripcion, 
        ap.precio,
        ap.disponibilidad, 
        (SELECT fa2.url_foto 
         FROM fotosapartamentos fa2 
         WHERE fa2.id_apartamento = ap.id_apartamento 
         ORDER BY fa2.portada DESC, fa2.id_foto ASC 
         LIMIT 1) AS portada
    FROM apartamentos ap 
    WHERE ap.disponibilidad = 1");
                            while ($apartamento = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo '<tr>';
                                echo '<td><img src="../../photo/' . htmlspecialchars($apartamento['portada']) . '" width="50" height="50"></td>';
                                echo '<td>' . htmlspecialchars($apartamento['titulo']) . '</td>';
                                echo '<td>' . htmlspecialchars($apartamento['descripcion']) . '</td>';
                                echo '<td>' . htmlspecialchars($apartamento['precio']) . ' &euro;</td>';
                                echo '<td>' . htmlspecialchars($apartamento['disponibilidad']) . '</td>';
                                echo '<td>
                                        <a href="editar_apartamento.php?id=' . $apartamento['id_apartamento'] . '" class="boton">Editar</a>
                                        <a href="eliminar_apartamento.php?id=' . $apartamento['id_apartamento'] . '" class="boton eliminar">Eliminar</a>
                                      </td>';
                                echo '</tr>';
                            }
                        } catch (Exception $e) {
                            echo '<tr><td colspan="5">Error al recuperar los apartamentos: ' . $e->getMessage() . '</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </section>
        </div>
    </body>
</html>

<?php
// Incluye el pie de página de la página
include('../../php/piedepagina.inc');
?>
