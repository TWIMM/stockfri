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
                    <form method="GET" action="/statistiques_client" class="mb-4">
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <label for="search">Recherche par nom</label>
                                <input type="text" name="search" id="search" class="form-control" value="{{ request('search') }}" placeholder="Rechercher un client...">
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
                                <a href="{{ route('clients.listes') }}" class="btn btn-secondary">Réinitialiser</a>
                            </div>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table class="table table-view">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Nombre commandes</th>
                                    <th>Prix Commande total (F CFA) </th>
                                    <th>Dette totale (F CFA) </th>
                                    <th>Voir plus</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($clients as $coequipier)
                                    <tr>
                                        <td>{{ $coequipier->name  }}</td>
                                        <td>{{ count($coequipier->purchases ) }}</td>
                                        <td>{{ $commandeTotalPerClient($coequipier->purchases) }}</td>
                                        <td>
                                            {{ number_format($coequipier->current_debt ) }}
                                        </td>
                                        <td>
                                            
                                            <form action="{{ route('statistiques.clients.stats', $coequipier->id) }}"
                                                method="POST" style="display:inline;">
                                                @csrf
                                                @method('GET')
                                                <button type="submit" class="btn btn-secondary"><i class="ti ti-eye"></i></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">Aucun coéquipier trouvé.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="d-flex justify-content-center">
                        {{ $clients->links() }}
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
