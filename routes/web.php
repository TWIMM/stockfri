<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PricingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TeamMemberController;
use App\Http\Controllers\ServicesController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\MagasinsController;
use App\Http\Controllers\FournisseurController;
use App\Http\Controllers\CategorieProduitController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\LivraisonController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\PayController;
use App\Http\Controllers\ProfileController;


Route::get('/invoice', [InvoiceController::class, 'generateInvoice'])->name('invoice');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->name('register.submit');
Route::post('/register-member/{id}', [TeamMemberController::class, 'updatePasswordTeamMember'])->name('register_member.submit');

Route::get('/pricing', [PricingController::class, 'showPricingPage'])->name('pricing.page');
Route::post('/pricing/select', [PricingController::class, 'selectPricing'])->name('pricing.select');

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
Route::get('/team-member-signin/{id}' , [TeamMemberController::class, 'returnConfirmTeamMemberPwd'])->name('signin.team_member');
Route::get('/user-signin/{id}' , [RegisterController::class, 'ValidateMail'])->name('signin.user_confirm');
Route::get('/logout', [LoginController::class, 'logout'])->name('logout');

//protected

Route::middleware(['auth'])->group(function () {
    //musthave paid a prrice

    //business
    Route::get('/business_list', [BusinessController::class, 'showBusinessListPage'])->name('business.listes');
    Route::post('/business/store', [BusinessController::class, 'store'])->name('business.store');
    Route::get('/business/{id}/delete', [BusinessController::class, 'del'])->name('business.delete');
    Route::post('/business/{id}/update', [BusinessController::class, 'updateBusiness'])->name('business.edit');
    Route::get('/business/{id}', [BusinessController::class, 'getBusiness']);
    Route::get('/team/{id}/businesses', [BusinessController::class, 'getBusinessesByTeam']);

    //teams
    Route::get('/teams_list', [TeamController::class, 'index'])->name('teams.listes');
    Route::post('/teams/store', [TeamController::class, 'store'])->name('teams.store');
    Route::post('/teams/{id}/update', [TeamController::class, 'update'])->name('teams.edit');
    Route::get('/teams/{id}/delete', [TeamController::class, 'del'])->name('teams.delete');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/teams/{id}', [TeamController::class, 'getTeamsById']);
    //teamMember
    Route::get('/team_member_list', [TeamMemberController::class, 'index'])->name('team_member.listes');
    Route::post('/team_member/store', [TeamMemberController::class, 'store'])->name('team_member.store');
    Route::post('/team_member/{id}/update', [TeamMemberController::class, 'update'])->name('team_member.edit');
    Route::get('/team_member/{id}/delete', [TeamMemberController::class, 'del'])->name('team_member.delete');
    Route::get('/team_member/{id}', [TeamMemberController::class, 'getTeamMemberById']);
    Route::get('/team_member_team/unlink', [TeamMemberController::class, 'unlink'])->name('remove.team.link');
    Route::get('/team_member_team/edit_perm', [TeamMemberController::class, 'edit_perm'])->name('update.permissions');
    Route::get('/team_member/{id}/teams', [TeamMemberController::class, 'getTeams']);
    Route::get("/team_member/{ids}/getperms" , [TeamMemberController::class , "getPermissions"])->name('teammember.store.permissions');

    //coequipier
    Route::get('/handle_coequipier', [CoequipierController::class, 'showCoequipier'])->name('ceoquipier.listes');
    Route::get('/store_coequipier', [CoequipierController::class, 'storeCoequipier'])->name('coequipiers.store');
    Route::get('/edit_coequipier', [CoequipierController::class, 'editCoequipier'])->name('coequipiers.edit');
    Route::post('/add_to_team', [TeamMemberController::class, 'assignMember'])->name('team_member.assign');

    //team_member_routes
    Route::get('/owner_teams_list', [TeamController::class, 'showOwnerTeamListPage'])->name('owner.teams.listes');
    Route::get('/owner_team_member_list', [TeamMemberController::class, 'showOwnerTeamMemberListPage'])->name('owner.team_member.listes');
    Route::get('/owner_business_list', [BusinessController::class, 'showOwnerBusinessListPage'])->name('owner.business.listes');
    Route::post('/business/store/team_member', [BusinessController::class, 'storeTeamMember'])->name('business.store.team_member');
    Route::get('/remove.team.link', [TeamMemberController::class, 'RemoveFrom'])->name('team.remove');

    //stock_routes
    Route::get('/owner_stock_list', [StockController::class, 'showOwnerStockListPage'])->name('owner.stock.listes');
    Route::get('/stock_list', [StockController::class, 'index'])->name('stock.listes');


    //services 

    Route::get('/services_list', [ServicesController::class, 'index'])->name('services.listes');
    Route::get('/owner_services_list', [ServicesController::class, 'showOwnerServicesListPage'])->name('owner.services.listes');
    Route::post('/services/store', [ServicesController::class, 'store'])->name('services.store');
    Route::get('/services/{id}', [ServicesController::class, 'edit']);  // Used for fetching service data for editing
    Route::post('/services/{id}/update', [ServicesController::class, 'update'])->name('services.update');
    Route::get('/services/delete/{id}', [ServicesController::class, 'destroy'])->name('services.delete');
    Route::post('/services/sell', [ServicesController::class, 'order'])->name('services.order');
    Route::get('/services_precommandes', [ServicesController::class, 'getPrecommandes'])->name('pre_commandes_s.services');
    Route::get('/services_commandes', [ServicesController::class, 'activeCommandes'])->name('commandes_s.services');
    Route::post('/update-tab-session', [DashboardController::class, 'updateTabSession'])->name('update-tab-session');
    Route::get('/send_invoices/{id}', [ServicesController::class, 'sendInvoiceToRecipient'])->name('services.send_invoices');
    Route::post('/update-tab-stat-session', [DashboardController::class, 'updateTabSessionStat'])->name('update-tab-stat-session');
    Route::post('/update-tab-stat-fournisseur-session', [DashboardController::class, 'updateTabFournisseurSessionStat'])->name('update-tab-fournisseur-stat-session');

    //stock 

    Route::get('/stock_list', [StockController::class, 'index'])->name('stock.listes');
    Route::get('/owner_services_list', [StockController::class, 'showOwnerStockListPage'])->name('owner.stock.listes');
    Route::post('/stock/store', [StockController::class, 'store'])->name('stock.store');
    Route::get('/stock/{id}', [StockController::class, 'edit'])->name('stock.edit');  // Used for fetching service data for editing
    Route::post('/stock/{id}/update', [StockController::class, 'update'])->name('stock.update');
    Route::delete('/stock/delete/{id}', [StockController::class, 'destroy'])->name('stock.delete');
    Route::get('/stock/faire_inventaire/{id}', [StockController::class, 'makeInventory'])->name('stock.faire_inventaire');
    Route::get('/stock/confirmer_inventaire/{id}', [StockController::class, 'confirmInventory'])->name('stock.confirmer_inventaire');
    Route::get('/fournisseurs_list', [FournisseurController::class, 'index'])->name('fournisseurs.listes');
    Route::get('/cat_prod_list', [CategorieProduitController::class, 'index'])->name('cat_prod.listes');
    Route::post('/stock/add_up_quantity', [StockController::class, 'add_up_quantity'])->name('stock.add_up_quantity');
    Route::get('/team_fournisseurs_list', [FournisseurController::class, 'indexTeamMember'])->name('team_member.fournisseurs.listes');


    Route::get('/check-livraison-exists/{commandeId}', [OrderController::class, 'checkExists'])->name('livraison.check-exists');
    Route::get('/get-delivery-personnel', [OrderController::class, 'getDeliveryPersonnel'])->name('livraison.get-personnel');
    Route::post('/create-livraison', [OrderController::class, 'createLivraison'])->name('livraison.create');
    Route::post('/update-livraison-status', [OrderController::class, 'updateStatus'])->name('livraison.update-status');
    
    Route::get('/livraison_data/{commande_id}', [OrderController::class, 'getLivDetail'])->name('livraison.getLivDetailexists');
    Route::get('/get_dettes', [ClientController::class, 'getDette'])->name('finances.dettes');
    Route::get('/get_pays', [ClientController::class, 'getPays'])->name('finances.paiement');
    Route::get('/mouvement_de_stock', [StockController::class, 'getMoveOfStocks'])->name('stock.moves');


    //mnagasins
    Route::get('/magasins_list', [MagasinsController::class, 'index'])->name('magasins.listes');
    Route::post('/magasins_store', [MagasinsController::class, 'store'])->name('magasins.store');
    Route::post('/magasins_add_produits' , [MagasinsController::class, 'addStockProduitToMagasin'])->name('magasins.magasins_add_produits');
    Route::get('/magasin_details/{id}', [MagasinsController::class, 'showList'])->name('magasins.detail');
    Route::get('/return_to_magasin', [StockController::class, 'returnToMagasin'])->name('stock.return_to_magasin');

    Route::get('fournisseurs/create', [FournisseurController::class, 'create'])->name('fournisseurs.create'); // Show create form
    Route::post('fournisseurs', [FournisseurController::class, 'store'])->name('fournisseurs.store'); // Store new supplier
    Route::get('fournisseurs/{id}', [FournisseurController::class, 'edit'])->name('fournisseurs.show'); // Show individual supplier
    Route::put('fournisseurs/{fournisseur}', [FournisseurController::class, 'update'])->name('fournisseurs.update'); // Update supplier
    Route::patch('fournisseurs/{fournisseur}', [FournisseurController::class, 'update']); // Alternative to PUT for update
    Route::delete('fournisseurs/{fournisseur}', [FournisseurController::class, 'destroy'])->name('fournisseurs.destroy'); // Delete supplier
    // Route to store a new category
    Route::post('/categories', [CategorieProduitController::class, 'store'])->name('categories.store');
    Route::post('/bonus_fournisseur', [StockController::class, 'bonus_fournisseur'])->name('stock.bonus_fournisseur');
    Route::post('/retrait_magasin', [StockController::class, 'retrait_magasin'])->name('stock.retrait_magasin');
    Route::get('/get-stocks/{magasin}', [StockController::class, 'getStocksOfMagasin']);

    // Route to show a single category
    Route::get('/categories/{category}', [CategorieProduitController::class, 'show'])->name('categories.show');

    // Route to show the edit form for a category
    Route::get('/categories/{id}', [CategorieProduitController::class, 'edit'])->name('categories.edit');
    Route::put('/update_general_profile', [ProfileController::class, 'updateGeneralProfile'])->name('profile.update');

    // Route to update a category
    Route::put('/categories/{category}', [CategorieProduitController::class, 'update'])->name('categories.update');

    // Route to delete a category
    Route::delete('/categories/{id}', [CategorieProduitController::class, 'destroy'])->name('categories.destroy');
    Route::post('clients_store', [ClientController::class, 'store'])->name('clients.store'); 
    Route::get('/clients_list', [ClientController::class, 'index'])->name('clients.listes');
    Route::delete('clients_destroy/{id}', [ClientController::class, 'destroy'])->name('clients.destroy'); // Delete supplier
    Route::get('clients/{id}', [ClientController::class, 'edit'])->name('clients.show'); // Show individual supplier
    Route::put('clients/update/{id}', [ClientController::class, 'update'])->name('clients.edit'); // Show individual supplier
    Route::post('/stock_fri_order_stock', [OrderController::class, 'store'])->name('stock.stock_fri_order_stock'); // Show individual supplier
    Route::post('/stock_fri_update_order_stock', [OrderController::class, 'update'])->name('stock.stock_fri_update_order_stock'); // Show individual supplier

    Route::get('/commandes_listes', [OrderController::class, 'index'])->name('commandes.listes');
    Route::get('/factures_listes', [InvoiceController::class, 'index'])->name('factures.listes');
    Route::get('/livraisons_listes', [LivraisonController::class, 'index'])->name('livraisons.listes');
    Route::get('/pre_commandes_listes', [OrderController::class, 'getPreCommandes'])->name('pre_commandes.listes');
    Route::get('/precommande/{id}', [OrderController::class, 'getPreCommandesSpec'])->name('pre_commandes.spec');
    Route::get('clients_data/{id}', [OrderController::class, 'showClientDetails'])->name('clients.showData'); // Show individual supplier
    Route::get('commande_data/{id}', [OrderController::class, 'showCommandeDetails'])->name('commandes.showData'); // Show individual supplier
    Route::post('trust_client', [OrderController::class, 'trustClient'])->name('commandes.trustClient'); // Show individual supplier
    Route::post('approve_client_order', [OrderController::class, 'approveClientOrder'])->name('commandes.approveClientOrder'); // Show individual supplier
    Route::get('/invoices/{id}', [InvoiceController::class, 'retrieveUrl'])->name('invoices.retrieveUrl'); // Show individual supplier
    Route::get('/clients/{client}/debts', [OrderController::class, 'getClientDebts'])->name('clients.debts');
    Route::get('/profile_page', [DashboardController::class, 'profilePage'])->name('profile.page');
    Route::post('/get-cities', [DashboardController::class, 'getCities'])->name('get.cities');
    Route::prefix('pays')->group(function () {
        Route::post('/', [PayController::class, 'pay'])->name('finances.handle_dette');
        Route::get('{commandId}', [PayController::class, 'show']);
        Route::get('user/{userId}', [PayController::class, 'getUserPayments']);
    });
    // Mark a debt as paid
    Route::post('/client-debts/{clientDebt}/pay', [OrderController::class, 'markAsPaid'])->name('clients.debts.pay');
    
    // Create a new debt from a command
    Route::post('/client-debts/create',  [OrderController::class, 'createDebtFromCommande'])->name('clients.debts.create');

    //Routes statistiques 

    Route::get('/statistiques_client', [ClientController::class, 'showStat'])->name('statistiques.client.show');
    Route::get('/statistiques_client/{client}', [ClientController::class, 'getStat'])->name('statistiques.clients.stats');
    Route::get('/statistiques_stock', [StockController::class, 'showStat'])->name('statistiques.stocks.show');
    Route::get('/statistiques_client/{client}', [ClientController::class, 'getStat'])->name('statistiques.clients.stats');
    Route::get('/statistiques_stock/{stock}', [StockController::class, 'getStat'])->name('statistiques.stocks.stats');
    Route::get('/statistiques_fournisseur/{fournisseur}', [FournisseurController::class, 'getStat'])->name('statistiques.fournisseur.stats');
    Route::get('/statistiques_service/{service}', [ServicesController::class, 'getStat'])->name('statistiques.services.stats');
    Route::get('/statistiques_service', [ServicesController::class, 'showStat'])->name('statistiques.services.show');
    Route::get('/statistiques_fournisseur', [FournisseurController::class, 'showStat'])->name('statistiques.fournisseur.show');

});

Route::middleware(['auth']) -> group(function(){ 
    Route::get('/dashboard_team_member', [DashboardController::class, 'team_member'])->name('dashboard_team_member');
    Route::get('/dashboard_admin', [DashboardController::class, 'dashboard_admin'])->name('dashboard_admin');

});


Route::get('/password/reset/request', [PasswordResetController::class, 'showResetForm'])->name('password.reset.request');

Route::post('/send/request/notification', [PasswordResetController::class, 'sendResetLinkEmail'])->name('send.request.notification');

Route::get('/password/reset', [PasswordResetController::class, 'resetPassword'])->name('password.reset');

Route::get('/password/reset/confirmation/{token}', [PasswordResetController::class, 'showResetConfirmForm'])->name('password.reset.request');

/* 
Route::get('/verify_2fa', function () {
    return view('auth/verify_2fa');
});
 */
