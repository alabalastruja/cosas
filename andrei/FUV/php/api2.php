<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");


error_reporting(E_ALL);
ini_set('display_errors', 1);

// Incluir la conexión a la base de datos
require_once "conexion.php";

// Verificar que la conexión esté activa
if (!$conn) {
    echo json_encode(["error" => "Error de conexión con la base de datos"]);
    exit;
}

// Obtener los recibos desde la base de datos
$query = "SELECT * FROM recibos ORDER BY id DESC";  // Puedes ordenar por el campo que prefieras
$result = $conn->query($query);

if ($result) {
    $recibos = $result->fetch_all(MYSQLI_ASSOC);  // Obtener los datos como un array asociativo
    echo json_encode($recibos);  // Enviar los datos como JSON
} else {
    echo json_encode(["error" => "Error al obtener los recibos"]);
}

// Cerrar la conexión
$conn->close();
?>
