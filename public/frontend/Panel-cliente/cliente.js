document.addEventListener("DOMContentLoaded", () => {
  // Tabs
  const tabs = document.querySelectorAll(".tab");
  const contents = document.querySelectorAll(".tab-content");
  tabs.forEach(tab => {
    tab.addEventListener("click", () => {
      tabs.forEach(t => t.classList.remove("active"));
      contents.forEach(c => c.classList.remove("active"));
      tab.classList.add("active");
      document.getElementById(tab.dataset.tab).classList.add("active");
    });
  });

  // Modal
  const modal = document.getElementById("modal");
  const openBtn = document.getElementById("openModal");
  const closeBtn = document.getElementById("closeModal");
  const cancelBtn = document.getElementById("cancelModal");

  openBtn.addEventListener("click", () => modal.classList.add("active"));
  [closeBtn, cancelBtn].forEach(btn =>
    btn.addEventListener("click", () => modal.classList.remove("active"))
  );

  // Formulario de Cita
  const agendar = document.getElementById("agendar-btn");
  
  agendar?.addEventListener("click", async (e) => {
    e.preventDefault();
    
    const servicio = document.getElementById("servicio").value;
    const vehiculo = document.querySelector('input[placeholder*="Ej. Toyota"]').value;
    const fecha = document.querySelector('input[type="date"]').value;
    const hora = document.getElementById("hora").value;

    // Validación
    if (!servicio || !vehiculo || !fecha || !hora) {
      alert("Por favor completa todos los campos");
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
          vehiculo: vehiculo,
          fecha: fecha,
          hora: hora
        })
      });

      const data = await response.json();

      if (response.ok) {
        alert("✓ Cita agendada exitosamente");
        modal.classList.remove("active");
        // Recargar la página para ver la nueva cita
        window.location.reload();
      } else {
        alert("Error: " + (data.message || "No se pudo agendar la cita"));
      }
    } catch (error) {
      console.error("Error:", error);
      alert("Error al enviar la solicitud");
    }
  });
});