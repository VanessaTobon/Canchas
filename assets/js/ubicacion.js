document.addEventListener("DOMContentLoaded", function () {
    const selectPais = document.getElementById("selectPais");
    const selectDepartamento = document.getElementById("selectDepartamento");
    const selectMunicipio = document.getElementById("selectMunicipio");

    function limpiarSelect(select, texto = "Seleccione una opción") {
        select.innerHTML = `<option value="">${texto}</option>`;
    }

    fetch("../assets/data/paises.json")
        .then(response => response.json())
        .then(data => {
            data.forEach(pais => {
                const option = document.createElement("option");
                option.value = pais.id;
                option.textContent = pais.nombre;
                selectPais.appendChild(option);
            });
        })
        .catch(error => {
            console.error("❌ Error al cargar países:", error);
        });

    selectPais.addEventListener("change", function () {
        limpiarSelect(selectDepartamento, "Seleccione un departamento");
        limpiarSelect(selectMunicipio, "Seleccione un municipio");

        if (this.value === "1") {
            fetch("../assets/data/Colombia.json")
                .then(response => response.json())
                .then(data => {
                    data.forEach(depto => {
                        const option = document.createElement("option");
                        option.value = depto.id;
                        option.textContent = depto.departamento;
                        selectDepartamento.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error("❌ Error al cargar departamentos:", error);
                });
        }
    });

    selectDepartamento.addEventListener("change", function () {
        const idDepto = this.value;
        limpiarSelect(selectMunicipio, "Seleccione un municipio");

        if (selectPais.value === "1") {
            fetch("../assets/data/Colombia.json")
                .then(response => response.json())
                .then(data => {
                    const deptoSeleccionado = data.find(depto => depto.id == idDepto);
                    if (deptoSeleccionado && Array.isArray(deptoSeleccionado.municipios)) {
                        deptoSeleccionado.municipios.forEach((municipio, index) => {
                            const option = document.createElement("option");
                            option.value = index + 1;
                            option.textContent = municipio;
                            selectMunicipio.appendChild(option);
                        });
                    }
                })
                .catch(error => {
                    console.error("❌ Error al cargar municipios:", error);
                });
        }
    });
});
