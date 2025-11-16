<?php

require_once __DIR__ . '/CanchaBase.php';
require_once __DIR__ . '/CanchaConPromocion.php';
require_once __DIR__ . '/CanchaConServicioExtra.php';

class CanchaDecoratorFactory
{
    public static function crearDesdeArray(array $row): CanchaComponent
    {
        $cancha = new CanchaBase($row);

        // Regla de ejemplo 1:
        // Si la cancha esta disponible, aplicamos una promocion del 10 %
        if (isset($row['estado']) && $row['estado'] === 'disponible') {
            $cancha = new CanchaConPromocion($cancha, 0.10);
        }

        // Regla de ejemplo 3:
        // Si el precio es alto, agregamos un servicio extra de implementos
        if (isset($row['precio']) && (float)$row['precio'] >= 60000) {
            $cancha = new CanchaConServicioExtra($cancha, 'Alquiler de implementos', 5000);
        }

        return $cancha;
    }
}