<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informe turístico {{ $informe->getKey() }}</title>
    <style>
        body { color: #172033; font-family: Arial, sans-serif; line-height: 1.45; margin: 32px; }
        h1, h2 { color: #123a63; }
        h1 { margin-bottom: 8px; }
        h2 { font-size: 18px; margin-top: 32px; }
        .metadata { background: #f3f6fa; border-radius: 8px; padding: 16px; }
        .metadata p { margin: 4px 0; }
        table { border-collapse: collapse; margin-top: 12px; width: 100%; }
        th, td { border: 1px solid #d7dee8; padding: 9px; text-align: left; }
        th { background: #e8eff7; color: #123a63; }
        .empty { color: #667085; font-style: italic; }
        .nota { color: #475467; margin: 4px 0 0; }
        .aviso { color: #93370d; }
        @if ($pdf ?? false)
            @page { margin: 32px 36px 44px; }
            body { font-family: "DejaVu Sans", sans-serif; font-size: 10px; margin: 0; }
            h1 { font-size: 22px; }
            h2 { font-size: 15px; margin-top: 22px; page-break-after: avoid; }
            .metadata, tr { page-break-inside: avoid; }
            thead { display: table-header-group; }
            th, td { padding: 7px; overflow-wrap: break-word; }
            table { table-layout: fixed; }
            footer { position: fixed; bottom: -25px; font-size: 9px; color: #667085; }
            .pagina:after { content: counter(page); }
        @endif
    </style>
</head>
<body>
    @if ($pdf ?? false)
        <footer>TurismoApp · Informe #{{ $informe->getKey() }} · Página <span class="pagina"></span></footer>
    @endif
    <h1>Informe de planificación turística</h1>

    <div class="metadata">
        <p><strong>Informe:</strong> #{{ $informe->getKey() }}</p>
        <p><strong>Estación:</strong> {{ $estacion }}</p>
        <p><strong>Fecha:</strong> {{ $fecha }}</p>
        <p><strong>Preferencias:</strong> {{ $categorias ?: 'Sin preferencias registradas' }}</p>
    </div>

    <h2>Zonas turísticas</h2>
    <p class="nota">Ruta peatonal de ida y vuelta desde {{ $estacion }}: el recorrido total suma la ida y el regreso.</p>
    <table>
        <thead>
            <tr>
                <th>Zona</th>
                <th>Categoría</th>
                <th>Recorrido total</th>
                <th>Tiempo estimado</th>
                <th>Dificultad</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($zonas as $zona)
                <tr>
                    <td>{{ $zona['nombre'] ?? 'Sin nombre' }}</td>
                    <td>{{ $zona['categoria'] ?? 'Sin categoría' }}</td>
                    <td>{{ number_format((float) ($zona['distancia_total'] ?? 0) / 1000, 1) }} km</td>
                    <td>{{ $zona['tiempo_minutos'] ?? 0 }} min</td>
                    <td>{{ \Illuminate\Support\Str::ucfirst($zona['dificultad'] ?? 'No indicada') }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="empty">No se encontraron zonas para esta planificación.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Trenes de llegada</h2>
    @if ($actualizacion['trenes'] ?? null)
        <p class="nota">Datos de PeruRail actualizados al {{ $actualizacion['trenes'] }}.</p>
    @endif
    <table>
        <thead>
            <tr>
                <th>Origen</th>
                <th>Servicio</th>
                <th>Salida</th>
                <th>Llegada</th>
                <th>Tiempo de viaje</th>
                <th>Precio</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($trenes as $tren)
                <tr>
                    <td>{{ $tren['origen'] ?? 'No indicado' }}</td>
                    <td>{{ $tren['servicio'] ?? 'No indicado' }}</td>
                    <td>{{ $tren['salida'] ?? '—' }}</td>
                    <td>{{ $tren['llegada'] ?? '—' }}</td>
                    <td>{{ $tren['duracion'] ?? '—' }}</td>
                    <td>S/ {{ number_format((float) ($tren['precio'] ?? 0), 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="empty">No hay trenes de llegada registrados.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Pronóstico del clima</h2>
    @unless ($climaVigente ?? true)
        <p class="nota aviso">El SENAMHI no entregó un pronóstico vigente; se muestra el último pronóstico disponible.</p>
    @endunless
    @if ($actualizacion['clima'] ?? null)
        <p class="nota">Pronóstico del SENAMHI actualizado al {{ $actualizacion['clima'] }}.</p>
    @endif
    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Condición</th>
                <th>Mínima</th>
                <th>Máxima</th>
                <th>Lluvia</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($clima as $dia)
                <tr>
                    <td>{{ $dia['fecha'] ?? 'No indicada' }}</td>
                    <td>{{ $dia['descripcion'] ?? 'No indicada' }}</td>
                    <td>{{ $dia['minima'] ?? '—' }} °C</td>
                    <td>{{ $dia['maxima'] ?? '—' }} °C</td>
                    <td>{{ $dia['lluvia'] ?? 0 }}%</td>
                </tr>
            @empty
                <tr><td colspan="5" class="empty">No hay pronóstico disponible.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
