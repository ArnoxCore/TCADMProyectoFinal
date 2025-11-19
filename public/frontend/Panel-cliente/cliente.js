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
                const target = document.getElementById(tab.dataset.tab);
                if (target) target.classList.add("active");
            }
        });
    });

    // ===============================
    //              MODAL
    // ===============================
    const modal      = document.getElementById("modal");
    const openBtn    = document.getElementById("openModal");
    const closeBtn   = document.getElementById("closeModal");
    const cancelBtn  = document.getElementById("cancelModal");

    // ==============================================
    //  VALIDACIÓN: PERFIL INCOMPLETO → BLOQUEAR
    // ==============================================
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
    //       FORMULARIO DE CITA
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
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.content || ""
                },
                body: JSON.stringify({
                    servicio_id: servicio,
                    vehiculo_id: vehiculo,
                    fecha: fecha,
                    hora: hora
                })
            });

            const data = await response.json();

            if (response.ok) {
                toast.success("Cita agendada exitosamente");
                modal?.classList.remove("active");
                window.location.reload();
            } else {
                toast.error(data.message || "No se pudo agendar la cita");
            }
        } catch (error) {
            console.error("Error:", error);
            toast.error("Error al enviar la solicitud");
        }
    });

    // ===============================
    //   MAYÚSCULAS AUTOMÁTICAS
    // ===============================
    const upperFields = ["vin-input", "marca", "modelo", "color"];
    upperFields.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener("input", () => el.value = el.value.toUpperCase());
    });

    // =======================================================
    //   VALIDACIÓN DE VIN
    // =======================================================
    const vinInput = document.getElementById("vin-input");
    const vinStatus = document.getElementById("vin-status");
    const buscarBtn = document.getElementById("buscar-vin-btn");

    const marcaEl = document.getElementById("marca");
    const modeloEl = document.getElementById("modelo");
    const anoEl = document.getElementById("ano");

    const resetVinFields = () => {
        marcaEl.value = "";
        modeloEl.value = "";
        anoEl.value = "";

        marcaEl.readOnly = false;
        modeloEl.readOnly = false;
        anoEl.readOnly = false;

        vinStatus.style.display = "none";
        vinStatus.textContent = "";
    };

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
                vinStatus.textContent = "VIN no disponible en la base de EE.UU. Llena los datos manualmente.";

                resetVinFields();

                toast.info("Este VIN no existe en la base estadounidense. Puedes llenar los datos manualmente.");
                return;
            }

            marcaEl.value = make.toUpperCase();
            modeloEl.value = model.toUpperCase();
            anoEl.value = year;

            marcaEl.readOnly = true;
            modeloEl.readOnly = true;
            anoEl.readOnly = true;

            vinStatus.style.color = "#16a34a";
            vinStatus.textContent = `Vehículo identificado: ${make} ${model} ${year}`;

            toast.success(`Vehículo identificado: ${make} ${model} ${year}`);

        } catch (error) {
            console.error("Error VIN API:", error);
            vinStatus.style.color = "#dc2626";
            vinStatus.textContent = "Error al consultar el VIN.";
            toast.error("No se pudo validar el VIN.");
            resetVinFields();
        }
    }

    if (buscarBtn) buscarBtn.addEventListener("click", buscarVin);

    if (vinInput) {
        vinInput.addEventListener("input", () => {
            if (vinInput.value.trim().length < 17) {
                resetVinFields();
            }
        });
    }

    // ==========================================================
    //   CARGA DINÁMICA DE IMÁGENES DE VEHÍCULOS + SKELETON
    // ==========================================================
    const carPhotos = document.querySelectorAll('.car-photo');

    carPhotos.forEach(async img => {

        const marca = img.dataset.marca?.trim();
        const modelo = img.dataset.modelo?.trim();
        const ano = img.dataset.ano?.trim();

        const skeleton = img.previousElementSibling;

        const fallback = "https://cdn.imagin.studio/getImage?customer=hrjavascript-mastery&make=generic&model=car&angle=23";

        try {
            const url = `https://cdn.imagin.studio/getImage?customer=hrjavascript-mastery&make=${encodeURIComponent(marca)}&model=${encodeURIComponent(modelo)}&modelYear=${encodeURIComponent(ano)}&angle=23`;

            img.src = url;

            img.onload = () => {
                img.style.display = "block";
                skeleton.remove();
            };

            img.onerror = () => {
                img.src = fallback;
                img.onload = () => {
                    img.style.display = "block";
                    skeleton.remove();
                };
            };

        } catch {
            img.src = fallback;
            img.onload = () => {
                img.style.display = "block";
                skeleton.remove();
            };
        }
    });

});
