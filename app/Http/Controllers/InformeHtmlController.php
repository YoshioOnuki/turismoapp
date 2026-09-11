<?php

namespace App\Http\Controllers;

use App\Models\Informe;
use App\Services\InformeExportacionService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class InformeHtmlController extends Controller
{
    public function __invoke(Informe $informe, InformeExportacionService $exportacion): Response
    {
        Gate::authorize('exportar', $informe);

        return response(view('informes.html', $exportacion->preparar($informe))->render())
            ->header('Content-Type', 'text/html; charset=UTF-8')
            ->header('Content-Disposition', sprintf('attachment; filename="informe-%d.html"', $informe->getKey()));
    }
}
