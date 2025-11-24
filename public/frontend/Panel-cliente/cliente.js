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
    const marcaSelect = document.getElementById("marca");
    const modeloSelect = document.getElementById("modelo");
    const anoSelect = document.getElementById("ano");
    const catalogoEndpoint = window.CATALOGO_ENDPOINTS?.modelos || null;
    const oldCatalog = window.CATALOGO_OLD || {};
    const modelosCache = new Map();

    function resetSelect(select, placeholder) {
        if (!select) return;
        select.innerHTML = `<option value="">${placeholder}</option>`;
        select.value = "";
        select.disabled = true;
    }

    function getSelectedMakeId() {
        if (!marcaSelect) return null;
        const option = marcaSelect.options[marcaSelect.selectedIndex];
        return option ? option.getAttribute("data-make-id") : null;
    }

    async function obtenerModelos(marcaId) {
        if (!catalogoEndpoint || !marcaId) return [];
        if (modelosCache.has(marcaId)) {
            return modelosCache.get(marcaId);
        }

        const url = catalogoEndpoint.replace("__MAKE__", marcaId);
        try {
            const res = await fetch(url, {
                headers: { "Accept": "application/json" }
            });
            if (!res.ok) {
                throw new Error(`Error HTTP ${res.status}`);
            }
            const data = await res.json();
            const modelos = data.modelos || [];
            modelosCache.set(marcaId, modelos);
            return modelos;
        } catch (error) {
            console.error("Error obteniendo modelos", error);
            toast?.error?.("No se pudieron cargar los modelos para la marca seleccionada.");
            return [];
        }
    }

    async function popularModelos(marcaId, modeloPreseleccionado = null, anoPreseleccionado = null) {
        if (!modeloSelect) return;
        resetSelect(modeloSelect, "Selecciona un modelo");
        resetSelect(anoSelect, "Selecciona un año");

        const modelos = await obtenerModelos(marcaId);
        if (!modelos.length) {
            return;
        }

        modelos.forEach((modelo) => {
            const option = document.createElement("option");
            option.value = modelo.nombre;
            option.textContent = modelo.nombre;
            option.dataset.years = JSON.stringify(modelo.years || []);
            modeloSelect.appendChild(option);
        });

        modeloSelect.disabled = false;

        if (modeloPreseleccionado) {
            const option = Array.from(modeloSelect.options).find(opt => opt.value === modeloPreseleccionado);
            if (option) {
                modeloSelect.value = modeloPreseleccionado;
                popularAnosDesdeOption(option, anoPreseleccionado);
            }
        }
    }

    function popularAnosDesdeOption(option, anoPreseleccionado = null) {
        if (!anoSelect || !option) return;
        resetSelect(anoSelect, "Selecciona un año");

        let anos = [];
        try {
            anos = JSON.parse(option.dataset.years || "[]");
        } catch (error) {
            console.warn("No se pudieron parsear los años del modelo", error);
        }

        if (!Array.isArray(anos) || !anos.length) return;

        anos.forEach((ano) => {
            const opt = document.createElement("option");
            opt.value = ano;
            opt.textContent = ano;
            anoSelect.appendChild(opt);
        });

        anoSelect.disabled = false;
        if (anoPreseleccionado && anos.includes(Number(anoPreseleccionado))) {
            anoSelect.value = anoPreseleccionado;
        }
    }

    marcaSelect?.addEventListener("change", async () => {
        const makeId = getSelectedMakeId();
        if (!makeId) {
            resetSelect(modeloSelect, "Selecciona un modelo");
            resetSelect(anoSelect, "Selecciona un año");
            return;
        }
        await popularModelos(makeId);
    });

    modeloSelect?.addEventListener("change", () => {
        const option = modeloSelect.options[modeloSelect.selectedIndex];
        if (!option) {
            resetSelect(anoSelect, "Selecciona un año");
            return;
        }
        popularAnosDesdeOption(option);
    });

    (async function inicializarCatalogo() {
        if (!marcaSelect || !marcaSelect.value) {
            return;
        }
        const makeId = getSelectedMakeId();
        if (!makeId) return;
        await popularModelos(makeId, oldCatalog?.modelo || null, oldCatalog?.ano || null);
    })();

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

                const marcaDetectada = info.Make?.toUpperCase();
                const modeloDetectado = info.Model?.toUpperCase();
                const anoDetectado = Number(info.ModelYear);

                const marcaOption = Array.from(marcaSelect?.options || []).find(
                    (opt) => opt.value === marcaDetectada
                );

                if (!marcaOption) {
                    vinStatus.style.color = "#d97706";
                    vinStatus.textContent = "La marca detectada no está en el catálogo disponible.";
                    toast.warning("La marca detectada no está en el catálogo. Selecciona una opción manualmente.");
                    return;
                }

                marcaSelect.value = marcaDetectada;
                const makeId = marcaOption.getAttribute("data-make-id");
                await popularModelos(makeId, modeloDetectado, anoDetectado);

                const modeloOption = Array.from(modeloSelect?.options || []).find(
                    (opt) => opt.value === modeloDetectado
                );

                if (!modeloOption) {
                    vinStatus.style.color = "#d97706";
                    vinStatus.textContent = "Modelo detectado fuera del catálogo. Selecciona una opción disponible.";
                    toast.warning("El modelo detectado no está en el catálogo. Selecciona uno disponible.");
                    return;
                }

                popularAnosDesdeOption(modeloOption, anoDetectado);

                if (!anoSelect.value) {
                    vinStatus.style.color = "#d97706";
                    vinStatus.textContent = "Año detectado fuera del catálogo. Selecciona uno disponible.";
                    toast.warning("El año detectado no está en el catálogo. Selecciona uno disponible.");
                    return;
                }

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
    // DATEPICKERS (Flatpickr + fallback)
    // =========================================================
    const TIMEZONE = "America/Mexico_City";

    function getTodayInTimezone() {
        return new Date(new Date().toLocaleString("en-US", { timeZone: TIMEZONE }));
    }

    function getRange(days = 7) {
        const today = getTodayInTimezone();
        const max = new Date(today.getTime());
        max.setDate(today.getDate() + days);
        return { today, max };
    }

    function aplicarFlatpickr(input) {
        if (!input) return null;

        const { today, max } = getRange();

        if (window.flatpickr) {
            if (input._flatpickr) {
                input._flatpickr.set("minDate", today);
                input._flatpickr.set("maxDate", max);
                return input._flatpickr;
            }

            return flatpickr(input, {
                locale: (flatpickr.l10ns && flatpickr.l10ns.es) ? flatpickr.l10ns.es : undefined,
                dateFormat: "Y-m-d",
                minDate: today,
                maxDate: max,
                disable: [
                    function(date) {
                        return date.getDay() === 0; // domingos
                    }
                ],
                allowInput: true,
                defaultDate: input.value || null
            });
        }

        limitarFechasFallback(input, today, max);
        return null;
    }

    function limitarFechasFallback(input, today, max) {
        if (!input) return;

        const yyyy = today.getFullYear();
        const mm = String(today.getMonth() + 1).padStart(2, "0");
        const dd = String(today.getDate()).padStart(2, "0");
        input.min = `${yyyy}-${mm}-${dd}`;

        const yyyy2 = max.getFullYear();
        const mm2 = String(max.getMonth() + 1).padStart(2, "0");
        const dd2 = String(max.getDate()).padStart(2, "0");
        input.max = `${yyyy2}-${mm2}-${dd2}`;

        input.addEventListener("change", () => {
            if (!input.value) return;

            const seleccion = new Date(`${input.value}T00:00:00`);
            if (seleccion.getUTCDay() === 0) {
                toast.error("No se pueden agendar citas los domingos.");
                input.value = "";
            }
        });
    }

    const fechaCrear = document.getElementById("fechaCita");
    const pickerCrear = aplicarFlatpickr(fechaCrear);

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

    const pickerEditar = aplicarFlatpickr(fechaEditar);

    document.querySelectorAll(".btn-modificar-cita").forEach(btn => {
        btn.addEventListener("click", () => {
            citaEditId = btn.dataset.citaId;
            if (pickerEditar) {
                pickerEditar.setDate(btn.dataset.fecha, true);
            } else if (fechaEditar) {
                fechaEditar.value = btn.dataset.fecha;
            }
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

            let confirmar = true;

            if (window.Swal) {
                const result = await Swal.fire({
                    title: "Cancelar cita",
                    text: "¿Seguro que deseas cancelar esta cita?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Sí, cancelar",
                    cancelButtonText: "No, volver",
                    reverseButtons: true,
                    focusCancel: true,
                    confirmButtonColor: "#dc2626",
                    cancelButtonColor: "#94a3b8",
                });
                confirmar = result.isConfirmed;
            } else {
                confirmar = confirm("¿Seguro que quieres cancelar esta cita?");
            }

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
