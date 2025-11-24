<div class="modal-backdrop" id="modalEditar">
    <div class="modal">
        <div class="m-head">
            <div class="m-title">Modificar Cita</div>
            <button id="closeModalEditar" class="modal-close">×</button>
        </div>

        <div class="m-body">

            <div class="row">
                {{-- FECHA --}}
                <label>
                    Fecha
                    <input
                        type="text"
                        id="editFechaCita"
                        placeholder="YYYY-MM-DD"
                        autocomplete="off"
                    >
                </label>

                {{-- HORA --}}
                <label>
                    Hora
                    <select id="editHoraCita">
                        <option value="">Selecciona hora</option>
                        <option value="08:00">08:00 AM</option>
                        <option value="09:00">09:00 AM</option>
                        <option value="10:00">10:00 AM</option>
                        <option value="11:00">11:00 AM</option>
                        <option value="12:00">12:00 PM</option>
                        <option value="13:00">1:00 PM</option>
                        <option value="14:00">2:00 PM</option>
                        <option value="15:00">3:00 PM</option>
                        <option value="16:00">4:00 PM</option>
                        <option value="17:00">5:00 PM</option>
                    </select>
                </label>
            </div>

        </div>

        <div class="m-footer">
            <button id="cancelModalEditar" class="button ghost">Cancelar</button>
            <button id="guardarCambiosCita" class="button primary">
                Guardar Cambios
            </button>
        </div>
    </div>
</div>
