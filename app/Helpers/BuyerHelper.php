<?php

namespace App\Helpers;

use App\Models\User;

class BuyerHelper
{
    public static function getListOfModelBuyers($model)
    {
        return $model::query()
            ->select('buyer_id')
            ->with([
                'buyer:id,buyer_name',
                'buyer.categories.category',
            ])
            ->latest()
            ->groupBy('buyer_id')
            ->get()
            ->map(function ($item) {
                $item->id = $item->buyer_id;

                return $item;
            });
    }

    public static function getAvailableBuyers()
    {
        return User::query()
            ->select(['id', 'buyer_name'])
            ->whereJsonContains('role', 'buyer')
            ->get()
            ->map(fn ($user) => ['value' => $user->id, 'label' => $user->buyer_name]);
    }

    public static function getAvailableGrowers()
    {
        return User::query()
            ->select(['id', 'grower_name'])
            ->whereJsonContains('role', 'grower')
            ->get()
            ->map(fn ($user) => ['value' => $user->id, 'label' => $user->grower_name]);
    }
}
