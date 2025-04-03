<?php

namespace App\Http\Controllers;

use App\Models\Services;
use App\Models\Clients;
use App\Models\Stock;
use App\Models\Commandes;
use App\Models\CommandeItem;
use App\Services\InvoiceService;
use App\Models\Invoice;
use Carbon\Carbon;
use App\Models\Magasins;
use App\Models\Team;
use App\Models\Business;
use App\Services\EmailService;

use App\Models\User;
use App\Models\Role;
use App\Models\TeamMember;
use App\Models\Fournisseur;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
class ServicesController extends Controller
{
    protected $invoiceService;
    protected $emailService;
    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
        $this->emailService = $emailService;

    }

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

    private function getInvoiceStatusLabel($status)
    {
        $statuses = [
            'paid' => 'Payée',
            'partially_paid' => 'Partiellement payée',
            'unpaid' => 'Non payée'
        ];
        
        return $statuses[$status] ?? $status;
    }

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
                'name' => $item->service->title,
                'description' => $item->service->description ?? 'Produit',
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

        $fileName = 'CMD-'. date('Ymd') . '-' . $commande->id;

        // Generate the invoice using the service
        $invoiceLink = $this->invoiceService->generateInvoice($clientData, $customerData, $itemsData, $notes , 'vendor/invoices/sample-logo.png' , $fileName);
        
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

    public function index()
    {
        if(auth()->user()->type === 'team_member'){
            //return redirect()->route('dashboard_team_member');

            $teamMember = Auth::user();
            $realTeamMember = TeamMember::firstWhere('email', $teamMember->email);

            
            $team = $realTeamMember->team()->first();  // ✅ Correct way to access the relationship
            /*  if (!$team) {
                abort(404, "Vous n'etes membre d'aucune equipe");
            } */

            
            //$intBus = Business::find(optional($team->pivot)->business_id);
            
            $teamBusinessOwner = User::findOrFail($realTeamMember->user_id);  // ✅ Correct way

            
            $clientOwner = User::findOrFail($realTeamMember->user_id);

            $businesses = $teamBusinessOwner->business()->paginate(10);
            $hasPhysique = $teamBusinessOwner->business()->where('type', 'business_physique')->exists();
            $hasPrestation = $teamBusinessOwner->business()->where('type', 'prestation_de_service')->exists();
            view()->share('realTeamMember', $realTeamMember);


            $role = Role::find($realTeamMember->role_id);
            $formattedRole = $role ? Str::title(str_replace('_', ' ', $role->name)) : 'Unknown';

            $role = Role::find($realTeamMember->role_id);
            view()->share('role', $formattedRole);
            view()->share('roleObj', $role);
            $clients = Clients::where('user_id' ,$teamBusinessOwner->id)->get();
            // Get all services related to the user's businesses
            $servicesAll = Services::whereIn('business_id', $teamBusinessOwner->business->pluck('id'))->get();
            // Get services related to the user's businesses with pagination
            $services = Services::whereIn('business_id', $teamBusinessOwner->business->pluck('id'))->paginate(10);
            $user = User::where('email' , $realTeamMember->email)->first();
            return view('dashboard_team_member.services.index', compact('services' , 'user','hasPhysique', 
                'hasPrestation', "businesses",  'realTeamMember' , 'clients' , 'servicesAll'));

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
            $isAuthTeamMemberQuestionMark = User::find(Auth::id());

            if($isAuthTeamMemberQuestionMark->type === 'client'){
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
            }else if ($isAuthTeamMemberQuestionMark->type === 'team_member') {
                
                $test = TeamMember::where('email' ,  $isAuthTeamMemberQuestionMark->email)->first();
                //dd(Auth::id());
                //dd( $test->user_id);
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
                    'user_id' => $test->user_id,
                ]);
                
            }
            
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

            $filePaths = [];

            if ($request->hasFile('factures_achat')) {
                foreach ($request->file('factures_achat') as $file) {
                    // Get the command ID and current timestamp
                    $commandeId = $commande->id; // Assuming you have the commande_id in the request
                    $createdAt = $commande->created_at; // Format: YYYY-MM-DD_HH-MM-SS
                    
                    // Create a custom filename using the commande ID and creation time
                    $filename = $commandeId . '_' . $createdAt . '.' . $file->getClientOriginalExtension();
                    
                    // Store the file with the custom name in the 'proof_client_achat' directory
                    $filePath = $file->storeAs('proof_client_achat', $filename, 'public');
                    
                    // Save the file path in the array for later use
                    $filePaths[] = $filePath;
                }
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

    public function getPrecommandes(){
        if(auth()->user()->type === 'team_member'){
            //return redirect()->route('dashboard_team_member');

            $teamMember = Auth::user();
            $realTeamMember = TeamMember::firstWhere('email', $teamMember->email);

            
            $team = $realTeamMember->team()->first();  // ✅ Correct way to access the relationship
            /*  if (!$team) {
                abort(404, "Vous n'etes membre d'aucune equipe");
            } */

            
            //$intBus = Business::find(optional($team->pivot)->business_id);
            
            $teamBusinessOwner = User::findOrFail($realTeamMember->user_id);  // ✅ Correct way

            
            $clientOwner = User::findOrFail($realTeamMember->user_id);

            $businesses = $teamBusinessOwner->business()->paginate(10);
            $hasPhysique = $teamBusinessOwner->business()->where('type', 'business_physique')->exists();
            $hasPrestation = $teamBusinessOwner->business()->where('type', 'prestation_de_service')->exists();
            view()->share('realTeamMember', $realTeamMember);


            $role = Role::find($realTeamMember->role_id);
            $formattedRole = $role ? Str::title(str_replace('_', ' ', $role->name)) : 'Unknown';

            $role = Role::find($realTeamMember->role_id);
            view()->share('role', $formattedRole);
            view()->share('roleObj', $role);



            //start query #



            $categories = $teamBusinessOwner->categorieProduits; 
            $fournisseurs = $teamBusinessOwner->fournisseurs; 
            $getClientFromId = function($id) {
                return Clients::find($id);
            };

            $query = Commandes::whereHas('commandeItems', function ($query) {
                $query->whereNull('stock_id'); // Filters CommandItems where stock_id is null
            })
            ->where('user_id', $teamBusinessOwner->id) // Filters by the user ID
            ->where('validation_status', 'not_approved');
            
            // Apply filters
            // Filter by client if provided
            if ($clientId = request('client')) {
                $query->where('client_id', $clientId);
            }

            // Filter by delivery status if provided
            if ($status = request('status')) {
                if ($status !== 'none') { // Avoid 'none' as it implies no filter
                    $query->where('invoice_status', $status);
                }
            }

            // Filter by price range if provided
            if ($minPrice = request('min_price')) {
                $query->where('total_price', '>=', $minPrice);
            }

            if ($maxPrice = request('max_price')) {
                $query->where('total_price', '<=', $maxPrice);
            }

            // Filter by date range if provided
            if ($dateStart = request('date_start')) {
                $query->where('created_at', '>=', $dateStart);
            }

            if ($dateEnd = request('date_end')) {
                $query->where('created_at', '<=', $dateEnd);
            }

            // Get the filtered commandes and paginate the results
            $commandeNotApproved = $query->paginate(10);

            // Get clients, magasins, and other necessary data
            $clients = Clients::where('user_id', $teamBusinessOwner->id)->get();
            $magasins = Magasins::where('user_id', $teamBusinessOwner->id)->paginate(10);
            $stocks = Stock::where('user_id', $teamBusinessOwner->id)->paginate(10);

            // Helper functions for client data (not changed)
            $getBadge = function($riskLevel) {
                switch ($riskLevel) {
                    case 'Très faible':
                        return '<span class="badge badge-pill badge-status bg-success">Très faible</span>';
                    case 'Faible':
                        return '<span class="badge badge-pill badge-status bg-info">Faible</span>';
                    case 'Moyen':
                        return '<span class="badge badge-pill badge-status bg-warning">Moyen</span>';
                    case 'Élevé':
                        return '<span class="badge badge-pill badge-status bg-danger">Élevé</span>';
                    case 'Très élevé':
                        return '<span class="badge badge-pill badge-status bg-dark">Très élevé</span>';
                    default:
                        return '<span class="badge badge-pill badge-status bg-secondary">Inconnu</span>';
                }
            };

            $getClientScoreDataByClientId = function ($id, $dataToReturn = null) {
                $client = Clients::find($id);

                if (!$client) {
                    return null;
                }

                $allData = [
                    'credit_score' => $client->credit_score,
                    'risk_level' => $client->getRiskLevel(),
                    'available_credit' => $client->credit_limit - $client->current_debt,
                    'credit_limit' => $client->credit_limit,
                    'current_debt' => $client->current_debt,
                    'last_score_update' => $client->last_score_update
                ];

                // If a specific data is requested, return that
                if ($dataToReturn && array_key_exists($dataToReturn, $allData)) {
                    return $allData[$dataToReturn];
                }

                // Return all data if no specific key is provided
                return (object)$allData;
            };
            $user = User::where('email' , $realTeamMember->email)->first();
            $services = Services::whereIn('business_id', $teamBusinessOwner->business->pluck('id'))->paginate(10);
            view()->share('services', $services);
            view()->share('realTeamMember', $realTeamMember);
            return view('dashboard_team_member.services.commande_not_approved', compact(
                'commandeNotApproved', 'hasPhysique', 'hasPrestation', 
                'getClientScoreDataByClientId', 'getBadge', 'magasins', 'businesses', 
                'services' , 'user', 'getClientFromId', 'realTeamMember', 'clients', 'categories', 'fournisseurs'
            ));


        }

        $user = Auth::user();
        $hasPhysique = $user->business()->where('type', 'business_physique')->exists();
        $hasPrestation = $user->business()->where('type', 'prestation_de_service')->exists();
        $businesses = $user->business; 
        $categories = $user->categorieProduits; 
        $fournisseurs = $user->fournisseurs; 
        $getClientFromId = function($id) {
            return Clients::find($id);
        };

        $query = Commandes::whereHas('commandeItems', function ($query) {
            $query->whereNull('stock_id'); // Filters CommandItems where stock_id is null
        })
        ->where('user_id', $user->id) // Filters by the user ID
        ->where('validation_status', 'not_approved');
        
        // Apply filters
        // Filter by client if provided
        if ($clientId = request('client')) {
            $query->where('client_id', $clientId);
        }

        // Filter by delivery status if provided
        if ($status = request('status')) {
            if ($status !== 'none') { // Avoid 'none' as it implies no filter
                $query->where('invoice_status', $status);
            }
        }

        // Filter by price range if provided
        if ($minPrice = request('min_price')) {
            $query->where('total_price', '>=', $minPrice);
        }

        if ($maxPrice = request('max_price')) {
            $query->where('total_price', '<=', $maxPrice);
        }

        // Filter by date range if provided
        if ($dateStart = request('date_start')) {
            $query->where('created_at', '>=', $dateStart);
        }

        if ($dateEnd = request('date_end')) {
            $query->where('created_at', '<=', $dateEnd);
        }

        // Get the filtered commandes and paginate the results
        $commandeNotApproved = $query->paginate(10);

        // Get clients, magasins, and other necessary data
        $clients = Clients::where('user_id', $user->id)->get();
        $magasins = Magasins::where('user_id', $user->id)->paginate(10);
        $stocks = Stock::where('user_id', $user->id)->paginate(10);

        // Helper functions for client data (not changed)
        $getBadge = function($riskLevel) {
            switch ($riskLevel) {
                case 'Très faible':
                    return '<span class="badge badge-pill badge-status bg-success">Très faible</span>';
                case 'Faible':
                    return '<span class="badge badge-pill badge-status bg-info">Faible</span>';
                case 'Moyen':
                    return '<span class="badge badge-pill badge-status bg-warning">Moyen</span>';
                case 'Élevé':
                    return '<span class="badge badge-pill badge-status bg-danger">Élevé</span>';
                case 'Très élevé':
                    return '<span class="badge badge-pill badge-status bg-dark">Très élevé</span>';
                default:
                    return '<span class="badge badge-pill badge-status bg-secondary">Inconnu</span>';
            }
        };
        $services = Services::whereIn('business_id', $user->business->pluck('id'))->paginate(10);
        view()->share('services', $services);
        
        $getClientScoreDataByClientId = function ($id, $dataToReturn = null) {
            $client = Clients::find($id);

            if (!$client) {
                return null;
            }

            $allData = [
                'credit_score' => $client->credit_score,
                'risk_level' => $client->getRiskLevel(),
                'available_credit' => $client->credit_limit - $client->current_debt,
                'credit_limit' => $client->credit_limit,
                'current_debt' => $client->current_debt,
                'last_score_update' => $client->last_score_update
            ];

            // If a specific data is requested, return that
            if ($dataToReturn && array_key_exists($dataToReturn, $allData)) {
                return $allData[$dataToReturn];
            }

            // Return all data if no specific key is provided
            return (object)$allData;
        };

        return view('users.services.commande_not_approved', compact(
            'commandeNotApproved', 'hasPhysique', 'hasPrestation', 
            'getClientScoreDataByClientId', 'getBadge', 'magasins', 'businesses', 
            'stocks'  , 'getClientFromId', 'user', 'clients', 'categories', 'fournisseurs'
        ));
    }
    public function activeCommandes(){
        if(auth()->user()->type === 'team_member'){
            //return redirect()->route('dashboard_team_member');
            $teamMember = Auth::user();
            $realTeamMember = TeamMember::firstWhere('email', $teamMember->email);

            
            $team = $realTeamMember->team()->first();  // ✅ Correct way to access the relationship
            /*  if (!$team) {
                abort(404, "Vous n'etes membre d'aucune equipe");
            } */

            
            //$intBus = Business::find(optional($team->pivot)->business_id);
            
            $teamBusinessOwner = User::findOrFail($realTeamMember->user_id);  // ✅ Correct way

            
            $clientOwner = User::findOrFail($realTeamMember->user_id);

            $businesses = $teamBusinessOwner->business()->paginate(10);
            $hasPhysique = $teamBusinessOwner->business()->where('type', 'business_physique')->exists();
            $hasPrestation = $teamBusinessOwner->business()->where('type', 'prestation_de_service')->exists();
            view()->share('realTeamMember', $realTeamMember);


            $role = Role::find($realTeamMember->role_id);
            $formattedRole = $role ? Str::title(str_replace('_', ' ', $role->name)) : 'Unknown';

            $role = Role::find($realTeamMember->role_id);
            view()->share('role', $formattedRole);
            view()->share('roleObj', $role);



            //start query #



            $categories = $teamBusinessOwner->categorieProduits; 
            $fournisseurs = $teamBusinessOwner->fournisseurs; 
            $getClientFromId = function($id) {
                return Clients::find($id);
            };

            $query = Commandes::whereHas('commandeItems', function ($query) {
                $query->whereNull('stock_id'); // Filters CommandItems where stock_id is null
            })
            ->where('user_id', $teamBusinessOwner->id) // Filters by the user ID
            ->where('validation_status', 'approved');
            
            // Apply filters
            // Filter by client if provided
            if ($clientId = request('client')) {
                $query->where('client_id', $clientId);
            }

            // Filter by delivery status if provided
            if ($status = request('status')) {
                if ($status !== 'none') { // Avoid 'none' as it implies no filter
                    $query->where('invoice_status', $status);
                }
            }

            // Filter by price range if provided
            if ($minPrice = request('min_price')) {
                $query->where('total_price', '>=', $minPrice);
            }

            if ($maxPrice = request('max_price')) {
                $query->where('total_price', '<=', $maxPrice);
            }

            // Filter by date range if provided
            if ($dateStart = request('date_start')) {
                $query->where('created_at', '>=', $dateStart);
            }

            if ($dateEnd = request('date_end')) {
                $query->where('created_at', '<=', $dateEnd);
            }

            // Get the filtered commandes and paginate the results
            $commandeNotApproved = $query->paginate(10);

            // Get clients, magasins, and other necessary data
            $clients = Clients::where('user_id', $teamBusinessOwner->id)->get();
            $magasins = Magasins::where('user_id', $teamBusinessOwner->id)->paginate(10);
            $stocks = Stock::where('user_id', $teamBusinessOwner->id)->paginate(10);
            $services = Services::whereIn('business_id', $teamBusinessOwner->business->pluck('id'))->paginate(10);
            view()->share('services', $services);
            // Helper functions for client data (not changed)
            $getBadge = function($riskLevel) {
                switch ($riskLevel) {
                    case 'Très faible':
                        return '<span class="badge badge-pill badge-status bg-success">Très faible</span>';
                    case 'Faible':
                        return '<span class="badge badge-pill badge-status bg-info">Faible</span>';
                    case 'Moyen':
                        return '<span class="badge badge-pill badge-status bg-warning">Moyen</span>';
                    case 'Élevé':
                        return '<span class="badge badge-pill badge-status bg-danger">Élevé</span>';
                    case 'Très élevé':
                        return '<span class="badge badge-pill badge-status bg-dark">Très élevé</span>';
                    default:
                        return '<span class="badge badge-pill badge-status bg-secondary">Inconnu</span>';
                }
            };

            $getClientScoreDataByClientId = function ($id, $dataToReturn = null) {
                $client = Clients::find($id);

                if (!$client) {
                    return null;
                }

                $allData = [
                    'credit_score' => $client->credit_score,
                    'risk_level' => $client->getRiskLevel(),
                    'available_credit' => $client->credit_limit - $client->current_debt,
                    'credit_limit' => $client->credit_limit,
                    'current_debt' => $client->current_debt,
                    'last_score_update' => $client->last_score_update
                ];

                // If a specific data is requested, return that
                if ($dataToReturn && array_key_exists($dataToReturn, $allData)) {
                    return $allData[$dataToReturn];
                }

                // Return all data if no specific key is provided
                return (object)$allData;
            };
            $user = User::where('email' , $realTeamMember->email)->first();
            return view('dashboard_team_member.services.commande_approved', compact(
                'commandeNotApproved', 'hasPhysique', 'hasPrestation', 'user',
                'getClientScoreDataByClientId', 'getBadge', 'magasins', 'businesses', 
                'services' , 'getClientFromId', 'realTeamMember', 'clients', 'categories', 'fournisseurs'
            ));
        }
        
        $user = Auth::user();
        $hasPhysique = $user->business()->where('type', 'business_physique')->exists();
        $hasPrestation = $user->business()->where('type', 'prestation_de_service')->exists();
        $businesses = $user->business;
        $categories = $user->categorieProduits;
        $fournisseurs = $user->fournisseurs;

        // Get the client by ID (for later use in views)
        $getClientFromId = function($id) {
            return Clients::find($id);
        };

        

        $query = $query = Commandes::whereHas('commandeItems', function ($query) {
            $query->whereNull('stock_id'); // Filters CommandItems where stock_id is null
        })
        ->where('user_id', $user->id)
        ->where('validation_status', 'approved');

        // Apply filters based on form input
        if ($clientId = request('client')) {
            $query->where('client_id', $clientId);  // Filter by client ID
        }

        if ($status = request('status')) {
            if ($status !== 'none') {  // If the status is provided (not 'none'), apply it
                $query->where('delivery_status', $status);  // Filter by delivery status
            }
        }

        if ($minPrice = request('min_price')) {
            $query->where('total_price', '>=', $minPrice);  // Filter by minimum price
        }

        if ($maxPrice = request('max_price')) {
            $query->where('total_price', '<=', $maxPrice);  // Filter by maximum price
        }

        if ($dateStart = request('date_start')) {
            $query->where('created_at', '>=', $dateStart);  // Filter by start date
        }

        if ($dateEnd = request('date_end')) {
            $query->where('created_at', '<=', $dateEnd);  // Filter by end date
        }

        // Execute the query and paginate the results
        $commandeNotApproved = $query->paginate(10);

        // Get additional data
        $clients = Clients::where('user_id', $user->id)->get();
        $magasins = Magasins::where('user_id', $user->id)->paginate(10);
        $stocks = Stock::where('user_id', $user->id)->paginate(10);
        $services = Services::whereIn('business_id', $user->business->pluck('id'))->paginate(10);
        // Helper function for badges based on risk levels (same as before)
        $getBadge = function($riskLevel) {
            switch ($riskLevel) {
                case 'Très faible':
                    return '<span class="badge badge-pill badge-status bg-success">Très faible</span>';
                case 'Faible':
                    return '<span class="badge badge-pill badge-status bg-info">Faible</span>';
                case 'Moyen':
                    return '<span class="badge badge-pill badge-status bg-warning">Moyen</span>';
                case 'Élevé':
                    return '<span class="badge badge-pill badge-status bg-danger">Élevé</span>';
                case 'Très élevé':
                    return '<span class="badge badge-pill badge-status bg-dark">Très élevé</span>';
                default:
                    return '<span class="badge badge-pill badge-status bg-secondary">Inconnu</span>';
            }
        };
        view()->share('services', $services);
        // Helper function to fetch client score data by client ID (same as before)
        $getClientScoreDataByClientId = function ($id, $dataToReturn = null) {
            $client = Clients::find($id);

            if (!$client) {
                return null;
            }

            // Default data for the client
            $allData = [
                'credit_score' => $client->credit_score,
                'risk_level' => $client->getRiskLevel(),
                'available_credit' => $client->credit_limit - $client->current_debt,
                'credit_limit' => $client->credit_limit,
                'current_debt' => $client->current_debt,
                'last_score_update' => $client->last_score_update
            ];

            // Return a specific data field if requested
            if ($dataToReturn && array_key_exists($dataToReturn, $allData)) {
                return $allData[$dataToReturn];
            }

            // Return all data by default
            return (object)$allData;
        };

        // Pass data to the view
        return view('users.services.commande_approved', compact(
            'commandeNotApproved', 'hasPhysique', 'hasPrestation',
            'getClientScoreDataByClientId', 'getBadge', 'magasins', 'businesses', 
            'stocks' , 'user', 'clients', 'categories', 'fournisseurs', 'getClientFromId'
        ));
    }  


    public function sendInvoiceToRecipient(Request $request){

        $request->validate([
            'commande_id' => 'required|exists:commandes,id',
            'email' => 'required|email',
            //'name' => 'required|string',
        ]);

        $invoice = Invoice::where('commande_id' , $request->commande_id)->first();
        $commande = Commandes::find($request->commande_id);
        $magasin = Magasins::find($commande->magasin_id);

        $client = Clients::find($commande->client_id);
        $success = $this->emailService->sendEmailWithTemplate($request->email, 'emails.invoice' , [
            'magasin' => $magasin->name,    
            'name' => $client->name,      
            'appLink' => $invoice->invoice_link,   
        ]);

        return redirect()->back()->with('success' , 'Invoice transmis');
    }  
}
