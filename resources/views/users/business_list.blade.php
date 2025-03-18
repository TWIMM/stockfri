@extends('layouts.app_layout')

@section('title', 'Business Page')

@section('content')

    @include('Modals.add_business')
    @include('Modals.edit_business')

    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">

                    <div class="col-6">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#staticBackdrop">
                            Ajouter un nouveau business
                        </button>
                    </div>
                </div>

                <div class="card-body">


                    <div class="table-responsive">
                        <table class="table  datanew ">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Ifu</th>
                                    <th>Type de Business</th>
                                    <th>Email profesionnel</th>
                                    <th>Telephone</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($businesses as $business)
                                    <tr>
                                        <td>{{ $business->name }}</td>
                                        <td>{{ $business->ifu }}</td>
                                        <td>{{ $business->type == 'business_physique' ? 'Business Physique' : 'Prestation de service' }}
                                        </td>
                                        <td>{{ $business->business_email }}</td>
                                        <td>{{ $business->number }}</td>
                                        <td>
                                            <!-- Edit Button -->
                                            <button type="button" data-id="{{ $business->id }}" class="btn btn-primary"
                                                id='edit-business-btn' data-bs-toggle="modal"
                                                data-bs-target="#editBusinessModal">
                                                <i class="ti ti-pencil"></i>
                                            </button>
                                            {{-- <a href="{{ route('business.edit', $business->id) }}" class="btn btn-primary">Edit</a> --}}
                                            <a href="{{ route('business.delete', $business->id) }}"
                                                class="btn btn-warning"> <i class="ti ti-trash"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">Aucun business trouvé.</td>
                                    </tr>
                                @endforelse
                            </tbody>

                        </table>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="d-flex justify-content-center">
                        {{ $businesses->links('pagination::bootstrap-5') }}

                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        // Fetch business data on Edit button click
        document.addEventListener('DOMContentLoaded', function() {
            // Get all edit buttons
            const editButtons = document.querySelectorAll('#edit-business-btn');

            // Loop through each button and add the event listener
            editButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    const businessId = button.getAttribute(
                        'data-id'); // Get the ID from the clicked button

                    // Make the AJAX request to get the business details
                    const xhr = new XMLHttpRequest();
                    xhr.open('GET', '/business/' + businessId, true);
                    xhr.setRequestHeader('Content-Type', 'application/json');
                    xhr.onload = function() {
                        if (xhr.status === 200) {
                            const data = JSON.parse(xhr
                                .responseText); // Parse the JSON response

                            // Populate the modal with the business data
                            document.getElementById('description_edit').value = data
                                .description;
                            document.getElementById('business_id_edit').value = data.id;
                            document.getElementById('name_edit').value = data.name;
                            document.getElementById('ifu_edit').value = data.ifu;
                            document.getElementById('commercial_number_edit').value = data
                                .commercial_number;
                            document.getElementById('business_email_edit').value = data
                                .business_email;
                            document.getElementById('number_edit').value = data.number;
                            

                            // Update the form action & method correctly
                            const editBusinessForm = document.getElementById('editBusinessForm');
                            editBusinessForm.action = `/business/${data.id}/update`;
                            editBusinessForm.method = 'POST';
                        } else {
                            alert('Failed to fetch business details.');
                        }
                    };
                    xhr.send();
                });
            });

            /* // Handle the form submission to update business details
            const editBusinessForm = document.getElementById('editBusinessForm');
            if (editBusinessForm) {
                editBusinessForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const formData = new FormData(editBusinessForm);
                    const businessId = document.getElementById('business_id_edit')
                        .value; // Get the business ID from the hidden input field

                    // Create an AJAX request for updating business details
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', '/business/' + businessId + '/update', true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

                    // Serialize the FormData
                    const data = new URLSearchParams(formData).toString();

                    xhr.onload = function() {
                        if (xhr.status === 200) {
                            //alert('Business details updated successfully!');
                            let response = JSON.parse(this.responseText);
                            if (response.message = 'Updated successfully!') {
                                const modal = document.getElementById('editBusinessModal');
                                if (modal) {
                                    modal.classList.remove('show');
                                }
                                location.reload();
                            } else if (response.message = 'Update failed!') {
                                Notiflix.Notify.failure(response.message, {
                                    timeout: 40000,
                                    zindex: 10000,

                                });
                            }
                        } else {
                            Notiflix.Notify.failure('Update failed!', {
                                timeout: 40000,
                                zindex: 10000,

                            });
                        }
                    };

                    xhr.send(data);
                });
            } */
        });
    </script>



@endsection
