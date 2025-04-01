@extends('layouts.team_member_layout')

@section('title', 'Stocks Page')

@section('content')

    @include('Modals.add_stocks')
    @include('Modals.addup_quantity')
    @include('Modals.edit_stocks')
    @include('Modals.make_ineventory')
    @include('Modals.remove_from_magasin')
    @include('Modals.bonus_from_fournisseur')

    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStockModal">
                        Ajouter Produits
                    </button>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addQuantityStockModal">
                        Ajouter Stock
                    </button>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#remove_from_magasin">
                        Retrait de magasin
                    </button>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#bonus_from_fournisseur">
                        Bonus Fournisseur
                    </button>
                </div>

                <div class="card-body">
                    <form method="GET" action="{{ route('stock.listes') }}" class="mb-4">
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
                                <a href="{{ route('stock.listes') }}" class="btn btn-secondary">Réinitialiser</a>
                            </div>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table class="table table-view">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Description</th>
                                    <th>Quantité </th>

                                    <th>Prix</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stocks as $stock)
                                    <tr>
                                        <td>{{ $stock->name }}</td>
                                        <td>{{ $stock->description }}</td>
                                        <td><span class="badge badge-pill badge-status bg-blue">{{number_format( $stock->quantity )}} </span> </td>

                                        <td>{{number_format( $stock->price )}} FCFA</td>
                                        <td>
                                            <button type="button" data-id='{{$stock->id}}' id='edit-stock-btn' class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#editStockModal">
                                                <i class="ti ti-pencil"></i>
                                           </button> 
                                            <form action="{{ route('stock.delete', $stock->id) }}" method="POST" style="display:inline;">
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
                    {{ $stocks->links() }}
                </div>
            </div>
        </div>
    </div>
    <script>
        // Fetch service data on Edit button click
        document.addEventListener('DOMContentLoaded', function() {
            // Get all edit buttons
            const editButtons = document.querySelectorAll('#edit-stock-btn');

            // Loop through each button and add the event listener
            editButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    const serviceId = button.getAttribute('data-id'); // Get the ID from the clicked button

                    // Make the AJAX request to get the service details
                    const xhr = new XMLHttpRequest();
                    xhr.open('GET', '/stock/' + serviceId, true);
                    xhr.setRequestHeader('Content-Type', 'application/json');
                    xhr.onload = function() {
                        if (xhr.status === 200) {
                            console.log(xhr.responseText);
                            const data = JSON.parse(xhr.responseText); // Parse the JSON response

                            // Populate the modal with the service data
                           // document.getElementById('quantity_edit').value = data.stock.quantity;
                            document.getElementById('name_edit').value = data.stock.name;
                            document.getElementById('description_edit').value = data.stock.description;
                            document.getElementById('price_edit').value = data.stock.price;
                            document.getElementById('business_id_edit').value = data.stock.business_id;
                            document.getElementById('category_id_edit').value = data.stock.category_id;
                            // Set the form action for updating the service
                            let editServiceForm = document.getElementById('editStockForm');
                            editServiceForm.action = `/stock/${data.stock.id}/update`;
                            editServiceForm.method = 'POST';
                        } else {
                            console.log(xhr.responseText)
                            alert('Failed to fetch service details.');
                        }
                    };
                    xhr.send();
                });
            });
        });
    </script>

@endsection
