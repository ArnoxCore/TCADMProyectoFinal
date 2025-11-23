document.addEventListener("DOMContentLoaded", () => {
    const searchCliente   = document.getElementById("searchCliente");
    const searchFecha     = document.getElementById("searchFecha");
    const searchMecanico  = document.getElementById("searchMecanico");
    const searchEstatus   = document.getElementById("searchEstatus");
    const btnClear        = document.getElementById("btnClear");
    const tableBody       = document.getElementById("tableBody");
    const kpiFiltrados    = document.getElementById("kpiFiltrados");
    const labelResultados = document.getElementById("labelResultados");

    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content') || '';

    const ROUTES = window.APP_ROUTES || {};

    function buildRoute(template, id) {
        if (!template) return "";
        return template.replace(':id', id);
    }

    let rows = [];

    function actualizarRows() {
        rows = Array.from(tableBody.querySelectorAll("tr"));
        aplicarFiltros();
    }

    function aplicarFiltros() {
        const clienteFiltro  = (searchCliente?.value || "").toLowerCase().trim();
        const mecanicoFiltro = (searchMecanico?.value || "").trim();
        const estatusFiltro  = (searchEstatus?.value || "").toLowerCase().trim();

        let visibles = 0;

        rows.forEach(row => {
            const cells = row.children;

            // Fila "No hay citas..." -> la dejamos visible y no la contamos
            if (cells.length < 8) {
                row.style.display = "";
                return;
            }

            // [Fecha, Hora, Cliente, Vehículo, Servicio, Mecánico, Estatus, Acciones]
            const colCliente  = cells[2];
            const colMecanico = cells[5];
            const colEstatus  = cells[6];

            const clienteTexto  = colCliente.textContent.toLowerCase();
            const mecanicoTexto = colMecanico.textContent.trim();
            const estatusTexto  = colEstatus.textContent.toLowerCase();

            let mostrar = true;

            if (clienteFiltro && !clienteTexto.includes(clienteFiltro)) {
                mostrar = false;
            }

            if (mecanicoFiltro && mecanicoTexto !== mecanicoFiltro) {
                mostrar = false;
            }

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

    function accionesHtmlDesdeJson(cita) {
        const estatus = cita.estatus?.value || "";
        let html = "";

        if (estatus === "pendiente") {
            html += `
                <button
                    type="button"
                    class="btn-accion btn-confirmar"
                    data-cita-id="${cita.id}"
                    data-fecha="${cita.fecha || ""}"
                >
                    Confirmar
                </button>
                <button
                    type="button"
                    class="btn-accion btn-cancelar"
                    data-cita-id="${cita.id}"
                >
                    Cancelar
                </button>
            `;
        } else if (estatus === "confirmada") {
            html += `
                <button
                    type="button"
                    class="btn-accion btn-cancelar"
                    data-cita-id="${cita.id}"
                >
                    Cancelar
                </button>
            `;
        }

        return html;
    }

    // ====== Cargar citas (fecha opcional) ======
    async function cargarCitas(fecha) {
        const params = new URLSearchParams();
        if (fecha) {
            params.append("fecha", fecha);
        }

        const baseUrl = ROUTES.citasPorFecha || "/recepcion/citas-por-fecha";
        const url = `${baseUrl}?${params.toString()}`;

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
                tr.innerHTML = `<td colspan="8">No hay citas para esta fecha.</td>`;
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
                    <td class="acciones">
                        ${accionesHtmlDesdeJson(cita)}
                    </td>
                `;

                tableBody.appendChild(tr);
            });

            actualizarRows();
        } catch (error) {
            console.error("Error cargando citas:", error);
        }
    }

    // ====== Handlers de acciones: Confirmar / Cancelar ======

    async function manejarConfirmarCita(citaId, row) {
        if (!citaId) return;

        const mecanicosUrl = buildRoute(ROUTES.mecanicosDisponibles, citaId);

        try {
            const resp = await fetch(mecanicosUrl);
            if (!resp.ok) {
                console.error("Error al cargar mecánicos:", resp.status);
                Swal.fire("Error", "No se pudieron cargar los mecánicos.", "error");
                return;
            }

            const json = await resp.json();
            if (!json.success) {
                console.error("Respuesta mecánicos no exitosa:", json);
                Swal.fire("Error", "No se pudieron cargar los mecánicos.", "error");
                return;
            }

            const lista = json.data || [];

            if (lista.length === 0) {
                Swal.fire(
                    "Sin disponibilidad",
                    "Ningún mecánico tiene cupo para esa fecha.",
                    "warning"
                );
                return;
            }

            const inputOptions = {};
            lista.forEach(m => {
                inputOptions[m.id] = `${m.nombre} (${m.citas_vigentes}/${m.max_citas})`;
            });

            const { value: mecanicoId } = await Swal.fire({
                title: "Asignar mecánico",
                text: "Selecciona un mecánico para confirmar la cita.",
                input: "select",
                inputOptions,
                inputPlaceholder: "Selecciona un mecánico",
                showCancelButton: true,
                confirmButtonText: "Confirmar cita",
                cancelButtonText: "Cancelar",
                preConfirm: (value) => {
                    if (!value) {
                        Swal.showValidationMessage("Debes seleccionar un mecánico");
                    }
                    return value;
                }
            });

            if (!mecanicoId) {
                // Usuario canceló
                return;
            }

            const url = buildRoute(ROUTES.asignarConfirmar, citaId);

            const respConfirm = await fetch(url, {
                method: "PATCH",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                },
                body: JSON.stringify({ mecanico_id: mecanicoId }),
            });

            if (!respConfirm.ok) {
                const errorText = await respConfirm.text();
                console.error("Error HTTP al confirmar cita:", respConfirm.status, errorText);
                Swal.fire("Error", "No se pudo confirmar la cita.", "error");
                return;
            }

            const jsonConfirm = await respConfirm.json();

            if (!jsonConfirm.success) {
                Swal.fire("Error", jsonConfirm.message || "No se pudo confirmar la cita.", "error");
                return;
            }

            // Actualizar fila: mecánico + badge + acciones
            const cells = row.children;
            const tdMecanico = cells[5];
            const tdEstatus  = cells[6];
            const tdAcciones = cells[7];

            tdMecanico.textContent = jsonConfirm.mecanico || "Sin asignar";

            tdEstatus.innerHTML = `
                <span class="badge badge-${jsonConfirm.estatus.value}">
                    ${jsonConfirm.estatus.label}
                </span>
            `;

            tdAcciones.innerHTML = `
                <button
                    type="button"
                    class="btn-accion btn-cancelar"
                    data-cita-id="${citaId}"
                >
                    Cancelar
                </button>
            `;

            Swal.fire("Listo", jsonConfirm.message || "Cita confirmada.", "success");
            actualizarRows();
        } catch (e) {
            console.error("Error confirmando cita:", e);
            Swal.fire("Error", "Ocurrió un error al confirmar la cita.", "error");
        }
    }

    async function manejarCancelarCita(citaId, row) {
        if (!citaId) return;

        const confirm = await Swal.fire({
            title: "Cancelar cita",
            text: "¿Seguro que deseas cancelar esta cita?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, cancelar",
            cancelButtonText: "No",
        });

        if (!confirm.isConfirmed) {
            return;
        }

        const url = buildRoute(ROUTES.cancelarCita, citaId);

        try {
            const resp = await fetch(url, {
                method: "PATCH",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                },
                body: JSON.stringify({}),
            });

            if (!resp.ok) {
                const errorText = await resp.text();
                console.error("Error HTTP al cancelar cita:", resp.status, errorText);
                Swal.fire("Error", "No se pudo cancelar la cita.", "error");
                return;
            }

            const jsonCancel = await resp.json();

            if (!jsonCancel.success) {
                Swal.fire("Error", jsonCancel.message || "No se pudo cancelar la cita.", "error");
                return;
            }

            const cells = row.children;
            const tdEstatus  = cells[6];
            const tdAcciones = cells[7];

            tdEstatus.innerHTML = `
                <span class="badge badge-${jsonCancel.estatus.value}">
                    ${jsonCancel.estatus.label}
                </span>
            `;

            tdAcciones.innerHTML = "";

            Swal.fire("Cancelada", jsonCancel.message || "La cita fue cancelada.", "success");
            actualizarRows();
        } catch (e) {
            console.error("Error cancelando cita:", e);
            Swal.fire("Error", "Ocurrió un error al cancelar la cita.", "error");
        }
    }

    // ====== Cambio de fecha -> recargar (si la borras, trae TODAS) ======
    if (searchFecha) {
        searchFecha.addEventListener("change", () => {
            const value = searchFecha.value || "";
            cargarCitas(value);
        });
    }

    // Filtros en vivo (cliente, mecánico, estatus)
    [searchCliente, searchMecanico, searchEstatus].forEach(input => {
        if (!input) return;
        input.addEventListener("input", aplicarFiltros);
        input.addEventListener("change", aplicarFiltros);
    });

    // Limpiar filtros
    if (btnClear) {
        btnClear.addEventListener("click", () => {
            if (searchCliente)  searchCliente.value = "";
            if (searchMecanico) searchMecanico.value = "";
            if (searchEstatus)  searchEstatus.value = "";

            if (searchFecha) {
                searchFecha.value = "";
                cargarCitas("");
            }

            aplicarFiltros();
        });
    }

    // Delegación de eventos para botones Confirmar / Cancelar
    document.addEventListener("click", (event) => {
        const target = event.target;

        if (target.classList.contains("btn-confirmar")) {
            const row = target.closest("tr");
            const citaId = target.getAttribute("data-cita-id");
            manejarConfirmarCita(citaId, row);
        }

        if (target.classList.contains("btn-cancelar")) {
            const row = target.closest("tr");
            const citaId = target.getAttribute("data-cita-id");
            manejarCancelarCita(citaId, row);
        }
    });

    // Inicial: usar las filas que pintó Blade para la fecha por defecto
    actualizarRows();
});
