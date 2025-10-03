@extends('layouts/default')

@section('title')
    Configurazione Intune
    @parent
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Configurazione sincronizzazione Intune</h3>
                </div>
                <div class="box-body">
                    <p class="text-muted">
                        Definisci i parametri necessari per collegare Snipe-IT al tenant Intune del tuo dominio.
                        Queste impostazioni verranno utilizzate durante la sincronizzazione dalla pagina di test.
                    </p>

                    @if (session('status'))
                        <div class="alert alert-success alert-dismissable">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            {{ session('status') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissable">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <ul class="list-unstyled" style="margin: 0;">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('custom.test.settings.update') }}" class="form-horizontal">
                        @csrf

                        <div class="form-group {{ $errors->has('tenant_domain') ? 'has-error' : '' }}">
                            <label for="tenant_domain" class="col-md-3 control-label">Dominio Intune *</label>
                            <div class="col-md-6">
                                <input type="text" name="tenant_domain" id="tenant_domain" value="{{ old('tenant_domain', $settings['tenant_domain'] ?? '') }}" class="form-control" required>
                                <p class="help-block">Esempio: contoso.onmicrosoft.com</p>
                                @if ($errors->has('tenant_domain'))
                                    <span class="help-block">{{ $errors->first('tenant_domain') }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group {{ $errors->has('application_id') ? 'has-error' : '' }}">
                            <label for="application_id" class="col-md-3 control-label">ID applicazione (client)</label>
                            <div class="col-md-6">
                                <input type="text" name="application_id" id="application_id" value="{{ old('application_id', $settings['application_id'] ?? '') }}" class="form-control">
                                <p class="help-block">L'ID dell'app registrata in Azure AD che eseguirà la sincronizzazione.</p>
                                @if ($errors->has('application_id'))
                                    <span class="help-block">{{ $errors->first('application_id') }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group {{ $errors->has('client_secret') ? 'has-error' : '' }}">
                            <label for="client_secret" class="col-md-3 control-label">Client secret</label>
                            <div class="col-md-6">
                                <input type="text" name="client_secret" id="client_secret" value="{{ old('client_secret', $settings['client_secret'] ?? '') }}" class="form-control">
                                <p class="help-block">Segreto associato all'applicazione per autenticarsi su Microsoft Graph.</p>
                                @if ($errors->has('client_secret'))
                                    <span class="help-block">{{ $errors->first('client_secret') }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group {{ $errors->has('device_filter') ? 'has-error' : '' }}">
                            <label for="device_filter" class="col-md-3 control-label">Filtro dispositivi</label>
                            <div class="col-md-6">
                                <input type="text" name="device_filter" id="device_filter" value="{{ old('device_filter', $settings['device_filter'] ?? '') }}" class="form-control">
                                <p class="help-block">Facoltativo: specifica un gruppo o tag Intune da sincronizzare.</p>
                                @if ($errors->has('device_filter'))
                                    <span class="help-block">{{ $errors->first('device_filter') }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-offset-3 col-md-6">
                                <hr>
                                <h4 style="margin-top: 0;">Mappatura colonne asset</h4>
                                <p class="text-muted" style="margin-bottom: 20px;">
                                    Associa ogni colonna degli asset di Snipe-IT al campo corrispondente di Intune da utilizzare durante la sincronizzazione.
                                </p>
                            </div>
                        </div>

                        @foreach ($assetColumns as $column => $label)
                            @php($fieldError = $errors->has("asset_column_map.$column"))
                            <div class="form-group {{ $fieldError ? 'has-error' : '' }}">
                                <label class="col-md-3 control-label">{{ $label }}</label>
                                <div class="col-md-6">
                                    <select name="asset_column_map[{{ $column }}]" class="form-control">
                                        <option value="">— Nessun campo —</option>
                                        @foreach ($intuneColumns as $intuneKey => $intuneLabel)
                                            <option value="{{ $intuneKey }}" @selected(old("asset_column_map.$column", $settings['asset_column_map'][$column] ?? '') === $intuneKey)>
                                                {{ $intuneLabel }} ({{ $intuneKey }})
                                            </option>
                                        @endforeach
                                    </select>

                                    @if ($fieldError)
                                        <span class="help-block">{{ $errors->first("asset_column_map.$column") }}</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach

                        <div class="form-group">
                            <div class="col-md-offset-3 col-md-6">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-save"></i> Salva impostazioni
                                </button>
                                <a href="{{ route('custom.test') }}" class="btn btn-link">Torna alla pagina di test</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
