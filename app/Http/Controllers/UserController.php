<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Helpers\CategoriesHelper;
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
                return $query->where('id', 'LIKE', "%$keyword%")
                    ->orWhere('name', 'LIKE', "%$keyword%")
                    ->orWhere('email', 'LIKE', "%$keyword%")
                    ->orWhere('username', 'LIKE', "%$keyword%")
                    ->orWhere('phone', 'LIKE', "%$keyword%")
                    ->orWhere('role', 'LIKE', "%$keyword%")
                    ->orWhere('grower_name', 'LIKE', "%$keyword%")
                    ->orWhere('grower_tags', 'LIKE', "%$keyword%")
                    ->orWhere('buyer_tags', 'LIKE', "%$keyword%")
                    ->orWhere('paddocks', 'LIKE', "%$keyword%");
            })
            ->get();

        $userId = $request->input('userId', $users->first()->id ?? 0);

        $user = User::with(['categories'])->find($userId);

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

        $user = User::create($inputs);

        CategoriesHelper::createRelationOfTypes($request->only(['buyer', 'grower']), $user->id, User::class);

        return back();
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::with(['categories'])->find($id);

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

        CategoriesHelper::createRelationOfTypes($request->only(['buyer', 'grower']), $user->id, User::class);

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        CategoriesHelper::deleteCategoryRealtions($id, User::class);
        User::destroy($id);
    }
}
