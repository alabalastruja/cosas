document.addEventListener("DOMContentLoaded", () => {
    const tablaRecibos = document.getElementById("tabla-recibos").getElementsByTagName('tbody')[0];
    const xhr = new XMLHttpRequest();

    xhr.open("GET", "http://localhost/fuv.org.uy/andrei/FUV/php/api2.php", true);


    xhr.onreadystatechange = () => {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                try {
                    const recibos = JSON.parse(xhr.responseText);

                    if (recibos.length === 0) {
                        tablaRecibos.innerHTML = "<tr><td colspan='10'>No hay recibos registrados.</td></tr>";
                    } else {
                        tablaRecibos.innerHTML = "";

                        const thead = document.querySelector("#tabla-recibos thead");
                        if (thead) {
                            thead.innerHTML = `
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Moneda</th>
                                    <th>Monto</th>
                                    <th>Monto en Letra</th>
                                    <th>Concepto</th>
                                    <th>Banco</th>
                                    <th>Fecha</th>
                                    <th>Estado</th>
                                    <th>Metodo de pago</th>
                                    <th>Acciones</th>
                                </tr>
                            `;
                        }

                        recibos.forEach((recibo) => {
                            const fila = tablaRecibos.insertRow();

                            let editarButton = "";
                            let imprimirButton = "";
                            let anularButton = "";

                            if (recibo.estado === "nuevo") {
                                editarButton = `<button class="btn-editar" data-id="${recibo.id}">Editar</button>`;
                                imprimirButton = `<button class="btn-imprimir" data-id="${recibo.id}">Imprimir</button>`;
                                anularButton = `<button class="btn-anular" data-id="${recibo.id}">Anular</button>`;
                            }

                            fila.innerHTML = `
                                <td>${recibo.id}</td>
                                <td>${recibo.nombre}</td>
                                <td>${recibo.moneda}</td>
                                <td>${recibo.monto}</td>
                                <td>${recibo.monto_letra}</td>
                                <td>${recibo.concepto}</td>
                                <td>${recibo.banco}</td>
                                <td>${recibo.dia}/${recibo.mes}/${recibo.anio}</td>
                                <td>${recibo.estado}</td>
                                <td>${recibo.metodo_pago}</td>
                                <td>${editarButton} ${imprimirButton} ${anularButton}</td>
                            `;

                            if (editarButton) {
                                fila.querySelector(".btn-editar").addEventListener("click", (e) => {
                                    const reciboId = e.target.dataset.id;
                                    window.location.href = `editar.html?id=${reciboId}`;
                                });
                            }

                            if (imprimirButton) {
                                fila.querySelector(".btn-imprimir").addEventListener("click", (e) => {
                                    const reciboId = e.target.dataset.id;
                                    window.location.href = `imprimir.html?id=${reciboId}`;
                                });
                            }

                            if (anularButton) {
                                fila.querySelector(".btn-anular").addEventListener("click", (e) => {
                                    const reciboId = e.target.dataset.id;
                                    const confirmar = confirm("¿Estás seguro de que deseas anular este recibo?");
                                    if (confirmar) {
                                        cambiarEstadoRecibo(reciboId, "anulado");
                                    }
                                });
                            }
                        });
                    }
                } catch (error) {
                    console.error("Error al parsear los recibos:", error);
                }
            } else {
                console.error("Error al obtener los recibos. Código:", xhr.status);
            }
        }
    };

    xhr.send();
});
function cambiarEstadoRecibo(id, nuevoEstado) {
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "http://localhost/fuv.org.uy/andrei/FUV/php/estado.php", true);
    xhr.setRequestHeader("Content-Type", "application/json");

    xhr.onreadystatechange = () => {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                try {
                    const result = JSON.parse(xhr.responseText);
                    if (result.success) {
                        alert(`El estado del recibo con ID ${id} se ha cambiado a ${nuevoEstado}`);
                        location.reload();
                    } else {
                        alert("Hubo un error al actualizar el estado del recibo.");
                    }
                } catch (error) {
                    console.error("Error al parsear la respuesta del servidor:", error);
                }
            } else {
                console.error("Error al cambiar el estado del recibo. Código:", xhr.status);
            }
        }
    };

    const body = JSON.stringify({ id: id, estado: nuevoEstado });
    xhr.send(body);
}
