<?php

namespace App\Services;

use App\Models\Tenant\Setting;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

class StoreAvailabilityService
{
    private const DAYS = ['su', 'mo', 'tu', 'we', 'th', 'fr', 'sa'];

    public function status(): array
    {
        $timezone = Setting::get('timezone', 'America/Chicago') ?: 'America/Chicago';
        $hours = Setting::get('store_hours', Setting::defaults()['store_hours']);
        $now = CarbonImmutable::now($timezone);
        $dayKey = self::DAYS[$now->dayOfWeek];
        $today = $hours[$dayKey] ?? null;
        $previousDayKey = self::DAYS[$now->subDay()->dayOfWeek];
        $previousDay = $hours[$previousDayKey] ?? null;
        $allowPickup = (bool) Setting::get('allow_pickup', true);
        $allowDelivery = (bool) Setting::get('allow_delivery', true);
        $open = ($this->isOpenForDay($today, $now) || $this->isOpenFromPreviousDay($previousDay, $now)) && ($allowPickup || $allowDelivery);

        return [
            'open' => $open,
            'message' => $open ? 'Open for online orders.' : 'We are currently closed for online orders.',
            'day' => $dayKey,
            'timezone' => $timezone,
            'today' => $today,
            'allow_pickup' => $allowPickup,
            'allow_delivery' => $allowDelivery,
        ];
    }

    public function ensureOnlineOrderingIsAvailable(?string $method = null): void
    {
        $status = $this->status();

        if (! $status['open']) {
            throw ValidationException::withMessages([
                'ordering' => [$status['message']],
            ]);
        }

        if ($method === 'pickup' && ! $status['allow_pickup']) {
            throw ValidationException::withMessages([
                'method' => ['Pickup is not available for this restaurant.'],
            ]);
        }

        if ($method === 'delivery' && ! $status['allow_delivery']) {
            throw ValidationException::withMessages([
                'method' => ['Delivery is not available for this restaurant.'],
            ]);
        }
    }

    public function ensureMethodIsAvailable(?string $method): void
    {
        $status = $this->status();

        if ($method === 'pickup' && ! $status['allow_pickup']) {
            throw ValidationException::withMessages([
                'method' => ['Pickup is not available for this restaurant.'],
            ]);
        }

        if ($method === 'delivery' && ! $status['allow_delivery']) {
            throw ValidationException::withMessages([
                'method' => ['Delivery is not available for this restaurant.'],
            ]);
        }
    }

    private function isOpenForDay(?array $hours, CarbonImmutable $now): bool
    {
        if (! $hours || ! empty($hours['closed'])) {
            return false;
        }

        $from = $this->minutesFromTime($hours['from'] ?? null);
        $to = $this->minutesFromTime($hours['to'] ?? null);

        if ($from === null || $to === null) {
            return false;
        }

        $current = ($now->hour * 60) + $now->minute;

        if ($to < $from) {
            return $current >= $from;
        }

        return $current >= $from && $current <= $to;
    }

    private function isOpenFromPreviousDay(?array $hours, CarbonImmutable $now): bool
    {
        if (! $hours || ! empty($hours['closed'])) {
            return false;
        }

        $from = $this->minutesFromTime($hours['from'] ?? null);
        $to = $this->minutesFromTime($hours['to'] ?? null);

        if ($from === null || $to === null || $to >= $from) {
            return false;
        }

        $current = ($now->hour * 60) + $now->minute;

        return $current <= $to;
    }

    private function minutesFromTime(?string $time): ?int
    {
        if (! $time) {
            return null;
        }

        $time = strtoupper(trim($time));
        if (! preg_match('/^(\d{1,2}):(\d{2})\s*(AM|PM)?$/', $time, $matches)) {
            return null;
        }

        $hour = (int) $matches[1];
        $minute = (int) $matches[2];
        $period = $matches[3] ?? null;

        if ($hour > 23 || $minute > 59) {
            return null;
        }

        if ($period === 'PM' && $hour !== 12) {
            $hour += 12;
        }

        if ($period === 'AM' && $hour === 12) {
            $hour = 0;
        }

        return ($hour * 60) + $minute;
    }
}