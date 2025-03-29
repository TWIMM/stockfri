@extends('layouts.app_layout')

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
                    <form method="GET" action="{{ route('stock.moves') }}" class="mb-4">
                        <div class="row">
                            <div class="col-md-3 mb-2">
                                <label for="product">Produit</label>
                                <select name="product" id="product" class="form-control">
                                    <option value="">Tous les produits</option>
                                    @foreach($stocks as $product)
                                        <option value="{{ $product->id }}" {{ request('product') == $product->id ? 'selected' : '' }}>
                                            {{ $product->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label for="type">Type de mouvement</label>
                                <select name="type" id="type" class="form-control">
                                    <option value="">Tous les types</option>
                                    <option value="entrée" {{ request('type') == 'entrée' ? 'selected' : '' }}>Entrée</option>
                                    <option value="sortie" {{ request('type') == 'sortie' ? 'selected' : '' }}>Sortie</option>
                                    <option value="ajustement" {{ request('type') == 'ajustement' ? 'selected' : '' }}>Ajustement</option>
                                    <option value="bonus" {{ request('type') == 'bonus' ? 'selected' : '' }}>Bonus</option>
                                </select>
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
                                <a href="{{ route('stock.moves') }}" class="btn btn-secondary">Réinitialiser</a>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-view">
                            <thead>
                                <tr>
                                    <th>Produit</th>
                                    <th>Date</th>
                                    <th>Quantité </th>

                                    <th>Type</th>
                                   
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($moves as $stock)
                                    <tr>
                                        <td>{{ $stock->stock_id }}</td>
                                        <td>{{ $stock->created_at }}</td>
                                        <td><span class="badge badge-pill badge-status bg-blue">{{number_format( $stock->quantity )}} </span> </td>

                                        <td>{{ $stock->type_de_mouvement }} </td>
                                       
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer">
                    {{ $moves->links() }}
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
                            document.getElementById('description_edit').value = data.stock.description;s
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
