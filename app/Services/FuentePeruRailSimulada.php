<?php

namespace App\Services;

final class FuentePeruRailSimulada implements FuentePeruRail
{
    /**
     * @return list<EstacionExterna>
     */
    public function obtenerEstaciones(): array
    {
        return [
            new EstacionExterna('WAN', 'Estación Wanchaq (Cusco)', -13.5236, -71.9669),
            new EstacionExterna('POR', 'Estación Poroy', -13.4953, -72.0419),
            new EstacionExterna('OLL', 'Estación Ollantaytambo', -13.2626, -72.2654),
            new EstacionExterna('MAP', 'Estación Machu Picchu (Aguas Calientes)', -13.1547, -72.5252),
            new EstacionExterna('PUN', 'Estación Puno', -15.8395, -70.0233),
        ];
    }

    /**
     * @return list<HorarioExterno>
     */
    public function obtenerHorarios(): array
    {
        return [
            new HorarioExterno('DEMO-31', 'POR', 'MAP', 'Vistadome', '06:40:00', '10:12:00', 420.00),
            new HorarioExterno('DEMO-33', 'POR', 'MAP', 'Expedition', '07:35:00', '11:15:00', 350.00),
            new HorarioExterno('DEMO-81', 'OLL', 'MAP', 'Expedition', '06:10:00', '07:40:00', 250.00),
            new HorarioExterno('DEMO-83', 'OLL', 'MAP', 'Vistadome', '07:05:00', '08:35:00', 310.00),
            new HorarioExterno('DEMO-32', 'MAP', 'POR', 'Vistadome', '15:20:00', '19:04:00', 420.00),
            new HorarioExterno('DEMO-84', 'MAP', 'OLL', 'Expedition', '14:55:00', '16:40:00', 250.00),
            new HorarioExterno('DEMO-86', 'MAP', 'OLL', 'Vistadome', '16:22:00', '18:10:00', 310.00),
            new HorarioExterno('DEMO-T1', 'WAN', 'PUN', 'Titicaca', '07:10:00', '17:40:00', 1100.00),
            new HorarioExterno('DEMO-T2', 'PUN', 'WAN', 'Titicaca', '07:10:00', '17:40:00', 1100.00),
        ];
    }
}
