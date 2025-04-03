<?php

namespace App\Services;

use LaravelDaily\Invoices\Invoice;
use LaravelDaily\Invoices\Classes\Party;
use LaravelDaily\Invoices\Classes\InvoiceItem;
use Illuminate\Support\Facades\Mail;
use App\Services\EmailService;

class InvoiceService
{
    /**
     * Generate an invoice and send it to the customer.
     *
     * @param array $clientData
     * @param array $customerData
     * @param array $itemsData
     * @param array $notes
     * @param string $logoPath
     * @return \LaravelDaily\Invoices\Invoice
     */
    public function generateInvoice(array $clientData, array $customerData, array $itemsData, array $notes, string $logoPath = 'vendor/invoices/sample-logo.png', $filename)
    {
        // Create the client (seller)
        $client = new Party([
            'name' => $clientData['name'],
            'phone' => $clientData['phone'] ?? '',
            'custom_fields' => $clientData['custom_fields'] ?? [],
        ]);

        // Create the customer (buyer)
        $customer = new Party([
            'name' => $customerData['name'],
            'address' => $customerData['address'] ?? '',
            'code' => $customerData['code'] ?? '',
            'custom_fields' => $customerData['custom_fields'] ?? [],
        ]);

        // Prepare invoice items
        $items = [];
        foreach ($itemsData as $item) {
            $items[] = InvoiceItem::make($item['name'])
                ->description($item['description'])
                ->pricePerUnit($item['price'])
                ->quantity($item['quantity'])
                ->discount($item['discount'] ?? 0);
        }

        // Prepare the notes
        $notes = implode("<br>", $notes);

        // Create the invoice
        $invoice = Invoice::make('receipt')
            ->series('BIG')
            ->status(__('invoices::invoice.paid'))
            ->sequence(667)
            ->serialNumberFormat('{SEQUENCE}/{SERIES}')
            ->seller($client)
            ->buyer($customer)
            ->date(now()->subWeeks(3))
            ->dateFormat('m/d/Y')
            ->payUntilDays(14)
            ->currencySymbol('XOF')
            ->currencyCode('FCFA')
            ->currencyFormat('{VALUE}{SYMBOL}')
            ->currencyThousandsSeparator('.')
            ->currencyDecimalPoint(',')
            ->filename($fileName)
            ->addItems($items)
            ->notes($notes)
            ->logo(public_path($logoPath))
            ->save('invoices');

        // Send email with the invoice link
        $link = $invoice->url();
        //$this->sendInvoiceEmail($customerData['email'], $link);
        $emailService = new EmailService();
        $emailService->sendEmailWithTemplate($customerData['email'], 'emails.teammate_confirm' , [
            'name' => $customerData['name'],      
            'appLink' => $link,   
        ]);
        return $link;
    }

   
}
