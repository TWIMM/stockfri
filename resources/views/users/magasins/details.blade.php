@extends('layouts.app_layout')

@section('title', 'Details magasin Page')

@section('content')
    @include('Modals.add_magasin')
    @include('Modals.edit_magasin')
    @include('Modals.transfert_au_magasin')

    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                 
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addToMagasinsModal">
                        Transférer au Magasin
                    </button>
                </div>

                <div class="card-body">
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
                                        <td>{{ $stock->quantity }}</td>

                                        <td>{{ $stock->price }} FCFA</td>
                                        <td>
                                            <button type="button" data-id='{{$stock->id}}' id='edit-stock-btn' class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#editStockModal">
                                                <i class="ti ti-receipt"></i>
                                           </button> 
                                            <form action="{{ route('stock.delete', $stock->id) }}" method="POST" style="display:inline;">
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
