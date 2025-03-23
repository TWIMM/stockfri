<?php

namespace App\Http\Controllers;

use App\Services\InvoiceService;
use App\Models\Invoice;

class InvoiceController extends Controller
{
    protected $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    public function generateInvoice()
    {
        $clientData = [
            'name' => 'Roosevelt Lloyd',
            'phone' => '(520) 318-9486',
            'custom_fields' => [
                'note' => 'IDDQD',
                'business id' => '365#GG',
            ],
        ];

        $customerData = [
            'name' => 'Ashley Medina',
            'address' => 'The Green Street 12',
            'code' => '#22663214',
            'custom_fields' => [
                'order number' => '> 654321 <',
            ],
            'email' => 'customer@example.com',
        ];

        $itemsData = [
            [
                'name' => 'Service 1',
                'description' => 'Your product or service description',
                'price' => 47.79,
                'quantity' => 2,
                'discount' => 10,
            ],
        ];

        $notes = [
            'your multiline',
            'additional notes',
            'in regards of delivery or something else',
        ];

        dd($this->invoiceService->generateInvoice($clientData, $customerData, $itemsData, $notes));
        return $this->invoiceService->generateInvoice($clientData, $customerData, $itemsData, $notes);
    }

    public function retrieveUrl($id){
        $invoice = Invoice::where('commande_id',  $id)->first();


        return response()->json([
            'invoicesurl' => $invoice->invoice_link,
        ]);
    }
}
