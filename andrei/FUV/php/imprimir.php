<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

require_once "conexion.php"; // Usar la conexión existente

// Verificar conexión a la base de datos
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Obtener el ID del recibo desde la URL
if (isset($_GET["id"])) {
    $id = intval($_GET["id"]);

    // Depurar el valor del ID recibido
    // var_dump($id); // Descomenta esta línea para ver el valor del ID

    // Usar declaración preparada para evitar inyección SQL
    $stmt = $conn->prepare("SELECT * FROM recibos WHERE id = ?");
    $stmt->bind_param("i", $id);  // "i" indica que el parámetro es un entero
    $stmt->execute();

    // Obtener el resultado
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode($row);
    } else {
        echo json_encode(["error" => "Recibo no encontrado"]);
    }

    // Cerrar la declaración preparada
    $stmt->close();
} else {
    echo json_encode(["error" => "ID no proporcionado"]);
}

// Cerrar la conexión a la base de datos
$conn->close();
?>
