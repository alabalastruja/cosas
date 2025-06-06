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
// Activar reporte de errores para depuración
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Incluir la conexión a la base de datos
require_once "conexion.php";

// Verificar si la conexión a la base de datos fue exitosa
if (!$conn) {
    // Si la conexión falla, se envía un mensaje de error
    echo json_encode(["error" => "Error de conexión con la base de datos"]);
    exit;
}

// Leer los datos del cuerpo de la solicitud
$raw_input = file_get_contents("php://input");
$data = json_decode($raw_input, true);

// Comprobar si los datos fueron decodificados correctamente
if (!$data) {
    // Si hay un error en la decodificación del JSON, se muestra el error
    echo json_encode(["error" => "Error al decodificar JSON", "raw_input" => $raw_input]);
    exit;
}

// Verificar que los campos necesarios estén presentes en los datos
if (!isset($data["moneda"], $data["moneda_nombre"], $data["monto"], $data["monto_letra"], $data["concepto"], $data["banco"], $data["dia"], $data["mes"], $data["anio"], $data["estado"], $data["nombre"], $data["metodo_pago"])) {
    // Si faltan datos en la solicitud, se devuelve un mensaje de error
    echo json_encode(["error" => "Faltan datos en la solicitud"]);
    exit;
}

// Consulta para obtener el último ID insertado en la base de datos
$result = $conn->query("SELECT MAX(id) AS last_id FROM recibos");
if ($result) {
    // Si la consulta se ejecuta correctamente, obtenemos el último ID insertado
    $row = $result->fetch_assoc();
    $last_id = $row['last_id']; // El último ID registrado
    $new_id = $last_id + 1; // Incrementamos el último ID para generar uno nuevo
} else {
    // Si no hay registros (por ejemplo, si la tabla está vacía), comenzamos desde el ID 1
    $new_id = 1;
}

// Incluir el campo metodo_pago en la consulta SQL
$stmt = $conn->prepare("INSERT INTO recibos (id, moneda, moneda_nombre, monto, monto_letra, concepto, banco, dia, mes, anio, estado, nombre, metodo_pago) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param("issssssiissss", $new_id, $data["moneda"], $data["moneda_nombre"], $data["monto"], $data["monto_letra"], $data["concepto"], $data["banco"], $data["dia"], $data["mes"], $data["anio"], $data["estado"], $data["nombre"], $data["metodo_pago"]);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Datos guardados correctamente", "id" => $new_id]);
} else {
    echo json_encode(["error" => "Error al guardar los datos: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
