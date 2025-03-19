@extends('layouts.app_layout')

@section('title', 'Fournisseurs Page')

@section('content')

    @include('Modals.add_fournisseurs') 
    @include('Modals.edit_fournisseurs') 

    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addFournisseurModal">
                        Ajouter Fournisseur
                    </button>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-view">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>IFU</th>
                                    <th>Email</th>
                                    <th>Téléphone</th>
                                    <th>Adresse</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($fournisseurs as $fournisseur)
                                    <tr>
                                        <td>{{ $fournisseur->name }}</td>
                                        <td>{{ $fournisseur->ifu ? $fournisseur->ifu : "Neant" }}</td>

                                        <td>{{ $fournisseur->email }}</td>
                                        <td>{{ $fournisseur->phone }}</td>
                                        <td>{{ $fournisseur->address }}</td>
                                        <td>
                                            <button type="button" data-id="{{ $fournisseur->id }}" id="edit-fournisseur-btn" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#editFournisseurModal">
                                                <i class="ti ti-pencil"></i>
                                            </button>
                                            
                                            <form action="{{ route('fournisseurs.destroy', $fournisseur->id) }}" method="POST" style="display:inline;">
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
                    {{ $fournisseurs->links() }} <!-- Pagination -->
                </div>
            </div>
        </div>
    </div>

    <script>
        // Fetch fournisseur data on Edit button click
        document.addEventListener('DOMContentLoaded', function() {
            const editButtons = document.querySelectorAll('#edit-fournisseur-btn');

            editButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    const fournisseurId = button.getAttribute('data-id'); // Get the ID from the clicked button

                    // Make the AJAX request to get the fournisseur details
                    const xhr = new XMLHttpRequest();
                    xhr.open('GET', '/fournisseurs/' + fournisseurId, true);
                    xhr.setRequestHeader('Content-Type', 'application/json');
                    xhr.onload = function() {
                        if (xhr.status === 200) {
                            const data = JSON.parse(xhr.responseText); // Parse the JSON response

                            // Populate the modal with the fournisseur data
                            document.getElementById('name_edit').value = data.fournisseur.name;
                            document.getElementById('email_edit').value = data.fournisseur.email;
                            document.getElementById('phone_edit').value = data.fournisseur.phone;
                            document.getElementById('address_edit').value = data.fournisseur.address;
                            document.getElementById('ifu_edit').value = data.fournisseur.ifu;

                            // Set the form action for updating the fournisseur
                            let editFournisseurForm = document.getElementById('editFournisseurForm');
                            editFournisseurForm.action = '/fournisseurs/' + data.fournisseur.id; // Set URL to edit
                        } else {
                            alert('Failed to fetch fournisseur details.');
                        }
                    };
                    xhr.send();
                });
            });
        });
    </script>

@endsection
