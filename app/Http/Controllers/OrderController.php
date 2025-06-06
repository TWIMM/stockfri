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
use Illuminate\Support\Facades\Auth;
use App\Models\Magasins;
use App\Models\Livraisons;
use App\Models\TeamMember;
use App\Models\ClientDebt;
use App\Models\User;
use App\Models\Pay;
use App\Models\MouvementDeStocks;
use App\Models\Team;
use App\Models\Role;
use App\Models\Fournisseur;

class OrderController extends Controller
{
    protected $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    /**
     * Get all unpaid debts for a client
     *
     * @param Clients $client
     * @return \Illuminate\Http\JsonResponse
     */
    public function getClientDebts(Clients $client)
    {
        // Get unpaid debts for the client
        $debts = ClientDebt::where('client_id', $client->id)
                        //->where('is_paid', false)
                        ->orderBy('due_date')
                        ->get();
        
        return response()->json($debts);
    }
    
    /**
     * Mark a debt as paid
     *
     * @param ClientDebt $clientDebt
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsPaid(ClientDebt $clientDebt)
    {
        try {
            $clientId = $clientDebt->client_id;
            
            // Mark the debt as paid
            $clientDebt->markAsPaid();
            
            return response()->json([
                'success' => true,
                'client_id' => $clientId,
                'message' => 'Dette marquée comme payée avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
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

    public function trustClient(Request $request){
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'trusted' => 'required|string',
            'new_credit_limit' => 'required|numeric|min:0',
        ]);
        
        $client = Clients::find($request->client_id);

        if($request->trusted === 'yes'){
            $client->trusted = 1;
        } else {
            $client->trusted = 0;
        }

        
        $client->limit_credit_for_this_user = $request->new_credit_limit;
        $client->save();

        return redirect()->back()->with('success', 'Success');
    }

    public function checkExists($commandeId)
    {
        $exists = Livraisons::where('commande_id', $commandeId)->exists();
        
        return response()->json([
            'exists' => $exists
        ]);
    }


    public function getDeliveryPersonnel()
    {
        // Assuming delivery personnel have a role or a flag in the users table
        // Adjust query according to your database structure
        $personnel = TeamMember::where('user_id', auth()->user()->id)
                        ->get(['id', 'name']);
        
        return response()->json([
            'personnel' => $personnel
        ]);
    }
    
    /**
     * Create a new livraison record with all details
     */
    public function createLivraison(Request $request)
    {
        $request->validate([
            'commande_id' => 'required|exists:commandes,id',
            'delivery_status' => 'required|string',
            'delivery_address' => 'required|string',
            'delivered_by' => 'required|exists:team_members,id',
            'shipping_method' => 'required|string',
            'delivery_date' => 'required|date',
            'is_shipping_paid' => 'required|boolean',
            'shipping_cost' => 'required_if:is_shipping_paid,1|nullable|numeric|min:0',
            'delivery_notes' => 'nullable|string'
        ]);
        
        // Check if livraison already exists
        $livraison = Livraisons::where('commande_id', $request->commande_id)->first();
        
        if ($livraison) {
            return response()->json([
                'success' => false,
                'message' => 'Une livraison existe déjà pour cette commande'
            ], 400);
        }
        
        // Create new livraison
        $livraison = new Livraisons();
        $livraison->commande_id = $request->commande_id;
        $livraison->delivery_status = $request->delivery_status;
        $livraison->delivery_address = $request->delivery_address;
        $livraison->delivered_by = $request->delivered_by;
        $livraison->shipping_method = $request->shipping_method;
        $livraison->delivery_date = $request->delivery_date;
        $livraison->delivery_notes = $request->delivery_notes;
        
        // Set shipping cost if paid
        if ($request->is_shipping_paid) {
            $livraison->shipping_cost = $request->shipping_cost;
        } else {
            $livraison->shipping_cost = 0;
        }
        
        // Generate tracking number
        $livraison->tracking_number = 'LIV-' . $request->commande_id . '-' . now()->format('YmdHis');
        
        $livraison->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Livraison créée avec succès',
            'livraison' => $livraison
        ]);
    }
    
    /**
     * Update an existing livraison status
     */
    public function updateStatus(Request $request)
    {
        $request->validate([
            'commande_id' => 'required|exists:commandes,id',
            'delivery_status' => 'required|string'
        ]);
        
        $commandeId = $request->commande_id;
        $status = $request->delivery_status;
        
        // Find existing livraison
        $livraison = Livraisons::where('commande_id', $commandeId)->first();
        
        if (!$livraison) {
            return response()->json([
                'success' => false,
                'message' => 'Livraison non trouvée'
            ], 404);
        }
        
        // Update existing livraison
        $livraison->delivery_status = $status;
        $livraison->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Statut de livraison mis à jour avec succès',
            'livraison' => $livraison
        ]);
    }
    


    public function approveClientOrder(Request $request){
        $request->validate([
            'commande_id' => 'required|exists:commandes,id',
            //'client_id' => 'required|exists:clients,id',
        ]);
        
        //$client = Client::find($request->client_id);
        $commandeNotApproved = Commandes::find($request->commande_id);
        $client = Clients::find($commandeNotApproved->client_id);
        $user = User::find($client->user_id);

        //is client trusted ? 

        if($client->trusted === 1){
            
            $commandeNotApproved->validation_status = 'approved';
            $commandeStatus = $commandeNotApproved->invoice_status;
            if($client->limit_credit_for_this_user <= 0 ){
                return redirect()->back()->with('error', 'La limite de credit de ce client est de zero');
            }
            if($commandeStatus === 'unpaid'){
                if($client->limit_credit_for_this_user < $commandeNotApproved->total_price){
                    return redirect()->back()->with('error', 'La limite de credit de ce client est insuffisante');

                }

                $client->limit_credit_for_this_user = $client->limit_credit_for_this_user - $commandeNotApproved->total_price;
                $client->current_debt = $client->current_debt + $commandeNotApproved->total_price;
                $commandeNotApproved->already_paid = $commandeNotApproved->total_price;
                $commandeNotApproved->rest_to_pay = 0;


                ClientDebt::create([
                    'client_id' => $client->id, 
                    'commande_id' => $commandeNotApproved->id, 
                    'amount' => $commandeNotApproved->total_price, 
                    //'payment_method' => 'credit_card', 
                    'due_date' => Carbon::now()->addDays($user->due_date_delay), 
                ]);


            } else if ($commandeStatus === 'partially_paid') {


                //handle partiall payement

                $payment = new Pay();
                $payment->user_id = $commandeNotApproved->user_id;
                $payment->amount = $commandeNotApproved->already_paid;
                $payment->payment_method = $commandeNotApproved->payment_mode;
                $payment->transaction_id = $this->generateTransactionId(); 
                $payment->commande_id = $commandeNotApproved->id;
                $paymentCreated = $payment->save();
                
                if(!$paymentCreated){
                    return redirect()->back()->with('error', 'Paiement non valide !!');
                }

                $remainingAmount = $commandeNotApproved->rest_to_pay;
                if($client->limit_credit_for_this_user < $remainingAmount){
                    return redirect()->back()->with('error', 'La limite de credit de ce client est insuffisante');
                }
                $client->limit_credit_for_this_user = $client->limit_credit_for_this_user - $remainingAmount;
                $client->current_debt = $client->current_debt + $remainingAmount;
                $commandeNotApproved->already_paid = $commandeNotApproved->total_price;
                $commandeNotApproved->rest_to_pay = 0;

                ClientDebt::create([
                    'client_id' => $client->id, 
                    'commande_id' => $commandeNotApproved->id, 
                    'amount' => $remainingAmount, 
                    //'payment_method' => 'credit_card', 
                    'due_date' => Carbon::now()->addDays($user->due_date_delay),  
                ]);

            }

            

            $client->save();
            $commandeNotApproved->save();

            $invoice = $this->generateInvoiceFromOrder($commandeNotApproved);
            
            return redirect()->back()->with('success', 'Success');

        } else if($client->trusted === 0) {
            
            return redirect()->back()->with('error', 'Veuillez activer le mode trusted');
        }


    }

    // Generate a unique transaction ID
    private function generateTransactionId()
    {
        return 'TXN-' . strtoupper(uniqid());
    }

    public function getLivDetail($commande_id) { 
        $exists = Livraisons::where('commande_id', $commande_id)->exists();
        $livraison = Livraisons::where('commande_id', $commande_id)->first();
    
        return response()->json([
            'exists' => $exists, 
            'livraison' => $livraison, 
        ]);
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




            $categories = $teamBusinessOwner->categorieProduits;
            $fournisseurs = $teamBusinessOwner->fournisseurs;

            // Get the client by ID (for later use in views)
            $getClientFromId = function($id) {
                return Clients::find($id);
            };

            

            $query = $query = Commandes::whereHas('commandeItems', function ($query) {
                $query->whereNull('service_id'); // Filters CommandItems where service_id is null
            })
            ->where('user_id', $teamBusinessOwner->id)
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
            $clients = Clients::where('user_id', $teamBusinessOwner->id)->get();
            $magasins = Magasins::where('user_id', $teamBusinessOwner->id)->paginate(10);
            $stocks = Stock::where('user_id', $teamBusinessOwner->id)->paginate(10);

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
            $user = User::where('email' , $realTeamMember->email)->first();
            // Pass data to the view
            return view('dashboard_team_member.commandes_approved.index', compact(
                'commandeNotApproved', 'hasPhysique', 'hasPrestation', 'user', 
                'getClientScoreDataByClientId', 'getBadge', 'magasins', 'businesses', 
                'stocks', 'realTeamMember', 'clients', 'categories', 'fournisseurs', 'getClientFromId'
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
            $query->whereNull('service_id'); // Filters CommandItems where service_id is null
        })
        ->where('user_id', $user->id) // Filters by the user ID
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

        return view('users.commandes_approved.index', compact(
            'commandeNotApproved', 'hasPhysique', 'hasPrestation', 
            'getClientScoreDataByClientId', 'getBadge', 'magasins', 'businesses', 
            'stocks' , 'getClientFromId', 'user', 'clients', 'categories', 'fournisseurs'
        ));
    }



    public function getPreCommandes()
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




            $categories = $teamBusinessOwner->categorieProduits;
            $fournisseurs = $teamBusinessOwner->fournisseurs;

            // Get the client by ID (for later use in views)
            $getClientFromId = function($id) {
                return Clients::find($id);
            };

            

            $query = $query = Commandes::whereHas('commandeItems', function ($query) {
                $query->whereNull('service_id'); // Filters CommandItems where service_id is null
            })
            ->where('user_id', $teamBusinessOwner->id)
            ->where('validation_status', 'not_approved');

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
            $clients = Clients::where('user_id', $teamBusinessOwner->id)->get();
            $magasins = Magasins::where('user_id', $teamBusinessOwner->id)->paginate(10);
            $stocks = Stock::where('user_id', $teamBusinessOwner->id)->paginate(10);

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
            $user = User::where('email' , $realTeamMember->email)->first();
            // Pass data to the view
            return view('dashboard_team_member.commandes_not_approved.index', compact(
                'commandeNotApproved', 'hasPhysique', 'hasPrestation', 'user',
                'getClientScoreDataByClientId', 'getBadge', 'magasins', 'businesses', 
                'stocks', 'realTeamMember', 'clients', 'categories', 'fournisseurs', 'getClientFromId'
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
            $query->whereNull('service_id'); // Filters CommandItems where service_id is null
        })
        ->where('user_id', $user->id)
        ->where('validation_status', 'not_approved');

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
        return view('users.commandes_not_approved.index', compact(
            'commandeNotApproved', 'hasPhysique', 'hasPrestation',
            'getClientScoreDataByClientId', 'getBadge', 'magasins', 'businesses', 
            'stocks', 'user', 'clients', 'categories', 'fournisseurs', 'getClientFromId'
        ));
    }


    public function showClientDetails($id)
    {
        $client = Clients::findOrFail($id);  

        $clientData = $client->getClientDetails();

        return response()->json([
            'clientData' => $clientData,
        ]);
    }

    public function showCommandeDetails($id)
    {
        $commande = Commandes::findOrFail($id);  

        $commandeData = $commande->getCommandeDetails();

        return response()->json([
            'commande' => $commandeData,
        ]);
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
                'name' => $item->stock->name ?? $item->service->title,
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

    public function getPreCommandesSpec($id){
        $commande = Commandes::findOrFail($id);
        $products = CommandeItem::where('commande_id', $id)->get();
        
        $paymentDetails = [];
        if ($commande->payment_mode) {
            // Récupérer les détails de paiement selon le mode
            // (logique similaire à l'exemple précédent)
           
        }
        
        return response()->json([
            'commande' => $commande,
            'products' => $products,
            'payment_details' => $paymentDetails
        ]);
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
            'magasin_id' => 'required|exists:magasins,id',
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


                $isAuthTeamMemberQuestionMark = User::find(Auth::id());

                if($isAuthTeamMemberQuestionMark->type === 'client'){
                    MouvementDeStocks::create([
                        'stock_id' => $productData['product_id'],
                        'magasin_id' => $request->client_id, //magasin id is client id in this case
                        'quantity' => $quantity,
                        'user_id' => Auth::id(),
                        'type_de_mouvement' => env('ACHAT_CLIENT'),
                        'files_paths' => null
                    ]);
                }else if ($isAuthTeamMemberQuestionMark->type === 'team_member') {
                    
                    $test = TeamMember::where('email' ,  $isAuthTeamMemberQuestionMark->email)->first();
                    //dd(Auth::id());
                    //dd( $test->user_id);
                    MouvementDeStocks::create([
                        'stock_id' => $productData['product_id'],
                        'magasin_id' => $request->client_id, //magasin id is client id in this case
                        'quantity' => $quantity,
                        'user_id' => $test->user_id,
                        'type_de_mouvement' => env('ACHAT_CLIENT'),
                        'files_paths' => null
                    ]);
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
            

            $isAuthTeamMemberQuestionMark = User::find(Auth::id());

            if($isAuthTeamMemberQuestionMark->type === 'client'){
                $commande = Commandes::create([
                    'client_id' => $request->client_id,
                    'total_price' => 0, // Will calculate after adding items
                    'payment_mode' => $request->payment_mode,
                    'invoice_status' => $request->invoice_status,
                    'tva' => $request->tva,
                    'magasin_id' => $request->magasin_id,
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
                    'magasin_id' => $request->magasin_id,
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
                    'stock_id' => $productData['product_id'],
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'discount' => $discount,
                    'total_price' => $itemTotal
                ]);
                
                // Reduce stock quantity

                $stockCibled = DB::table('magasin_stock')
                ->where('magasin_id', $request->magasin_id)
                ->where('stock_id', $productData['product_id'])
                ->first(); 
                if ($stockCibled) {
                    $newQuantity = $stockCibled->quantity - $quantity;
                    
                    DB::table('magasin_stock')
                        ->where('magasin_id', $request->magasin_id)
                        ->where('stock_id', $productData['product_id'])
                        ->update(['quantity' => $newQuantity]);
                }

                
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



    protected function storeInternally(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'magasin_id' => 'required|exists:magasins,id',
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


                $isAuthTeamMemberQuestionMark = User::find(Auth::id());

                if($isAuthTeamMemberQuestionMark->type === 'client'){
                    MouvementDeStocks::create([
                        'stock_id' => $productData['product_id'],
                        'magasin_id' => $request->client_id, //magasin id is client id in this case
                        'quantity' => $quantity,
                        'user_id' => Auth::id(),
                        'type_de_mouvement' => env('ACHAT_CLIENT'),
                        'files_paths' => null
                    ]);
                }else if ($isAuthTeamMemberQuestionMark->type === 'team_member') {
                    
                    $test = TeamMember::where('email' ,  $isAuthTeamMemberQuestionMark->email)->first();
                    //dd(Auth::id());
                    //dd( $test->user_id);
                    MouvementDeStocks::create([
                        'stock_id' => $productData['product_id'],
                        'magasin_id' => $request->client_id, //magasin id is client id in this case
                        'quantity' => $quantity,
                        'user_id' => $test->user_id,
                        'type_de_mouvement' => env('ACHAT_CLIENT'),
                        'files_paths' => null
                    ]);
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
            

            $isAuthTeamMemberQuestionMark = User::find(Auth::id());

            if($isAuthTeamMemberQuestionMark->type === 'client'){
                $commande = Commandes::create([
                    'client_id' => $request->client_id,
                    'total_price' => 0, // Will calculate after adding items
                    'payment_mode' => $request->payment_mode,
                    'invoice_status' => $request->invoice_status,
                    'tva' => $request->tva,
                    'magasin_id' => $request->magasin_id,
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
                    'magasin_id' => $request->magasin_id,
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
                    'stock_id' => $productData['product_id'],
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'discount' => $discount,
                    'total_price' => $itemTotal
                ]);
                
                // Reduce stock quantity

                $stockCibled = DB::table('magasin_stock')
                ->where('magasin_id', $request->magasin_id)
                ->where('stock_id', $productData['product_id'])
                ->first(); 
                if ($stockCibled) {
                    $newQuantity = $stockCibled->quantity - $quantity;
                    
                    DB::table('magasin_stock')
                        ->where('magasin_id', $request->magasin_id)
                        ->where('stock_id', $productData['product_id'])
                        ->update(['quantity' => $newQuantity]);
                }

                
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
            
            return redirect()->back()->with('success', 'Pré-Commande re-créée avec succès.' );
        } catch (\Exception $e) {
            // Rollback the transaction if something goes wrong
            DB::rollBack();
            return back()->with('error', 'Une erreur est survenue: ' . $e->getMessage());
        }
    }


    public function update(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:commandes,id',
            
        ]);
        
        try {
            DB::beginTransaction();
            
            // Find the existing order
            $existingOrder = Commandes::findOrFail($request->order_id);

            $existingOrder->commandeItems()->delete();
            $existingOrder->delete();

            if($existingOrder->magasin_id){
                // 1. Reverse stock quantities for existing order items
                $existingItems = CommandeItem::where('commande_id', $existingOrder->id)->get();
                foreach ($existingItems as $item) {
                    // Increase stock quantity (reverse the previous reduction)
                    $stockCibled = DB::table('magasin_stock')
                        ->where('magasin_id', $existingOrder->magasin_id)
                        ->where('stock_id', $item->stock_id)
                        ->first();
                        
                    if ($stockCibled) {
                        $newQuantity = $stockCibled->quantity + $item->quantity;

                        $isAuthTeamMemberQuestionMark = User::find(Auth::id());

                        if($isAuthTeamMemberQuestionMark->type === 'client'){
                            MouvementDeStocks::create([
                                'stock_id' => $item->id,
                                'magasin_id' =>  $existingOrder->magasin_id, //magasin id is client id in this case
                                'quantity' => $item->quantity,
                                'user_id' => Auth::id(),
                                'type_de_mouvement' => 'PASSAGE_TEMPORAIRE',
                                'files_paths' => null
                            ]);
                        }else if ($isAuthTeamMemberQuestionMark->type === 'team_member') {
                                
                            $test = TeamMember::where('email' ,  $isAuthTeamMemberQuestionMark->email)->first();
                            //dd(Auth::id());
                            //dd( $test->user_id);
                            MouvementDeStocks::create([
                                'stock_id' => $item->id,
                                'magasin_id' => $existingOrder->magasin_id, //magasin id is client id in this case
                                'quantity' => $item->quantity,
                                'user_id' => $test->user_id,
                                'type_de_mouvement' => 'PASSAGE_TEMPORAIRE',
                                'files_paths' => null
                            ]);
                        }
                        
                        DB::table('magasin_stock')
                            ->where('magasin_id', $existingOrder->magasin_id)
                            ->where('stock_id', $item->stock_id)
                            ->update(['quantity' => $newQuantity]);
                    }
                }

                $this->storeInternally($request);
            } else {
                $this->storeInternallyOrderService($request);
            }


            return redirect()->back()->with('success', 'Pré-Commande re-créée avec succès.' );

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Une erreur est survenue: ' . $e->getMessage());
        }
    }

    protected function storeInternallyOrderService(Request $request){
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
            
            //return redirect()->back()->with('success', 'Pré-Commande créée avec succès.' );
        } catch (\Exception $e) {
            // Rollback the transaction if something goes wrong
            DB::rollBack();
            return back()->with('error', 'Une erreur est survenue: ' . $e->getMessage());
        }
    }


    public function getPays(){
        if(auth()->user()->type === 'team_member'){
            return redirect()->route('dashboard_team_member');
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

        $magasins = Magasins::where('user_id' ,$user->id)->paginate(10);

        $stocks = Stock::where('user_id' ,$user->id)->paginate(10); 
        $commandeNotApproved = Commandes::where('user_id' , auth()->id())
        ->where('validation_status' , 'not_approved')
        ->paginate(10); 
        //dd($commandeNotApproved);
        $clients = Clients::where('user_id' ,$user->id)->get();


        $getBadge = function($riskLevel)
        {
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
                return null; // Retourne null si le client n'existe pas
            }
            
            // Créer un tableau de toutes les données disponibles
            if($client->trusted === 1){
                $allData = [
                    'credit_score' => 90,
                    'risk_level' => 'Très faible',
                    'available_credit' => $client->limit_credit_for_this_user - $client->current_debt,
                    'credit_limit' => $client->limit_credit_for_this_user,
                    'current_debt' => $client->current_debt,
                    'last_score_update' => $client->last_score_update
                ];
            }
            $allData = [
                'credit_score' => $client->credit_score,
                'risk_level' => $client->getRiskLevel(),
                'available_credit' => $client->credit_limit - $client->current_debt,
                'credit_limit' => $client->credit_limit,
                'current_debt' => $client->current_debt,
                'last_score_update' => $client->last_score_update
            ];
            
            // Si une clé spécifique est demandée et existe dans le tableau
            if ($dataToReturn && array_key_exists($dataToReturn, $allData)) {
                return $allData[$dataToReturn];
            }
            
            // Si un tableau de clés est fourni
            if (is_array($dataToReturn)) {
                $result = [];
                foreach ($dataToReturn as $key) {
                    if (array_key_exists($key, $allData)) {
                        $result[$key] = $allData[$key];
                    }
                }
                return $result;
            }
            
            // Par défaut, retourner toutes les données
            return (object)$allData;
        };

        return view('users.commandes_not_approved.index', compact('commandeNotApproved','hasPhysique', 
            'hasPrestation' , 'getClientScoreDataByClientId' , 'getBadge', 'magasins', "businesses", 'stocks',  'user' , 'clients', "categories" , "fournisseurs" , 'getClientFromId'));
    }

    
}