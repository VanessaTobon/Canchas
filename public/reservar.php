<?php
require_once '../includes/header.php';
require_once '../controllers/reservaController.php';
require_once __DIR__ . '/../controllers/CanchaController.php';

$canchaController = new CanchaController();

// SI NO HAY SESIÓN, SE ENVÍA AL LOGIN
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

$id_usuario = $_SESSION['usuario']['id_usuario'];
$mensaje = "";

// Obtener canchas con ubicación
$canchas = getCanchasConUbicacion();

// Inicializar campos del formulario
$fecha = $hora_inicio = $hora_fin = $id_cancha = "";

// ======================
//   PROCESAR FORMULARIO
// ======================
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $id_cancha   = intval($_POST["id_cancha"]);
    $fecha       = $_POST["fecha"];
    $hora_inicio = $_POST["hora_inicio"];
    $hora_fin    = $_POST["hora_fin"];

    if ($hora_inicio >= $hora_fin) {
        $mensaje = "La hora de inicio debe ser menor que la hora de fin.";
    } else {
        // LLAMAMOS A LA FUNCIÓN DEL CONTROLLER
        $resultado = crearReserva($id_usuario, $id_cancha, $fecha, $hora_inicio, $hora_fin);

        if ($resultado === true) {
            $mensaje = "Reserva creada exitosamente.";
            // Opcional: limpiar inputs
            $fecha = $hora_inicio = $hora_fin = $id_cancha = "";
        } else {
            $mensaje = "Ya existe una reserva en ese horario.";
        }
    }
}
?>

<main class="container">
    <h2>Reservar Cancha</h2>

    <!-- 🚨 MENSAJE -->
    <?php if (!empty($mensaje)): ?>
        <div style="
            margin:10px 0;
            padding:10px;
            font-weight:bold;
            border-radius:5px;
            background: <?= str_contains($mensaje,'❌') ? '#ffdddd' : '#ddffdd' ?>;
            color: <?= str_contains($mensaje,'❌') ? 'red' : 'green' ?>;
        ">
            <?= $mensaje ?>
        </div>
    <?php endif; ?>

    <!-- FORMULARIO -->
    <form method="POST">

        <label>Selecciona la Cancha:</label>
        <select name="id_cancha" required>
        <?php
        $canchaController = new CanchaController();
        $grouped = $canchaController->obtenerCompositeUbicacionesAsArray();

        foreach ($grouped as $pais => $estados) {
            echo '<optgroup label="' . htmlspecialchars($pais) . '">';
            foreach ($estados as $estado => $municipios) {
                foreach ($municipios as $municipio => $canchas) {
                    foreach ($canchas as $cancha) {
                        $label = sprintf("%s — %s, %s", $cancha['nombre_cancha'], $municipio, $estado);
                        echo '<option value="' . intval($cancha['id_cancha']) . '">' . htmlspecialchars($label) . '</option>';
                    }
                }
            }
            echo '</optgroup>';
        }
        ?>
        </select>
        <label>Fecha:</label>
        <input type="date" name="fecha"
               value="<?= $fecha ?>"
               min="<?= date('Y-m-d'); ?>"
               required>

        <label>Hora de Inicio:</label>
        <input type="time" name="hora_inicio"
               value="<?= $hora_inicio ?>" required>

        <label>Hora de Fin:</label>
        <input type="time" name="hora_fin"
               value="<?= $hora_fin ?>" required>

        <button type="submit">Reservar</button>
    </form>
</main>

<?php include '../includes/footer.php'; ?>
