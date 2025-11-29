document.addEventListener("DOMContentLoaded", () => {
    const searchCliente   = document.getElementById("searchCliente");
    const searchFechaInicio = document.getElementById("searchFechaInicio");
    const searchFechaFin     = document.getElementById("searchFechaFin");
    const searchMecanico  = document.getElementById("searchMecanico");
    const searchEstatus   = document.getElementById("searchEstatus");
    const btnClear        = document.getElementById("btnClear");
    const tableBody       = document.getElementById("tableBody");
    const kpiFiltrados    = document.getElementById("kpiFiltrados");
    const labelResultados = document.getElementById("labelResultados");
    const formFiltros     = document.getElementById("formFiltros");
    const inputShowAll    = document.getElementById("show_all");
    const viewToggles        = document.querySelectorAll(".view-toggle");
    const tableView          = document.getElementById("tableView");
    const calendarView       = document.getElementById("calendarView");
    const calendarElement    = document.getElementById("recepcionCalendar");
    const rangeAppliedField  = document.getElementById("rangeApplied");
    const btnToday           = document.getElementById("btnToday");

    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content') || '';

    const ROUTES = window.APP_ROUTES || {};

    let rows = [];
    let activeView = "tableView";
    let calendarInstance = null;
    let lastCalendarRange = null;
    let calendarNeedsRefresh = true;
    let calendarRequestId = 0;
    let rangeApplied = rangeAppliedField?.value === "1";

    function buildRoute(template, id) {
        if (!template) return "";
        return template.replace('__ID__', id).replace(':id', id);
    }

    function formatDateForInput(date) {
        const tzOffset = date.getTimezoneOffset() * 60000;
        const local = new Date(date.getTime() - tzOffset);
        return local.toISOString().slice(0, 10);
    }

    function normalizeStatusValue(value) {
        if (!value) return "";
        const key = value.toLowerCase().trim();
        const map = {
            'pendiente': 'pendiente',
            'confirmada': 'confirmada',
            'en proceso': 'en_proceso',
            'en_proceso': 'en_proceso',
            'completada': 'completada',
            'cancelada': 'cancelada',
        };
        return map[key] || "";
    }

    function updateTableIndicators(count) {
        if (labelResultados) {
            labelResultados.textContent = `Mostrando ${count} citas`;
        }

        if (activeView === 'tableView' && kpiFiltrados) {
            kpiFiltrados.textContent = count;
        }
    }

    function collectCalendarFilters() {
        return {
            cliente: (searchCliente?.value || "").trim(),
            mecanico: (searchMecanico?.value || "").trim(),
            estatus: normalizeStatusValue(searchEstatus?.value || ""),
            fecha_inicio: rangeApplied ? (searchFechaInicio?.value || "") : "",
            fecha_fin: rangeApplied ? (searchFechaFin?.value || "") : "",
        };
    }

    function switchView(targetId) {
        if (!targetId) return;

        activeView = targetId;

        viewToggles.forEach(btn => {
            const isActive = btn.dataset.target === targetId;
            btn.classList.toggle("active", isActive);
        });

        if (tableView) {
            tableView.style.display = targetId === 'tableView' ? '' : 'none';
        }

        if (calendarView) {
            calendarView.style.display = targetId === 'calendarView' ? '' : 'none';
        }

        if (targetId === 'calendarView') {
            initCalendar();

            if (calendarNeedsRefresh && lastCalendarRange) {
                refreshCalendarEvents();
            }
        } else {
            aplicarFiltros();
        }
    }

    function initCalendar() {
        if (!calendarElement || calendarInstance) {
            return;
        }

        const Calendar = window.FullCalendar?.Calendar;

        if (!Calendar) {
            console.warn('FullCalendar no está disponible.');
            return;
        }

        const today = new Date();
        const minDate = new Date(today.getFullYear(), today.getMonth() - 3, 1);
        const maxDate = new Date(today.getFullYear(), today.getMonth() + 4, 0);

        calendarInstance = new Calendar(calendarElement, {
            initialView: 'dayGridMonth',
            locale: 'es',
            timeZone: 'local',
            firstDay: 1,
            height: 'auto',
            validRange() {
                const toIso = date => new Date(date.getTime() - date.getTimezoneOffset() * 60000)
                    .toISOString()
                    .slice(0, 10);

                return {
                    start: toIso(minDate),
                    end: toIso(maxDate),
                };
            },
            navLinks: false,
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: ''
            },
            eventDisplay: 'block',
            eventTimeFormat: {
                hour: '2-digit',
                minute: '2-digit',
                meridiem: false
            },
            datesSet(info) {
                lastCalendarRange = info;

                if (activeView === 'calendarView') {
                    fetchCalendarEvents(info);
                } else {
                    calendarNeedsRefresh = true;
                }
            },
            eventClick(info) {
                info.jsEvent?.preventDefault();
                mostrarDetallesEvento(info.event);
            }
        });

        calendarInstance.render();
    }

    async function fetchCalendarEvents(rangeInfo) {
        if (!ROUTES.calendarEvents || !rangeInfo) {
            calendarNeedsRefresh = true;
            return;
        }

        calendarRequestId += 1;
        const requestId = calendarRequestId;

        const params = new URLSearchParams({
            start: rangeInfo.startStr,
            end: rangeInfo.endStr,
        });

        const filters = collectCalendarFilters();

        Object.entries(filters).forEach(([key, value]) => {
            if (value) {
                params.append(key, value);
            }
        });

        try {
            const response = await fetch(`${ROUTES.calendarEvents}?${params.toString()}`, {
                headers: {
                    'Accept': 'application/json',
                },
            });

            const payload = await response.json();

            if (requestId !== calendarRequestId) {
                return;
            }

            if (!response.ok || !payload?.success) {
                throw new Error(payload?.message || 'No se pudo cargar el calendario.');
            }

            const events = Array.isArray(payload.data) ? payload.data : [];

            calendarInstance.removeAllEvents();
            calendarInstance.addEventSource(events);

            calendarNeedsRefresh = false;

            if (activeView === 'calendarView' && kpiFiltrados) {
                kpiFiltrados.textContent = events.length;
            }
        } catch (error) {
            console.error('Error al cargar el calendario', error);
            calendarNeedsRefresh = true;
            Swal.fire('Error', error.message || 'No se pudieron cargar las citas del calendario.', 'error');
        }
    }

    function refreshCalendarEvents() {
        if (activeView !== 'calendarView') {
            calendarNeedsRefresh = true;
            return;
        }

        if (!calendarInstance || !lastCalendarRange) {
            calendarNeedsRefresh = true;
            return;
        }

        fetchCalendarEvents(lastCalendarRange);
    }

    function mostrarDetallesEvento(evento) {
        if (typeof Swal === 'undefined' || !evento) {
            return;
        }

        const props = evento.extendedProps || {};
        const detalle = `
            <div class="calendar-event-details">
                <p><strong>Cliente:</strong> ${props.cliente || '—'}</p>
                <p><strong>Mecánico:</strong> ${props.mecanico || 'Sin asignar'}</p>
                <p><strong>Estatus:</strong> ${props.estatus || '—'}</p>
                <p><strong>Servicios:</strong> ${props.servicios || '—'}</p>
                <p><strong>Vehículo:</strong> ${props.vehiculo || '—'}</p>
                <p><strong>Asistencia:</strong> ${props.asistencia || 'Pendiente'}</p>
            </div>
        `;

        Swal.fire({
            title: `Cita #${props.folio ?? evento.id}`,
            html: detalle,
            confirmButtonText: 'Cerrar',
            width: 480,
        });
    }

    function actualizarRows() {
        if (!tableBody) return;

        rows = Array.from(tableBody.querySelectorAll("tr"));
        aplicarFiltros();
    }

    function aplicarFiltros() {
        if (!rows.length) {
            updateTableIndicators(0);
            return;
        }

        const clienteFiltro  = (searchCliente?.value || "").toLowerCase().trim();
        const mecanicoFiltro = (searchMecanico?.value || "").trim();
        const estatusFiltro  = (searchEstatus?.value || "").toLowerCase().trim();

        let visibles = 0;

        rows.forEach(row => {
            const cells = row.children;

            if (cells.length < 9) {
                row.style.display = "";
                return;
            }

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

        updateTableIndicators(visibles);
    }

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
                <button
                    type="button"
                    class="btn-accion btn-icon btn-checkin"
                    data-cita-id="${citaId}"
                    title="Registrar llegada"
                    aria-label="Registrar llegada"
                >
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M5 12l4 4 10-10" />
                    </svg>
                </button>
            `);
        }

        if (actions?.can_start) {
            buttons.push(`
                <button
                    type="button"
                    class="btn-accion btn-icon btn-start"
                    data-cita-id="${citaId}"
                    title="Iniciar servicio"
                    aria-label="Iniciar servicio"
                >
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M8 5v14l10-7z" />
                    </svg>
                </button>
            `);
        }

        if (actions?.can_mark_no_show) {
            buttons.push(`
                <button
                    type="button"
                    class="btn-accion btn-icon btn-no-show"
                    data-cita-id="${citaId}"
                    title="Marcar inasistencia"
                    aria-label="Marcar inasistencia"
                >
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="12" cy="12" r="9" />
                        <path d="M6 6l12 12" />
                    </svg>
                </button>
            `);
        }

        if (actions?.can_cancel) {
            buttons.push(`
                <button
                    type="button"
                    class="btn-accion btn-icon btn-cancelar"
                    data-cita-id="${citaId}"
                    title="Cancelar cita"
                    aria-label="Cancelar cita"
                >
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M6 6l12 12M18 6l-12 12" />
                    </svg>
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
        refreshCalendarEvents();
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
        refreshCalendarEvents();
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
        refreshCalendarEvents();
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
        refreshCalendarEvents();
    }

    const setRangeApplied = (value) => {
        rangeApplied = value;
        if (rangeAppliedField) {
            rangeAppliedField.value = value ? "1" : "0";
        }
    };

    const onRangeChange = () => {
        setRangeApplied(true);
        if (inputShowAll) inputShowAll.value = "0";
        formFiltros?.submit();
    };

    if (searchFechaInicio) {
        searchFechaInicio.addEventListener("change", onRangeChange);
    }

    if (searchFechaFin) {
        searchFechaFin.addEventListener("change", onRangeChange);
    }

    if (btnToday) {
        btnToday.addEventListener("click", () => {
            const todayValue = formatDateForInput(new Date());

            if (searchFechaInicio) searchFechaInicio.value = todayValue;
            if (searchFechaFin) searchFechaFin.value = todayValue;

            setRangeApplied(true);

            if (inputShowAll) inputShowAll.value = "0";

            formFiltros?.submit();
        });
    }

    const onLiveFilterChange = () => {
        aplicarFiltros();
        refreshCalendarEvents();
    };

    [searchCliente, searchMecanico, searchEstatus].forEach(input => {
        if (!input) return;
        input.addEventListener("input", onLiveFilterChange);
        input.addEventListener("change", onLiveFilterChange);
    });

    if (btnClear && formFiltros) {
        btnClear.addEventListener("click", () => {
            if (searchCliente)  searchCliente.value = "";
            if (searchMecanico) searchMecanico.value = "";
            if (searchEstatus)  searchEstatus.value = "";
            if (searchFechaInicio) searchFechaInicio.value = "";
            if (searchFechaFin) searchFechaFin.value = "";

            if (inputShowAll) inputShowAll.value = "1";
            setRangeApplied(false);

            formFiltros.submit();
        });
    }

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

    viewToggles.forEach(btn => {
        btn.addEventListener('click', () => {
            const target = btn.dataset.target;
            switchView(target);
        });
    });

    actualizarRows();
    switchView('tableView');
});
