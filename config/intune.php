<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Dispositivi Intune di esempio
    |--------------------------------------------------------------------------
    |
    | Questi dati vengono utilizzati come esempio per illustrare il flusso
    | della sincronizzazione dalla pagina di test. Sostituiscili con una
    | chiamata reale a Microsoft Graph oppure popola questo array con i dati
    | provenienti dal tuo tenant Intune.
    |
    */

    'devices' => [
        [
            'id' => 'intune-001',
            'name' => 'PC-Ufficio-01',
            'asset_tag' => 'PC-0001',
            'serial' => 'INTUNE-SN-001',
            'model' => 'Lenovo ThinkPad L14',
            'user' => 'marco.rossi',
            'last_sync' => '2024-03-15T09:30:00+00:00',
        ],
        [
            'id' => 'intune-002',
            'name' => 'PC-Ufficio-02',
            'asset_tag' => 'PC-0002',
            'serial' => 'INTUNE-SN-002',
            'model' => 'HP EliteBook 840',
            'user' => 'laura.bianchi',
            'last_sync' => '2024-03-14T17:05:00+00:00',
        ],
        [
            'id' => 'intune-003',
            'name' => 'Portatile-TeamVendite',
            'asset_tag' => 'PC-0105',
            'serial' => 'INTUNE-SN-003',
            'model' => 'Dell XPS 13',
            'user' => 'sales.team',
            'last_sync' => '2024-03-16T12:20:00+00:00',
        ],
    ],
];
