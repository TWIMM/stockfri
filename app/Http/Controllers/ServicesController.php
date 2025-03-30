<?php

namespace App\Http\Controllers;

use App\Models\Services;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Clients;
use App\Models\Stock;
use App\Models\Commandes;
use App\Models\CommandeItem;

class ServicesController extends Controller
{
    public function index()
    {
        if(auth()->user()->type === 'team_member'){
            return redirect()->route('dashboard_team_member');
        }
        $user = Auth::user();
        $hasPhysique = $user->business()->where('type', 'business_physique')->exists();
        $hasPrestation = $user->business()->where('type', 'prestation_de_service')->exists();
        $businesses = $user->business; 
        $clients = Clients::where('user_id' ,$user->id)->get();
        // Get all services related to the user's businesses
        $servicesAll = Services::whereIn('business_id', $user->business->pluck('id'))->get();
        // Get services related to the user's businesses with pagination
        $services = Services::whereIn('business_id', $user->business->pluck('id'))->paginate(10);

        return view('users.services.index', compact('services','hasPhysique', 
            'hasPrestation', "businesses",  'user' , 'clients' , 'servicesAll'));
    }

    public function order(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            //'magasin_id' => 'required|exists:magasins,id',
            'products' => 'required|array',
            'products.*.product_id' => 'required|exists:stocks,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.price' => 'required|numeric|min:0',
            'products.*.discount' => 'nullable|numeric|min:0|max:100',
            'tva' => 'required|numeric|min:0',
            //'already_paid' => 'required|string',
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
            $totalOrderPrices = 0;

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
                
                $totalOrderPrices += $itemTotal;
            }
            
            // Apply TVA to total price
            $tvaAmount = $totalOrderPrices * ($request->tva / 100);
            $totalWithTva = $totalOrderPrices + $tvaAmount;
            
            if($request->already_paid)
            {
                if($request->already_paid > $totalWithTva){
                    return back()->with('error', 'Une erreur est survenue: ' . 'Le montant payé ne peut depasser le total de la facture');

                }
            }            
            // Create the order
            $commande = Commandes::create([
                'client_id' => $request->client_id,
                'total_price' => 0, // Will calculate after adding items
                'payment_mode' => $request->payment_mode,
                'invoice_status' => $request->invoice_status,
                'tva' => $request->tva,
                //'magasin_id' => $request->magasin_id,
                // Payment details based on payment mode
                'mobile_number' => $request->mobile_number,
                'mobile_reference' => $request->mobile_reference,
                'bank_name' => $request->bank_name,
                'bank_reference' => $request->bank_reference,
                'card_type' => $request->card_type,
                'card_reference' => $request->card_reference,
                'cash_reference' => $request->cash_reference,
                'user_id' => auth()->id(),
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
                    'service_id' => $productData['product_id'],
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'discount' => $discount,
                    'total_price' => $itemTotal
                ]);
                
                
                $totalOrderPrice += $itemTotal;
            }
            
            // Apply TVA to total price
            $tvaAmount = $totalOrderPrice * ($request->tva / 100);
            $totalWithTva = $totalOrderPrice + $tvaAmount;
            
            // Update the order with the calculated total price
            $commande->total_price = $totalWithTva;
            if($request->invoice_status === 'unpaid'){
                $already_paid = 0.00; 
                $rest_to_pay = $commande->total_price; 
                $statutValidation = 'not_approved';
            } else if ($request->invoice_status  === 'partially_paid') {
                $already_paid = floatval($request->already_paid ?? 0); 
                $rest_to_pay = $commande->total_price - $already_paid; 
                $statutValidation = 'not_approved';
            }
            else if ($request->invoice_status  === 'paid') {
                $already_paid = $commande->total_price; 
                $statutValidation = 'approved';
                $rest_to_pay = 0.00; 
            }
            $commande->already_paid = $already_paid;
            $commande->rest_to_pay = $rest_to_pay;
            $commande->validation_status = $statutValidation;
            $commande->save();

            if($statutValidation === 'approved'){
                $invoice = $this->generateInvoiceFromOrder($commande);
                return redirect()->back()->with('success', 'Commande créée avec succès.' . $invoice->invoice_link);
            }

            
            // Commit the transaction
            DB::commit();
            
            return redirect()->back()->with('success', 'Pré-Commande créée avec succès.' );
        } catch (\Exception $e) {
            // Rollback the transaction if something goes wrong
            DB::rollBack();
            return back()->with('error', 'Une erreur est survenue: ' . $e->getMessage());
        }
    }

    public function showOwnerServicesListPage()
    {
        if(auth()->user()->type === 'client'){
            return redirect()->route('dashboard');
        }

        //$services = Services::where('business_id', auth()->user()->business_id)->get();
        return view('dashboard_team_member.services.owner_index', compact('services'));
    }

    public function create()
    {
        if(auth()->user()->type === 'client'){
            return redirect()->route('dashboard');
        }

        return view('dashboard_team_member.services.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|unique:services,title|string|max:255',
            'description' => 'required|string|max:1000',
            'business_id' => 'exists:business,id',
            'price' => 'required|numeric',
        ]);

        $business = Business::find($request->business_id); 

        if(!$business || $business->type === 'business_physique'){
            return back()->with('error', 'Le business est un business physique');
        }

        $service = new Services();
        //$service->business_id = auth()->user()->business_id;
        $service->title = $request->title;
        $service->description = $request->description;
        $service->price = $request->price;
        $service->quantity = 0;
        $service->business_id = $request->business_id;
        $service->save();

        return back()->with('success', 'Service created successfully');
    }



    public function edit($id)
    {
        $service = Services::findOrFail($id);

        return response()->json([
            'service'       => $service,
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'price' => 'required|numeric',
        ]);

        $service = Services::findOrFail($id);
        $service->title = $request->title;
        $service->description = $request->description;
        $service->price = $request->price;

        $service->save();

        return back()->with('success', 'Service updated successfully');
    }

    public function destroy($id)
    {
        $service = Services::findOrFail($id);
        $service->delete();

        return back()->with('success', 'Service deleted successfully');
    }
}
