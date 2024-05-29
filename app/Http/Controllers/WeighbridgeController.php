<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Unload;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class WeighbridgeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $unloads = Unload::query()
            ->with(['weighbridges.creator:id,name', 'categories.category'])
            ->has('weighbridges')
            ->when($request->input('search'), function (Builder $query, $search) {
                return $query->where(function (Builder $subQuery) use ($search) {
                    $search = $search === 'BU1' ? 'weighbridge' : $search;

                    return $subQuery
                        ->where('id', 'LIKE', "%$search%")
                        ->orWhere('channel', 'LIKE', "%$search%")
                        ->orWhere('bin_size', 'LIKE', "%$search%")
                        ->orWhere('system', 'LIKE', "%$search%")
                        ->orWhere('no_of_bins', 'LIKE', "%$search%")
                        ->orWhereRaw("CONCAT(`weight`, ' kg') LIKE '%{$search}%'")
                        ->orWhereRelation('categories.category', function (Builder $catQuery) use ($search) {
                            return $catQuery->where('name', 'LIKE', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString()
            ->onEachSide(1);

        return Inertia::render('Weighbridge/Index', [
            'unloads' => $unloads,
            'filters' => $request->only(['search']),
        ]);
    }
}
