@extends('layouts.app_layout')

@section('title', 'Services Page')

@section('content')

    @include('Modals.add_services')
    @include('Modals.edit_services')

    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <div class="col-6">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addServiceModal">
                            Ajouter un nouveau service
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table datanew">
                            <thead>
                                <tr>
                                    <th>Titre du service</th>
                                    <th>Description</th>
                                    <th>Prix</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($services as $service)
                                    <tr>
                                        <td>{{ $service->title }}</td>
                                        <td>{{ $service->description }}</td>
                                        <td>{{ $service->price }} €</td>
                                        <td>
                                            <!-- Edit Button -->
                                            <button type="button" data-id="{{ $service->id }}" class="btn btn-primary"
                                                id="edit-service-btn" data-bs-toggle="modal" data-bs-target="#editServiceModal">
                                                <i class="ti ti-pencil"></i>
                                            </button>
                                            <a href="services/delete/{{ $service->id}}" class="btn btn-warning"> <i class="ti ti-trash"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">Aucun service trouvé.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="d-flex justify-content-center">
                        {{ $services->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Fetch service data on Edit button click
        document.addEventListener('DOMContentLoaded', function() {
            // Get all edit buttons
            const editButtons = document.querySelectorAll('#edit-service-btn');

            // Loop through each button and add the event listener
            editButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    const serviceId = button.getAttribute('data-id'); // Get the ID from the clicked button

                    // Make the AJAX request to get the service details
                    const xhr = new XMLHttpRequest();
                    xhr.open('GET', '/services/' + serviceId, true);
                    xhr.setRequestHeader('Content-Type', 'application/json');
                    xhr.onload = function() {
                        if (xhr.status === 200) {
                            console.log(xhr.responseText);
                            const data = JSON.parse(xhr.responseText); // Parse the JSON response

                            // Populate the modal with the service data
                            document.getElementById('service_id_edit').value = data.service.id;
                            document.getElementById('title_edit').value = data.service.title;
                            document.getElementById('description_edit').value = data.service.description;
                            document.getElementById('price_edit').value = data.service.price;
                            document.getElementById('business_id_edit').value = data.service.business_id;

                            // Set the form action for updating the service
                            let editServiceForm = document.getElementById('editServiceForm');
                            editServiceForm.action = `/services/${data.service.id}/update`;
                            editServiceForm.method = 'POST';
                        } else {
                            alert('Failed to fetch service details.');
                        }
                    };
                    xhr.send();
                });
            });
        });
    </script>

@endsection
