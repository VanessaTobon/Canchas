<?php
require_once '../config/config.php';

header('Content-Type: application/json');

$id_pais = isset($_POST['id_pais']) ? intval($_POST['id_pais']) : 0;

$sql = "SELECT id_estado, nombre_estado FROM tbl_estado WHERE id_pais = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_pais);
$stmt->execute();
$result = $stmt->get_result();

$estados = [];
while ($row = $result->fetch_assoc()) {
    $estados[] = $row;
}

echo json_encode($estados);
