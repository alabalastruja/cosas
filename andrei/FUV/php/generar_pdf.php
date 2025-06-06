<?php
require_once __DIR__ . '/composer/vendor/autoload.php';

if (!file_exists(__DIR__ . '/composer/vendor/autoload.php')) {
    die("Error: No se encontró el archivo autoload.php. Ejecuta 'composer install'.");
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Error: ID de recibo no válido.");
}

$id = intval($_GET['id']);
$reciboData = file_get_contents("http://localhost/fuv.org.uy/andrei/FUV/php/imprimir.php?id=" . urlencode($id));

if ($reciboData === false) {
    die("Error: No se pudo obtener la información del recibo.");
}

$recibo = json_decode($reciboData, true);
$camposRequeridos = ['dia', 'mes', 'anio', 'nombre', 'moneda', 'moneda_nombre', 'monto', 'monto_letra', 'concepto', 'metodo_pago', 'banco'];
foreach ($camposRequeridos as $campo) {
    if (empty($recibo[$campo])) {
        die("Error: Datos del recibo incompletos o inválidos. Falta el campo: " . $campo);
    }
}

if (!class_exists('\Mpdf\Mpdf')) {
    die("⚠️ Error: mPDF no está disponible. Verifica vendor/autoload.php.");
}

try {
    $mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4']);

$estilos = '
<style>
    body {
        font-family: monospace;
        font-size: 12pt;
    }

    .tabla-recibo {
        width: 100%;
        table-layout: fixed;
    }

    .derecha {
        text-align: right;
    }

    .izquierda {
        text-align: left;
    }

    .centrado {
        text-align: center;
    }

    .contenedor-doble {
        page-break-inside: avoid;
    }

    .recibo-container {
        position: relative;
        height: 420px;
        margin-top: 100px; /* 👈 este valor ajusta el espacio desde arriba */
        margin-bottom: 0;
    }

    .margen-izquierda-ajustada {
        margin-left: 90px;
        margin-top: 8px;
        max-width: 500px;
        word-wrap: break-word;
    }

    .linea-separada {
        margin-top: 8px;
        max-width: 500px;
        word-wrap: break-word;
    }

    .bloque-concepto {
        margin-bottom: 50px;
        max-height: 120px;
        overflow: hidden;
    }

    .metodo-banco-fijo {
        position: absolute;
        bottom: 10px;
        left: 300px;
        width: 80%;
    }
</style>';


$contenido = '
<div class="contenedor-doble">
    <div class="recibo-container">
        <table class="tabla-recibo">
            <tr>
                <td class="derecha">' . $recibo['dia'] . ' &nbsp;&nbsp; ' . $recibo['mes'] . ' &nbsp;&nbsp; ' . $recibo['anio'] . ' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ' . $recibo['moneda'] . ' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ' . number_format($recibo['monto'], 2, ',', '.') . '</td>
            </tr>
        </table>

        <div class="izquierda margen-izquierda-ajustada">
            <div>' . htmlspecialchars($recibo['nombre']) . '</div>
            <div class="linea-separada">' . htmlspecialchars($recibo['moneda_nombre']) . ' ' . htmlspecialchars($recibo['monto_letra']) . '</div>
            <div class="linea-separada bloque-concepto">' . htmlspecialchars($recibo['concepto']) . '</div>
        </div>

        <div class="metodo-banco-fijo">
            <table class="tabla-recibo" style="text-align: right;">
                <tr>
                    <td>' . htmlspecialchars($recibo['metodo_pago']) . '</td>
                    <td>' . htmlspecialchars($recibo['banco']) . '</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="recibo-container">
        <!-- misma estructura duplicada -->
        <table class="tabla-recibo">
            <tr>
                <td class="derecha">' . $recibo['dia'] . ' &nbsp;&nbsp; ' . $recibo['mes'] . ' &nbsp;&nbsp; ' . $recibo['anio'] . ' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ' . $recibo['moneda'] . ' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ' . number_format($recibo['monto'], 2, ',', '.') . '</td>
            </tr>
        </table>

        <div class="izquierda margen-izquierda-ajustada">
            <div>' . htmlspecialchars($recibo['nombre']) . '</div>
            <div class="linea-separada">' . htmlspecialchars($recibo['moneda_nombre']) . ' ' . htmlspecialchars($recibo['monto_letra']) . '</div>
            <div class="linea-separada bloque-concepto">' . htmlspecialchars($recibo['concepto']) . '</div>
        </div>

        <div class="metodo-banco-fijo">
            <table class="tabla-recibo" style="text-align: right;">
                <tr>
                    <td>' . htmlspecialchars($recibo['metodo_pago']) . '</td>
                    <td>' . htmlspecialchars($recibo['banco']) . '</td>
                </tr>
            </table>
        </div>
    </div>
</div>';



$mpdf->WriteHTML($estilos);
$mpdf->WriteHTML($contenido);
$mpdf->Output("recibo_{$id}.pdf", "D");


    $mpdf->Output("recibo_{$id}.pdf", "D");
} catch (Exception $e) {
    die("Error al generar el PDF: " . $e->getMessage());
}