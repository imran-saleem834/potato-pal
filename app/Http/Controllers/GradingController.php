<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Grading;
use App\Models\Allocation;
use App\Helpers\BuyerHelper;
use Illuminate\Http\Request;
use App\Helpers\NotificationHelper;
use App\Http\Requests\GradingRequest;

class GradingController extends Controller
{
    /**
     * @param  Request  $request
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $navBuyers = BuyerHelper::getListOfModelBuyers(Grading::class, $request->input('buyer'));

        $firstBuyerId = $navBuyers->first()->buyer_id ?? '';
        $inputBuyerId = $request->input('buyerId', $firstBuyerId);

        $gradings = $this->getGradings($inputBuyerId, $request->input('search'));
        if ($gradings->isEmpty() && ((int)$inputBuyerId) !== ((int)$firstBuyerId)) {
            $gradings = $this->getGradings($firstBuyerId, $request->input('search'));
        }

        return Inertia::render('Grading/Index', [
            'navBuyers' => $navBuyers,
            'single'    => $gradings,
            'buyers'    => fn() => BuyerHelper::getAvailableBuyers(),
            'filters'   => $request->only(['search', 'buyer']),
        ]);
    }

    public function allocations(Request $request, $id)
    {
        $allocations = Allocation::query()
            ->with(['item', 'categories.category', 'grower:id,grower_name'])
            ->where('buyer_id', $id)
            ->get();

        return response()->json($allocations->toArray());
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $grade = $this->getGrade($id);

        return response()->json($grade);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(GradingRequest $request)
    {
        $allocation = $request->validated('selected_allocation');
        $inputs = ['allocation_id' => $allocation['id'], 'buyer_id' => $allocation['buyer_id']];
        
        $grading = Grading::create(array_merge($request->validated(), $inputs));

        NotificationHelper::addedAction('Grading', $grading->id);

        return back();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(GradingRequest $request, string $id)
    {
        $allocation = $request->validated('selected_allocation');
        $inputs = ['allocation_id' => $allocation['id'], 'buyer_id' => $allocation['buyer_id']];
        
        $grading = Grading::find($id);
        $grading->update(array_merge($request->validated(), $inputs));
        $grading->save();

        NotificationHelper::updatedAction('Grading', $id);

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Grading::destroy($id);

        NotificationHelper::deleteAction('Grade', $id);

        return back();
    }

    private function getGradings($buyerId, $search = '')
    {
        return Grading::query()
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
