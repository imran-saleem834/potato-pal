<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Helpers\BuyerHelper;
use Illuminate\Http\Request;
use App\Models\ChemicalApplicant;
use App\Helpers\NotificationHelper;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Requests\ChemicalApplicantRequest;

class ChemicalApplicantController extends Controller
{
    /**
     * @param Request $request
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $navBuyers = BuyerHelper::getListOfModelBuyers(ChemicalApplicant::class, $request->input('buyer'));

        $firstBuyerId = $navBuyers->first()->buyer_id ?? '';
        $inputBuyerId = $request->input('buyerId', $firstBuyerId);

        $chemicalApplicants = $this->getChemicalApplicants($inputBuyerId, $request->input('search'));
        if ($chemicalApplicants->isEmpty() && ((int)$inputBuyerId) !== ((int)$firstBuyerId)) {
            $chemicalApplicants = $this->getChemicalApplicants($firstBuyerId, $request->input('search'));
        }

        return Inertia::render('ChemicalApplicant/Index', [
            'navBuyers' => $navBuyers,
            'single'    => $chemicalApplicants,
            'buyers'    => fn() => BuyerHelper::getAvailableBuyers(),
            'filters'   => $request->only(['search', 'buyer']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ChemicalApplicantRequest $request)
    {
        $allocation = $request->validated('selected_allocation');
        $inputs     = ['allocation_id' => $allocation['id'], 'buyer_id' => $allocation['buyer_id']];

        $chemicalApplicant = ChemicalApplicant::create(array_merge($request->validated(), $inputs));

        NotificationHelper::addedAction('ChemicalApplicant', $chemicalApplicant->id);

        return back();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ChemicalApplicantRequest $request, string $id)
    {
        $allocation = $request->validated('selected_allocation');
        $inputs     = ['allocation_id' => $allocation['id'], 'buyer_id' => $allocation['buyer_id']];

        $chemicalApplicant = ChemicalApplicant::find($id);
        $chemicalApplicant->update(array_merge($request->validated(), $inputs));
        $chemicalApplicant->save();

        NotificationHelper::updatedAction('ChemicalApplicant', $id);

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        ChemicalApplicant::destroy($id);

        NotificationHelper::deleteAction('ChemicalApplicant', $id);

        return back();
    }

    private function getChemicalApplicants($buyerId, $search = '')
    {
        return ChemicalApplicant::query()
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
