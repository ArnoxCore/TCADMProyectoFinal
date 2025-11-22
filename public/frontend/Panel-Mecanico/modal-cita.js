// Variables globales del modal
let citaIdActual = null;
let estatusActual = '';
let mecanicoIdActual = null; // Ya no se usa para asignar mecánico, se mantiene por compatibilidad

/**
 * Abre el modal con los detalles de la cita
 */
function abrirModalCita(id, servicio, vehiculo, fecha, horaHora, estatus, mecanicoId, observaciones, clienteNombre) {
    citaIdActual = id;
    estatusActual = estatus;
    mecanicoIdActual = mecanicoId;

    // Llenar campos del modal
    document.getElementById('citaTitle').innerText = servicio;
    document.getElementById('citaServicio').innerText = servicio || 'N/A';
    document.getElementById('citaVehiculo').innerText = vehiculo || 'N/A';
    document.getElementById('citaFechaHora').innerText = (fecha ? fecha + ' - ' : '') + (horaHora || 'N/A');
    document.getElementById('citaEstatus').value = estatus;
    // Mostrar nombre del cliente si se pasa
    const clienteDiv = document.getElementById('citaCliente');
    if (clienteDiv && clienteNombre) {
        clienteDiv.innerText = clienteNombre;
    }

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
// Eliminada la carga de mecánicos: ya no se asignan desde el panel

/**
 * Actualiza el estatus de la cita
 */
function actualizarEstatus() {
    const nuevoEstatus = document.getElementById('citaEstatus').value;
    
    if (nuevoEstatus === estatusActual) {
        return; // Sin cambios
    }
    // Si el mecánico intenta confirmar la cita, pedir confirmación explícita
    const proceedToUpdate = () => {
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
                toast.success(data.message || 'Estado actualizado');
            } else {
                alert('Error: ' + (data.message || 'No se pudo actualizar el estado'));
                // Revertir select al estado anterior
                document.getElementById('citaEstatus').value = estatusActual;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al actualizar el estado');
            document.getElementById('citaEstatus').value = estatusActual;
        });
    };

    if (nuevoEstatus === 'confirmada') {
        // Mostrar modal de confirmación personalizado
        mostrarConfirmacion('¿Seguro que quieres confirmar esta cita?', 'Sí, confirmar', 'No')
            .then(confirmed => {
                if (confirmed) {
                    proceedToUpdate();
                } else {
                    // Revertir selección
                    document.getElementById('citaEstatus').value = estatusActual;
                }
            });
    } else {
        proceedToUpdate();
    }
}


/**
 * Muestra un modal de confirmación reutilizable.
 * Devuelve una Promise que resuelve true si el usuario confirma, false si cancela.
 */
function mostrarConfirmacion(mensaje, textoConfirmar = 'Sí', textoCancelar = 'No') {
    return new Promise((resolve) => {
        // Crear elementos si no existen
        let confirmModal = document.getElementById('confirmModal');
        if (!confirmModal) {
            confirmModal = document.createElement('div');
            confirmModal.id = 'confirmModal';
            confirmModal.style.position = 'fixed';
            confirmModal.style.top = '0';
            confirmModal.style.left = '0';
            confirmModal.style.right = '0';
            confirmModal.style.bottom = '0';
            confirmModal.style.background = 'rgba(0,0,0,0.5)';
            confirmModal.style.zIndex = '1100';
            confirmModal.style.display = 'flex';
            confirmModal.style.justifyContent = 'center';
            confirmModal.style.alignItems = 'center';

            confirmModal.innerHTML = `
                <div style="background:white; padding:22px; border-radius:8px; width:420px; max-width:90%; box-shadow:0 8px 30px rgba(0,0,0,0.15);">
                    <div style="font-size:16px; color:#333; margin-bottom:12px;">${mensaje}</div>
                    <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:8px;">
                        <button id="confirmNo" class="button ghost">${textoCancelar}</button>
                        <button id="confirmYes" class="button primary">${textoConfirmar}</button>
                    </div>
                </div>
            `;
            document.body.appendChild(confirmModal);
        } else {
            // actualizar texto
            confirmModal.querySelector('div > div').innerText = mensaje;
            confirmModal.style.display = 'flex';
            const yesBtn = confirmModal.querySelector('#confirmYes');
            const noBtn = confirmModal.querySelector('#confirmNo');
            if (yesBtn) yesBtn.innerText = textoConfirmar;
            if (noBtn) noBtn.innerText = textoCancelar;
        }

        // Attach handlers
        const yesHandler = () => {
            confirmModal.style.display = 'none';
            cleanup();
            resolve(true);
        };

        const noHandler = () => {
            confirmModal.style.display = 'none';
            cleanup();
            resolve(false);
        };

        function cleanup() {
            const yes = confirmModal.querySelector('#confirmYes');
            const no = confirmModal.querySelector('#confirmNo');
            if (yes) yes.removeEventListener('click', yesHandler);
            if (no) no.removeEventListener('click', noHandler);
        }

        // Add listeners
        confirmModal.querySelector('#confirmYes').addEventListener('click', yesHandler);
        confirmModal.querySelector('#confirmNo').addEventListener('click', noHandler);
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
