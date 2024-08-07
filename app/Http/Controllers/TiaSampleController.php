<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Category;
use App\Models\Receival;
use App\Models\TiaSample;
use Illuminate\Http\Request;
use App\Helpers\ReceivalHelper;
use App\Models\CategoriesRelation;
use App\Helpers\NotificationHelper;
use App\Http\Requests\TiaSampleRequest;
use Illuminate\Database\Eloquent\Builder;

class TiaSampleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $tiaSamples = TiaSample::query()
            ->with(['receival:id,grower_id', 'receival.grower:id,grower_name'])
            ->select('id', 'receival_id')
            ->when($request->input('search'), function (Builder $query, $search) {
                return $query->search($search);
            })
            ->latest()
            ->paginate(20)
            ->withQueryString()
            ->onEachSide(1);

        $tiaSampleId = $request->input('tiaSampleId', $tiaSamples->items()[0]->id ?? 0);

        $tiaSample = $this->getTiaSample($tiaSampleId);

        $receivals = Receival::query()
            ->with(['grower:id,grower_name'])
            ->select(['id', 'grower_id'])
            ->get()
            ->map(function ($receival) {
                return [
                    'value' => $receival->id,
                    'label' => "Receival id: {$receival->id}; {$receival->grower->grower_name}",
                ];
            });

        return Inertia::render('TiaSample/Index', [
            'tiaSamples' => $tiaSamples,
            'single'     => $tiaSample,
            'receivals'  => $receivals,
            'filters'    => $request->only(['search']),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $tiaSample = $this->getTiaSample($id);

        return response()->json($tiaSample);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TiaSampleRequest $request, string $id)
    {
        $tiaSample = TiaSample::find($id);
        $tiaSample->update($request->validated());
        $tiaSample->save();

        $this->updateSeedClassBaseOnStatus($tiaSample);

        NotificationHelper::updatedAction('Tia Sample', $id);

        return to_route('tia-samples.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        TiaSample::destroy($id);

        NotificationHelper::deleteAction('Tia Sample', $id);

        return to_route('tia-samples.index');
    }

    private function getTiaSample(string $id)
    {
        return TiaSample::query()
            ->with([
                'receival.grower.categories.category',
                'receival.categories.category',
                'receival.unloads.categories.category',
            ])
            ->find($id);
    }

    private function updateSeedClassBaseOnStatus(TiaSample $tiaSample)
    {
        if (! ReceivalHelper::isSeedClassPending($tiaSample->receival_id)) {
            return;
        }

        $tiaSample->loadMissing(['receival:id,grower_id']);

        $categoryName = $tiaSample->status === 'not-certified' ? 'Provisional' : ($tiaSample->status === 'qa' ? 'QA only' : 'Certified');
        $category     = Category::where('type', 'seed-class')->where('name', $categoryName)->first();

        if ($category) {
            CategoriesRelation::query()
                ->where([
                    'categorizable_id'   => $tiaSample->receival_id,
                    'categorizable_type' => Receival::class,
                    'type'               => $category->type,
                ])
                ->update(['category_id' => $category->id]);

            ReceivalHelper::calculateRemainingReceivals($tiaSample->receival->grower_id);
        }
    }
}
