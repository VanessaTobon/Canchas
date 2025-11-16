<?php

require_once __DIR__ . '/CanchaComponent.php';

abstract class CanchaDecorator implements CanchaComponent
{
    protected CanchaComponent $base;

    public function __construct(CanchaComponent $base)
    {
        $this->base = $base;
    }

    public function getPrecio(): float
    {
        return $this->base->getPrecio();
    }

    public function getDescripcion(): string
    {
        return $this->base->getDescripcion();
    }
}