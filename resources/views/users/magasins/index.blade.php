@extends('layouts.app_layout')

@section('title', 'Stocks Page')

@section('content')
    @include('Modals.add_magasin')
    @include('Modals.edit_magasin')
    @include('Modals.transfert_au_magasin')

    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMagasinsModal">
                        Ajouter Magasins
                    </button>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addToMagasinsModal">
                        Transférer au Magasin
                    </button>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Description</th>
                                    <th>Adresse</th>
                                    <th>Email</th>
                                    <th>Téléphone</th>
                                    <th>Entreprise</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($magasins as $magasin)
                                    <tr>
                                        <td>{{ $magasin->name }}</td>
                                        <td>{{ $magasin->description }}</td>
                                        <td>{{ $magasin->address }}</td>
                                        <td>{{ $magasin->email }}</td>
                                        <td>{{ $magasin->tel }}</td>

                                        <!-- Display related business info -->
                                        <td>
                                            @if($magasin->business)
                                                {{ $magasin->business->name }}
                                            @else
                                                Aucun
                                            @endif
                                        </td>

                                        <td>
                                            <button type="button" data-id="{{ $magasin->id }}" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#editStockModal">
                                                <i class="ti ti-pencil"></i>
                                            </button>
                                            <form action="{{ route('stock.delete', $magasin->id) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger"><i class="ti ti-trash"></i></button>
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
