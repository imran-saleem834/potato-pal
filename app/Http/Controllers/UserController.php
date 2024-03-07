<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Helpers\CategoriesHelper;
use App\Http\Requests\UserRequest;
use App\Helpers\NotificationHelper;
use Illuminate\Database\Eloquent\Builder;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $users = User::select('id', 'name', 'email')
            ->when($request->input('search'), function (Builder $query, $search) {
                return $query->where(function (Builder $subQuery) use ($search) {
                    return $subQuery
                        ->where('id', 'LIKE', "%$search%")
                        ->orWhere('name', 'LIKE', "%$search%")
                        ->orWhere('email', 'LIKE', "%$search%")
                        ->orWhere('username', 'LIKE', "%$search%")
                        ->orWhere('phone', 'LIKE', "%$search%")
                        ->orWhere('role', 'LIKE', "%$search%")
                        ->orWhere('grower_name', 'LIKE', "%$search%")
                        ->orWhere('grower_tags', 'LIKE', "%$search%")
                        ->orWhere('buyer_name', 'LIKE', "%$search%")
                        ->orWhere('buyer_tags', 'LIKE', "%$search%")
                        ->orWhere('paddocks', 'LIKE', "%$search%")
                        ->orWhereRelation('categories.category', function (Builder $catQuery) use ($search) {
                            return $catQuery->where('name', 'LIKE', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString()
            ->onEachSide(1);

        $userId = $request->input('userId', $users->items()[0]->id ?? 0);

        return Inertia::render('User/Index', [
            'users'      => $users,
            'single'     => $this->getUser($userId),
            'categories' => Category::whereIn('type', User::CATEGORY_TYPES)->get(),
            'filters'    => $request->only(['search']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserRequest $request)
    {
        $inputs = $request->validated();
        $inputs = array_merge($inputs, [
            'password'          => bcrypt($request->validated('password')),
            'email_verified_at' => now(),
        ]);
        $user = User::create($inputs);

        $categories = $request->only(User::CATEGORY_INPUTS);
        CategoriesHelper::createRelationOfTypes($categories, $user->id, User::class);

        NotificationHelper::addedAction('User', $user->id);

        return to_route('users.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return response()->json($this->getUser($id));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserRequest $request, string $id)
    {
        $inputs = $request->validated();

        if ($request->filled('password')) {
            $inputs = array_merge($inputs, ['password' => bcrypt($request->validated('password'))]);
        } else {
            unset($inputs['password']);
        }

        $user = User::find($id);
        $user->update($inputs);
        $user->save();

        $categories = $request->only(User::CATEGORY_INPUTS);

        CategoriesHelper::createRelationOfTypes($categories, $user->id, User::class);

        NotificationHelper::updatedAction('User', $id);

        return to_route('users.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        CategoriesHelper::deleteCategoryRelations($id, User::class);

        User::destroy($id);

        NotificationHelper::deleteAction('User', $id);

        return to_route('users.index');
    }

    private function getUser($id)
    {
        return User::with(['categories.category'])->find($id);
    }
}
