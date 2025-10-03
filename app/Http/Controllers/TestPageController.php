<?php

namespace App\Http\Controllers;

use App\Services\IntuneSyncService;
use App\Support\IntuneSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
            'intuneSettings' => IntuneSettings::get(),
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

    /**
     * Show the configuration page for Intune synchronisation settings.
     */
    public function settings(): View
    {
        return view('custom.test-settings', [
            'settings' => IntuneSettings::get(),
            'assetColumns' => IntuneSettings::assetColumnLabels(),
            'intuneColumns' => IntuneSettings::intuneColumnOptions(),
        ]);
    }

    /**
     * Persist the Intune synchronisation settings provided by the user.
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        $assetColumns = IntuneSettings::assetColumnLabels();
        $intuneColumns = IntuneSettings::intuneColumnOptions();

        $validated = $request->validate([
            'tenant_domain' => ['required', 'string', 'max:255'],
            'application_id' => ['nullable', 'string', 'max:255'],
            'client_secret' => ['nullable', 'string', 'max:255'],
            'device_filter' => ['nullable', 'string', 'max:255'],
            'asset_column_map' => ['required', 'array'],
            'asset_column_map.*' => ['nullable', 'string', Rule::in(array_keys($intuneColumns))],
        ]);

        $validated['asset_column_map'] = collect($validated['asset_column_map'] ?? [])
            ->only(array_keys($assetColumns))
            ->map(fn ($value) => $value ?: null)
            ->toArray();

        IntuneSettings::update($validated);

        return redirect()
            ->route('custom.test.settings')
            ->with('status', __('Impostazioni Intune aggiornate con successo.'));
    }
}
