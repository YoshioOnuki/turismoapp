export function mapaPlanificacion(estacion, zonas) {
    let mapa;
    let limites;
    let destruido = false;
    let observador;
    const recorridos = new Map();

    function contenidoPopup(titulo, detalle) {
        const contenido = document.createElement('div');
        const encabezado = document.createElement('strong');
        encabezado.textContent = titulo;
        contenido.append(encabezado, document.createElement('br'), document.createTextNode(detalle));

        return contenido;
    }

    return {
        cargando: true,
        aviso: '',
        zonaSeleccionada: '',

        async init() {
            try {
                const L = await import('leaflet');
                await import('leaflet/dist/leaflet.css');

                if (destruido) {
                    return;
                }

                const origen = [estacion.latitud, estacion.longitud];
                mapa = L.map(this.$refs.mapa, { scrollWheelZoom: false, zoomControl: false });
                L.control.zoom({ zoomInTitle: 'Acercar', zoomOutTitle: 'Alejar' }).addTo(mapa);
                mapa.on('popupopen', ({ popup }) => {
                    const cerrar = popup.getElement().querySelector('.leaflet-popup-close-button');
                    cerrar?.setAttribute('aria-label', 'Cerrar detalle');
                    cerrar?.setAttribute('title', 'Cerrar detalle');
                });
                limites = L.latLngBounds([origen]);

                L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                })
                    .on('tileerror', () => {
                        this.aviso =
                            'No se pudo cargar parte del fondo del mapa. Puedes consultar las ubicaciones y los datos de la tabla.';
                    })
                    .addTo(mapa);

                L.marker(origen, {
                    title: `Estación ${estacion.nombre}`,
                    icon: L.divIcon({
                        className: 'marcador-mapa marcador-mapa-estacion',
                        html: 'E',
                        iconSize: [32, 32],
                        iconAnchor: [16, 16],
                    }),
                })
                    .addTo(mapa)
                    .bindPopup(contenidoPopup(estacion.nombre, 'Salida y regreso a la estación'));

                zonas.forEach((zona, indice) => {
                    const destino = [zona.latitud, zona.longitud];
                    const ruta = L.polyline([origen, destino], {
                        color: '#2563eb',
                        weight: 3,
                        opacity: 0.65,
                        dashArray: '6 8',
                    }).addTo(mapa);
                    const marcador = L.marker(destino, {
                        title: `${indice + 1}. ${zona.nombre}`,
                        icon: L.divIcon({
                            className: 'marcador-mapa marcador-mapa-zona',
                            html: String(indice + 1),
                            iconSize: [32, 32],
                            iconAnchor: [16, 16],
                        }),
                    })
                        .addTo(mapa)
                        .bindPopup(
                            contenidoPopup(
                                zona.nombre,
                                `Ida y vuelta: ${zona.distancia_total} m · ${zona.tiempo_minutos} min aprox. · Dificultad ${zona.dificultad}`,
                            ),
                        );

                    marcador.on('click', () => {
                        this.zonaSeleccionada = String(zona.codigo);
                        this.seleccionarZona();
                    });
                    recorridos.set(String(zona.codigo), { ruta, marcador });
                    limites.extend(destino);
                });

                mapa.fitBounds(limites, { padding: [40, 40], maxZoom: 16 });
                observador = new ResizeObserver(() => mapa?.invalidateSize());
                observador.observe(this.$refs.mapa);
                this.seleccionarZona();
            } catch (error) {
                this.aviso = 'No pudimos mostrar el mapa. Las distancias y los tiempos siguen disponibles en la tabla.';
                console.error('No fue posible mostrar el mapa de planificación.', error);
            } finally {
                this.cargando = false;
            }
        },

        seleccionarZona() {
            if (!mapa) {
                return;
            }

            recorridos.forEach(({ ruta }, codigo) => {
                ruta.setStyle({ opacity: !this.zonaSeleccionada || codigo === this.zonaSeleccionada ? 0.85 : 0.2 });
            });

            const seleccionado = recorridos.get(this.zonaSeleccionada);

            if (seleccionado) {
                mapa.fitBounds(seleccionado.ruta.getBounds(), { padding: [60, 60], maxZoom: 16 });
                seleccionado.marcador.openPopup();
            } else {
                mapa.closePopup();
                mapa.fitBounds(limites, { padding: [40, 40], maxZoom: 16 });
            }
        },

        destroy() {
            destruido = true;
            observador?.disconnect();
            mapa?.remove();
            mapa = null;
            recorridos.clear();
        },
    };
}
