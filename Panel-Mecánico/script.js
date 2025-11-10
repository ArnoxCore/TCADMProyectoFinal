document.addEventListener("DOMContentLoaded", () => {
  const fecha = document.getElementById("fecha");
  const limpiarBtn = document.querySelector(".button");

  limpiarBtn.addEventListener("click", () => {
    fecha.value = "";
  });
});