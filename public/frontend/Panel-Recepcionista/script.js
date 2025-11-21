document.addEventListener("DOMContentLoaded", () => {
  const searchCliente = document.getElementById("searchCliente");
  const searchFecha = document.getElementById("searchFecha");
  const searchMecanico = document.getElementById("searchMecanico");
  const searchEstatus = document.getElementById("searchEstatus");
  const btnClear = document.getElementById("btnClear");
  const tableBody = document.getElementById("tableBody");
  const rows = Array.from(tableBody.querySelectorAll("tr"));

function filtrar() {
    const clienteFiltro = searchCliente.value.toLowerCase().trim();
    const fechaFiltro = searchFecha.value;
    const mecanicoFiltro = searchMecanico.value;
    const estatusFiltro = searchEstatus.value;

    let visibles = 0;

    rows.forEach(row => {
        const [colFecha, , colCliente, , , colMecanico, colEstatus] = row.children;

        const clienteTexto = colCliente.textContent.toLowerCase();
        const fechaTexto = colFecha.textContent.trim();
        const mecanicoTexto = colMecanico.textContent.trim();
        const estatusTexto = colEstatus.textContent.trim();

        let mostrar = true;

        if (clienteFiltro && !clienteTexto.includes(clienteFiltro)) {
            mostrar = false;
        }

        if (fechaFiltro && fechaTexto !== fechaFiltro) {
            mostrar = false;
        }

        if (mecanicoFiltro && mecanicoTexto !== mecanicoFiltro) {
            mostrar = false;
        }

        if (estatusFiltro && estatusTexto !== estatusFiltro) {
            mostrar = false;
        }

        row.style.display = mostrar ? "" : "none";

        if (mostrar) visibles++;
    });

    // 🔹 Actualizar KPI de filtrados
    const kpiFiltrados = document.getElementById('kpiFiltrados');
    if (kpiFiltrados) {
        kpiFiltrados.textContent = visibles;
    }

    // 🔹 Actualizar texto de “Mostrando X citas”
    const labelResultados = document.getElementById('labelResultados');
    if (labelResultados) {
        labelResultados.textContent = `Mostrando ${visibles} citas`;
    }
}

  [searchCliente, searchFecha, searchMecanico, searchEstatus].forEach(input =>
    input.addEventListener("input", filtrar)
  );

  btnClear.addEventListener("click", () => {
    searchCliente.value = "";
    searchFecha.value = "";
    searchMecanico.value = "";
    searchEstatus.value = "";
    filtrar();
  });
});