<?php

require_once __DIR__ . '/CanchaDecorator.php';

class CanchaConServicioExtra extends CanchaDecorator
{
    private string $nombreServicio;
    private float $costoExtra;

    public function __construct(CanchaComponent $base, string $nombreServicio, float $costoExtra)
    {
        parent::__construct($base);
        $this->nombreServicio = $nombreServicio;
        $this->costoExtra     = $costoExtra;
    }

    public function getPrecio(): float
    {
        return $this->base->getPrecio() + $this->costoExtra;
    }

    public function getDescripcion(): string
    {
        return $this->base->getDescripcion()
            . " | Servicio extra: {$this->nombreServicio} (+{$this->costoExtra})";
    }
}