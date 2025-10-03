<?php

namespace App\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

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
        if (! Storage::disk('local')->exists(self::STORAGE_PATH)) {
            return self::defaults();
        }

        $decoded = json_decode(Storage::disk('local')->get(self::STORAGE_PATH), true);

        if (! is_array($decoded)) {
            return self::defaults();
        }

        return array_merge(self::defaults(), $decoded);
    }

    /**
     * Persist the provided Intune settings.
     */
    public static function update(array $settings): void
    {
        $payload = array_merge(self::defaults(), Arr::only($settings, array_keys(self::defaults())));

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
        ];
    }
}
