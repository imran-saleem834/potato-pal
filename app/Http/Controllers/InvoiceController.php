<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Grade;
use App\Models\Cutting;
use App\Models\Invoice;
use App\Models\Receival;
use Illuminate\Http\Request;
use Dcblogdev\Xero\Facades\Xero;
use App\Helpers\NotificationHelper;
use App\Http\Requests\InvoiceRequest;
use Illuminate\Database\Eloquent\Builder;

class InvoiceController extends Controller
{
    /**
     * @param  Request  $request
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // 9739F7D32A684FAD8378E3E06BED2D9A
        // bQecDVAQuQv5d8f7NgG1XSQvzv5qizXGrjUouFHeOZJCphDM
        $invoices = Invoice::query()
            // ->when($request->input('search'), function (Builder $query, $search) {
            //     return $query->where(function (Builder $subQuery) use ($search) {
            //         return $subQuery->search($search);
            //     });
            // })
            ->latest()
            ->paginate(20)
            ->withQueryString()
            ->onEachSide(1);

        $invoiceId = $request->input('invoiceId', $invoices->items()[0]->id ?? 0);

        return Inertia::render('Invoice/Index', [
            'invoices'  => $invoices,
            'single'    => $this->getInvoice($invoiceId),
            'receivals' => $this->getReceivals(),
            'grades'    => $this->getGrades(),
            'cuttings'  => $this->getCuttings(),
            'filters'   => $request->only(['search']),
        ]);
    }

    private function getReceivals()
    {
        return Receival::query()
            ->with(['grower' => fn ($query) => $query->select(['id', 'grower_name'])])
            ->get();
    }

    private function getGrades()
    {
        return Grade::query()
            ->with([
                'unload:id,receival_id',
                'unload.receival:id,grower_id',
                'unload.receival.grower:id,grower_name',
            ])
            ->get();
    }

    private function getCuttings()
    {
        return Cutting::query()
            ->with(['buyer' => fn ($query) => $query->select(['id', 'buyer_name'])])
            ->get();
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $invoice = $this->getInvoice($id);

        return response()->json($invoice);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(InvoiceRequest $request)
    {
        $invoice = Invoice::create($request->validated());

        $this->createInvoice($invoice);

        NotificationHelper::addedAction('Invoice', $invoice->id);

        return to_route('invoices.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(InvoiceRequest $request, string $id)
    {
        $invoice = Invoice::find($id);
        $invoice->update($request->validated());
        $invoice->save();

        NotificationHelper::updatedAction('Invoice', $id);

        return to_route('invoices.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Invoice::destroy($id);

        NotificationHelper::deleteAction('Invoice', $id);

        return to_route('invoices.index');
    }

    private function getInvoice($invoiceId)
    {
        return Invoice::with(['invoiceable'])->find($invoiceId);
    }

    private function createInvoice($invoice)
    {
        // $xeroContactId = $this->getXeroContactId();
        $xeroInvoice = Xero::invoices()->store([
            'Type'            => 'ACCREC',
            // 'CurrencyCode'    => 'USD',
            'Contact'         => [
                // 'ContactID' => $xeroContactId,
                'ContactID' => '70ce1193-fd7e-4fd4-b4da-cf39d19dcf09',
            ],
            'Status'          => 'AUTHORISED',
            'Date'            => now()->format('Y-m-d'),
            'DueDate'         => now()->addDays(7)->format('Y-m-d'),
            'LineAmountTypes' => 'Exclusive',
            'LineItems'       => [
                [
                    'Description' => 'Cutting amount',
                    'Quantity'    => 1,
                    'UnitAmount'  => $invoice?->amount ?? 10,
                    'AccountCode' => 200,
                    'TaxType'     => 'NONE',

                    //                            <ItemCode>".$line_item['ItemCode']."</ItemCode>
                    //					        <Description>".$line_item['Description']."</Description>
                    //							<UnitAmount>".$line_item['UnitAmount']."</UnitAmount>
                    //							<TaxType>".$line_item['TaxType']."</TaxType>
                    //							<TaxAmount>".$line_item['TaxAmount']."</TaxAmount>
                    //		  					<LineAmount>".$line_item['LineAmount']."</LineAmount>
                    //		                    <AccountCode>".$line_item['AccountCode']."</AccountCode>
                    //							<Quantity>".$line_item['Quantity']."</Quantity>
                    //							<LineItemID>".$line_item['LineItemID']."</LineItemID>

                    //                            <ItemCode>".$Code."</ItemCode>
                    //					        <Description>".$Description."</Description>
                    //							<UnitAmount>".$UnitPrice."</UnitAmount>
                    //							<TaxType>".$TaxType."</TaxType>
                    //		  					<LineAmount>".$LineAmount."</LineAmount>
                    //		                    <AccountCode>".$AccountCode."</AccountCode>
                    //							<Quantity>".$invoice_net_weight."</Quantity>
                ],
            ],
        ]);

        if ($xeroInvoice) {
            $invoice->invoice_id     = $xeroInvoice['InvoiceID'];
            $invoice->invoice_number = $xeroInvoice['InvoiceNumber'];
            $invoice->invoice_url    = Xero::invoices()->onlineUrl($xeroInvoice['InvoiceID']);
            $invoice->save();

            if (config('xero.emailingInvoiceStatus')) {
                Xero::post('Invoices/'.$xeroInvoice['InvoiceID'].'/Email');
            }
        }
    }

    private function getXeroContactId()
    {
        $user = auth()->user();

        if ($user->xero_contact_id) {
            return $user->xero_contact_id;
        }

        $xeroContact = Xero::contacts()->store([
            'name'          => $user->name,
            'ContactStatus' => 'ACTIVE',
            'EmailAddress'  => $user->email,
            'IsCustomer'    => true,
            'IsSupplier'    => false,
        ]);

        if ($xeroContact) {
            $user->update(['xero_contact_id' => $xeroContact['ContactID']]);
        }

        return $xeroContact['ContactID'];
    }
}
