import { mapaPlanificacion } from './mapa-planificacion';

const registrarMapa = () => {
    window.Alpine.data('mapaPlanificacion', mapaPlanificacion);
};

if (window.Alpine) {
    registrarMapa();
} else {
    document.addEventListener('alpine:init', registrarMapa, { once: true });
}
