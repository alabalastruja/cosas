<?php
// Configuración de cabeceras para permitir solicitudes CORS (Cross-Origin Resource Sharing)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

// Activar reporte de errores para depuración
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "conexion.php";

// Verificar si la conexión a la base de datos fue exitosa
if (!$conn) {
    // Si la conexión falla, se envía un mensaje de error
    echo json_encode(["error" => "Error de conexión con la base de datos"]);
    exit;
}


// Obtener los datos JSON
$data = json_decode(file_get_contents("php://input"), true);
if (!$data || !isset($data['id'])) {
    echo json_encode(["success" => false, "error" => "Datos JSON inválidos o falta ID"]);
    exit;
}

// Preparar la consulta para evitar inyecciones SQL
$stmt = $conn->prepare("UPDATE recibos SET nombre=?, dia=?, mes=?, anio=?, moneda=?, monto=?, concepto=?, banco=?, estado=?, monto_letra=?, moneda_nombre=?, metodo_pago=? WHERE id=?");
$stmt->bind_param(
    "siiisdssssssi", 
    $data['nombre'], $data['dia'], $data['mes'], $data['anio'], $data['moneda'],
    $data['monto'], $data['concepto'], $data['banco'], $data['estado'], 
    $data['monto_letra'], $data['moneda_nombre'], $data['metodo_pago'], $data['id']
);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => $stmt->error]);
}

$stmt->close();
$conn->close();
?>
