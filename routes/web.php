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


Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->name('register.submit');
Route::post('/register-member/{id}', [TeamMemberController::class, 'updatePasswordTeamMember'])->name('register_member.submit');

Route::get('/pricing', [PricingController::class, 'showPricingPage'])->name('pricing.page');
Route::post('/pricing/select', [PricingController::class, 'selectPricing'])->name('pricing.select');

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
Route::get('/team-member-signin/{id}' , [TeamMemberController::class, 'returnConfirmTeamMemberPwd'])->name('signin.team_member');

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


    //mnagasins
    Route::get('/magasins_list', [MagasinsController::class, 'index'])->name('magasins.listes');
    Route::post('/magasins_store', [MagasinsController::class, 'store'])->name('magasins.store');
    Route::post('/magasins_add_produits' , [MagasinsController::class, 'magasins_add_produits'])->name('magasins.magasins_add_produits');

    Route::get('fournisseurs/create', [FournisseurController::class, 'create'])->name('fournisseurs.create'); // Show create form
    Route::post('fournisseurs', [FournisseurController::class, 'store'])->name('fournisseurs.store'); // Store new supplier
    Route::get('fournisseurs/{id}', [FournisseurController::class, 'edit'])->name('fournisseurs.show'); // Show individual supplier
    Route::put('fournisseurs/{fournisseur}', [FournisseurController::class, 'update'])->name('fournisseurs.update'); // Update supplier
    Route::patch('fournisseurs/{fournisseur}', [FournisseurController::class, 'update']); // Alternative to PUT for update
    Route::delete('fournisseurs/{fournisseur}', [FournisseurController::class, 'destroy'])->name('fournisseurs.destroy'); // Delete supplier
    // Route to store a new category
    Route::post('/categories', [CategorieProduitController::class, 'store'])->name('categories.store');

    // Route to show a single category
    Route::get('/categories/{category}', [CategorieProduitController::class, 'show'])->name('categories.show');

    // Route to show the edit form for a category
    Route::get('/categories/{id}', [CategorieProduitController::class, 'edit'])->name('categories.edit');

    // Route to update a category
    Route::put('/categories/{category}', [CategorieProduitController::class, 'update'])->name('categories.update');

    // Route to delete a category
    Route::delete('/categories/{id}', [CategorieProduitController::class, 'destroy'])->name('categories.destroy');

});

Route::middleware(['auth']) -> group(function(){
    Route::get('/dashboard_team_member', [DashboardController::class, 'team_member'])->name('dashboard_team_member');

});

/* 
Route::get('/verify_2fa', function () {
    return view('auth/verify_2fa');
});
 */
