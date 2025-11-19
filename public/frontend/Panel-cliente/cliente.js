document.addEventListener("DOMContentLoaded", () => {

    // ===============================
    //              TABS
    // ===============================
    const tabs = document.querySelectorAll(".tab");
    const contents = document.querySelectorAll(".tab-content");

    tabs.forEach(tab => {
        tab.addEventListener("click", () => {
            tabs.forEach(t => t.classList.remove("active"));
            contents.forEach(c => c.classList.remove("active"));

            tab.classList.add("active");
            if (tab.dataset.tab) {
                document.getElementById(tab.dataset.tab)?.classList.add("active");
            }
        });
    });

    // ===============================
    //              MODAL AGENDAR
    // ===============================
    const modal = document.getElementById("modal");
    const openBtn = document.getElementById("openModal");
    const closeBtn = document.getElementById("closeModal");
    const cancelBtn = document.getElementById("cancelModal");

    if (openBtn && modal) {
        openBtn.addEventListener("click", () => {
            const perfilCompleto = openBtn.dataset.perfil == "1";

            if (!perfilCompleto) {
                toast.error("Debes completar tu perfil antes de agendar una cita.");
                return;
            }

            modal.classList.add("active");
        });
    }

    if (closeBtn) closeBtn.addEventListener("click", () => modal.classList.remove("active"));
    if (cancelBtn) cancelBtn.addEventListener("click", () => modal.classList.remove("active"));

    // ===============================
    //              AGENDAR CITA
    // ===============================
    const agendar = document.getElementById("agendar-btn");

    agendar?.addEventListener("click", async (e) => {
        e.preventDefault();

        const servicio = document.getElementById("servicio")?.value;
        const vehiculo = document.getElementById("vehiculo_id")?.value;
        const fecha = document.querySelector("input[type='date']")?.value;
        const hora = document.getElementById("hora")?.value;

        if (!servicio || !vehiculo || !fecha || !hora) {
            toast.warning("Por favor completa todos los campos");
            return;
        }

        try {
            const response = await fetch("/citas", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    servicio_id: servicio,
                    vehiculo_id: vehiculo,
                    fecha,
                    hora
                })
            });

            const data = await response.json();

            if (response.ok) {
                toast.success("Cita agendada exitosamente");
                modal.classList.remove("active");
                location.reload();
            } else {
                toast.error(data.message || "No se pudo agendar la cita");
            }
        } catch (error) {
            console.error(error);
            toast.error("Error al enviar la solicitud");
        }
    });

    // =======================================================
    //   VALIDACIÓN DE VIN
    // =======================================================
    const vinInput = document.getElementById("vin-input");
    const vinStatus = document.getElementById("vin-status");
    const buscarBtn = document.getElementById("buscar-vin-btn");

    async function buscarVin() {
        const vin = vinInput.value.toUpperCase().trim();

        if (vin.length !== 17) {
            toast.warning("El VIN debe tener exactamente 17 caracteres.");
            return;
        }

        vinStatus.style.display = "block";
        vinStatus.style.color = "#62748e";
        vinStatus.textContent = "Validando VIN…";

        try {
            const url = `https://vpic.nhtsa.dot.gov/api/vehicles/DecodeVinValues/${vin}?format=json`;
            const response = await fetch(url);
            const data = await response.json();
            const info = data.Results[0];

            const make = info.Make;
            const model = info.Model;
            const year = info.ModelYear;

            if (!make || !model || !year) {
                vinStatus.style.color = "#d97706";
                vinStatus.textContent = "VIN no disponible en la base de EE.UU.";
                return;
            }

            document.getElementById("marca").value = make.toUpperCase();
            document.getElementById("modelo").value = model.toUpperCase();
            document.getElementById("ano").value = year;

            vinStatus.style.color = "#16a34a";
            vinStatus.textContent = `Vehículo identificado: ${make} ${model} ${year}`;
            toast.success(`Vehículo identificado: ${make} ${model} ${year}`);

        } catch (err) {
            console.error(err);
            vinStatus.style.color = "#dc2626";
            vinStatus.textContent = "Error al consultar el VIN.";
        }
    }

    if (buscarBtn) buscarBtn.addEventListener("click", buscarVin);

    // ==========================================================
    //   VALIDACIÓN DE PLACA
    // ==========================================================
    const placaInput = document.getElementById("placa");

    if (placaInput) {
        placaInput.addEventListener("input", () => {
            placaInput.value = placaInput.value.toUpperCase();
        });

        placaInput.addEventListener("keyup", () => {
            let raw = placaInput.value.replace(/[^A-Z0-9]/g, "");

            if (raw.length >= 3 && raw.length <= 6) {
                raw = raw.replace(/^([A-Z]{3})(\d{1,3})$/, "$1-$2");
            }
            if (raw.length > 6) {
                raw = raw.replace(/^([A-Z]{3})(\d{3})([A-Z0-9]{1,2})$/, "$1-$2-$3");
            }
            placaInput.value = raw;
        });

        placaInput.addEventListener("blur", () => {
            const v = placaInput.value;
            const regex = /^[A-Z]{3}-\d{3}-[A-Z0-9]{1,2}$/;
            if (!regex.test(v)) toast.warning("Formato inválido: ABC-123-A");
        });
    }

    // ==========================================================
    //   IMÁGENES (CarsXE)
    // ==========================================================
    const carPhotos = document.querySelectorAll(".car-photo");

    carPhotos.forEach(async img => {
        const marca = img.dataset.marca;
        const modelo = img.dataset.modelo;
        const ano = img.dataset.ano;
        const skeleton = img.previousElementSibling;
        const fallback = "/images/fallback-car.png";

        try {
            const url = `/api/car-image?make=${encodeURIComponent(marca)}&model=${encodeURIComponent(modelo)}&year=${encodeURIComponent(ano)}`;

            const response = await fetch(url);
            if (!response.ok) throw new Error();

            const data = await response.json();
            if (!data.image) throw new Error();

            img.src = data.image;

            img.onload = () => {
                img.style.display = "block";
                skeleton.remove();
            };

            img.onerror = () => {
                img.src = fallback;
                skeleton.remove();
            };

        } catch {
            img.src = fallback;
            img.onload = () => skeleton.remove();
        }
    });

    // =========================================================
    // 🔥 FUNCIÓN GENERAL PARA LIMITAR DATEPICKERS
    // =========================================================
    function limitarFechas(input) {
        const hoy = new Date();
        const yyyy = hoy.getFullYear();
        const mm = String(hoy.getMonth() + 1).padStart(2, "0");
        const dd = String(hoy.getDate()).padStart(2, "0");

        input.min = `${yyyy}-${mm}-${dd}`;

        const max = new Date();
        max.setDate(hoy.getDate() + 7);

        const yyyy2 = max.getFullYear();
        const mm2 = String(max.getMonth() + 1).padStart(2, "0");
        const dd2 = String(max.getDate()).padStart(2, "0");

        input.max = `${yyyy2}-${mm2}-${dd2}`;

        input.addEventListener("change", () => {
            const d = new Date(input.value);
            if (d.getDay() === 0) {
                toast.warning("No se pueden agendar citas los domingos.");
                input.value = "";
            }
        });
    }

    // Aplicar límites a modal AGENDAR
    const fechaCrear = document.getElementById("fechaCita");
    if (fechaCrear) limitarFechas(fechaCrear);

    // =========================================================
    // 🔥 MODAL EDITAR CITA
    // =========================================================

    const modalEditar = document.getElementById("modalEditar");
    const fechaEditar = document.getElementById("editFechaCita");
    const horaEditar = document.getElementById("editHoraCita");
    const closeEdit = document.getElementById("closeModalEditar");
    const cancelEdit = document.getElementById("cancelModalEditar");
    const guardarEdit = document.getElementById("guardarCambiosCita");

    let citaEditId = null;

    // Aplicamos limites igual que crear
    if (fechaEditar) limitarFechas(fechaEditar);

    // Abrir modal editar
    document.querySelectorAll(".btn-modificar-cita").forEach(btn => {
        btn.addEventListener("click", () => {
            citaEditId = btn.dataset.citaId;

            fechaEditar.value = btn.dataset.fecha;
            horaEditar.value = btn.dataset.hora;

            modalEditar.classList.add("active");
        });
    });

    // Cerrar modal
    if (closeEdit) closeEdit.addEventListener("click", () => modalEditar.classList.remove("active"));
    if (cancelEdit) cancelEdit.addEventListener("click", () => modalEditar.classList.remove("active"));

    // Guardar actualización
    guardarEdit?.addEventListener("click", async () => {
        if (!fechaEditar.value || !horaEditar.value) {
            toast.error("Completa la fecha y hora");
            return;
        }

        try {
            const res = await fetch(`/mi-cuenta/citas/${citaEditId}/actualizar`, {
                method: "PATCH",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    fecha: fechaEditar.value,
                    hora: horaEditar.value
                })
            });

            const data = await res.json();

            if (data.success) {
                toast.success("Cita actualizada");
                modalEditar.classList.remove("active");
                setTimeout(() => location.reload(), 800);
            } else {
                toast.error("No se pudo actualizar");
            }

        } catch (err) {
            console.error(err);
            toast.error("Error al actualizar");
        }
    });

});
