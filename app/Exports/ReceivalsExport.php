<?php

namespace App\Exports;

use App\Models\Receival;
use Maatwebsite\Excel\Concerns\FromCollection;

class ReceivalsExport implements FromCollection
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $eager = [
            'categories.category',
            'grower:id,grower_name',
            'dummyBuyer:id,buyer_name',
        ];
        $receivals = Receival::with($eager)->get()->map(function ($receival) {
            return [
                $receival->id,
                $receival->grower->grower_name,
                $receival->categories->where('type', 'grower-group')->first()?->category?->name,
                $receival->created_at,
                implode('; ', $receival->paddocks),
                $receival->categories->where('type', 'seed-variety')->first()?->category?->name,
                $receival->categories->where('type', 'seed-generation')->first()?->category?->name,
                $receival->categories->where('type', 'seed-class')->first()?->category?->name,
                $receival->grower_docket_no,
                $receival->chc_receival_docket_no,
                $receival->categories->where('type', 'delivery-type')->first()?->category?->name,
                $receival->categories->where('type', 'transport')->first()?->category?->name,
                $receival->driver_name,
                $receival->dummyBuyer?->buyer_name,
                $receival->comments,
            ];
        });

        return $receivals->prepend([
            'Receival ID',
            'Grower',
            'Grower Group',
            'Receival Time',
            'Paddock',
            'Seed Variety',
            'Seed Generation',
            'Seed Class',
            'Grower\'s Docket No',
            'CHC Receival Docket No',
            'Delivery Type',
            'Transport Co',
            'Driver',
            'Buyer',
            'Comments',
        ]);
    }
}
