<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Helpers\BuyerHelper;
use Illuminate\Http\Request;
use App\Helpers\NotificationHelper;
use App\Models\ChemicalApplication;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Requests\ChemicalApplicationRequest;

class ChemicalApplicationController extends Controller
{
    /**
     * @param  Request  $request
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $navBuyers = BuyerHelper::getListOfModelBuyers(ChemicalApplication::class, $request->input('buyer'));

        $firstBuyerId = $navBuyers->first()->buyer_id ?? '';
        $inputBuyerId = $request->input('buyerId', $firstBuyerId);

        $chemicalApplications = $this->getChemicalApplications($inputBuyerId, $request->input('search'));
        if ($chemicalApplications->isEmpty() && ((int) $inputBuyerId) !== ((int) $firstBuyerId)) {
            $chemicalApplications = $this->getChemicalApplications($firstBuyerId, $request->input('search'));
        }

        return Inertia::render('ChemicalApplication/Index', [
            'navBuyers' => $navBuyers,
            'single'    => $chemicalApplications,
            'buyers'    => fn () => BuyerHelper::getAvailableBuyers(),
            'filters'   => $request->only(['search', 'buyer']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ChemicalApplicationRequest $request)
    {
        $allocation = $request->validated('selected_allocation');
        $inputs     = ['allocation_id' => $allocation['id'], 'buyer_id' => $allocation['buyer_id']];

        $chemicalApplication = ChemicalApplication::create(array_merge($request->validated(), $inputs));

        NotificationHelper::addedAction('ChemicalApplication', $chemicalApplication->id);

        return back();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ChemicalApplicationRequest $request, string $id)
    {
        $allocation = $request->validated('selected_allocation');
        $inputs     = ['allocation_id' => $allocation['id'], 'buyer_id' => $allocation['buyer_id']];

        $chemicalApplication = ChemicalApplication::find($id);
        $chemicalApplication->update(array_merge($request->validated(), $inputs));
        $chemicalApplication->save();

        NotificationHelper::updatedAction('ChemicalApplication', $id);

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        ChemicalApplication::destroy($id);

        NotificationHelper::deleteAction('ChemicalApplication', $id);

        return back();
    }

    private function getChemicalApplications($buyerId, $search = '')
    {
        return ChemicalApplication::query()
            ->with([
                'allocation.item',
                'allocation.grower',
                'allocation.categories.category',
            ])
            ->when($search, function (Builder $query, $search) {
                return $query->where(function (Builder $subQuery) use ($search) {
                    return $subQuery
                        ->where('no_of_crew', 'LIKE', "%{$search}%")
                        ->orWhere('comments', 'LIKE', "%{$search}%")
                        ->orWhere('fungicide_used', 'LIKE', "%{$search}%")
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
