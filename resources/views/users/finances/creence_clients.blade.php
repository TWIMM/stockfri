@extends('layouts.app_layout')

@section('title', 'Gestion des Coéquipiers')

@section('content')

    @include('Modals.remboursement')

    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                  <h5>Créentié(e)s</h5>  
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-view">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Dette actuelle</th>
                                    <th>Limite de crédit</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($clients as $coequipier)
                                    <tr>
                                        <td>{{ $coequipier->name }}</td>
                                        <td>{{ number_format($coequipier->current_debt) }} FCFA</td>
                                        <td>{{ number_format($coequipier->limit_credit_for_this_user) }} FCFA</td>
                                        <td>
                                            <button type="button" class="btn btn-secondary edit-btn"
                                                data-id="{{ $coequipier->id }}" data-name="{{ $coequipier->name }}"
                                                data-email="{{ $coequipier->email }}" data-tel="{{ $coequipier->tel }}"
                                                data-bs-toggle="modal" data-bs-target="#remboursementModal">
                                                <i class="ti ti-receipt"></i>
                                            </button>
                                           
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
                document.getElementById('put_id_in_there').value = clientId;
                
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
