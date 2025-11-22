// Activa el link de navegación según data-page en <body>
document.addEventListener("DOMContentLoaded", () => {
  const current = document.body.dataset.page;
  document.querySelectorAll(".nav a[data-nav]").forEach(a => {
    if (a.dataset.nav === current) a.classList.add("active");
  });

  // Modales (abrir/cerrar)
  document.querySelectorAll("[data-open]").forEach(btn => {
    btn.addEventListener("click", () => {
      const targetSel = btn.getAttribute("data-open");
      const el = document.querySelector(targetSel);
      if (el) el.style.display = "grid";
    });
  });
  document.querySelectorAll("[data-close]").forEach(btn => {
    btn.addEventListener("click", () => {
      const modal = btn.closest(".modal-backdrop");
      if (modal) modal.style.display = "none";
    });
  });
  document.querySelectorAll(".modal-backdrop").forEach(backdrop => {
    backdrop.addEventListener("click", e => {
      if (e.target === backdrop) backdrop.style.display = "none";
    });
  });

  // Charts: solo inicializa si existe el canvas
  if (document.getElementById("servicesBarChart") && window.Chart) {
    const ctx = document.getElementById("servicesBarChart").getContext("2d");
    new Chart(ctx, {
      type: "bar",
      data: {
        labels: [
          "Cambio de aceite",
          "Alineación y balanceo",
          "Revisión de frenos",
          "Mantenimiento general",
          "Cambio de neumáticos",
          "Diagnóstico electrónico",
          "Cambio de batería"
        ],
        datasets: [{
          label: "Número de citas",
          data: [12, 9, 7, 10, 6, 5, 4],
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: true },
          tooltip: { enabled: true }
        },
        scales: {
          y: { beginAtZero: true }
        }
      }
    });
  }

  if (document.getElementById("statusPieChart") && window.Chart) {
    const ctx = document.getElementById("statusPieChart").getContext("2d");
    new Chart(ctx, {
      type: "doughnut",
      data: {
        labels: ["Pendiente", "Confirmada", "Completada", "Cancelada"],
        datasets: [{
          data: [40, 20, 40, 0],
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { position: "bottom" }
        },
        cutout: "55%"
      }
    });
  }
});