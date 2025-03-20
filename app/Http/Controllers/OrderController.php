<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Clients;
use App\Models\Commandes;
use App\Models\CommandeItem;
use App\Models\Stock;
use Illuminate\Support\Facades\DB;


class OrderController extends Controller
{
    //

    public function store(Request $request)
    {
       // dd($request->all());
        // Validate the incoming request
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'products' => 'required|array',
            'products.*.product_id' => 'required|exists:stocks,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.price' => 'required|numeric|min:0',
            'products.*.discount' => 'nullable|numeric|min:0|max:100',
            'tva' => 'required|numeric|min:0',
            'payment_mode' => 'required|string',
            'invoice_status' => 'required|string',
            // Conditional validation for payment details
            'mobile_number' => 'required_if:payment_mode,mobile_money',
            'mobile_reference' => 'required_if:payment_mode,mobile_money',
            'bank_name' => 'required_if:payment_mode,bank_transfer',
            'bank_reference' => 'required_if:payment_mode,bank_transfer',
            'card_type' => 'required_if:payment_mode,credit_card',
            'card_reference' => 'required_if:payment_mode,credit_card',
            'cash_reference' => 'required_if:payment_mode,cash',
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
            
            // Commit the transaction
            DB::commit();
            
            return redirect()->back()->with('success', 'Commande créée avec succès');
        } catch (\Exception $e) {
            // Rollback the transaction if something goes wrong
            DB::rollBack();
            return back()->with('error', 'Une erreur est survenue: ' . $e->getMessage());
        }
    }
}
