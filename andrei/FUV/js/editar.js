document.addEventListener("DOMContentLoaded", async () => {
    const params = new URLSearchParams(window.location.search);
    const reciboId = params.get("id");
    document.getElementById("concepto").addEventListener("input", function () {
    const longitud = this.value.length;
    document.getElementById("contador").textContent = `${longitud}/200`;
});
    const conceptoInput = document.getElementById("concepto");
    const LIMITE_CONCEPTO = 200;

    if (conceptoInput.value.length > LIMITE_CONCEPTO) {
        conceptoInput.value = conceptoInput.value.substring(0, LIMITE_CONCEPTO);
        alert("El texto del concepto fue recortado a los primeros 200 caracteres.");
    }


    if (!reciboId) {
        alert("ID de recibo no encontrado.");
        return;
    }

    try {
        const response = await fetch(`http://localhost/fuv.org.uy/andrei/FUV/php/imprimir.php?id=${reciboId}`);
        if (!response.ok) throw new Error("Error al obtener los datos del recibo");

        const recibo = await response.json();
        console.log("Recibo cargado:", recibo);

        if (!recibo || Object.keys(recibo).length === 0) {
            alert("Recibo no encontrado.");
            return;
        }


        const campos = ["id", "nombre", "dia", "mes", "anio", "moneda", "monto", "concepto", 
                        "banco", "estado", "monto_letra", "moneda_nombre", "metodo_pago"];

        campos.forEach(campo => {
            const elemento = document.getElementById(campo);
            if (elemento) elemento.value = recibo[campo] || "";
        });

    } catch (error) {
        console.error("Error al cargar el recibo:", error);
        alert("Hubo un problema al cargar el recibo.");
    }

    const montoInput = document.getElementById("monto");
    const montoLetraInput = document.getElementById("monto_letra");

    if (montoInput && montoLetraInput) {
        montoInput.addEventListener("input", () => {
            const valor = parseInt(montoInput.value) || 0;
            montoLetraInput.value = numeroALetras(valor);
        });
    }
});

// Función para guardar cambios
async function guardarCambios() {
    let params = new URLSearchParams(window.location.search);
    let reciboId = params.get("id");

    // Si no encuentra el ID en la URL, intenta obtenerlo desde el input
    if (!reciboId) {
        reciboId = document.getElementById("id").value;
    }

    if (!reciboId) {
        alert("ID de recibo no encontrado.");
        return;
    }

    const campos = ["id", "nombre", "dia", "mes", "anio", "moneda", "monto", "concepto", 
                    "banco", "estado", "monto_letra", "moneda_nombre", "metodo_pago"];

    let datos = {};
    campos.forEach(campo => {
        const elemento = document.getElementById(campo);
        if (elemento) {
            let valor = elemento.value;

            datos[campo] = valor;
        }
    });

    console.log("Enviando datos:", datos);

    try {
        const response = await fetch("http://localhost/fuv.org.uy/andrei/FUV/php/actualizar.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(datos)
        });

        const text = await response.text();
        console.log("Respuesta recibida:", text);

        let resultado;
        try {
            resultado = JSON.parse(text);
        } catch (error) {
            throw new Error("La respuesta no es JSON válido: " + text);
        }

        if (resultado.success) {
            alert("Recibo actualizado correctamente.");
        } else {
            alert("Error al actualizar el recibo: " + (resultado.error || "Error desconocido"));
        }

    } catch (error) {
        console.error("Error al actualizar:", error);
        alert("Hubo un problema al actualizar el recibo.");
    }
}

// Actualizar automáticamente el nombre de la moneda al cambiar la selección
function updateMonedaNombre() {
    const moneda = document.getElementById("moneda").value;
    const nombreMoneda = {
        "UYU": "Pesos Uruguayos",
        "USD": "Dólares Estadounidenses",
        "EUR": "Euros",
        "CHF": "Francos Suizos",
    };
    
    document.getElementById("moneda_nombre").value = nombreMoneda[moneda] || "Otra moneda";
}

// Convertir un número a letras (hasta 999,999)
function numeroALetras(num) {
    const unidades = ['cero', 'uno', 'dos', 'tres', 'cuatro', 'cinco', 'seis', 'siete', 'ocho', 'nueve'];
    const decenas = ['diez', 'once', 'doce', 'trece', 'catorce', 'quince', 'dieciséis', 'diecisiete', 'dieciocho', 'diecinueve'];
    const decenasBase = ['', '', 'veinte', 'treinta', 'cuarenta', 'cincuenta', 'sesenta', 'setenta', 'ochenta', 'noventa'];
    const centenas = ['', 'cien', 'doscientos', 'trescientos', 'cuatrocientos', 'quinientos', 'seiscientos', 'setecientos', 'ochocientos', 'novecientos'];

    if (num === 0) return 'cero';
    if (num < 10) return unidades[num];
    if (num < 20) return decenas[num - 10];
    if (num < 100) return decenasBase[Math.floor(num / 10)] + (num % 10 ? " y " + unidades[num % 10] : '');
    if (num < 1000) return (num === 100 ? 'cien' : centenas[Math.floor(num / 100)]) + (num % 100 ? " " + numeroALetras(num % 100) : '');
    if (num < 1000000) return (Math.floor(num / 1000) === 1 ? 'mil' : numeroALetras(Math.floor(num / 1000)) + " mil") + (num % 1000 ? " " + numeroALetras(num % 1000) : '');

    return num.toString();
}
