console.log("CLIENTE.JS NUEVO CARGADO");

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

    closeBtn?.addEventListener("click", () => modal.classList.remove("active"));
    cancelBtn?.addEventListener("click", () => modal.classList.remove("active"));

    // ===============================
    //              AGENDAR CITA
    // ===============================
    const agendar = document.getElementById("agendar-btn");

    agendar?.addEventListener("click", async e => {
        e.preventDefault();

        const servicio = document.getElementById("servicio")?.value;
        const vehiculo = document.getElementById("vehiculo_id")?.value;
        const fecha = document.getElementById("fechaCita")?.value;
        const hora = document.getElementById("hora")?.value;

        if (!servicio || !vehiculo || !fecha || !hora) {
            toast.error("Por favor completa todos los campos");
            return;
        }

        try {
            const res = await fetch("/mi-cuenta/citas", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                    "Accept": "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                },
                body: JSON.stringify({
                    vehiculo_id: vehiculo,
                    servicios: [servicio],
                    fecha,
                    hora
                })
            });

            const data = await res.json();

            if (res.ok && data.success) {
                toast.success(data.message || "Cita agendada exitosamente");
                modal.classList.remove("active");
                setTimeout(() => location.reload(), 800);
            } else {
                const msg =
                    data?.message ||
                    (data?.errors ? Object.values(data.errors)[0][0] : null) ||
                    "No se pudo agendar la cita";
                toast.error(msg);
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
            toast.error("El VIN debe tener exactamente 17 caracteres.");
            return;
        }

        vinStatus.style.display = "block";
        vinStatus.style.color = "#62748e";
        vinStatus.textContent = "Validando VIN…";

        try {
            const url = `https://vpic.nhtsa.dot.gov/api/vehicles/DecodeVinValues/${vin}?format=json`;
            const res = await fetch(url);
            const data = await res.json();
            const info = data.Results[0];

            if (!info.Make || !info.Model || !info.ModelYear) {
                vinStatus.style.color = "#d97706";
                vinStatus.textContent = "VIN no disponible en la base de EE.UU.";
                return;
            }

            const marcaEl  = document.getElementById("marca");
            const modeloEl = document.getElementById("modelo");
            const anoEl    = document.getElementById("ano");

            if (marcaEl)  marcaEl.value  = info.Make.toUpperCase();
            if (modeloEl) modeloEl.value = info.Model.toUpperCase();
            if (anoEl)    anoEl.value    = info.ModelYear;

            vinStatus.style.color = "#16a34a";
            vinStatus.textContent = `Vehículo identificado: ${info.Make} ${info.Model} ${info.ModelYear}`;
            toast.success(`Vehículo identificado: ${info.Make} ${info.Model} ${info.ModelYear}`);

        } catch (err) {
            console.error(err);
            vinStatus.style.color = "#dc2626";
            vinStatus.textContent = "Error al consultar el VIN.";
        }
    }

    buscarBtn?.addEventListener("click", buscarVin);

    // ==========================================================
    //   VALIDACIONES EXTRA DE VEHÍCULOS
    // ==========================================================
    function mayus(id) {
        const el = document.getElementById(id);
        if (!el) return;
        el.addEventListener("input", () => {
            el.value = el.value.toUpperCase();
        });
    }

    mayus("marca");
    mayus("modelo");
    mayus("color");

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
            const regex = /^[A-Z]{3}-\d{3}-[A-Z0-9]{1,2}$/;
            if (!regex.test(placaInput.value)) {
                toast.error("Formato inválido: ABC-123-A");
            }
        });
    }

    // ==========================================================
    //   IMÁGENES (CarsXE) — PARCHEADO
    // ==========================================================
    const carPhotos = document.querySelectorAll(".car-photo");

    if (carPhotos.length > 0) {
        carPhotos.forEach((img) => {
            const marca = img.dataset.marca;
            const modelo = img.dataset.modelo;
            const ano = img.dataset.ano;
            const skeleton = img.previousElementSibling;

            // OJO: pon este archivo en public/images/fallback-car.png
            const fallback = "/images/fallback-car.png";

            // Quitar skeleton de entrada y poner fallback
            if (skeleton) skeleton.remove();
            img.style.display = "block";
            img.src = fallback;

            if (!marca || !modelo || !ano) {
                console.warn("car-photo sin datos suficientes", { marca, modelo, ano });
                return;
            }

            (async () => {
                try {
                    const url = `/api/car-image?make=${encodeURIComponent(
                        marca
                    )}&model=${encodeURIComponent(modelo)}&year=${encodeURIComponent(ano)}`;

                    console.log("➡️ Fetch car image:", url);

                    const res = await fetch(url, {
                        headers: {
                            "Accept": "application/json",
                            "X-Requested-With": "XMLHttpRequest",
                        },
                    });

                    console.log("HTTP status car-image:", res.status);

                    if (!res.ok) {
                        console.warn("❌ car-image HTTP error:", res.status);
                        return;
                    }

                    const data = await res.json();
                    console.log("✅ car-image response:", data);

                    if (!data.image) {
                        console.warn("❌ car-image sin 'image' en la respuesta");
                        return;
                    }

                    img.src = data.image;
                } catch (err) {
                    console.error("🔥 Error cargando imagen de vehículo:", err);
                    // nos quedamos con el fallback
                }
            })();
        });
    }

    // =========================================================
    // FUNCIÓN GENERAL PARA LIMITAR DATEPICKERS
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
                toast.error("No se pueden agendar citas los domingos.");
                input.value = "";
            }
        });
    }

    const fechaCrear = document.getElementById("fechaCita");
    if (fechaCrear) limitarFechas(fechaCrear);

    // =========================================================
    // MODAL EDITAR CITA
    // =========================================================
    const modalEditar = document.getElementById("modalEditar");
    const fechaEditar = document.getElementById("editFechaCita");
    const horaEditar = document.getElementById("editHoraCita");
    const closeEdit = document.getElementById("closeModalEditar");
    const cancelEdit = document.getElementById("cancelModalEditar");
    const guardarEdit = document.getElementById("guardarCambiosCita");

    let citaEditId = null;

    if (fechaEditar) limitarFechas(fechaEditar);

    document.querySelectorAll(".btn-modificar-cita").forEach(btn => {
        btn.addEventListener("click", () => {
            citaEditId = btn.dataset.citaId;
            fechaEditar.value = btn.dataset.fecha;
            horaEditar.value = btn.dataset.hora;
            modalEditar.classList.add("active");
        });
    });

    closeEdit?.addEventListener("click", () => modalEditar.classList.remove("active"));
    cancelEdit?.addEventListener("click", () => modalEditar.classList.remove("active"));

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

    // =========================================================
    // CANCELAR CITA DESDE DASHBOARD
    // =========================================================
    const cancelarBtns = document.querySelectorAll(".btn-cancelar-cita");

    cancelarBtns.forEach((btn) => {
        btn.addEventListener("click", async () => {
            const citaId = btn.dataset.citaId;
            const url = btn.dataset.cancelUrl;

            if (!url || !citaId) {
                console.warn("Falta data-cancel-url o data-cita-id en el botón de cancelar.");
                return;
            }

            const confirmar = confirm("¿Seguro que quieres cancelar esta cita?");
            if (!confirmar) return;

            try {
                const res = await fetch(url, {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                        "Accept": "application/json",
                        "X-Requested-With": "XMLHttpRequest",
                    },
                });

                let data = {};
                try {
                    data = await res.json();
                } catch (_) {
                    // puede venir sin body JSON
                }

                if (res.ok && (data.success ?? true)) {
                    toast.success(data.message || "Cita cancelada correctamente");
                    setTimeout(() => location.reload(), 800);
                } else {
                    toast.error(
                        data.message || "No se pudo cancelar la cita"
                    );
                }
            } catch (err) {
                console.error(err);
                toast.error("Error al cancelar la cita");
            }
        });
    });
});
