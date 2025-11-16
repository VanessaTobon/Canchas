document.addEventListener("DOMContentLoaded", () => {
    console.log("🟢 Script cargado correctamente.");

    const formularios = document.querySelectorAll("form");

    formularios.forEach(form => {
        // Prevenir validación automática del navegador
        form.setAttribute("novalidate", true);

        form.addEventListener("submit", function (e) {
            const inputs = form.querySelectorAll("input[required], select[required], textarea[required]");
            let valido = true;

            // Eliminar errores anteriores antes de validar
            form.querySelectorAll(".mensaje-error").forEach(error => error.remove());

            inputs.forEach(input => {
                const valor = input.value.trim();
                const padre = input.parentNode;

                // Eliminar clase de error si ya no aplica
                input.classList.remove("input-error");

                if (valor === "") {
                    valido = false;
                    input.classList.add("input-error");

                    // Evitar duplicar mensaje
                    if (!input.nextElementSibling || !input.nextElementSibling.classList.contains("mensaje-error")) {
                        const error = document.createElement("div");
                        error.className = "mensaje-error";
                        error.innerText = "Este campo es obligatorio";
                        padre.insertBefore(error, input.nextSibling);
                    }
                }
            });

            if (!valido) {
                e.preventDefault();
                alert("⚠ Por favor completa todos los campos obligatorios.");
            }
        });
    });
});
