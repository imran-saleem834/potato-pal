<?php

namespace App\Http\Controllers;

use App\Models\CategoriesRelation;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $keyword = $request->input('keyword');
        $users   = User::select('id', 'name', 'email')
            ->when($keyword, function ($query, $keyword) {
                $keyword = strtolower($keyword);
                return $query->whereRaw("`id` LIKE '%$keyword%'")
                    ->orWhereRaw("LOWER(`name`) LIKE '%$keyword%'")
                    ->orWhereRaw("LOWER(`email`) LIKE '%$keyword%'")
                    ->orWhereRaw("LOWER(`username`) LIKE '%$keyword%'")
                    ->orWhereRaw("`phone` LIKE '%$keyword%'")
                    ->orWhereRaw("LOWER(`role`) LIKE '%$keyword%'")
                    ->orWhereRaw("LOWER(`grower_name`) LIKE '%$keyword%'")
                    ->orWhereRaw("LOWER(`grower_tags`) LIKE '%$keyword%'")
                    ->orWhereRaw("LOWER(`buyer_tags`) LIKE '%$keyword%'")
                    ->orWhereRaw("LOWER(`paddocks`) LIKE '%$keyword%'");
            })
            ->get();

        $userId = $request->input('userId', $users->first()->id ?? 0);

        $user = User::find($userId);
        $user = $this->getRelations($user);

        return response()->json([
            'users' => $users,
            'user'  => $user,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserRequest $request)
    {
        $inputs = $request->validated();

        $user = User::create($request->validated());

        foreach ($inputs['buyer_groups'] ?? [] as $input) {
            $this->createCategoriesRelation($user->id, $input, 'buyer');
        }
        foreach ($inputs['grower_groups'] ?? [] as $input) {
            $this->createCategoriesRelation($user->id, $input, 'grower');
        }

        return back();
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::find($id);
        $user = $this->getRelations($user);

        return response()->json($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserRequest $request, string $id)
    {
        $inputs = $request->validated();

        $user = User::find($id);
        $user->update($inputs);
        $user->save();

        $categoryIds = [];
        foreach ($inputs['buyer_groups'] ?? [] as $input) {
            $categoryIds[] = $this->createCategoriesRelation($user->id, $input, 'buyer');
        }
        foreach ($inputs['grower_groups'] ?? [] as $input) {
            $categoryIds[] = $this->createCategoriesRelation($user->id, $input, 'grower');
        }

        CategoriesRelation::where('user_id', $user->id)->whereNotIn('categorizable_id', $categoryIds)->delete();

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        User::destroy($id);
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
