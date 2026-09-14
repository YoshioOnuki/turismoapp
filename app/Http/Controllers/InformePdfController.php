<?php

namespace App\Http\Controllers;

use App\Models\Informe;
use App\Services\InformeExportacionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class InformePdfController extends Controller
{
    public function __invoke(Informe $informe, InformeExportacionService $exportacion): Response
    {
        Gate::authorize('exportar', $informe);

        return Pdf::loadView('informes.html', [...$exportacion->preparar($informe), 'pdf' => true])
            ->setPaper('a4')
            ->setOption([
                'isRemoteEnabled' => false,
                'isPhpEnabled' => false,
                'isJavascriptEnabled' => false,
            ])
            ->stream(sprintf('informe-%d.pdf', $informe->getKey()));
    }
}
