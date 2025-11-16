<?php
require_once '../includes/header.php';
require_once '../controllers/usuarioController.php';

$mensaje = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    echo "<pre>";
    print_r($_POST); // 👈 Aquí ves si el rol está llegando
    echo "</pre>";
    
    $nombre = trim($_POST['nombre']);
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $password = trim($_POST['password']);
    $telefono = trim($_POST['telefono']);
    $direccion = trim($_POST['direccion']);
    $rol = $_POST['rol'] ?? 'Cliente'; // Asegúrate que coincida con la BD
    
    if ($nombre && $email && $password && $telefono && $direccion) {
        $resultado = registrarUsuario($nombre, $email, $password, $rol, $telefono, $direccion);

        if ($resultado === true) {
            header("Location: login.php?registro=exitoso");
            exit;
        } else {
            $mensaje = $resultado; // ya viene con mensaje de error si falló
        }

    } else {
        $mensaje = "❗Todos los campos son obligatorios.";
    }
}
?>

<main class="container">
    <h2>Registro de Usuario</h2>

    <?php if (!empty($mensaje)): ?>
        <div class="mensaje-error"><?php echo $mensaje; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="nombre">Nombre:</label>
        <input type="text" name="nombre" id="nombre" required>

        <label for="email">Correo electrónico:</label>
        <input type="email" name="email" id="email" required>

        <label for="password">Contraseña:</label>
        <input type="password" name="password" id="password" required>

        <label for="telefono">Teléfono:</label>
        <input type="text" name="telefono" id="telefono" required>

        <label for="direccion">Dirección:</label>
        <input type="text" name="direccion" id="direccion" required>

        <label for="rol">Tipo de cuenta:</label>
        <select name="rol" id="rol">
            <option value="cliente" selected>Cliente</option>
            <option value="admin">Administrador</option>
        </select>

        <button type="submit">Registrarse</button>
    </form>

    <p>¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a>.</p>
</main>

<?php require_once '../includes/footer.php'; ?>
