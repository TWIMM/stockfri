<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Clients;
use App\Models\Commandes;
use App\Models\CommandeItem;
use App\Models\Stock;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;
use App\Services\InvoiceService;
use Carbon\Carbon;

class OrderController extends Controller
{
    protected $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    /**
     * Generate an invoice for an existing order
     * 
     * @param int $commande_id
     * @return \Illuminate\Http\Response
     */
    public function generateOrderInvoice($commande_id)
    {
        try {
            $commande = Commandes::with(['client', 'items.stock'])->findOrFail($commande_id);
            
            // Generate and save invoice
            $invoice = $this->generateInvoiceFromOrder($commande);
            
            return redirect()->back()->with('success', 'Facture générée avec succès. Lien: ' . $invoice->invoice_link);
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la génération de la facture: ' . $e->getMessage());
        }
    }

    /**
     * Helper method to generate invoice from order
     * 
     * @param Commandes $commande
     * @return Invoice
     */
    private function generateInvoiceFromOrder(Commandes $commande)
    {
        // Format client data
        $clientData = [
            'name' => $commande->client->name,
            'phone' => $commande->client->phone,
            'custom_fields' => [
                'ID Client' => $commande->client->id,
                'Email' => $commande->client->email ?? '',
            ],
        ];

        // Format customer data (using the same client data for now)
        $customerData = [
            'name' => $commande->client->name,
            'address' => $commande->client->address ?? '',
            'code' => 'CMD-' . $commande->id,
            'custom_fields' => [
                'Date Commande' => $commande->created_at->format('d/m/Y'),
            ],
            'email' => $commande->client->email ?? '',
        ];

        // Format items data
        $itemsData = [];
        foreach ($commande->commandeItems as $item) {
            $itemsData[] = [
                'name' => $item->stock->name,
                'description' => $item->stock->description ?? 'Produit',
                'price' => $item->unit_price,
                'quantity' => $item->quantity,
                'discount' => $item->discount,
            ];
        }

        // Add notes
        $notes = [
            'Mode de paiement: ' . $this->getPaymentModeLabel($commande->payment_mode),
            'TVA: ' . $commande->tva . '%',
            'Statut: ' . $this->getInvoiceStatusLabel($commande->invoice_status),
        ];

        // Generate the invoice using the service
        $invoiceLink = $this->invoiceService->generateInvoice($clientData, $customerData, $itemsData, $notes);
        
        // Create invoice record in database
        $invoiceNumber = 'INV-' . date('Ymd') . '-' . $commande->id;
        
        $invoice = Invoice::create([
            'commande_id' => $commande->id,
            'invoice_number' => $invoiceNumber,
            'invoice_date' => Carbon::now(),
            'invoice_link' => $invoiceLink,
            'total_amount' => $commande->total_price,
            'status' => $this->mapInvoiceStatus($commande->invoice_status)
        ]);
        
        return $invoice;
    }
    
    /**
     * Map invoice status from order status
     */
    private function mapInvoiceStatus($orderStatus)
    {
        switch ($orderStatus) {
            case 'paid':
                return 'paid';
            case 'partially_paid':
                return 'partial';
            case 'unpaid':
                return 'pending';
            default:
                return 'generated';
        }
    }
    
    /**
     * Get user-friendly payment mode label
     */
    private function getPaymentModeLabel($mode)
    {
        $modes = [
            'cash' => 'Espèces',
            'credit_card' => 'Carte de crédit',
            'mobile_money' => 'Mobile Money',
            'bank_transfer' => 'Virement bancaire'
        ];
        
        return $modes[$mode] ?? $mode;
    }
    
