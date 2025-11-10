<?php

namespace App\Services;

use App\Models\DashboardCounter;
use Illuminate\Support\Facades\DB;

class SerialNumberGenerator
{
    public function next(string $key = 'sales'): string
    {
        $value = DB::transaction(function () use ($key) {
            $counter = DashboardCounter::query()->lockForUpdate()->firstOrCreate(
                ['name' => $key],
                ['value' => 0],
            );

            $counter->increment('value');

            return $counter->value;
        });

        return sprintf('TM%d', $value);
    }
}
