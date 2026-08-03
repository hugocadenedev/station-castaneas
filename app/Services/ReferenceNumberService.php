<?php

namespace App\Services;

use App\Models\CustomerOrder;
use App\Models\Palox;
use App\Models\Reception;
use Carbon\CarbonInterface;

class ReferenceNumberService
{
    public function makeReceptionNumber(CarbonInterface $receivedAt, int $receptionId): string
    {
        return sprintf('REC-%s-%04d', $receivedAt->format('Ymd'), $receptionId);
    }

    public function makePaloxNumber(CarbonInterface $labeledAt, int $paloxId): string
    {
        return sprintf('PAL-%s-%04d', $labeledAt->format('Ymd'), $paloxId);
    }

    public function makeOrderNumber(CarbonInterface $orderedAt, int $orderId): string
    {
        return sprintf('CMD-%s-%04d', $orderedAt->format('Ymd'), $orderId);
    }

    public function assignReceptionNumber(Reception $reception): Reception
    {
        if (! $reception->reception_number || str_starts_with($reception->reception_number, 'TMP-REC-')) {
            $reception->forceFill([
                'reception_number' => $this->makeReceptionNumber($reception->received_at, $reception->id),
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