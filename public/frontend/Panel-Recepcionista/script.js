document.addEventListener("DOMContentLoaded", () => {
    const searchCliente = document.getElementById("searchCliente");
    const searchFecha = document.getElementById("searchFecha");
    const searchMecanico = document.getElementById("searchMecanico");
    const searchEstatus = document.getElementById("searchEstatus");
    const btnClear = document.getElementById("btnClear");
    const tableBody = document.getElementById("tableBody");
    const kpiFiltrados = document.getElementById("kpiFiltrados");
    const labelResultados = document.getElementById("labelResultados");

    let rows = [];

    function actualizarRows() {
        rows = Array.from(tableBody.querySelectorAll("tr"));
        aplicarFiltros();
    }

    function aplicarFiltros() {
        const clienteFiltro = (searchCliente?.value || "").toLowerCase().trim();
        const mecanicoFiltro = (searchMecanico?.value || "").trim();
        const estatusFiltro = (searchEstatus?.value || "").trim();

        let visibles = 0;

        rows.forEach(row => {
            // [Fecha, Hora, Cliente, Vehículo, Servicio, Mecánico, Estatus]
            const [colFecha, , colCliente, , , colMecanico, colEstatus] = row.children;

            const clienteTexto = colCliente.textContent.toLowerCase();
            const mecanicoTexto = colMecanico.textContent.trim();
            const estatusTexto = colEstatus.textContent.trim();

            let mostrar = true;

            if (clienteFiltro && !clienteTexto.includes(clienteFiltro)) {
                mostrar = false;
            }

            if (mecanicoFiltro && mecanicoTexto !== mecanicoFiltro) {
                mostrar = false;
            }

            // includes para que "Pendiente de Confirmación" matchee con "Pendiente"
            if (estatusFiltro && !estatusTexto.includes(estatusFiltro)) {
                mostrar = false;
            }

            row.style.display = mostrar ? "" : "none";
            if (mostrar) visibles++;
        });

        if (kpiFiltrados) {
            kpiFiltrados.textContent = visibles;
        }

        if (labelResultados) {
            labelResultados.textContent = `Mostrando ${visibles} citas`;
        }
    }

    // ====== Cargar citas (fecha opcional) ======
    async function cargarCitas(fecha) {
        const params = new URLSearchParams();
        if (fecha) {
            params.append("fecha", fecha);
        }

        const url = `/recepcion/citas-por-fecha?${params.toString()}`;

        try {
            const response = await fetch(url);

            if (!response.ok) {
                console.error("Error HTTP al cargar citas:", response.status);
                return;
            }

            const json = await response.json();

            if (!json.success) {
                console.error("Respuesta no exitosa:", json);
                return;
            }

            tableBody.innerHTML = "";

            if (!json.data || json.data.length === 0) {
                const tr = document.createElement("tr");
                tr.innerHTML = `<td colspan="7">No hay citas para esta fecha.</td>`;
                tableBody.appendChild(tr);
                actualizarRows();
                return;
            }

            json.data.forEach(cita => {
                const tr = document.createElement("tr");

                tr.innerHTML = `
                    <td>${cita.fecha || ""}</td>
                    <td>${cita.hora || ""}</td>
                    <td>${cita.cliente || "—"}</td>
                    <td>${cita.vehiculo || "—"}</td>
                    <td>${cita.servicio || "—"}</td>
                    <td>${cita.mecanico || "Sin asignar"}</td>
                    <td>
                        <span class="badge badge-${cita.estatus.value}">
                            ${cita.estatus.label}
                        </span>
                    </td>
                `;

                tableBody.appendChild(tr);
            });

            actualizarRows();
        } catch (error) {
            console.error("Error cargando citas:", error);
        }
    }

    // Cambio de fecha -> recargar (si la borras, trae TODAS)
    if (searchFecha) {
        searchFecha.addEventListener("change", () => {
            const value = searchFecha.value || ""; // si queda vacío, backend entiende "todas"
            cargarCitas(value);
        });
    }

    // Filtros en vivo (cliente, mecánico, estatus)
    [searchCliente, searchMecanico, searchEstatus].forEach(input => {
        if (!input) return;
        input.addEventListener("input", aplicarFiltros);
        input.addEventListener("change", aplicarFiltros);
    });

    // Limpiar filtros: borra cliente/mecánico/estatus y TAMBIÉN la fecha,
    // y recarga TODAS las citas
    if (btnClear) {
        btnClear.addEventListener("click", () => {
            if (searchCliente) searchCliente.value = "";
            if (searchMecanico) searchMecanico.value = "";
            if (searchEstatus) searchEstatus.value = "";

            if (searchFecha) {
                searchFecha.value = "";
                cargarCitas(""); // sin fecha -> todas las citas
            }

            aplicarFiltros();
        });
    }

    // Inicial: usar las filas que pintó Blade para la fecha por defecto
    actualizarRows();
});
