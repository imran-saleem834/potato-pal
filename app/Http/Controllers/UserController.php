<?php

namespace App\Http\Controllers;

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
                    ->orWhereRaw("LOWER(`grower_name`) LIKE '%$keyword%'");
            })
            ->get();

        $user    = User::find($users->first()->id ?? 0);

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
         User::create($request->validated());
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::find($id);

        return response()->json($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserRequest $request, string $id)
    {
         User::find($id)->update($request->validated());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
         User::destroy($id);
    }
}
