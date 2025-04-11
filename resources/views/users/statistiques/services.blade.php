@extends('layouts.app_layout')

@section('title', 'Gestion des Coéquipiers')

@section('content')

    @include('Modals.add_client')
    @include('Modals.edit_client')
    @include('Modals.order_clients')

    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    
                </div>

                <div class="card-body">
                    <form method="GET" action="/statistiques_stock" class="mb-4">
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <label for="search">Recherche par nom</label>
                                <input type="text" name="search" id="search" class="form-control" value="{{ request('search') }}" placeholder="Rechercher un stock...">
                            </div>
                            
                            <div class="col-md-12 mt-2">
                                <button type="submit" class="btn btn-primary">Filtrer</button>
                                <a href="/statistiques_stock" class="btn btn-secondary">Réinitialiser</a>
                            </div>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table class="table table-view">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Nombre vendu</th>
                                    <th>Total vendu (F CFA) </th>
                                    <th>Nombre d'acheteur  </th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($services as $service)
                                    <tr>
                                        <td>{{ $service->name  }}</td>
                                        <td>{{ $commandeTotalPerStock($service->id , 'quantity') }}</td>
                                        <td>{{ $commandeTotalPerStock($service->id , 'price') }}</td>
                                        <td>
                                            {{ $commandeTotalPerStock($service->id , 'nombre_acheteur') }}
                                        </td>
                                        
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">Aucun services trouvé.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="d-flex justify-content-center">
                        {{ $services->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Listen for click events on "Edit" buttons
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', function () {
                // Get the data-id from the clicked button
                const clientId = this.getAttribute('data-id');
                
                // Use AJAX to get the client data based on the ID
                fetch(`/clients/${clientId}`)
                    .then(response => response.json())
                    .then(data => {
                        // Populate the modal fields with the retrieved data
                        document.getElementById('edit-client-id').value = data.id;
                        document.getElementById('edit-name').value = data.name;
                        document.getElementById('edit-email').value = data.email;
                        document.getElementById('edit-tel').value = data.tel;
                        document.getElementById('edit-address').value = data.address;
    
                        // Set the form action to the correct route for the update
                        document.getElementById('editForm').action = `/clients/update/${data.id}`;
                    })
                    .catch(error => console.error('Error fetching client data:', error));
             });
        });
    </script>
    
@endsection
