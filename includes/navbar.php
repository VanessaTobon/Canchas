<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$usuario = $_SESSION['usuario'] ?? null;
?>

<nav class="navbar">
    <ul class="nav-list">
        <li><a href="../public/index.php">Inicio</a></li>

        <?php if ($usuario): ?>
            <?php if ($usuario['rol'] === 'admin'): ?>
                <li><a href="../admin/gestionar_canchas.php">Gestionar Canchas</a></li>
                <li><a href="../admin/gestionar_reservas.php">Reservas</a></li>
            <?php else: ?>
                <li><a href="../cliente/mis_reservas.php">Mis Reservas</a></li>
                <li><a href="../public/reservar.php">Reservar Cancha</a></li>
            <?php endif; ?>
            <li><a href="../public/logout.php" class="logout-btn">Cerrar Sesión</a></li>
        <?php else: ?>
            <li><a href="../public/login.php" class="login-btn">Iniciar Sesión</a></li>
            <li><a href="../public/registro.php" class="register-btn">Registrarse</a></li>
        <?php endif; ?>
    </ul>
</nav>

