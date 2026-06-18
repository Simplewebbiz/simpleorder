<?php

namespace App\Services;

use App\Models\Tenant\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeocodingService
{
    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.google_maps.key', env('GOOGLE_MAPS_API_KEY', ''));
    }

    /**
     * Returns ['lat' => float, 'lng' => float] or null on failure.
     */
    public function geocode(string $address): ?array
    {
        if (empty($this->apiKey)) {
            return null;
        }

        $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
            'address' => $address,
            'key'     => $this->apiKey,
        ]);

        if (! $response->successful()) {
            return null;
        }

        $data = $response->json();

        if (($data['status'] ?? '') !== 'OK' || empty($data['results'][0]['geometry']['location'])) {
            return null;
        }

        $loc = $data['results'][0]['geometry']['location'];
        return ['lat' => (float) $loc['lat'], 'lng' => (float) $loc['lng']];
    }

    /**
     * Straight-line distance in miles between two lat/lng pairs (Haversine formula).
     */
    public function distanceMiles(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 3958.8;

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return 2 * $earthRadius * asin(sqrt($a));
    }

    /**
     * Validates that a delivery address is within the tenant's configured radius.
     * Returns null on success, or an error string on failure.
     */
    public function validateDeliveryAddress(string $deliveryAddress): ?string
    {
        $radius   = (float) Setting::get('delivery_radius', 10);
        $storeRaw = Setting::get('store_address', []);

        if (is_string($storeRaw)) {
            $storeRaw = json_decode($storeRaw, true) ?? [];
        }

        $storeAddrStr = trim(
            ($storeRaw['address'] ?? '') . ' ' .
            ($storeRaw['city']    ?? '') . ' ' .
            ($storeRaw['state']   ?? '') . ' ' .
            ($storeRaw['zip']     ?? '')
        );

        if (empty($storeAddrStr)) {
            return null; // No store address configured — allow all
        }

        $storeLoc = $this->geocode($storeAddrStr);
        if (! $storeLoc) {
            return null; // Geocoding unavailable — allow
        }

        $deliveryLoc = $this->geocode($deliveryAddress);
        if (! $deliveryLoc) {
            return 'Could not verify the delivery address. Please check and try again.';
        }

        $distance = $this->distanceMiles(
            $storeLoc['lat'], $storeLoc['lng'],
            $deliveryLoc['lat'], $deliveryLoc['lng']
        );

        if ($distance > $radius) {
            return "Sorry, that address is outside our delivery area ({$radius} miles).";
        }

        return null;
    }
}
