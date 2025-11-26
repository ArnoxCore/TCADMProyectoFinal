// Variables globales del modal
let citaIdActual = null;
let estatusActual = '';
let mecanicoIdActual = null; // Ya no se usa para asignar mecánico, se mantiene por compatibilidad
let observacionesActuales = [];
let clienteAsistioActual = false;

const escapeHtml = (input = '') => input
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');

const getBaseUrl = () => window.location.pathname.includes('test-mecanico') ? '/test-mecanico' : '/mecanico';

/**
 * Abre el modal con los detalles de la cita
 */
function abrirModalCita(id, servicio, vehiculo, fecha, horaHora, estatus, mecanicoId, observaciones = [], clienteNombre = '', clienteAsistio = false) {
    citaIdActual = id;
    estatusActual = estatus;
    mecanicoIdActual = mecanicoId;
    observacionesActuales = Array.isArray(observaciones) ? observaciones : [];
    clienteAsistioActual = Boolean(clienteAsistio);

    // Llenar campos del modal
    document.getElementById('citaTitle').innerText = servicio;
    document.getElementById('citaServicio').innerText = servicio || 'N/A';
    document.getElementById('citaVehiculo').innerText = vehiculo || 'N/A';
    document.getElementById('citaFechaHora').innerText = (fecha ? fecha + ' - ' : '') + (horaHora || 'N/A');
    const clienteDiv = document.getElementById('citaCliente');
    if (clienteDiv) {
        clienteDiv.innerText = clienteNombre || 'Sin cliente asignado';
    }
    const textarea = document.getElementById('citaObservaciones');
    if (textarea) {
        textarea.value = '';
    }
    renderObservaciones(observacionesActuales);
    syncEstatusControls();

    const select = document.getElementById('citaEstatus');
    const allowedStatuses = ['en_proceso', 'completada'];
    if (allowedStatuses.includes(estatus)) {
        select.value = estatus;
    } else {
        select.value = allowedStatuses[0];
        estatusActual = allowedStatuses[0];
    }
    // Mostrar nombre del cliente si se pasa

    // Mostrar modal
    const modal = document.getElementById('citaModal');
    modal.style.display = 'flex';

    // Cerrar modal al hacer clic fuera
    modal.onclick = function(event) {
        if (event.target === modal) {
            cerrarModal();
        }
    };
}

function renderObservaciones(lista = []) {
    const contenedor = document.getElementById('observacionesHistorial');
    if (!contenedor) {
        return;
    }

    if (!lista.length) {
        contenedor.innerHTML = '<p style="margin:0; color:#777;">Sin observaciones registradas.</p>';
        return;
    }

    contenedor.innerHTML = lista.map((item) => {
        const fecha = escapeHtml(item.fecha || 'Sin fecha');
        const texto = escapeHtml(item.texto || '');
        const autor = escapeHtml(item.mecanico || 'Mecánico');
        return `
            <div style="padding:8px 10px; border-bottom:1px solid #e4e7ed;">
                <div style="font-size:12px; color:#6c757d; margin-bottom:4px;">${fecha} · ${autor}</div>
                <div style="font-size:14px; color:#2f3542; white-space:pre-wrap;">${texto}</div>
            </div>
        `;
    }).join('');
}

function syncEstatusControls() {
    const select = document.getElementById('citaEstatus');
    const helper = document.getElementById('estatusHelper');
    if (!select) {
        return;
    }

    if (clienteAsistioActual) {
        select.disabled = false;
        if (helper) {
            helper.textContent = 'Cliente presente. Puedes actualizar el estado.';
            helper.style.color = '#198754';
        }
    } else {
        select.disabled = true;
        select.value = estatusActual;
        if (helper) {
            helper.textContent = 'Recepción debe registrar la llegada del cliente para habilitar este cambio.';
            helper.style.color = '#d9534f';
        }
    }
}

/**
 * Cierra el modal
 */
function cerrarModal() {
    document.getElementById('citaModal').style.display = 'none';
    citaIdActual = null;
    observacionesActuales = [];
    clienteAsistioActual = false;
}

/**
 * Carga la lista de mecánicos disponibles
 */
// Eliminada la carga de mecánicos: ya no se asignan desde el panel

/**
 * Actualiza el estatus de la cita
 */
function actualizarEstatus() {
    const nuevoEstatus = document.getElementById('citaEstatus').value;
    
    if (!clienteAsistioActual) {
        toast.warning('Debes esperar a que recepción marque la llegada del cliente.');
        document.getElementById('citaEstatus').value = estatusActual;
        return;
    }

    if (nuevoEstatus === estatusActual) {
        return; // Sin cambios
    }
    const baseUrl = getBaseUrl();

    fetch(`${baseUrl}/citas/${citaIdActual}/estatus`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: JSON.stringify({ estatus: nuevoEstatus })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            estatusActual = nuevoEstatus;
            console.log('Estado actualizado:', data.message);
            toast.success(data.message || 'Estado actualizado');
        } else {
            alert('Error: ' + (data.message || 'No se pudo actualizar el estado'));
            document.getElementById('citaEstatus').value = estatusActual;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al actualizar el estado');
        document.getElementById('citaEstatus').value = estatusActual;
    });
}

function guardarObservacion() {
    if (!citaIdActual) {
        toast.error('Selecciona una cita antes de guardar tu comentario.');
        return;
    }

    const textarea = document.getElementById('citaObservaciones');
    const btn = document.getElementById('btnGuardarObservacion');
    const texto = textarea ? textarea.value.trim() : '';

    if (!texto || texto.length < 5) {
        toast.warning('La observación debe tener al menos 5 caracteres.');
        return;
    }

    const baseUrl = getBaseUrl();
    if (btn) {
        btn.disabled = true;
        btn.textContent = 'Guardando...';
    }

    fetch(`${baseUrl}/citas/${citaIdActual}/observaciones`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: JSON.stringify({ observacion: texto })
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            throw new Error(data.message || 'No se pudo guardar la observación.');
        }

        toast.success(data.message || 'Observación registrada.');
        textarea.value = '';
        if (data.observacion) {
            observacionesActuales = [data.observacion, ...observacionesActuales];
            renderObservaciones(observacionesActuales);
        }
    })
    .catch(error => {
        console.error(error);
        toast.error(error.message || 'Error al guardar la observación.');
    })
    .finally(() => {
        if (btn) {
            btn.disabled = false;
            btn.textContent = 'Guardar observación';
        }
    });
}

/**
 * Asigna un mecánico a la cita
 */
// Eliminada función asignarMecanico: no se permite asignar desde este modal

/**
 * Guarda los cambios realizados en el modal
 */
function guardarCambios() {
    alert('Los cambios se han guardado. La página se recargará para reflejar los cambios.');
    cerrarModal();
    location.reload();
}
