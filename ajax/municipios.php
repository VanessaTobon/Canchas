<?php
require_once '../config/config.php';

header('Content-Type: application/json');

$id_estado = isset($_POST['id_estado']) ? intval($_POST['id_estado']) : 0;

$sql = "SELECT id_municipio, nombre_municipio FROM tbl_municipio WHERE id_estado = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_estado);
$stmt->execute();
$result = $stmt->get_result();

$municipios = [];
while ($row = $result->fetch_assoc()) {
    $municipios[] = $row;
}

echo json_encode($municipios);
