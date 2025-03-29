@extends('layouts.app_layout')

@section('title', 'Details magasin Page')

@section('content')
    @include('Modals.add_magasin')
    @include('Modals.edit_magasin')
    @include('Modals.transfert_au_magasin')
    @include('Modals.modal_prevente')
    @include('Modals.add_client')

    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                 
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addToMagasinsModal">
                        <i class="ti ti-arrow-forward"></i>  Transférer au Magasin  
                    </button>
                    <button type="button"  id='edit-stock-btn' class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#modalVente">
                        <i class="ti ti-receipt"></i>
                    </button> 
                    <button type="button"  id='edit-stock-btn' class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#addClientModal">
                        <i class="ti ti-plus"></i>
                    </button> 
                </div>

                <div class="card-body">
                    <form method="GET" action="{{ route('magasins.listes') }}" class="mb-4">
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <label for="name">Nom</label>
                                <input type="text" name="name" id="name" class="form-control" value="{{ request('name') }}" placeholder="Filtrer par nom">
                            </div>
                            <div class="col-md-4 mb-2">
                                <label for="quantity">Quantité</label>
                                <input type="number" name="quantity" id="quantity" class="form-control" value="{{ request('quantity') }}" placeholder="Filtrer par quantité">
                            </div>
                            <div class="col-md-4 mb-2">
                                <label for="price">Prix</label>
                                <input type="number" name="price" id="price" class="form-control" value="{{ request('price') }}" placeholder="Filtrer par prix">
                            </div>
                            <div class="col-md-12 mt-2">
                                <button type="submit" class="btn btn-primary">Filtrer</button>
                                <a href="{{ route('magasins.listes') }}" class="btn btn-secondary">Réinitialiser</a>
                            </div>
                        </div>
                    </form>
                    
                    <div class="table-responsive">
                        <table class="table table-view">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Quantité </th>

                                    <th>Prix</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stocksArray as $stock)
                                    <tr>
                                        <td>{{ $stock->name }}</td>
                                        <td><span class="badge badge-pill badge-status bg-green">
                                            {{number_format( $stock->quantity )}}
                                            </span></td>

                                        <td><span class="badge badge-pill badge-status bg-blue">
                                            {{number_format( $stock->price )}} FCFA
                                            </span></td>
                                        <td>
                                            
                                            <form action="{{ route('stock.return_to_magasin', $stock->id) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger"><i class="ti ti-arrow-back"></i></button>
                                            </form>
                                                                                  
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer">
                    {{ $magasins->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
