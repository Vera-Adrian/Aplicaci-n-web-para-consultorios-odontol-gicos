// Pide confirmación en botones o enlaces que tengan data-confirmar="mensaje"
document.addEventListener('click', (evento) => {
    const elemento = evento.target.closest('[data-confirmar]');
    if (elemento && !confirm(elemento.dataset.confirmar)) {
        evento.preventDefault();
    }
});

// Evita que un formulario se envíe dos veces por doble clic
document.addEventListener('submit', (evento) => {
    const boton = evento.target.querySelector('button[type="submit"]');
    if (boton) {
        boton.disabled = true;
    }
});

// Si el usuario vuelve con el botón "Atrás", reactiva los botones
window.addEventListener('pageshow', () => {
    document.querySelectorAll('button[type="submit"]').forEach((boton) => {
        boton.disabled = false;
    });
});