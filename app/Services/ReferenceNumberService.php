<?php

namespace App\Services;

use App\Models\CustomerOrder;
use App\Models\Palox;
use App\Models\Reception;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class ReferenceNumberService
{
    public function makeReceptionNumber(int $receptionId): string
    {
        return sprintf('%04d', $receptionId);
    }

    public function makePaloxNumber(CarbonInterface $labeledAt, int $paloxId): string
    {
        $prefix = $labeledAt->format('y').'-';

        $sequence = $this->existingPaloxSequencesForYear($labeledAt, $paloxId)
            ->max() ?? 0;

        return sprintf('%s%03d', $prefix, $sequence + 1);
    }

    private function existingPaloxSequencesForYear(CarbonInterface $labeledAt, int $paloxId): Collection
    {
        $prefix = $labeledAt->format('y').'-';

        return Palox::query()
            ->whereYear('labeled_at', $labeledAt->year)
            ->whereKeyNot($paloxId)
            ->where('palox_number', 'like', $prefix.'%')
            ->lockForUpdate()
            ->pluck('palox_number')
            ->map(function (string $paloxNumber) use ($prefix): int {
                $suffix = substr($paloxNumber, strlen($prefix));

                return ctype_digit($suffix) ? (int) $suffix : 0;
            });
    }

    public function makeOrderNumber(CarbonInterface $orderedAt, int $orderId): string
    {
        return sprintf('CMD-%s-%04d', $orderedAt->format('Ymd'), $orderId);
    }

    public function assignReceptionNumber(Reception $reception): Reception
    {
        if (! $reception->reception_number || str_starts_with($reception->reception_number, 'TMP-REC-')) {
            $reception->forceFill([
                'reception_number' => $this->makeReceptionNumber($reception->id),
            ])->save();
        }

        return $reception;
    }

    public function assignPaloxNumber(Palox $palox): Palox
    {
        if (! $palox->palox_number || str_starts_with($palox->palox_number, 'TMP-PAL-')) {
            $palox->forceFill([
                'palox_number' => $this->makePaloxNumber($palox->labeled_at, $palox->id),
            ]);
            $palox->refreshAvailabilityStatus();
            $palox->save();
        }

        return $palox;
    }

    public function assignOrderNumber(CustomerOrder $order): CustomerOrder
    {
        if (! $order->order_number || str_starts_with($order->order_number, 'TMP-CMD-')) {
            $order->forceFill([
                'order_number' => $this->makeOrderNumber($order->ordered_at, $order->id),
            ])->save();
        }

        return $order;
    }
}