<?php

namespace App\Services;

use App\Models\CustomerOrder;
use App\Models\Palox;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StockService
{
    /**
    * @param array<int, array{palox_id:int, picked_net_weight_kg?:numeric-string|int|float|null}> $lines
     */
    public function createOrderWithLines(CustomerOrder $order, array $lines): CustomerOrder
    {
        return DB::transaction(function () use ($order, $lines) {
            foreach ($lines as $line) {
                $palox = Palox::query()->lockForUpdate()->findOrFail($line['palox_id']);

                if ($palox->availability_status === 'exhausted') {
                    throw new InvalidArgumentException('Le palox selectionne est deja epuise.');
                }

                $remaining = round((float) $palox->remaining_net_weight_kg, 3);

                if ($remaining <= 0) {
                    throw new InvalidArgumentException('Le palox selectionne ne contient plus de stock disponible.');
                }

                $pickedWeight = array_key_exists('picked_net_weight_kg', $line) && $line['picked_net_weight_kg'] !== null && $line['picked_net_weight_kg'] !== ''
                    ? round((float) $line['picked_net_weight_kg'], 3)
                    : $remaining;

                if ($pickedWeight <= 0) {
                    throw new InvalidArgumentException('Le poids preleve doit etre superieur a zero.');
                }

                if ($pickedWeight > $remaining) {
                    throw new InvalidArgumentException('Le poids preleve ne peut pas depasser le stock disponible du palox.');
                }

                $newRemaining = round($remaining - $pickedWeight, 3);

                if ($newRemaining < 0) {
                    throw new InvalidArgumentException('Le stock ne peut pas devenir negatif.');
                }

                $palox->remaining_net_weight_kg = number_format($newRemaining, 3, '.', '');
                $palox->refreshAvailabilityStatus();
                $palox->save();

                $order->paloxes()->attach($palox->id, [
                    'picked_net_weight_kg' => number_format($pickedWeight, 3, '.', ''),
                ]);
            }

            return $order->load('paloxes.reception.variety');
        });
    }
}