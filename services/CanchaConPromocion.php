<?php

require_once __DIR__ . '/CanchaDecorator.php';

class CanchaConPromocion extends CanchaDecorator
{
    private float $porcentajeDescuento; // 0.10 = 10 %

    public function __construct(CanchaComponent $base, float $porcentajeDescuento)
    {
        parent::__construct($base);
        $this->porcentajeDescuento = $porcentajeDescuento;
    }

    public function getPrecio(): float
    {
        $precioBase = $this->base->getPrecio();
        return round($precioBase * (1 - $this->porcentajeDescuento), 2);
    }

    public function getDescripcion(): string
    {
        $porc = (int)round($this->porcentajeDescuento * 100);
        return $this->base->getDescripcion()
            . " | Promocion: {$porc}% de descuento";
    }
}