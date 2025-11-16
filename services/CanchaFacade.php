<?php
require_once __DIR__ . '/../models/CanchaModel.php';
require_once __DIR__ . '/CanchaComposite.php';


class CanchaFacade {

    private CanchaModel $canchaModel;

    public function __construct() {
        $this->canchaModel = new CanchaModel();
    }

    /* =========================================================
        OBTENER CANCHAS CON UBICACIÓN
    ========================================================== */
    public function obtenerCanchasConUbicacion(): array {
        return $this->canchaModel->getCanchasConUbicacion();
    }

    /* =========================================================
        OBTENER CANCHA POR ID
    ========================================================== */
    public function obtenerPorId(int $id): ?array {
        return $this->canchaModel->getById($id);
    }

    /* =========================================================
        AGREGAR CANCHA
    ========================================================== */
    public function agregar(array $data): bool {
        return $this->canchaModel->insertarCancha($data);
    }

    /* =========================================================
        EDITAR CANCHA
    ========================================================== */
    public function editar(int $id, array $data): bool {
        return $this->canchaModel->actualizarCancha($id, $data);
    }

    /* =========================================================
        ELIMINAR CANCHA
    ========================================================== */
    public function eliminar(int $id): bool {
        return $this->canchaModel->eliminarCancha($id);
    }

    /* =========================================================
        VERIFICAR SI YA EXISTE
    ========================================================== */
    public function canchaYaExiste(string $nombre, string $direccion): bool {
        return $this->canchaModel->canchaYaExiste($nombre, $direccion);
    }

    /* =========================================================
        VALIDAR SI HAY CRUCE DE RESERVA
    ========================================================== */
    public function hayCruceReserva(int $id, string $fecha, string $inicio, string $fin): bool {
        return $this->canchaModel->checkOverlap($id, $fecha, $inicio, $fin);
    }

    /**
     * Devuelve el composite raíz (países) con toda la jerarquía.
     * Retorna UbicacionComposite (raiz) o array serializable según $asArray.
     */
    public function obtenerCompositeUbicaciones(bool $asArray = false) {
        $rows = $this->canchaModel->getAllWithLocation();

        // Crear mapa para países → estados → municipios
        $paisMap = [];

        foreach ($rows as $r) {
            $paisName = $r['nombre_pais'] ?? 'Sin país';
            $estadoName = $r['nombre_estado'] ?? 'Sin estado';
            $municipioName = $r['nombre_municipio'] ?? 'Sin municipio';

            // preparar estructura de datos de cancha (puedes añadir más campos)
            $canchaInfo = [
                'id_cancha' => (int)$r['id_cancha'],
                'nombre_cancha' => $r['nombre_cancha'],
                'tipo_cancha' => $r['tipo_cancha'],
                'estado' => $r['estado'],
                'direccion' => $r['direccion'],
                'capacidad' => $r['capacidad'],
                'precio' => $r['precio']
            ];

            // inicializar mapas si no existen
            if (!isset($paisMap[$paisName])) $paisMap[$paisName] = [];
            if (!isset($paisMap[$paisName][$estadoName])) $paisMap[$paisName][$estadoName] = [];
            if (!isset($paisMap[$paisName][$estadoName][$municipioName])) $paisMap[$paisName][$estadoName][$municipioName] = [];

            // empujar cancha en el municipio
            $paisMap[$paisName][$estadoName][$municipioName][] = $canchaInfo;
        }

        // Construir Composite
        $root = new UbicacionComposite('root');

        foreach ($paisMap as $paisName => $estados) {
            $paisNode = new UbicacionComposite($paisName);
            foreach ($estados as $estadoName => $municipios) {
                $estadoNode = new UbicacionComposite($estadoName);
                foreach ($municipios as $municipioName => $canchasArr) {
                    // cada municipio es una hoja que contiene canchas
                    $leaf = new UbicacionLeaf($municipioName, $canchasArr);
                    $estadoNode->agregar($leaf);
                }
                $paisNode->agregar($estadoNode);
            }
            $root->agregar($paisNode);
        }

        if ($asArray) {
            // convertir a array simple (útil para JSON/JS)
            return $this->compositeToArray($root);
        }

        return $root;
    }

    private function compositeToArray(UbicacionComposite $root): array {
        $out = [];
        foreach ($root->getCanchas() as $ignored) { /* not used here */ }
        // Instead iterate via children — but UbicacionComposite doesn't expose children; we'll reconstruct:
        // Simpler: call getAllWithLocation() and return the grouped $paisMap (fast)
        $rows = $this->canchaModel->getAllWithLocation();
        // build grouped array same as above
        $grouped = [];
        foreach ($rows as $r) {
            $pais = $r['nombre_pais'] ?? 'Sin país';
            $estado = $r['nombre_estado'] ?? 'Sin estado';
            $municipio = $r['nombre_municipio'] ?? 'Sin municipio';
            $grouped[$pais][$estado][$municipio][] = [
                'id_cancha'=>$r['id_cancha'],
                'nombre_cancha'=>$r['nombre_cancha'],
                'tipo_cancha'=>$r['tipo_cancha'],
                'estado'=>$r['estado'],
                'direccion'=>$r['direccion'],
                'capacidad'=>$r['capacidad'],
                'precio'=>$r['precio']
            ];
        }
        return $grouped;
    }

}
