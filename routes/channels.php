<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('admin.{tenantId}.orders', function ($user, string $tenantId) {
    return tenancy()->initialized
        && (string) tenant()->id === (string) $tenantId
        && in_array($user->role, ['owner', 'manager', 'fulfillment'], true);
}, ['guards' => ['tenant']]);