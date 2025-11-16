<?php 
session_start();
require_once '../config/config.php';
require_once '../controllers/reservaController.php';

// No permitir acceso sin login
if (!isset($_SESSION['usuario'])) {
    header("Location: ../public/login.php");
    exit;
}

$id_usuario = intval($_SESSION['usuario']['id_usuario']);
$mensaje = '';
$hoy = date('Y-m-d');

// Crear controlador
$reservaController = new ReservaController();

/* =====================================================
   PROCESAR CANCELACIÓN - SOLO CLIENTE
===================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['cancelar_id'], $_POST['motivo'])) {

        $id_reserva = intval($_POST['cancelar_id']);
        $motivo = trim($_POST['motivo']);

        if (strlen($motivo) < 3 || strlen($motivo) > 255) {
            $mensaje = "El motivo debe tener entre 3 y 255 caracteres.";
        } else {
            $resultado = $reservaController->cancelarReservaCliente($id_reserva, $motivo);
            $mensaje = $reservaController->getMensaje();
        }
    }
}

// Obtener reservas del usuario
$reservas = $reservaController->obtenerReservasUsuario($id_usuario);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Reservas</title>
</head>
<body>

<?php require_once '../includes/header.php'; ?>

<style>
</style>

<main class="reservas-container">
    <h2>📋 Mis Reservas</h2>

    <?php if ($mensaje): ?>
        <div class="mensaje <?= str_contains($mensaje, '❌') ? 'mensaje-error' : 'mensaje-exito' ?>">
            <?= htmlspecialchars($mensaje) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($reservas)): ?>
        <table class="tabla-reservas">
            <thead>
                <tr>
                    <th>ID</th><th>Cancha</th><th>Fecha</th><th>Hora Inicio</th><th>Fin</th>
                    <th>Estado</th><th>Motivo</th><th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($reservas as $r):

                $estado = strtolower($r['estado']);
                $puedeCancelar =
                    ($estado === 'pendiente' || $estado === 'confirmada')
                    && $r['fecha_reserva'] >= $hoy;
            ?>
                <tr class="reserva-<?= $estado ?>">
                    <td><b>#<?= $r['id_reserva'] ?></b></td>
                    <td><?= htmlspecialchars($r['nombre_cancha']) ?></td>
                    <td><?= htmlspecialchars($r['fecha_reserva']) ?></td>
                    <td><?= substr($r['hora_inicio'], 0, 5) ?></td>
                    <td><?= substr($r['hora_fin'], 0, 5) ?></td>

                    <td><span class="estado-badge estado-<?= $estado ?>"><?= ucfirst($estado) ?></span></td>

                    <td>
                        <?= $estado === 'cancelada'
                            ? htmlspecialchars($r['motivo_cancelacion'] ?? "Sin motivo")
                            : "<span class='texto-muted'>-</span>" ?>
                    </td>

                    <td>
                         <?php if ($puedeCancelar): ?>
                            <form method="POST" class="form-cancelacion">
                                <input type="hidden" name="cancelar_id" value="<?= $r['id_reserva'] ?>">
                                <input required minlength="3" maxlength="255"
                                    class="campo-motivo" name="motivo"
                                    placeholder="Motivo cancelación">

                                <button class="boton boton-cancelar">Cancelar</button>
                            </form>

                            <?php else: ?>
                                <span class="texto-muted">
                                    <?php
                                    // Usamos el estado real
                                    if ($estado === 'cancelada') {
                                        echo 'Cancelada';
                                    } elseif ($estado === 'completada') {
                                        echo 'Completada';
                                    } elseif ($r['fecha_reserva'] < $hoy) {
                                        // Reserva pasada pero no cancelada ni marcada como completada
                                        echo 'Vencida';
                                    } else {
                                        // Cualquier otro caso raro
                                        echo '-';
                                    }
                                    ?>
                                </span>
                            <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>

    <?php else: ?>
        <div class="sin-reservas">
            📭 No tienes reservas aún.<br><br>
            <a href="../public/reservar.php" class="boton boton-principal">🎾 Reservar ahora</a>
        </div>
    <?php endif ?>
</main>

<script>
setTimeout(() => {
    document.querySelectorAll('.mensaje').forEach(el => {
        el.style.opacity = '0';
        setTimeout(() => el.remove(), 500);
    });
}, 5000);

// Confirmación antes de cancelar
document.addEventListener("submit", (e) => {
    if (e.target.classList.contains("form-cancelacion")) {
        if(!confirm("¿Seguro que deseas cancelar la reserva?")) {
            e.preventDefault();
        }
    }
});
</script>

<?php require '../includes/footer.php'; ?>

</body>
</html>
