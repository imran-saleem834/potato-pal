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
                return $query->where('id', 'LIKE', "%$search%")
                    ->orWhere('action', 'LIKE', "%$search%")
                    ->orWhere('notification', 'LIKE', "%$search%");
            })
            ->latest()
            ->get();

        return Inertia::render('Notification/Index', [
            'notifications' => $notifications,
            'filters'       => $request->only(['search']),
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request)
    {
        $keyword       = $request->input('keyword', '');
        $notifications = Notification::query()
            ->when($keyword, function ($query, $keyword) {
                return $query->where('id', 'LIKE', "%$keyword%")
                    ->orWhere('action', 'LIKE', "%$keyword%")
                    ->orWhere('notification', 'LIKE', "%$keyword%");
            })
            ->latest()
            ->get();

        return response()->json($notifications);
    }
}
