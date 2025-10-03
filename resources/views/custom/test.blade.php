@extends('layouts/default')

@section('title')
Test
@parent
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Test</h3>
                <div class="box-tools pull-right">
                    <a href="{{ route('custom.test.settings') }}" class="btn btn-default btn-sm">
                        <i class="fa fa-cog"></i> Configura sincronizzazione Intune
                    </a>
                </div>
            </div>
            <div class="box-body">
                @if (session('status'))
                    <div class="alert alert-success alert-dismissable">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->has('intune'))
                    <div class="alert alert-danger alert-dismissable">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        {{ $errors->first('intune') }}
                    </div>
                @endif

                @if (! empty($intuneSettings['tenant_domain']) || ! empty($intuneSettings['device_filter']))
                    <div class="alert alert-info" style="margin-bottom: 20px;">
                        <strong>Configurazione corrente:</strong>
                        <span>Dominio {{ $intuneSettings['tenant_domain'] ?: 'non impostato' }}</span>
                        @if (! empty($intuneSettings['device_filter']))
                            <span>· Filtro dispositivi: {{ $intuneSettings['device_filter'] }}</span>
                        @endif
                    </div>
                @endif

                <p class="text-muted">
                    In questa pagina di test puoi avviare una sincronizzazione manuale con Microsoft Intune.
                    Dopo l'analisi ti verranno proposti i dispositivi da aggiungere e quelli da aggiornare,
                    lasciandoti la libertà di scegliere come procedere.
                </p>

                <form method="POST" action="{{ route('custom.test.sync') }}" class="form-inline">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-sync"></i> Avvia sincronizzazione con Intune
                    </button>
                </form>

                @if (! empty($syncResults))
                    <hr>
                    <h4>Risultati dell'ultima sincronizzazione</h4>
                    <p class="text-muted">
                        Generato il {{ $syncResults['generated_at'] ?? '' }}
                        ({{ $syncResults['source_count'] ?? 0 }} dispositivi analizzati).
                    </p>

                    <form method="POST" action="{{ route('custom.test.apply') }}">
                        @csrf

                        <h5>Nuovi dispositivi proposti</h5>
                        @if (! empty($syncResults['new_devices']))
                            <div class="list-group">
                                @foreach ($syncResults['new_devices'] as $device)
                                    <label class="list-group-item">
                                        <input type="checkbox" name="add[]" value="{{ $device['identifier'] }}" style="margin-right: 8px;" checked>
                                        <strong>{{ $device['display_name'] }}</strong>
                                        <span class="text-muted">
                                            @if (! empty($device['serial']))
                                                · Seriale: {{ $device['serial'] }}
                                            @endif
                                            @if (! empty($device['asset_tag']))
                                                · Asset tag: {{ $device['asset_tag'] }}
                                            @endif
                                            @if (! empty($device['model']))
                                                · Modello: {{ $device['model'] }}
                                            @endif
                                            @if (! empty($device['user']))
                                                · Utente: {{ $device['user'] }}
                                            @endif
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted">Nessun nuovo dispositivo da aggiungere.</p>
                        @endif

                        <h5 style="margin-top: 20px;">Dispositivi esistenti con differenze</h5>
                        @if (! empty($syncResults['updated_devices']))
                            <div class="list-group">
                                @foreach ($syncResults['updated_devices'] as $device)
                                    <div class="list-group-item">
                                        <label>
                                            <input type="checkbox" name="update[]" value="{{ $device['identifier'] }}" style="margin-right: 8px;" checked>
                                            <strong>{{ $device['display_name'] }}</strong>
                                        </label>
                                        <div class="small text-muted" style="margin-top: 8px;">
                                            @foreach ($device['differences'] as $field => $diff)
                                                <div>
                                                    <strong>{{ $field === 'asset_tag' ? 'Asset tag' : 'Nome' }}:</strong>
                                                    <span>attuale <code>{{ $diff['current'] ?? '—' }}</code></span>
                                                    <span>→ Intune <code>{{ $diff['incoming'] ?? '—' }}</code></span>
                                                </div>
                                            @endforeach
                                        </div>
                                        @if (! empty($device['existing']['id']))
                                            <p class="small text-muted" style="margin-top: 6px;">
                                                Asset Snipe-IT collegato #{{ $device['existing']['id'] }}
                                            </p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted">Nessun dispositivo esistente richiede modifiche.</p>
                        @endif

                        <button type="submit" class="btn btn-success" style="margin-top: 20px;">
                            <i class="fa fa-save"></i> Salva la selezione
                        </button>
                    </form>
                @endif

                @if (! empty($selectionSummary))
                    <hr>
                    <h4>Ultima scelta salvata</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Da aggiungere</h5>
                            @if (! empty($selectionSummary['adds']))
                                <ul class="list-unstyled">
                                    @foreach ($selectionSummary['adds'] as $device)
                                        <li>
                                            <strong>{{ $device['display_name'] }}</strong>
                                            @if (! empty($device['serial']))
                                                <span class="text-muted">(Seriale: {{ $device['serial'] }})</span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-muted">Nessun nuovo dispositivo selezionato.</p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <h5>Da aggiornare</h5>
                            @if (! empty($selectionSummary['updates']))
                                <ul class="list-unstyled">
                                    @foreach ($selectionSummary['updates'] as $device)
                                        <li>
                                            <strong>{{ $device['display_name'] }}</strong>
                                            @if (! empty($device['differences']))
                                                <ul class="list-unstyled small text-muted">
                                                    @foreach ($device['differences'] as $field => $diff)
                                                        <li>{{ $field === 'asset_tag' ? 'Asset tag' : 'Nome' }}: {{ $diff['current'] ?? '—' }} → {{ $diff['incoming'] ?? '—' }}</li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-muted">Nessun aggiornamento selezionato.</p>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
