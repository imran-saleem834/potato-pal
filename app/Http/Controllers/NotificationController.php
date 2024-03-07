<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $notifications = Notification::query()
            ->when($request->input('search'), function (Builder $query, $search) {
                return $query->where(function (Builder $subQuery) use ($search) {
                    return $subQuery->where('id', 'LIKE', "%$search%")
                        ->orWhere('action', 'LIKE', "%$search%")
                        ->orWhere('notification', 'LIKE', "%$search%");
                });
            })
            ->latest()
            ->paginate(40)
            ->onEachSide(1);

        return Inertia::render('Notification/Index', [
            'notifications' => $notifications,
            'filters'       => $request->only(['search']),
        ]);
    }
}
