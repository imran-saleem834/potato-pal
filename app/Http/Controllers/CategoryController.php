<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Category;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Models\CategoriesRelation;
use App\Helpers\NotificationHelper;
use App\Http\Requests\CategoryRequest;

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
        ['slug' => 'transport', 'label' => 'Transport Co.'],
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $keyword    = $request->input('keyword');
        $categories = Category::whereIn('type', $request->input('type'))
            ->when($keyword, function ($query, $keyword) {
                return $query->where('name', 'LIKE', "%$keyword%");
            })
            ->get();

        return response()->json($categories);
    }

    /**
     * Display a listing of the resource.
     */
    public function options()
    {
        return Inertia::render('AdminOption/Index', [
            'optionTypes' => $this->optionTypes
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

        return back();
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

        return back();
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
    }
}
