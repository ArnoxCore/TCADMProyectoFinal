// Variables globales del modal
let citaIdActual = null;
let estatusActual = '';
let mecanicoIdActual = null;

/**
 * Abre el modal con los detalles de la cita
 */
function abrirModalCita(id, servicio, vehiculo, fecha, horaHora, estatus, mecanicoId, observaciones) {
    citaIdActual = id;
    estatusActual = estatus;
    mecanicoIdActual = mecanicoId;

    // Llenar campos del modal
    document.getElementById('citaTitle').innerText = servicio;
    document.getElementById('citaServicio').innerText = servicio || 'N/A';
    document.getElementById('citaVehiculo').innerText = vehiculo || 'N/A';
    document.getElementById('citaFechaHora').innerText = (fecha ? fecha + ' - ' : '') + (horaHora || 'N/A');
    document.getElementById('citaEstatus').value = estatus;
    document.getElementById('citaObservaciones').innerText = observaciones || 'Sin observaciones';

    // Cargar lista de mecánicos
    cargarMecanicos(mecanicoId);

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

/**
 * Cierra el modal
 */
function cerrarModal() {
    document.getElementById('citaModal').style.display = 'none';
    citaIdActual = null;
}

/**
 * Carga la lista de mecánicos disponibles
 */
function cargarMecanicos(mecanicoIdActual) {
    const baseUrl = window.location.pathname.includes('test-mecanico') ? '/test-mecanico' : '/mecanico';
    
    fetch(`${baseUrl}/mecanicos-list`)
        .then(response => response.json())
        .then(mecanicos => {
            const select = document.getElementById('citaMecanico');
            select.innerHTML = '<option value="">-- Sin asignar --</option>';
            
            mecanicos.forEach(mecanico => {
                const option = document.createElement('option');
                option.value = mecanico.id;
                option.innerText = mecanico.nombre;
                if (mecanico.id === mecanicoIdActual) {
                    option.selected = true;
                }
                select.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error cargando mecánicos:', error);
            document.getElementById('mecanicoLoading').innerText = 'Error al cargar mecánicos';
        });
}

/**
 * Actualiza el estatus de la cita
 */
function actualizarEstatus() {
    const nuevoEstatus = document.getElementById('citaEstatus').value;
    
    if (nuevoEstatus === estatusActual) {
        return; // Sin cambios
    }

    const baseUrl = window.location.pathname.includes('test-mecanico') ? '/test-mecanico' : '/mecanico';
    
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
        } else {
            alert('Error: ' + (data.message || 'No se pudo actualizar el estado'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al actualizar el estado');
    });
}

/**
 * Asigna un mecánico a la cita
 */
function asignarMecanico() {
    const nuevoMecanicoId = document.getElementById('citaMecanico').value;
    
    if (!nuevoMecanicoId || nuevoMecanicoId === mecanicoIdActual) {
        return; // Sin cambios o sin seleccionar
    }

    const baseUrl = window.location.pathname.includes('test-mecanico') ? '/test-mecanico' : '/mecanico';
    
    fetch(`${baseUrl}/citas/${citaIdActual}/mecanico`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: JSON.stringify({ mecanico_id: nuevoMecanicoId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mecanicoIdActual = nuevoMecanicoId;
            console.log('Mecánico asignado:', data.message);
        } else {
            alert('Error: ' + (data.message || 'No se pudo asignar el mecánico'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al asignar el mecánico');
    });
}

/**
 * Guarda los cambios realizados en el modal
 */
function guardarCambios() {
    alert('Los cambios se han guardado. La página se recargará para reflejar los cambios.');
    cerrarModal();
    location.reload();
}
