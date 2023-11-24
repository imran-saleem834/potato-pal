<?php

namespace App\Http\Controllers;

use App\Models\CategoriesRelation;
use App\Models\Category;
use App\Models\Receival;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\ReceivalRequest;
use Inertia\Inertia;

class ReceivalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Receival/Index');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request)
    {
        $keyword   = $request->input('keyword', '');
        $receivals = Receival::query()
            ->with([
                'user' => function ($query) {
                    return $query->select('id', 'name');
                }
            ])
            ->select('id', 'user_id')
            ->when($keyword, function ($query, $keyword) {
                $keyword = strtolower($keyword);
                return $query->whereRaw("`id` LIKE '%$keyword%'")
                    ->orWhereRaw("LOWER(`paddocks`) LIKE '%$keyword%'")
                    ->orWhereRaw("LOWER(`tia_sample_id`) LIKE '%$keyword%'")
                    ->orWhereRaw("LOWER(`unloading_status`) LIKE '%$keyword%'")
                    ->orWhereRaw("`unloading_id` LIKE '%$keyword%'")
                    ->orWhereRaw("LOWER(`transport`) LIKE '%$keyword%'")
                    ->orWhereRaw("`grower_docket_no` LIKE '%$keyword%'")
                    ->orWhereRaw("`chc_receival_docket_no` LIKE '%$keyword%'")
                    ->orWhereRaw("`driver_name` LIKE '%$keyword%'")
                    ->orWhereRaw("`comments` LIKE '%$keyword%'");
            })
            ->get();

        $receivalId = $request->input('receivalId', $receivals->first()->id ?? 0);

        $receival = Receival::with([
            'user' => function ($query) {
                return $query->select('id', 'name');
            }
        ])->find($receivalId);
        //        $receival = $this->getRelations($receival);

        return response()->json([
            'receivals' => $receivals,
            'receival' => $receival,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ReceivalRequest $request)
    {
        $inputs = $request->validated();

        $user = Receival::create($inputs);

//        foreach ($inputs['grower_groups'] ?? [] as $input) {
//            $this->createCategoriesRelation($user->id, $input, 'grower');
//        }

        return back();
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $receival = Receival::with([
            'user' => function ($query) {
                return $query->select('id', 'name');
            }
        ])->find($id);
        // $user = $this->getRelations($user);

        return response()->json($receival);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ReceivalRequest $request, string $id)
    {
        $inputs = $request->validated();

        $receival = Receival::find($id);
        $receival->update($inputs);
        $receival->save();

//        $categoryIds = [];
//        foreach ($inputs['buyer_groups'] ?? [] as $input) {
//            $categoryIds[] = $this->createCategoriesRelation($user->id, $input, 'buyer');
//        }
//        foreach ($inputs['grower_groups'] ?? [] as $input) {
//            $categoryIds[] = $this->createCategoriesRelation($user->id, $input, 'grower');
//        }
//
//        CategoriesRelation::where('user_id', $user->id)->whereNotIn('categorizable_id', $categoryIds)->delete();

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Receival::destroy($id);
    }

    private function createCategoriesRelation($userId, $categoryId, $type)
    {
        if (!is_int($categoryId)) {
            $categoryId = $this->firstOrCreateCategory($categoryId, $type);
        }

        CategoriesRelation::firstOrCreate([
            'user_id'            => $userId,
            'categorizable_id'   => $categoryId,
            'categorizable_type' => $type
        ]);

        return $categoryId;
    }

    private function firstOrCreateCategory(string $name, string $type)
    {
        $lowerName = strtolower($name);
        $category  = Category::whereRaw("LOWER(`name`) = '$lowerName'")->where('type', $type)->first();
        if (!$category) {
            $category = Category::create(['name' => $name, 'type' => $type]);
        }
        return $category->id;
    }

    private function getRelations($user)
    {
        $relations = CategoriesRelation::where('user_id', $user->id)->get();

        $user->grower_groups = $relations->where('categorizable_type', 'grower')->pluck('categorizable_id')->toArray();
        $user->buyer_groups  = $relations->where('categorizable_type', 'buyer')->pluck('categorizable_id')->toArray();

        return $user;
    }
}
