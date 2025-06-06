document.addEventListener("DOMContentLoaded", async () => {
    const form = document.getElementById("form-nuevo-recibo");
    const montoInput = document.getElementById("monto");
    const montoLetraInput = document.getElementById("monto_letra");
    const monedaSelect = document.getElementById("moneda");
    const nombreInput = document.getElementById("nombre");
    const metodoPagoSelect = document.getElementById("metodo_pago");

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



    const monedaNombreInput = document.createElement('input');
    monedaNombreInput.type = 'hidden';
    monedaNombreInput.id = 'moneda_nombre';
    monedaNombreInput.name = 'moneda_nombre';
    form.appendChild(monedaNombreInput);

    if (!form || !montoInput || !montoLetraInput || !monedaSelect || !nombreInput || !metodoPagoSelect) {
        console.error("Uno o más elementos no se encontraron en el DOM.");
        return;
    }

    let id = parseInt(sessionStorage.getItem('numeroRecibo')) || await obtenerNumeroRecibo();
    sessionStorage.setItem('numeroRecibo', id);

    montoInput.addEventListener("input", () => {
        const numero = parseInt(montoInput.value, 10) || 0;
        montoLetraInput.value = numeroALetras(numero);
    });

    const updateMonedaNombre = () => {
        const selectedOption = monedaSelect.options[monedaSelect.selectedIndex];
        const monedaNombre = selectedOption.getAttribute('data-nombre');
        monedaNombreInput.value = monedaNombre;
    };


    updateMonedaNombre();

    monedaSelect.addEventListener('change', updateMonedaNombre);

    form.addEventListener("submit", async (event) => {
        event.preventDefault();

        const dia = parseInt(document.getElementById("dia")?.value, 10);
        const mes = parseInt(document.getElementById("mes")?.value, 10);
        const anio = parseInt(document.getElementById("anio")?.value, 10);
        const concepto = document.getElementById("concepto")?.value?.trim();
        const banco = document.getElementById("banco")?.value?.trim();
        const monto = parseFloat(montoInput.value) || 0;
        const monto_letra = montoLetraInput.value.trim();
        const moneda = monedaSelect.value.trim();
        const nombre = nombreInput.value.trim();
        const moneda_nombre = monedaNombreInput.value.trim();
        const metodo_pago = metodoPagoSelect.value.trim();
        if (!isValidDate(dia, mes, anio)) {
            alert("Fecha inválida. Por favor, verifica los valores.");
            return;
        }

        const datos = {
            id: ++id,
            dia,
            mes,
            anio,
            moneda,
            moneda_nombre,
            monto,
            monto_letra,
            concepto,
            banco,
            nombre,
            metodo_pago,
            estado: 'nuevo'
        };

        if (Object.values(datos).some(valor => valor === "" || valor === null || valor === undefined)) {
            alert("Todos los campos son obligatorios.");
            return;
        }

        console.log("Enviando datos:", datos);
        await enviarRecibo(datos);
    });
});

async function obtenerNumeroRecibo() {
    try {
        const response = await fetch("http://localhost/fuv.org.uy/andrei/FUV/php/api.php");

        if (!response.ok) throw new Error("Error en la respuesta del servidor");

        const text = await response.text();

        try {
            const data = JSON.parse(text);
            return data.numero || 0;
        } catch (error) {
            console.error("❌ Error al analizar JSON. Respuesta inesperada:", text);
            return 0;
        }

    } catch (error) {
        console.error("Error al obtener el número de recibo:", error);
        alert("❌ Error al conectar con el servidor.");
        return 0; // En caso de error, retornamos 0
    }
}

async function enviarRecibo(datos) {
    try {
        const response = await fetch("http://localhost/fuv.org.uy/andrei/FUV/php/api.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(datos)
        });

        if (!response.ok) throw new Error("Error en la respuesta del servidor");

        const text = await response.text();
        console.log("📌 Respuesta del servidor (texto):", text);

        try {
            const jsonData = JSON.parse(text.trim());
            console.log("✅ JSON recibido:", jsonData);

            if (jsonData.success) {
                alert("✅ Recibo creado correctamente.");
            } else {
                alert("⚠ Hubo un problema al crear el recibo.");
            }
        } catch (error) {
            console.error("❌ Error al analizar JSON. Respuesta inesperada:", text);
            alert("⚠ Se recibió una respuesta inesperada del servidor.");
        }

    } catch (error) {
        console.error("🚨 Error al hacer la solicitud:", error);
        alert("❌ Error al enviar el recibo. Inténtalo nuevamente.");
    }
}



function isValidDate(dia, mes, anio) {
    const fecha = new Date(anio, mes - 1, dia);
    return fecha.getFullYear() === anio && fecha.getMonth() === mes - 1 && fecha.getDate() === dia;
}

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
