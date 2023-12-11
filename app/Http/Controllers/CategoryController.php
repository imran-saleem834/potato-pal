<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Category;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Models\CategoriesRelation;
use App\Helpers\NotificationHelper;
use App\Http\Requests\CategoryRequest;
use Illuminate\Database\Eloquent\Builder;

class CategoryController extends Controller
{
    /**
     * @var array $optionTypes
     */
    private array $optionTypes = [
        ['slug' => 'seed-class', 'label' => 'Seed Class'],
        ['slug' => 'delivery-type', 'label' => 'Delivery Type'],
        ['slug' => 'fungicide', 'label' => 'Fungicide'],
        ['slug' => 'seed-generation', 'label' => 'Seed Generation'],
        ['slug' => 'buyer', 'label' => 'Buyer Group Type'],
        ['slug' => 'grower', 'label' => 'Grower Group Type'],
        ['slug' => 'seed-type', 'label' => 'Seed Type'],
        ['slug' => 'seed-variety', 'label' => 'Seed Variety'],
        ['slug' => 'cool-store', 'label' => 'Cool Store'],
        ['slug' => 'transport', 'label' => 'Transport Co.'],
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $type       = $request->input('type', $this->optionTypes[0]['slug']);
        $categories = Category::where('type', $type)
            ->when($request->input('search'), function (Builder $query, $search) {
                return $query->where('name', 'LIKE', "%$search%");
            })
            ->get();

        return Inertia::render('AdminOption/Index', [
            'optionTypes' => $this->optionTypes,
            'categories'  => $categories,
            'filters'     => array_merge($request->only(['search']), ['type' => $type]),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoryRequest $request)
    {
        $category = Category::create($request->validated());

        $type       = $request->input('type');
        $optionType = Arr::first($this->optionTypes, function ($option) use ($type) {
            return $option['slug'] === $type;
        }, ['label' => $type]);

        NotificationHelper::addedAction($optionType['label'], $category->id);

        return to_route('categories.index', ['type' => $type]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CategoryRequest $request, string $id)
    {
        Category::find($id)->update($request->validated());

        $type       = $request->input('type');
        $optionType = Arr::first($this->optionTypes, function ($option) use ($type) {
            return $option['slug'] === $type;
        }, ['label' => $type]);

        NotificationHelper::updatedAction($optionType['label'], $id);

        return to_route('categories.index', ['type' => $type]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        CategoriesRelation::where('category_id', $id)->delete();
        Category::destroy($id);

        $type       = $request->input('type');
        $optionType = Arr::first($this->optionTypes, function ($option) use ($type) {
            return $option['slug'] === $type;
        }, ['label' => $type]);

        NotificationHelper::deleteAction($optionType['label'], $id);

        return to_route('categories.index', ['type' => $type]);
    }
}