    /**
     * Get user-friendly invoice status label
     */
    private function getInvoiceStatusLabel($status)
    {
        $statuses = [
            'paid' => 'Payée',
            'partially_paid' => 'Partiellement payée',
            'unpaid' => 'Non payée'
        ];
        
        return $statuses[$status] ?? $status;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'products' => 'required|array',
            'products.*.product_id' => 'required|exists:stocks,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.price' => 'required|numeric|min:0',
            'products.*.discount' => 'nullable|numeric|min:0|max:100',
            'tva' => 'required|numeric|min:0',
            'payment_mode' => 'required|string',
            'invoice_status' => 'required|string|in:paid,partially_paid,unpaid',
            
            // Mobile Money payment details
            'mobile_number' => [
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->payment_mode === 'mobile_money' && 
                        in_array($request->invoice_status, ['paid', 'partially_paid']) && 
                        empty($value)) {
                        $fail('Le numéro mobile est requis pour les paiements par mobile money lorsque la facture est payée ou partiellement payée.');
                    }
                }
            ],
            'mobile_reference' => [
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->payment_mode === 'mobile_money' && 
                        in_array($request->invoice_status, ['paid', 'partially_paid']) && 
                        empty($value)) {
                        $fail('La référence mobile est requise pour les paiements par mobile money lorsque la facture est payée ou partiellement payée.');
                    }
                }
            ],
            
            // Bank Transfer payment details
            'bank_name' => [
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->payment_mode === 'bank_transfer' && 
                        in_array($request->invoice_status, ['paid', 'partially_paid']) && 
                        empty($value)) {
                        $fail('Le nom de la banque est requis pour les virements bancaires lorsque la facture est payée ou partiellement payée.');
                    }
                }
            ],
            'bank_reference' => [
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->payment_mode === 'bank_transfer' && 
                        in_array($request->invoice_status, ['paid', 'partially_paid']) && 
                        empty($value)) {
                        $fail('La référence bancaire est requise pour les virements bancaires lorsque la facture est payée ou partiellement payée.');
                    }
                }
            ],
            
            // Credit Card payment details
            'card_type' => [
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->payment_mode === 'credit_card' && 
                        in_array($request->invoice_status, ['paid', 'partially_paid']) && 
                        empty($value)) {
                        $fail('Le type de carte est requis pour les paiements par carte de crédit lorsque la facture est payée ou partiellement payée.');
                    }
                }
            ],
            'card_reference' => [
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->payment_mode === 'credit_card' && 
                        in_array($request->invoice_status, ['paid', 'partially_paid']) && 
                        empty($value)) {
                        $fail('La référence de carte est requise pour les paiements par carte de crédit lorsque la facture est payée ou partiellement payée.');
                    }
                }
            ],
            
            // Cash payment details
            'cash_reference' => [
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->payment_mode === 'cash' && 
                        in_array($request->invoice_status, ['paid', 'partially_paid']) && 
                        empty($value)) {
                        $fail('La référence du paiement en espèces est requise lorsque la facture est payée ou partiellement payée.');
                    }
                }
            ],
        ]);
        
        try {
            DB::beginTransaction();
            
            // Create the order
            $commande = Commandes::create([
                'client_id' => $request->client_id,
                'total_price' => 0, // Will calculate after adding items
                'payment_mode' => $request->payment_mode,
                'invoice_status' => $request->invoice_status,
                'tva' => $request->tva,
                // Payment details based on payment mode
                'mobile_number' => $request->mobile_number,
                'mobile_reference' => $request->mobile_reference,
                'bank_name' => $request->bank_name,
                'bank_reference' => $request->bank_reference,
                'card_type' => $request->card_type,
                'card_reference' => $request->card_reference,
                'cash_reference' => $request->cash_reference,
            ]);
            
            $totalOrderPrice = 0;
            
            // Process each product item
            foreach ($request->products as $productData) {
                $stock = Stock::findOrFail($productData['product_id']);
                
                // Calculate item total price with discount
                $unitPrice = $productData['price'];
                $quantity = $productData['quantity'];
                $discount = isset($productData['discount']) ? $productData['discount'] : 0;
                $itemTotal = $unitPrice * $quantity;

                if ($discount > 99) {
                    return back()->with('error', 'Une erreur est survenue: Vous offrez des reduction avoisinant 99,99%');

                }
                
                if ($discount > 0) {
                    $itemTotal = $itemTotal - ($itemTotal * ($discount / 100));
                }
                
                // Add the order item
                $commandeItem = CommandeItem::create([
                    'commande_id' => $commande->id,
                    'stock_id' => $productData['product_id'],
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'discount' => $discount,
                    'total_price' => $itemTotal
                ]);
                
                // Reduce stock quantity
                $stock->quantity -= $quantity;
                $stock->save();
                
                $totalOrderPrice += $itemTotal;
            }
            
            // Apply TVA to total price
            $tvaAmount = $totalOrderPrice * ($request->tva / 100);
            $totalWithTva = $totalOrderPrice + $tvaAmount;
            
            // Update the order with the calculated total price
            $commande->total_price = $totalWithTva;
            $commande->save();
            
            // Generate the invoice for this order
            $invoice = $this->generateInvoiceFromOrder($commande);
            
            // Commit the transaction
            DB::commit();
            
            return redirect()->back()->with('success', 'Commande créée avec succès. Facture générée: ' . $invoice->invoice_link);
        } catch (\Exception $e) {
            // Rollback the transaction if something goes wrong
            DB::rollBack();
            return back()->with('error', 'Une erreur est survenue: ' . $e->getMessage());
        }
    }
}