<?php

namespace App\Http\Controllers;

use App\Models\Informe;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class InformeHtmlController extends Controller
{
    public function __invoke(Informe $informe): RedirectResponse
    {
        Gate::authorize('ver', $informe);

        return redirect()->route('turista.informes.detalle', $informe);
    }
}
