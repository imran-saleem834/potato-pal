<?php

namespace App\Helpers;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class BuyerHelper
{
    public static function getListOfModelBuyers($model, $search = '')
    {
        return $model::query()
            ->select('buyer_id')
            ->with([
                'buyer:id,buyer_name,name',
                'buyer.categories.category',
            ])
            ->when($search, function ($query, $search) {
                return $query->where(function ($subQuery) use ($search) {
                    return $subQuery
                        ->whereRelation('buyer', function (Builder $query) use ($search) {
                            return $query->where('name', 'LIKE', "%{$search}%")
                                ->orWhere('buyer_name', 'LIKE', "%{$search}%");
                        })
                        ->orWhereRelation('buyer.categories.category', 'name', 'LIKE', "%{$search}%");
                });
            })
            ->latest()
            ->groupBy('buyer_id')
            ->get()
            ->sortBy(fn ($item) => $item->buyer->buyer_name)
            ->map(function ($item) {
                $item->id = $item->buyer_id;

                return $item;
            })
            ->values();
    }

    public static function getAvailableBuyers()
    {
        return User::query()
            ->select(['id', 'buyer_name'])
            ->whereJsonContains('access', 'buyer')
            ->get()
            ->map(fn ($user) => ['value' => $user->id, 'label' => $user->buyer_name]);
    }

    public static function getAvailableGrowers()
    {
        return User::query()
            ->select(['id', 'grower_name'])
            ->whereJsonContains('access', 'grower')
            ->get()
            ->map(fn ($user) => ['value' => $user->id, 'label' => $user->grower_name]);
    }
}
