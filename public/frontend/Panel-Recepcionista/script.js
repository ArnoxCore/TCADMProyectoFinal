document.addEventListener("DOMContentLoaded", () => {
    const searchCliente   = document.getElementById("searchCliente");
    const searchFecha     = document.getElementById("searchFecha");
    const searchMecanico  = document.getElementById("searchMecanico");
    const searchEstatus   = document.getElementById("searchEstatus");
    const btnClear        = document.getElementById("btnClear");
    const tableBody       = document.getElementById("tableBody");
    const kpiFiltrados    = document.getElementById("kpiFiltrados");
    const labelResultados = document.getElementById("labelResultados");
    const formFiltros     = document.getElementById("formFiltros");
    const inputShowAll    = document.getElementById("show_all");

    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content') || '';

    const ROUTES = window.APP_ROUTES || {};

    function buildRoute(template, id) {
        if (!template) return "";
        return template.replace('__ID__', id).replace(':id', id);
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
            if (cells.length < 9) {
                row.style.display = "";
                return;
            }

            // [Fecha, Hora, Cliente, Vehículo, Servicio, Mecánico, Asistencia, Estatus, Acciones]
            const colCliente  = cells[2];
            const colMecanico = cells[5];
            const colEstatus  = cells[7];

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

    // ========= Handlers de acciones: asistencia =========

    function updateStatusCell(cell, estatus) {
        if (!cell || !estatus) return;
        cell.innerHTML = `
            <span class="badge badge-${estatus.value}">
                ${estatus.label}
            </span>
        `;
    }

    function updateAttendanceCell(cell, attendance) {
        if (!cell) return;

        const label = attendance?.label || "Pendiente";
        const secondary = attendance?.secondary;

        let html = `<div class="attendance-state"><span>${label}</span>`;
        if (secondary) {
            html += `<small>${secondary}</small>`;
        }
        html += "</div>";

        cell.innerHTML = html;
    }

    function renderActionsCell(row, actions, citaIdOverride) {
        const td = row?.children?.[8];
        if (!td) return;

        const citaId = citaIdOverride || row?.dataset?.citaId || "";
        const buttons = [];

        if (actions?.can_check_in) {
            buttons.push(`
                <button type="button" class="btn-accion btn-checkin" data-cita-id="${citaId}">
                    Registrar llegada
                </button>
            `);
        }

        if (actions?.can_start) {
            buttons.push(`
                <button type="button" class="btn-accion btn-start" data-cita-id="${citaId}">
                    Iniciar servicio
                </button>
            `);
        }

        if (actions?.can_mark_no_show) {
            buttons.push(`
                <button type="button" class="btn-accion btn-no-show" data-cita-id="${citaId}">
                    No asistió
                </button>
            `);
        }

        if (actions?.can_cancel) {
            buttons.push(`
                <button type="button" class="btn-accion btn-cancelar" data-cita-id="${citaId}">
                    Cancelar
                </button>
            `);
        }

        td.innerHTML = buttons.length
            ? `<div class="acciones-group">${buttons.join("")}</div>`
            : '<div class="acciones-group"><span class="acciones-placeholder">—</span></div>';
    }

    function applyActionPayload(row, payload) {
        if (!row || !payload) return;
        const cells = row.children;
        updateAttendanceCell(cells[6], payload.attendance);
        updateStatusCell(cells[7], payload.estatus);
        renderActionsCell(row, payload.actions);
    }

    async function sendPatchAction(url) {
        if (!url) return null;

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

            const data = await resp.json();

            if (!resp.ok || !data.success) {
                const message = data?.message || "No se pudo completar la acción.";
                Swal.fire("Error", message, "error");
                return null;
            }

            return data;
        } catch (error) {
            console.error("Error en la acción", error);
            Swal.fire("Error", "Ocurrió un error al procesar la acción.", "error");
            return null;
        }
    }

    async function manejarCheckIn(citaId, row) {
        if (!citaId) return;

        const confirm = await Swal.fire({
            title: "Registrar llegada",
            text: "¿Registrar el check-in del cliente?",
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Sí, registrar",
            cancelButtonText: "No",
        });

        if (!confirm.isConfirmed) {
            return;
        }

        const url = buildRoute(ROUTES.checkIn, citaId);
        const data = await sendPatchAction(url);

        if (!data) return;

        applyActionPayload(row, data);
        Swal.fire("Listo", data.message || "Asistencia registrada.", "success");
        actualizarRows();
    }

    async function manejarInicioServicio(citaId, row) {
        if (!citaId) return;

        const confirm = await Swal.fire({
            title: "Iniciar servicio",
            text: "¿Registrar la hora de inicio?",
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Sí, iniciar",
            cancelButtonText: "No",
        });

        if (!confirm.isConfirmed) {
            return;
        }

        const url = buildRoute(ROUTES.startService, citaId);
        const data = await sendPatchAction(url);

        if (!data) return;

        applyActionPayload(row, data);
        Swal.fire("Registrado", data.message || "Inicio registrado.", "success");
        actualizarRows();
    }

    async function manejarNoShow(citaId, row) {
        if (!citaId) return;

        const confirm = await Swal.fire({
            title: "Marcar inasistencia",
            text: "¿Confirmas que el cliente no asistió?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, marcar",
            cancelButtonText: "No",
        });

        if (!confirm.isConfirmed) {
            return;
        }

        const url = buildRoute(ROUTES.noShow, citaId);
        const data = await sendPatchAction(url);

        if (!data) return;

        applyActionPayload(row, data);
        Swal.fire("Actualizado", data.message || "Se registró la inasistencia.", "success");
        actualizarRows();
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
        const data = await sendPatchAction(url);

        if (!data) return;

        applyActionPayload(row, data);
        Swal.fire("Cancelada", data.message || "La cita fue cancelada.", "success");
        actualizarRows();
    }

    // ====== Cambio de fecha -> recargar con paginación de Laravel ======
    if (searchFecha && formFiltros) {
        searchFecha.addEventListener("change", () => {
            if (inputShowAll) inputShowAll.value = "0"; // modo "solo esa fecha"
            formFiltros.submit();
        });
    }

    // Filtros en vivo (cliente, mecánico, estatus) - solo front
    [searchCliente, searchMecanico, searchEstatus].forEach(input => {
        if (!input) return;
        input.addEventListener("input", aplicarFiltros);
        input.addEventListener("change", aplicarFiltros);
    });

    // Limpiar filtros -> ver TODAS las citas (sin fecha, con paginación)
    if (btnClear && formFiltros) {
        btnClear.addEventListener("click", () => {
            if (searchCliente)  searchCliente.value = "";
            if (searchMecanico) searchMecanico.value = "";
            if (searchEstatus)  searchEstatus.value = "";
            if (searchFecha)    searchFecha.value = "";

            if (inputShowAll) inputShowAll.value = "1"; // modo "ver todas"

            formFiltros.submit();
        });
    }

    // Delegación de eventos para botones de asistencia
    document.addEventListener("click", (event) => {
        const target = event.target;

        const btnCancel = target.closest(".btn-cancelar");
        if (btnCancel) {
            const row = btnCancel.closest("tr");
            const citaId = btnCancel.getAttribute("data-cita-id");
            manejarCancelarCita(citaId, row);
            return;
        }

        const btnCheckIn = target.closest(".btn-checkin");
        if (btnCheckIn) {
            const row = btnCheckIn.closest("tr");
            const citaId = btnCheckIn.getAttribute("data-cita-id");
            manejarCheckIn(citaId, row);
            return;
        }

        const btnStart = target.closest(".btn-start");
        if (btnStart) {
            const row = btnStart.closest("tr");
            const citaId = btnStart.getAttribute("data-cita-id");
            manejarInicioServicio(citaId, row);
            return;
        }

        const btnNoShow = target.closest(".btn-no-show");
        if (btnNoShow) {
            const row = btnNoShow.closest("tr");
            const citaId = btnNoShow.getAttribute("data-cita-id");
            manejarNoShow(citaId, row);
        }
    });

    // Inicial: usar las filas que pintó Blade para la página actual
    actualizarRows();
});
