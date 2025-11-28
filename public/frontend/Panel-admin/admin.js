// Activa el link de navegación según data-page en <body>
document.addEventListener("DOMContentLoaded", () => {
  const current = document.body.dataset.page;
  document.querySelectorAll(".nav a[data-nav]").forEach(a => {
    if (a.dataset.nav === current) a.classList.add("active");
  });

  const ensureToast = () => {
    if (!window.Notyf) {
      console.warn("Notyf no está disponible en el panel de administración.");
      return null;
    }

    if (!window.toast) {
      window.toast = new Notyf({
        duration: 4000,
        dismissible: true,
        position: { x: "right", y: "top" }
      });
    }

    return window.toast;
  };

  const toastNodes = document.querySelectorAll("[data-toast]");
  if (toastNodes.length) {
    const toastInstance = ensureToast();
    if (toastInstance) {
      toastNodes.forEach(node => {
        const type = (node.dataset.toast || "success").toLowerCase();
        const message = (node.dataset.message || node.textContent || "").trim();
        if (!message) {
          node.remove();
          return;
        }

        if (type === "error") {
          toastInstance.error(message);
        } else if (type === "warning") {
          toastInstance.open({
            message,
            background: "#f59e0b",
            duration: 4000,
            dismissible: true
          });
        } else {
          toastInstance.success(message);
        }

        node.remove();
      });
    }
  }

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
  const servicesCanvas = document.getElementById("servicesBarChart");
  if (servicesCanvas && window.Chart) {
    const ctx = servicesCanvas.getContext("2d");
    let dataset = null;
    if (servicesCanvas.dataset.chart) {
      try {
        dataset = JSON.parse(servicesCanvas.dataset.chart);
      } catch (e) {
        console.warn("No se pudo parsear data-chart para servicesBarChart", e);
      }
    }

    const defaultLabels = [
      "Cambio de aceite",
      "Alineación y balanceo",
      "Revisión de frenos",
      "Mantenimiento general",
      "Cambio de neumáticos",
      "Diagnóstico electrónico",
      "Cambio de batería"
    ];
    const defaultTotals = [12, 9, 7, 10, 6, 5, 4];
    const labels = dataset?.labels ?? defaultLabels;
    const totals = dataset?.totals ?? defaultTotals;

    new Chart(ctx, {
      type: "bar",
      data: {
        labels,
        datasets: [{
          label: "Número de citas",
          data: totals,
          backgroundColor: "#93c5fd",
          borderColor: "#3b82f6",
          borderWidth: 1
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
          y: { beginAtZero: true, ticks: { precision: 0 } }
        }
      }
    });
  }

  const statusCanvas = document.getElementById("statusPieChart");
  if (statusCanvas && window.Chart) {
    const ctx = statusCanvas.getContext("2d");
    let dataset = null;
    if (statusCanvas.dataset.chart) {
      try {
        dataset = JSON.parse(statusCanvas.dataset.chart);
      } catch (e) {
        console.warn("No se pudo parsear data-chart para statusPieChart", e);
      }
    }

    const defaultLabels = ["Pendiente", "Confirmada", "En proceso", "Completada", "Cancelada"];
    const defaultTotals = [40, 20, 15, 20, 5];
    const labels = dataset?.labels ?? defaultLabels;
    const totals = dataset?.totals ?? defaultTotals;
    const colors = ["#3b82f6", "#ec4899", "#f97316", "#10b981", "#facc15"];

    new Chart(ctx, {
      type: "doughnut",
      data: {
        labels,
        datasets: [{
          data: totals,
          backgroundColor: colors,
          borderWidth: 0,
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { position: "bottom" },
          tooltip: { enabled: true }
        },
        cutout: "55%"
      }
    });
  }
});