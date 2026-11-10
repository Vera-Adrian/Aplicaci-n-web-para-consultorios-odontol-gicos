(() => {
    const formulario = document.querySelector('#formAtencion');
    if (!formulario) return;

    const procedimiento = formulario.querySelector('#procedimiento_id');
    const pieza = formulario.querySelector('#pieza_dental');
    const avisoPieza = formulario.querySelector('#piezaRequerida');
    const costo = formulario.querySelector('#costo');

    const actualizar = () => {
        const opcion = procedimiento.selectedOptions[0];
        const requierePieza = opcion?.dataset.requierePieza === '1';

        pieza.required = requierePieza;
        avisoPieza.classList.toggle('d-none', !requierePieza);

        // Solo autocompleta si el costo está vacío o lo puso el sistema
        if (opcion?.dataset.precio && (costo.value === '' || costo.dataset.auto === '1')) {
            costo.value = opcion.dataset.precio;
            costo.dataset.auto = '1';
        }
    };

    costo.addEventListener('input', () => {
        costo.dataset.auto = '0';
    });

    procedimiento.addEventListener('change', actualizar);
    actualizar();
})();