<?php
require_once '../includes/header.php';
require_once '../controllers/usuarioController.php';

$mensaje = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'];

    if ($email && $password && iniciarSesion($email, $password)) {
        $usuario = $_SESSION['usuario'];

        if ($usuario['rol'] === 'admin') {
            // Validación adicional opcional (ej. solo emails específicos)
            // if ($usuario['email'] !== 'admin@midominio.com') {
            //     session_destroy();
            //     $mensaje = "Acceso restringido para administradores.";
            // } else {
            header("Location: ../admin/gestionar_canchas.php");
            // }

        } elseif ($usuario['rol'] === 'cliente') {
            header("Location: ../cliente/mis_reservas.php");
        } else {
            // Si por alguna razón el rol no es válido
            session_destroy();
            $mensaje = "Rol de usuario no válido.";
        }
        exit;
    } else {
        $mensaje = "Credenciales incorrectas. Intenta nuevamente.";
    }
}
?>

<main class="container">
    <h2>Iniciar Sesión</h2>

    <?php if (!empty($mensaje)): ?>
        <div class="mensaje-error"><?php echo $mensaje; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="email">Correo electrónico:</label>
        <input type="email" name="email" id="email" required>

        <label for="password">Contraseña:</label>
        <input type="password" name="password" id="password" required>

        <button type="submit">Iniciar Sesión</button>
    </form>

    <p>¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a>.</p>
</main>

<?php include '../includes/footer.php'; ?>
