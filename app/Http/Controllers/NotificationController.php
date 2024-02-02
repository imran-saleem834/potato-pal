<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Notification;
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
            ->paginate(40);

        return Inertia::render('Notification/Index', [
            'notifications' => $notifications,
            'filters'       => $request->only(['search']),
        ]);
    }
}
