


document.addEventListener('DOMContentLoaded', () => {
    const selectPais = document.getElementById('selectPais');
    const selectDepartamento = document.getElementById('selectDepartamento');
    const selectMunicipio = document.getElementById('selectMunicipio');
    const formAgregar = document.getElementById('formAgregarCancha');

    function limpiarSelect(select, placeholder = 'Seleccione') {
        select.innerHTML = `<option value="">${placeholder}</option>`;
    }

    function cargarPaises() {
        fetch('../ajax/paises.php')
            .then(res => res.json())
            .then(data => {
                limpiarSelect(selectPais, 'Seleccione un país');
                data.forEach(pais => {
                    const option = document.createElement('option');
                    option.value = pais.id_pais;
                    option.textContent = pais.nombre_pais;
                    selectPais.appendChild(option);
                });
            })
            .catch(error => {
                console.error('❌ Error al cargar países:', error);
            });
    }

    selectPais.addEventListener('change', () => {
        const idPais = selectPais.value;
        limpiarSelect(selectDepartamento, 'Seleccione un departamento');
        limpiarSelect(selectMunicipio, 'Seleccione un municipio');

        if (idPais) {
            fetch('../ajax/estados.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `id_pais=${encodeURIComponent(idPais)}`
            })
                .then(res => res.json())
                .then(data => {
                    data.forEach(estado => {
                        const option = document.createElement('option');
                        option.value = estado.id_estado; // ✅ Usa el ID
                        option.textContent = estado.nombre_estado; // 🟢 Muestra el nombre
                        selectDepartamento.appendChild(option);
                    });
                    
                })
                .catch(error => {
                    console.error('❌ Error al cargar departamentos:', error);
                });
        }
    });

    selectDepartamento.addEventListener('change', () => {
        const idEstado = selectDepartamento.value;
        limpiarSelect(selectMunicipio, 'Seleccione un municipio');

        if (idEstado) {
            fetch('../ajax/municipios.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `id_estado=${encodeURIComponent(idEstado)}`
            })
                .then(res => res.json())
                .then(data => {
                    data.forEach(muni => {
                        const option = document.createElement('option');
                        option.value = muni.id_municipio; // ✅ Usa el ID
                        option.textContent = muni.nombre_municipio;
                        selectMunicipio.appendChild(option);
                    });
                    
                })
                .catch(error => {
                    console.error('❌ Error al cargar municipios:', error);
                });
        }
    });

    // ✅ ÚNICO Submit que valida
    formAgregar.addEventListener('submit', (e) => {
        const pais = selectPais.value;
        const estado = selectDepartamento.value;
        const municipio = selectMunicipio.value;

        console.log('📦 Enviando datos del formulario:');
        console.log('País:', pais);
        console.log('Estado:', estado);
        console.log('Municipio:', municipio);

        if (!pais || !estado || !municipio) {
            alert('❌ Debes seleccionar país, departamento y municipio.');
            e.preventDefault();
        }
    });

    cargarPaises();
});
