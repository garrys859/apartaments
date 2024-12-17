<?php
include('error.inc');
// Incluye la conexión a la base de datos
include('../php/conecta.inc');
// Verifica si el usuario ha iniciado sesión y si tiene el rol de 'administrador'
include('seguridad.inc');
// Variables para los filtros
$filtro = $_GET['filtro'] ?? 'diario';
$fecha_inicio = '';
$fecha_fin = date('Y-m-d H:i:s');

switch ($filtro) {
    case 'semanal':
        $fecha_inicio = date('Y-m-d H:i:s', strtotime('-7 days'));
        break;
    case 'mensual':
        $fecha_inicio = date('Y-m-d H:i:s', strtotime('-1 month'));
        break;
    case 'anual':
        $fecha_inicio = date('Y-m-d H:i:s', strtotime('-1 year'));
        break;
    default:
        $fecha_inicio = date('Y-m-d 00:00:00');
}

// Consultas para las estadísticas
try {
    $stmtTotalClientes = $pdoAdmin->prepare('SELECT COUNT(*) AS total FROM Usuarios WHERE fecha_registro BETWEEN ? AND ?');
    $stmtTotalClientes->execute([$fecha_inicio, $fecha_fin]);
    $totalClientes = $stmtTotalClientes->fetch(PDO::FETCH_ASSOC)['total'];

    $stmtTotalApartamentos = $pdoAdmin->prepare('SELECT COUNT(*) AS total FROM Apartamentos WHERE fecha_creacion BETWEEN ? AND ?');
    $stmtTotalApartamentos->execute([$fecha_inicio, $fecha_fin]);
    $totalApartamentos = $stmtTotalApartamentos->fetch(PDO::FETCH_ASSOC)['total'];

    $stmtTotalReservas = $pdoAdmin->prepare('SELECT COUNT(*) AS total FROM Reservas WHERE fecha_inicio BETWEEN ? AND ?');
    $stmtTotalReservas->execute([$fecha_inicio, $fecha_fin]);
    $totalReservas = $stmtTotalReservas->fetch(PDO::FETCH_ASSOC)['total'];

    $stmtIngresos = $pdoAdmin->prepare('SELECT SUM(monto_pagado) AS total FROM Pagos WHERE fecha_pago BETWEEN ? AND ?');
    $stmtIngresos->execute([$fecha_inicio, $fecha_fin]);
    $totalIngresos = $stmtIngresos->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    // Consultar los apartamentos con menos visitas
    $stmtVisitas = $pdoAdmin->prepare("
        SELECT ap.id_apartamento, ap.titulo, COUNT(va.id_visita) AS total_visitas 
        FROM Apartamentos ap 
        LEFT JOIN VisitasApartamentos va ON ap.id_apartamento = va.id_apartamento 
        WHERE va.fecha_visita BETWEEN ? AND ? 
        GROUP BY ap.id_apartamento 
        ORDER BY total_visitas ASC");
    $stmtVisitas->execute([$fecha_inicio, $fecha_fin]);
    $apartamentosMenosVisitados = $stmtVisitas->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    echo 'Error al obtener las estadísticas: ' . $e->getMessage();
}
?>

<!doctype html>
<html lang="es">
    <head>
        <title>Panel de Control</title>
        <link rel="stylesheet" href="css/miestilo.css">
        <meta charset="UTF-8">
    </head>
    <body>
        <div id="contenedor">
            <header>
                <a href="index.php"><h1>Panel de control</h1></a>
                <h2>Información general</h2>
                <a href='productos/panel.php' class="botonmenu">Gestionar productos</a>
                <a href='productos/clientes.php' class="botonmenu">Gestionar clientes</a>
                <a href='productos/auditoria_dashboard.php' class="botonmenu">Auditoría</a>
                <a href='../php/installbd.php' class="botonmenu">Reestablecer base de datos</a>
                <a href='../logout.php' class="botonmenu">Cerrar sesión</a>
                <div style="clear:both;"></div>
            </header>
            
            <section>
                <h2>Estadísticas generales</h2>
                <form method="get" action="">
                    <label for="filtro">Filtrar por:</label>
                    <select name="filtro" id="filtro" onchange="this.form.submit()">
                        <option value="diario" <?= $filtro === 'diario' ? 'selected' : '' ?>>Diario</option>
                        <option value="semanal" <?= $filtro === 'semanal' ? 'selected' : '' ?>>Semanal</option>
                        <option value="mensual" <?= $filtro === 'mensual' ? 'selected' : '' ?>>Mensual</option>
                        <option value="anual" <?= $filtro === 'anual' ? 'selected' : '' ?>>Anual</option>
                    </select>
                </form>

                <h2>Apartamentos menos visitados</h2>
                <table border="1">
                    <thead>
                        <tr>
                            <th>Título</th>
                            <th>Visitas</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($apartamentosMenosVisitados as $apartamento): ?>
                            <tr>
                            <td><?= htmlspecialchars($apartamento['titulo']) ?></td>
                <td><?= $apartamento['total_visitas'] ?></td>
                <td>
                <form method="POST" action="productos/aplicar_descuento.php?id=<?= $apartamento['id_apartamento'] ?>" style="display:inline;">
    <input type="number" name="descuento" placeholder="Descuento (%)" min="-100" max="100" required>
    <button type="submit" class="boton-descuento">Aplicar Descuento</button>
</form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        </div>
    </body>
</html>

<?php
// Incluye el pie de página de la página
include('../php/piedepagina.inc');
?>
