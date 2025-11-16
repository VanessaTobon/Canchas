<?php
require_once '../config/config.php';

header('Content-Type: application/json');

$sql = "SELECT id_pais, nombre_pais FROM tbl_pais ORDER BY nombre_pais";
$result = $conexion->query($sql);

$paises = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $paises[] = $row;
    }
}

echo json_encode($paises);
