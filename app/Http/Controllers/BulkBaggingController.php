<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\BulkBagging;
use Illuminate\Http\Request;
use App\Helpers\BuyerHelper;
use App\Helpers\NotificationHelper;
use App\Http\Requests\BulkBaggingRequest;

class BulkBaggingController extends Controller
{
    /**
     * @param Request $request
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $navBuyers = BuyerHelper::getListOfModelBuyers(BulkBagging::class, $request->input('buyer'));

        $firstBuyerId = $navBuyers->first()->buyer_id ?? '';
        $inputBuyerId = $request->input('buyerId', $firstBuyerId);

        $bulkBaggings = $this->getBulkBaggings($inputBuyerId, $request->input('search'));
        if ($bulkBaggings->isEmpty() && ((int)$inputBuyerId) !== ((int)$firstBuyerId)) {
            $bulkBaggings = $this->getBulkBaggings($firstBuyerId, $request->input('search'));
        }

        return Inertia::render('BulkBagging/Index', [
            'navBuyers' => $navBuyers,
            'single'    => $bulkBaggings,
            'buyers'    => fn() => BuyerHelper::getAvailableBuyers(),
            'filters'   => $request->only(['search', 'buyer']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BulkBaggingRequest $request)
    {
        $allocation = $request->validated('selected_allocation');
        $inputs     = ['allocation_id' => $allocation['id'], 'buyer_id' => $allocation['buyer_id']];

        $bulkBagging = BulkBagging::create(array_merge($request->validated(), $inputs));

        NotificationHelper::addedAction('BulkBagging', $bulkBagging->id);

        return back();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BulkBaggingRequest $request, string $id)
    {
        $allocation = $request->validated('selected_allocation');
        $inputs     = ['allocation_id' => $allocation['id'], 'buyer_id' => $allocation['buyer_id']];

        $bulkBagging = BulkBagging::find($id);
        $bulkBagging->update(array_merge($request->validated(), $inputs));
        $bulkBagging->save();

        NotificationHelper::updatedAction('BulkBagging', $id);

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        BulkBagging::destroy($id);

        NotificationHelper::deleteAction('BulkBagging', $id);

        return back();
    }

    private function getBulkBaggings($buyerId, $search = '')
    {
        return BulkBagging::query()
            ->with([
                'allocation.item',
                'allocation.grower',
                'allocation.categories.category',
            ])
            ->when($search, function ($query, $search) {
                return $query->where(function ($subQuery) use ($search) {
                    return $subQuery
                        ->where('no_of_crew', 'LIKE', "%{$search}%")
                        ->orWhere('comments', 'LIKE', "%{$search}%")
                        ->orWhere('weight', 'LIKE', "%{$search}%")
                        ->orWhere('no_of_bulk_bags_out', 'LIKE', "%{$search}%")
                        ->orWhere('net_weight_bags_out', 'LIKE', "%{$search}%")
                        ->orWhereRelation('allocation', 'paddock', 'LIKE', "%{$search}%")
                        ->orWhereRelation('allocation.categories.category', 'name', 'LIKE', "%{$search}%");
                });
            })
            ->where('buyer_id', $buyerId)
            ->latest()
            ->paginate(10)
            ->withQueryString()
            ->onEachSide(1);
    }
}
