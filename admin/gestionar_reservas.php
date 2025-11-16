<?php
require_once '../includes/header.php';
require_once '../services/ReservaFacade.php';
require_once '../controllers/reservaController.php';

// Verificar rol administrador
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    header("Location: ../public/login.php");
    exit;
}

$facade     = new ReservaFacade();
$controller = new ReservaController();
$mensaje    = "";

// ======================================================
// PROCESAR ACCIONES DEL ADMIN
// ======================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["id_reserva"])) {

    $id_reserva = intval($_POST['id_reserva']);

    if (isset($_POST['confirmar'])) {
        $mensaje = $facade->confirmarReserva($id_reserva);
    }

    if (isset($_POST['completar'])) {
        $mensaje = $facade->completarReserva($id_reserva);
    }

    if (isset($_POST['cancelar_admin'])) {
        $motivo = trim($_POST['motivo']);
        if (strlen($motivo) < 2) {
            $mensaje = "⚠ El motivo debe tener al menos 2 caracteres";
        } else {
            $mensaje = $facade->cancelarReserva($id_reserva, $motivo);
        }
    }
}

// ======================================================
// OBTENER RESERVAS
// ======================================================
$filtro_estado = $_GET['estado'] ?? "";
$reservas      = $facade->obtenerTodasLasReservas($filtro_estado);
?>

<main class="contenido">

<h2>Gestión de Reservas</h2>

<?php if ($mensaje): ?>
    <p class="mensaje"><?= $mensaje ?></p>
<?php endif; ?>

<!-- FILTRO -->
<form method="GET" class="filtro">
    <label>Filtrar por estado:</label>
    <select name="estado" onchange="this.form.submit()">
        <option value="">Todas</option>
        <option value="pendiente"   <?= $filtro_estado === "pendiente" ? "selected" : "" ?>>Pendientes</option>
        <option value="confirmada"  <?= $filtro_estado === "confirmada" ? "selected" : "" ?>>Confirmadas</option>
        <option value="completada"  <?= $filtro_estado === "completada" ? "selected" : "" ?>>Completadas</option>
        <option value="cancelada"   <?= $filtro_estado === "cancelada" ? "selected" : "" ?>>Canceladas</option>
    </select>
</form>

<!-- TABLA -->
<table>
    <thead>
        <tr>
            <th>Usuario</th>
            <th>Cancha</th>
            <th>Fecha</th>
            <th>Inicio</th>
            <th>Fin</th>
            <th>Estado</th>
            <th>Motivo</th>
            <th>Acciones</th>
        </tr>
    </thead>

<tbody>

<?php if (empty($reservas)): ?>
    <tr><td colspan="8">No hay reservas registradas</td></tr>
<?php endif; ?>

<?php foreach ($reservas as $reserva): ?>
<tr>
    <td><?= $reserva['usuario'] ?? "Sin nombre" ?></td>
    <td><?= $reserva['cancha'] ?? "No registrada" ?></td>
    <td><?= $reserva['fecha_reserva'] ?></td>
    <td><?= $reserva['hora_inicio'] ?></td>
    <td><?= $reserva['hora_fin'] ?></td>
    <td><?= ucfirst($reserva['estado']) ?></td>
    <td><?= $reserva['estado'] === "cancelada" ? ($reserva['motivo_cancelacion'] ?? "-") : "-" ?></td>

    <td>
        <form method="POST">
            <input type="hidden" name="id_reserva" value="<?= $reserva['id_reserva'] ?>">

            <?php if ($reserva['estado'] === "pendiente"): ?>
                <button name="confirmar" class="boton-confirmar">Confirmar</button>
                <button type="button" onclick="mostrarMotivo(<?= $reserva['id_reserva'] ?>)" class="boton-cancelar">Cancelar</button>

            <?php elseif ($reserva['estado'] === "confirmada"): ?>
                <button name="completar" class="boton-completar">Completar</button>
                <button type="button" onclick="mostrarMotivo(<?= $reserva['id_reserva'] ?>)" class="boton-cancelar">Cancelar</button>

            <?php else: ?>
                -
            <?php endif; ?>

            <!-- Campo oculto de motivo -->
            <div id="motivo_<?= $reserva['id_reserva'] ?>" class="motivo-campo" style="display:none;">
                <input type="text" name="motivo" placeholder="Motivo de cancelación">
                <button name="cancelar_admin" class="boton-cancelar">Confirmar cancelación</button>
            </div>
        </form>
    </td>
</tr>
<?php endforeach; ?>

</tbody>
</table>

</main>


<script>
function mostrarMotivo(id) {
    document.getElementById("motivo_" + id).style.display = "block";
}
</script>

<?php require_once '../includes/footer.php'; ?>
