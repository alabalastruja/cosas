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




// Incluir la conexión a la base de datos
require_once "conexion.php";

// Verificar que la conexión esté activa
if (!$conn) {
    echo json_encode(["error" => "Error de conexión con la base de datos"]);
    exit;
}

// Leer los datos del cuerpo de la solicitud
$raw_input = file_get_contents("php://input");
$data = json_decode($raw_input, true);

// Comprobar si los datos son correctos
if (!$data) {
    echo json_encode(["error" => "Error al decodificar JSON"]);
    exit;
}

// Verificar que los campos necesarios estén presentes
if (!isset($data["id"], $data["estado"])) {
    echo json_encode(["error" => "Faltan datos en la solicitud"]);
    exit;
}

// Preparar y ejecutar la consulta SQL para actualizar el estado del recibo
$stmt = $conn->prepare("UPDATE recibos SET estado = ? WHERE id = ?");
$stmt->bind_param("si", $data["estado"], $data["id"]);

// Ejecutar la consulta y verificar si fue exitosa
if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Estado del recibo actualizado correctamente"]);
} else {
    echo json_encode(["error" => "Error al actualizar el estado: " . $stmt->error]);
}

// Cerrar la declaración y la conexión
$stmt->close();
$conn->close();
?>
