@extends('layouts.team_member_layout')

@section('title', 'Stocks Page')

@section('content')
    @include('Modals.add_magasin')
    @include('Modals.edit_magasin')

    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMagasinsModal">
                        <i class="ti ti-plus"></i> Ajouter Magasins
                    </button>
                   
                </div>

                <div class="card-body">
                    <form method="GET" action="{{ route('magasins.listes') }}" class="mb-4">
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <label for="search">Recherche par nom</label>
                                <input type="text" name="search" id="search" class="form-control" value="{{ request('search') }}" placeholder="Rechercher un magasin...">
                            </div>
                            <div class="col-md-4 mb-2">
                                <label for="email">Email</label>
                                <input type="text" name="email" id="email" class="form-control" value="{{ request('email') }}" placeholder="Filtrer par email">
                            </div>
                            <div class="col-md-4 mb-2">
                                <label for="tel">Téléphone</label>
                                <input type="text" name="tel" id="tel" class="form-control" value="{{ request('tel') }}" placeholder="Filtrer par téléphone">
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
                                    <th>Description</th>
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
                                            <a href="magasin_details/{{$magasin->id}}">
                                                <button type="button" data-id="{{ $magasin->id }}" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#editStockModal">
                                                    <i class="ti ti-eye"></i>
                                                </button>
                                            </a>
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
