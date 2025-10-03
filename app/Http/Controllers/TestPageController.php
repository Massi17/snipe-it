<?php

namespace App\Http\Controllers;

use App\Services\IntuneSyncService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TestPageController extends Controller
{
    /**
     * Display the custom test page.
     */
    public function index(Request $request): View
    {
        return view('custom.test', [
            'syncResults' => $request->session()->get('intune_sync_results'),
            'selectionSummary' => $request->session()->get('intune_sync_selection'),
        ]);
    }

    /**
     * Execute a preview synchronisation with Intune and store the results in the session.
     */
    public function sync(Request $request, IntuneSyncService $service): RedirectResponse
    {
        $results = $service->preview();

        $request->session()->put('intune_sync_results', $results);
        $request->session()->forget('intune_sync_selection');

        return redirect()->route('custom.test')->with('status', __('Sincronizzazione Intune completata.')); // @codeCoverageIgnore
    }

    /**
     * Persist the user decision about which devices should be created or updated.
     */
    public function applySelection(Request $request): RedirectResponse
    {
        $results = $request->session()->get('intune_sync_results');

        if (! $results) {
            return redirect()->route('custom.test')->withErrors([
                'intune' => __('Esegui prima una sincronizzazione con Intune per ottenere dei risultati.'),
            ]);
        }

        $newDevices = collect($results['new_devices'] ?? []);
        $updatedDevices = collect($results['updated_devices'] ?? []);

        $selectedAdds = $newDevices->whereIn('identifier', $request->input('add', []))->values()->all();
        $selectedUpdates = $updatedDevices->whereIn('identifier', $request->input('update', []))->values()->all();

        $request->session()->put('intune_sync_selection', [
            'adds' => $selectedAdds,
            'updates' => $selectedUpdates,
        ]);

        return redirect()->route('custom.test')->with('status', __('Selezione dispositivi salvata.'));
    }
}
