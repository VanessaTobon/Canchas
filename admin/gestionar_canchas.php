<?php
session_start();

require_once '../config/config.php';
require_once '../includes/header.php';
require_once '../services/CanchaFacade.php';
require_once '../services/UsuarioProxy.php';
require_once '../services/CanchaDecoratorFactory.php';

// Validación de rol administrador
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    header("Location: ../public/login.php");
    exit;
}

$facade = new CanchaFacade();
$proxy  = new UsuarioProxy($_SESSION['usuario'] ?? null);
$mensaje = "";


// ============================================================
// ELIMINAR CANCHA
// ============================================================
if (isset($_GET['eliminar'])) {

    $id = intval($_GET['eliminar']);

    if ($id > 0) {
    $resultado = $proxy->eliminarCancha($id);

    if ($resultado === true) {
        $mensaje = "Cancha eliminada correctamente.";
    } else {
        $mensaje = is_string($resultado)
            ? $resultado
            : "Error al eliminar la cancha.";
    }
}
}


// ============================================================
// EDITAR CANCHA
// ============================================================
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["editar"])) {

    $data = [
        "id_cancha"   => intval($_POST["id_cancha"]),
        "nombre_cancha" => trim($_POST["nombre_cancha"]),
        "tipo_cancha"   => trim($_POST["tipo_cancha"]),
        "estado"        => trim($_POST["estado"]),
        "id_pais"       => intval($_POST["id_pais"]),
        "id_estado"     => intval($_POST["id_estado"]),
        "id_municipio"  => intval($_POST["id_municipio"]),
        "direccion"     => trim($_POST["direccion"]),
        "capacidad"     => intval($_POST["capacidad"]),
        "precio"        => floatval($_POST["precio"]),
    ];

    if ($data["id_cancha"] > 0 && $data["capacidad"] > 0 && $data["precio"] > 0) {
        $mensaje = $facade->editar($data["id_cancha"], $data)
            ? "Cancha actualizada correctamente."
            : "Error al actualizar la cancha.";
    } else {
        $mensaje = "Datos inválidos en la edición.";
    }
}


// ============================================================
// AGREGAR CANCHA
// ============================================================
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["agregar"])) {

   $data = [
    "nombre_cancha"  => trim($_POST["nombre_cancha"]),
    "tipo_cancha"    => trim($_POST["tipo_cancha"]),
    "estado"         => trim($_POST["estado"]),
    "id_pais"        => intval($_POST["id_pais"]),
    "id_estado"      => intval($_POST["id_estado"]),
    "id_municipio"   => intval($_POST["id_municipio"]),
    "direccion"      => trim($_POST["direccion"]),
    "capacidad"      => intval($_POST["capacidad"]),
    "precio"         => floatval($_POST["precio"]),
    ];

    if (
         !empty($data["nombre_cancha"]) &&
        !empty($data["tipo_cancha"]) &&
        !empty($data["estado"]) &&
        !empty($data["direccion"]) &&
        $data["id_pais"] > 0 &&
        $data["id_estado"] > 0 &&
        $data["id_municipio"] > 0 &&
        $data["capacidad"] > 0 &&
        $data["precio"] > 0
    ) {
        $mensaje = $facade->agregar($data)
            ? "Cancha agregada correctamente."
            : "Error al agregar la cancha.";
    } else {
        $mensaje = "Todos los campos son obligatorios.";
    }
}


// ============================================================
// OBTENER LISTA DE CANCHAS
// ============================================================
$canchas = $facade->obtenerCanchasConUbicacion();

?>

<main class="contenido">

<h2>Gestión de Canchas</h2>

<?php if ($mensaje): ?>
    <p class="mensaje"><?= htmlspecialchars($mensaje) ?></p>
<?php endif; ?>


<!-- ======================================================
//FORMULARIO AGREGAR CANCHA
======================================================= -->
<form method="POST" id="formAgregarCancha">
    <h3>Agregar Nueva Cancha</h3>

    <label>Nombre:</label>
    <input type="text" name="nombre_cancha" required>

    <label>Tipo:</label>
    <input type="text" name="tipo_cancha" required>

    <label>Estado:</label>
    <select name="estado" required>
        <option value="disponible">Disponible</option>
        <option value="ocupada">Ocupada</option>
        <option value="mantenimiento">Mantenimiento</option>
    </select>

    <label>País:</label>
    <select name="id_pais" id="selectPais" required></select>

    <label>Departamento:</label>
    <select name="id_estado" id="selectDepartamento" required></select>

    <label>Municipio:</label>
    <select name="id_municipio" id="selectMunicipio" required></select>

    <label>Dirección:</label>
    <input type="text" name="direccion" required>

    <label>Capacidad:</label>
    <input type="number" min="1" name="capacidad" required>

    <label>Precio:</label>
    <input type="number" min="1" step="0.01" name="precio" required>

    <button type="submit" name="agregar">Agregar Cancha</button>
</form>


<hr>


<!-- ======================================================
    TABLA DE CANCHAS
======================================================= -->
<h3>Lista de Canchas</h3>

<table>
<thead>
<tr>
    <th>Nombre</th>
    <th>Tipo</th>
    <th>Estado</th>
    <th>Dirección</th>
    <th>Departamento</th>
    <th>Municipio</th>
    <th>Capacidad</th>
    <th>Precio</th>
    <th>Acciones</th>
</tr>
</thead>

<tbody>

<?php if (!empty($canchas)): ?>
<?php foreach ($canchas as $cancha): ?>

<?php
    // INICIO CAMBIO 2: crear objeto decorado de cancha
    $canchaDecorada = CanchaDecoratorFactory::crearDesdeArray($cancha);
    $precioDecorado = $canchaDecorada->getPrecio();
    $descripcionDecorada = $canchaDecorada->getDescripcion();
    // FIN CAMBIO 2
?>

<tr>
<form method="POST">

<input type="hidden" name="id_cancha" value="<?= $cancha['id_cancha'] ?>">

<td><input type="text" name="nombre_cancha" value="<?= $cancha['nombre_cancha'] ?>"></td>
<td><input type="text" name="tipo_cancha" value="<?= $cancha['tipo_cancha'] ?>"></td>
<td><input type="text" name="estado" value="<?= $cancha['estado'] ?>"></td>
<td><input type="text" name="direccion" value="<?= $cancha['direccion'] ?>"></td>

<td><?= $cancha['nombre_estado'] ?></td>
<td><?= $cancha['nombre_municipio'] ?></td>

<td><input type="number" name="capacidad" value="<?= $cancha['capacidad'] ?>"></td>
<td>
    <input type="number" step="0.01" name="precio" value="<?= $cancha['precio'] ?>">
    <br>
    <small>Precio final: <?= number_format($precioDecorado, 2) ?></small>
</td>
<td>

    <!-- Ubicación oculta -->
    <input type="hidden" name="id_pais" value="<?= $cancha['id_pais'] ?>">
    <input type="hidden" name="id_estado" value="<?= $cancha['id_estado'] ?>">
    <input type="hidden" name="id_municipio" value="<?= $cancha['id_municipio'] ?>">

    <button type="submit" name="editar">Guardar</button>

    <a href="gestionar_canchas.php?eliminar=<?= $cancha['id_cancha'] ?>"
       onclick="return confirm('¿Eliminar cancha?')">
       Eliminar
    </a>

</td>

</form>
</tr>

<?php endforeach; ?>
<?php else: ?>
<tr><td colspan="9">No hay canchas registradas</td></tr>
<?php endif; ?>

</tbody>
</table>

</main>

<script src="../assets/js/formulario.js"></script>
<?php require_once '../includes/footer.php'; ?>

