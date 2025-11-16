<?php  
require_once '../includes/header.php';
require_once '../config/config.php';
require_once '../controllers/canchaController.php';

$canchas = obtenerCanchasConUbicacionCompleta();
?>

<header>
    <h1>Canchas Disponibles</h1>
</header>

<main>
    <section>
        <h2>Lista de Canchas</h2>

        <?php if (!empty($canchas)): ?>
            <ul class="lista-canchas">
                <?php foreach ($canchas as $cancha): ?>
                    <?php
                        $horarios = obtenerHorariosPorCancha($cancha['id_cancha']);
                    ?>
                    <li class="cancha-card">
                        

                        <h3><?= htmlspecialchars($cancha['nombre_cancha']) ?></h3>
                        <p>Tipo: <?= htmlspecialchars($cancha['tipo_cancha']) ?></p>
                        <p>Ubicación: <?= htmlspecialchars($cancha['direccion']) ?>,
                            <?= isset($cancha['municipio']) ? htmlspecialchars($cancha['municipio']) : 'Municipio no disponible'; ?>                        </p>
                        <p>Capacidad: <?= intval($cancha['capacidad']) ?> personas</p>
                        <p>Precio: $<?= number_format($cancha['precio'], 2) ?></p>
                        
                        <p><strong>Horarios disponibles:</strong></p>
                        <ul class="horarios">
                            <?php if (!empty($horarios)): ?>
                                <?php foreach ($horarios as $h): ?>
                                    <li><?= htmlspecialchars($h['hora_apertura']) ?> - <?= htmlspecialchars($h['hora_cierre']) ?></li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li>No hay horarios disponibles</li>
                            <?php endif; ?>
                        </ul>
                        <a href="reservar.php?id=<?= $cancha['id_cancha'] ?>" class="btn-reservar">Reservar</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No hay canchas disponibles en este momento.</p>
        <?php endif; ?>
    </section>
</main>

<?php include '../includes/footer.php'; ?>
