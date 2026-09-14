import { mapaPlanificacion } from './mapa-planificacion';

const registrarMapa = () => {
    window.Alpine.data('mapaPlanificacion', mapaPlanificacion);
};

if (window.Alpine) {
    registrarMapa();
} else {
    document.addEventListener('alpine:init', registrarMapa, { once: true });
}

let navegacionIniciada = false;

document.addEventListener('livewire:navigate', () => {
    navegacionIniciada = true;
});

document.addEventListener('livewire:navigated', () => {
    if (!navegacionIniciada) {
        return;
    }

    navegacionIniciada = false;

    if ('startViewTransition' in document || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }

    const contenido = document.querySelector('[wire\\:transition\\.navigate]');

    if (!(contenido instanceof HTMLElement)) {
        return;
    }

    contenido.classList.add('navegacion-contenido-entrada');
    contenido.addEventListener('animationend', () => contenido.classList.remove('navegacion-contenido-entrada'), {
        once: true,
    });
});
