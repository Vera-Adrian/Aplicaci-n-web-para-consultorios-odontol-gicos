(() => {
    const formulario = document.querySelector('[data-buscador]');
    if (!formulario) return;

    const campo = formulario.querySelector('input[name="buscar"]');
    const lista = formulario.querySelector('.buscador-resultados');
    let temporizador = null;
    let controlador = null;

    const cerrar = () => {
        lista.classList.add('d-none');
        lista.innerHTML = '';
    };

    const mostrar = (resultados) => {
        lista.innerHTML = '';

        if (!resultados.length) {
            const aviso = document.createElement('div');
            aviso.className = 'list-group-item small text-muted';
            aviso.textContent = 'Sin coincidencias. Pulsa Enter para ver la lista completa.';
            lista.append(aviso);
        }

        resultados.forEach((p) => {
            const enlace = document.createElement('a');
            enlace.className = 'list-group-item list-group-item-action';
            enlace.href = formulario.dataset.historia + p.id;
            enlace.textContent = `${p.nombre}, ${p.cedula}, ${p.edad} años`;
            lista.append(enlace);
        });

        lista.classList.remove('d-none');
    };

    campo.addEventListener('input', () => {
        clearTimeout(temporizador);
        const consulta = campo.value.trim();

        if (consulta.length < 2) {
            cerrar();
            return;
        }

        // Espera 250 ms después de la última tecla para no saturar el servidor
        temporizador = setTimeout(async () => {
            controlador?.abort();
            controlador = new AbortController();

            try {
                const respuesta = await fetch(
                    formulario.dataset.url + '&q=' + encodeURIComponent(consulta),
                    { signal: controlador.signal }
                );

                if (respuesta.status === 401) {
                    window.location.reload(); // La sesión expiró: recargar lleva al login
                    return;
                }

                const datos = await respuesta.json();
                mostrar(datos.resultados);
            } catch (error) {
                if (error.name !== 'AbortError') cerrar();
            }
        }, 250);
    });

    campo.addEventListener('keydown', (evento) => {
        if (evento.key === 'Escape') cerrar();
    });

    document.addEventListener('click', (evento) => {
        if (!formulario.contains(evento.target)) cerrar();
    });
})();