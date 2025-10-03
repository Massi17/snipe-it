<?php

namespace App\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class IntuneSettings
{
    /**
     * Location of the stored Intune settings.
     */
    private const STORAGE_PATH = 'intune-settings.json';

    /**
     * Retrieve the current Intune settings.
     */
    public static function get(): array
    {
        $defaults = self::defaults();

        if (! Storage::disk('local')->exists(self::STORAGE_PATH)) {
            return $defaults;
        }

        $decoded = json_decode(Storage::disk('local')->get(self::STORAGE_PATH), true);

        if (! is_array($decoded)) {
            return $defaults;
        }

        if (isset($decoded['asset_column_map']) && is_array($decoded['asset_column_map'])) {
            $decoded['asset_column_map'] = array_merge(
                $defaults['asset_column_map'],
                Arr::only($decoded['asset_column_map'], array_keys($defaults['asset_column_map']))
            );
        }

        return array_merge($defaults, $decoded);
    }

    /**
     * Persist the provided Intune settings.
     */
    public static function update(array $settings): void
    {
        $defaults = self::defaults();

        $payload = $defaults;

        foreach (Arr::only($settings, array_keys($defaults)) as $key => $value) {
            if ($key === 'asset_column_map') {
                if (is_array($value)) {
                    $payload[$key] = array_merge(
                        $defaults[$key],
                        Arr::only($value, array_keys($defaults[$key]))
                    );
                }

                continue;
            }

            $payload[$key] = $value;
        }

        Storage::disk('local')->put(
            self::STORAGE_PATH,
            json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    /**
     * Default values for the Intune settings.
     */
    private static function defaults(): array
    {
        return [
            'tenant_domain' => '',
            'application_id' => '',
            'client_secret' => '',
            'device_filter' => '',
            'asset_column_map' => [
                'asset_tag' => 'asset_tag',
                'serial' => 'serial',
                'name' => 'display_name',
            ],
        ];
    }

    /**
     * Labels for the asset columns that can be mapped to Intune fields.
     */
    public static function assetColumnLabels(): array
    {
        return [
            'asset_tag' => __('Tag asset'),
            'serial' => __('Numero di serie'),
            'name' => __('Nome asset'),
        ];
    }

    /**
     * Available Intune columns that can be mapped to asset columns.
     */
    public static function intuneColumnOptions(): array
    {
        $columns = collect(config('intune.devices', []))
            ->flatMap(fn (array $device) => array_keys($device))
            ->merge([
                'asset_tag',
                'display_name',
                'displayName',
                'id',
                'last_sync',
                'lastSyncDateTime',
                'model',
                'name',
                'primaryUser',
                'serial',
                'serialNumber',
                'user',
            ])
            ->unique()
            ->sort()
            ->values();

        return $columns->mapWithKeys(function ($column) {
            $label = Str::headline(str_replace('_', ' ', $column));

            return [$column => $label];
        })->all();
    }
}
