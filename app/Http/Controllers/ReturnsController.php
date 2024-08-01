<?php

namespace App\Http\Controllers;

use App\Helpers\NotificationHelper;
use App\Http\Requests\ReturnRequest;
use App\Models\Allocation;
use App\Models\AllocationItem;
use App\Models\Cutting;
use App\Models\Dispatch;
use App\Models\DispatchReturn;
use App\Models\Reallocation;
use App\Models\SizingItem;
use Carbon\Carbon;

class ReturnsController extends Controller
{
    public function store(ReturnRequest $request)
    {
        $inputs = $request->validated('dispatch');

        $return = DispatchReturn::create($request->validated());

        if (! empty($request->validated('created_at'))) {
            $return->created_at = Carbon::parse($request->validated('created_at'));
            $return->save();
        }

        AllocationItem::create([
            'allocatable_type' => Dispatch::class,
            'allocatable_id'   => $inputs['id'],
            'foreignable_type' => $this->getForeignableType($inputs['dispatch_type']),
            'foreignable_id'   => $inputs['item']['foreignable']['id'],
            'returned_id'      => $return->id,
            'half_tonnes'      => $request->validated('half_tonnes') ?? 0,
            'one_tonnes'       => $request->validated('one_tonnes') ?? 0,
            'two_tonnes'       => $request->validated('two_tonnes') ?? 0,
        ]);

        NotificationHelper::addedAction('Return', $inputs['id']);

        return to_route('dispatches.index', ['buyerId' => $inputs['buyer_id']]);
    }
    
    public function update(ReturnRequest $request, $id)
    {
        $inputs = $request->validated('dispatch');

        $return = DispatchReturn::find($id);
        $return->update($request->validated());
        if (! empty($request->validated('created_at'))) {
            $return->created_at = Carbon::parse($request->validated('created_at'));
            $return->save();
        }
        $return->save();

        AllocationItem::where([
            'allocatable_type' => Dispatch::class,
            'allocatable_id'   => $inputs['id'],
            'foreignable_type' => $this->getForeignableType($inputs['dispatch_type']),
            'foreignable_id'   => $inputs['item']['foreignable']['id'],
            'returned_id'      => $return->id,
        ])->update([
            'half_tonnes'      => $request->validated('half_tonnes') ?? 0,
            'one_tonnes'       => $request->validated('one_tonnes') ?? 0,
            'two_tonnes'       => $request->validated('two_tonnes') ?? 0,
        ]);

        NotificationHelper::updatedAction('Return', $inputs['id']);

        return to_route('dispatches.index', ['buyerId' => $inputs['buyer_id']]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $return = DispatchReturn::find($id);
        $item = AllocationItem::query()
            ->where('allocatable_type', Dispatch::class)
            ->where('returned_id', $return->id)
            ->first();
        
        $dispatch = Dispatch::select(['id', 'buyer_id'])->find($item->allocatable_id);
        $item->delete();
        $return->delete();

        NotificationHelper::deleteAction('returns', $id);

        return to_route('dispatches.index', ['buyerId' => $dispatch->buyer_id]);
    }

    private function getForeignableType($type)
    {
        if ($type === 'reallocation') {
            return Reallocation::class;
        } elseif ($type === 'cutting') {
            return Cutting::class;
        } elseif ($type === 'sizing') {
            return SizingItem::class;
        }

        return Allocation::class;
    }
}
