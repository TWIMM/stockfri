@extends('layouts.app_layout')

@section('title', 'Stocks Page')

@section('content')

    @include('Modals.listes_des_produits_no_update')
    @include('Modals.order_clients')
    @include('Modals.risk_high')
    @include('Modals.orderpay')
    @include('Modals.risk_low')
    @include('Modals.pdf_viewer')

    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h4>Liste des commandes</h4>
                </div>

                <div class="card-body">
                    <form method="GET" action="{{ route('commandes.listes') }}" class="mb-4">
                        <div class="row">
                            <div class="col-md-3 mb-2">
                                <label for="client">Client</label>
                                <select name="client" id="client" class="form-control">
                                    <option value="">Tous les clients</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}" {{ request('client') == $client->id ? 'selected' : '' }}>
                                            {{ $client->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label for="status">Statut livraison</label>
                                <select name="status" id="status" class="form-control">
                                    <option value="">Tous les statuts</option>
                                    <option value="none" {{ request('status') == 'none' ? 'selected' : '' }}>Aucun</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                                    <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Livré</option>
                                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>En cours</option>
                                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Annulé</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label for="min_price">Prix minimum</label>
                                <input min=0 type="number" name="min_price" id="min_price" class="form-control" value="{{ request('min_price') }}">
                            </div>
                            <div class="col-md-3 mb-2">
                                <label for="max_price">Prix maximum</label>
                                <input min=0 type="number" name="max_price" id="max_price" class="form-control" value="{{ request('max_price') }}">
                            </div>
                            <div class="col-md-3 mb-2">
                                <label for="date_start">Date début</label>
                                <input type="date" name="date_start" id="date_start" class="form-control" value="{{ request('date_start') }}">
                            </div>
                            <div class="col-md-3 mb-2">
                                <label for="date_end">Date fin</label>
                                <input type="date" name="date_end" id="date_end" class="form-control" value="{{ request('date_end') }}">
                            </div>
                            
                            <div class="col-md-12 mt-2">
                                <button type="submit" class="btn btn-primary">Filtrer</button>
                                <a href="{{ route('commandes.listes') }}" class="btn btn-secondary">Réinitialiser</a>
                            </div>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table class="table table-view">
                            <thead>
                                <tr>
                                    <th>Tracking number</th>
                                    <th>Commande</th>
                                    <th>Details de la livraion</th>
                                    <th>Statut livraison </th>
                                    <th>Prix livraison </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($livraisons as $eachLiv)
                                    <tr>
                                        <td> <span class="badge badge-pill badge-status bg-blue">{{$eachLiv->tracking_number}}</span></td>
                                        <td> 
                                            <button type="button" class="btn btn-sm btn-secondary"><i class="ti ti-arrow-eyes"></i></button>
                                        </td>
                                        <td> {{$eachLiv->tracking_number}}</td>
                                        <td> {{$eachLiv->tracking_number}}</td>
                                        <td> {{$eachLiv->tracking_number}}</td>

                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer">
                    {{ $livraisons->links() }}
                </div>
            </div>
        </div>
    </div>
    
    
@endsection
