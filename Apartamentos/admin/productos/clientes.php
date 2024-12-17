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
        <title>Gestionar Clientes</title>
        <link rel="stylesheet" href="../css/miestilo.css">
    </head>
    <body>
        <div class="admin-container">
            <header>
                <h1>Gestor de Clientes</h1>
                <nav>
                    <a href="../index.php">Volver al Panel</a>
                    <a href="../../logout.php">Cerrar sesión</a>
                </nav>
            </header>

            <section>
                <h2>Clientes</h2>
                
                <table border="1">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Correo</th>
                            <th>Teléfono</th>
                            <th>Rol</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        try {
                            $stmt = $pdoAdmin->query('SELECT * FROM Usuarios');
                            while ($usuario = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                $rol = $usuario['administrador'] ? 'Administrador' : 'Cliente';
                                echo '<tr>';
                                echo '<td>' . htmlspecialchars($usuario['nombre']) . '</td>';
                                echo '<td>' . htmlspecialchars($usuario['correo_electronico']) . '</td>';
                                echo '<td>' . htmlspecialchars($usuario['telefono']) . '</td>';
                                echo '<td>' . $rol . '</td>';
                                echo '<td>
                                        <a href="promocionar_usuario.php?id=' . $usuario['id_usuario'] . '" class="boton">' . ($usuario['administrador'] ? 'Hacer Cliente' : 'Hacer Administrador') . '</a>
                                        <a href="eliminar_usuario.php?id=' . $usuario['id_usuario'] . '" class="boton eliminar">Eliminar</a>
                                      </td>';
                                echo '</tr>';
                            }
                        } catch (Exception $e) {
                            echo '<tr><td colspan="5">Error al recuperar los clientes: ' . $e->getMessage() . '</td></tr>';
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
