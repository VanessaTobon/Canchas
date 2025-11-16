<?php 
session_start(); 

require_once '../config/config.php'; 
require_once '../includes/header.php';

// Validar si el usuario es admin
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    header('Location: ../public/index.php');
    exit();
}

// Obtener listado de canchas
$query = "SELECT id_cancha, nombre_cancha, tipo_cancha, direccion, capacidad, precio FROM tbl_canchas";
$stmt = $conexion->prepare($query);
$stmt->execute();
$result = $stmt->get_result();
?>

<main class="contenido">
    <h2>Gestionar Canchas</h2>

    <?php if ($result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Tipo</th>
                    <th>Ubicación</th>
                    <th>Capacidad</th>
                    <th>Precio</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['nombre_cancha']) ?></td>
                        <td><?= htmlspecialchars($row['tipo_cancha']) ?></td>
                        <td><?= htmlspecialchars($row['direccion']) ?></td>
                        <td><?= htmlspecialchars($row['capacidad']) ?></td>
                        <td>$<?= number_format($row['precio'], 2) ?></td>
                 
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="mensaje">No hay canchas registradas.</p>
    <?php endif; ?>
</main>

<?php 
$stmt->close();
require_once '../includes/footer.php'; 
?>
