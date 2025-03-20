@extends('layouts.app_layout')

@section('title', 'Gestion des Coéquipiers')

@section('content')

    @include('Modals.add_client')
    @include('Modals.edit_client')

    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addClientModal">
                        Ajouter un client
                    </button>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-view">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Email</th>
                                    <th>Téléphone</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($clients as $coequipier)
                                    <tr>
                                        <td>{{ $coequipier->name }}</td>
                                        <td>{{ $coequipier->email }}</td>
                                        <td>{{ $coequipier->tel }}</td>
                                        <td>
                                            <button type="button" class="btn btn-secondary edit-btn"
                                                data-id="{{ $coequipier->id }}" data-name="{{ $coequipier->name }}"
                                                data-email="{{ $coequipier->email }}" data-tel="{{ $coequipier->tel }}"
                                                data-bs-toggle="modal" data-bs-target="#editClientModal">
                                                <i class="ti ti-pencil"></i>
                                            </button>
                                            <form action="{{ route('clients.destroy', $coequipier->id) }}"
                                                method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger"> <i class="ti ti-trash"></i></button>
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
