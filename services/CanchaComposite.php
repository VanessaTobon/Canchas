<?php
// services/CanchaComposite.php

interface UbicacionComponent {
    public function getNombre(): string;
    public function getCanchas(): array; // lista de canchas bajo este nodo
    public function agregar(UbicacionComponent $component): void;
}

class UbicacionLeaf implements UbicacionComponent {
    private string $nombre;
    private array $canchas; // array de canchas
    public function __construct(string $nombre, array $canchas = []) {
        $this->nombre = $nombre;
        $this->canchas = $canchas;
    }
    public function getNombre(): string { return $this->nombre; }
    public function getCanchas(): array { return $this->canchas; }
    public function agregar(UbicacionComponent $component): void {
        throw new LogicException("No se puede agregar a una hoja");
    }
}

class UbicacionComposite implements UbicacionComponent {
    private string $nombre;
    /** @var UbicacionComponent[] */
    private array $children = [];
    public function __construct(string $nombre) { $this->nombre = $nombre; }
    public function getNombre(): string { return $this->nombre; }
    public function agregar(UbicacionComponent $component): void {
        $this->children[] = $component;
    }
    public function getCanchas(): array {
        $result = [];
        foreach ($this->children as $c) {
            $result = array_merge($result, $c->getCanchas());
        }
        return $result;
    }
}
