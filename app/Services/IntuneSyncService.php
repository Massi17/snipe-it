<?php

namespace App\Services;

use App\Models\Asset;

class IntuneSyncService
{
    /**
     * Build a preview of the differences between Intune devices and the current assets.
     */
    public function preview(): array
    {
        $intuneDevices = collect(config('intune.devices', []))
            ->map(fn (array $device) => $this->normaliseDevice($device));

        $existingAssets = Asset::query()
            ->select(['id', 'name', 'asset_tag', 'serial'])
            ->whereNotNull('serial')
            ->get()
            ->keyBy('serial')
            ->map(fn (Asset $asset) => [
                'id' => $asset->id,
                'name' => $asset->name,
                'asset_tag' => $asset->asset_tag,
                'serial' => $asset->serial,
            ]);

        $newDevices = [];
        $updatedDevices = [];

        foreach ($intuneDevices as $device) {
            $existing = $device['serial']
                ? $existingAssets->get($device['serial'])
                : null;

            if (! $existing) {
                $newDevices[] = $device;

                continue;
            }

            $differences = $this->diffAgainstExisting($device, $existing);

            if (! empty($differences)) {
                $updatedDevices[] = array_merge($device, [
                    'existing' => $existing,
                    'differences' => $differences,
                ]);
            }
        }

        return [
            'generated_at' => now()->toDateTimeString(),
            'source_count' => $intuneDevices->count(),
            'new_devices' => $newDevices,
            'updated_devices' => $updatedDevices,
        ];
    }

    /**
     * Normalise the Intune payload to a consistent structure.
     */
    private function normaliseDevice(array $device): array
    {
        $serial = $device['serial']
            ?? $device['serialNumber']
            ?? null;

        return [
            'identifier' => $serial
                ?? $device['id']
                ?? md5(json_encode($device)),
            'id' => $device['id'] ?? null,
            'display_name' => $device['name']
                ?? $device['displayName']
                ?? __('Dispositivo senza nome'),
            'asset_tag' => $device['asset_tag'] ?? null,
            'serial' => $serial,
            'model' => $device['model'] ?? null,
            'user' => $device['user'] ?? $device['primaryUser'] ?? null,
            'last_sync' => $device['last_sync'] ?? $device['lastSyncDateTime'] ?? null,
            'raw' => $device,
        ];
    }

    /**
     * Determine what has changed between the local asset and the Intune device.
     */
    private function diffAgainstExisting(array $device, array $existing): array
    {
        $differences = [];

        if (($existing['name'] ?? null) !== ($device['display_name'] ?? null)) {
            $differences['name'] = [
                'current' => $existing['name'] ?? null,
                'incoming' => $device['display_name'] ?? null,
            ];
        }

        if (($existing['asset_tag'] ?? null) !== ($device['asset_tag'] ?? null)) {
            $differences['asset_tag'] = [
                'current' => $existing['asset_tag'] ?? null,
                'incoming' => $device['asset_tag'] ?? null,
            ];
        }

        return $differences;
    }
}
