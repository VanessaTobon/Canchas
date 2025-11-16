<?php

require_once __DIR__ . '/CanchaComponent.php';

class CanchaBase implements CanchaComponent
{
    private array $data;

    public function __construct(array $row)
    {
        $this->data = $row;
    }

    public function getPrecio(): float
    {
        // Aseguramos que exista el índice para evitar avisos
        return isset($this->data['precio']) ? (float)$this->data['precio'] : 0.0;
    }

    public function getDescripcion(): string
    {
        $nombre     = $this->data['nombre_cancha']  ?? 'Cancha';
        $tipo       = $this->data['tipo_cancha']    ?? 'Sin tipo';
        $capacidad  = $this->data['capacidad']      ?? 0;

        return sprintf(
            '%s (%s) - Capacidad: %d personas',
            $nombre,
            $tipo,
            (int)$capacidad
        );
    }

    public function getData(): array
    {
        return $this->data;
    }
}