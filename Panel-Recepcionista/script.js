document.addEventListener("DOMContentLoaded", () => {
  const searchCliente = document.getElementById("searchCliente");
  const searchFecha = document.getElementById("searchFecha");
  const searchMecanico = document.getElementById("searchMecanico");
  const searchEstatus = document.getElementById("searchEstatus");
  const btnClear = document.getElementById("btnClear");
  const tableBody = document.getElementById("tableBody");
  const rows = Array.from(tableBody.querySelectorAll("tr"));

  function filtrar() {
    const cliente = searchCliente.value.toLowerCase();
    const fecha = searchFecha.value;
    const mecanico = searchMecanico.value;
    const estatus = searchEstatus.value;

    rows.forEach(row => {
      const [colFecha, , colCliente, , , colMecanico, colEstatus] = row.children;
      const match =
        (!cliente || colCliente.textContent.toLowerCase().includes(cliente)) &&
        (!fecha || colFecha.textContent === fecha) &&
        (!mecanico || colMecanico.textContent === mecanico) &&
        (!estatus || colEstatus.textContent.includes(estatus));
      row.style.display = match ? "" : "none";
    });
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