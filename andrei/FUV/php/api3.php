<?php
// Manejar solicitudes OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    header("Access-Control-Max-Age: 86400"); // Cache preflight por 1 día
    http_response_code(204); // No Content
    exit;
}
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Incluir la conexión a la base de datos
require_once "conexion.php";

// Verificar que la conexión esté activa
if (!$conn) {
    echo json_encode(["error" => "Error de conexión con la base de datos"]);
    exit;
}

// Verificar si se ha recibido un ID a través de GET
$id = isset($_GET['id']) ? $_GET['id'] : null;

if ($id === null) {
    echo json_encode(["error" => "No se ha proporcionado un ID"]);
    exit;
}

// Preparar la consulta SQL
$query = "SELECT * FROM recibos WHERE id = ?";
$stmt = $conn->prepare($query);

// Verificar si la preparación fue exitosa
if ($stmt === false) {
    echo json_encode(["error" => "Error al preparar la consulta"]);
    exit;
}

// Vincular el parámetro a la consulta
$stmt->bind_param("i", $id); // Asumiendo que id es un entero (int)

// Ejecutar la consulta
$stmt->execute();

// Obtener el resultado
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $recibos = $result->fetch_all(MYSQLI_ASSOC);  // Obtener los datos como un array asociativo
    echo json_encode($recibos);  // Enviar los datos como JSON
} else {
    echo json_encode(["error" => "No se encontraron recibos con ese ID"]);
}

// Cerrar la conexión
$stmt->close();
$conn->close();
?>
