@extends('layouts.app_layout')

@section('title', 'Team Page')

@section('content')

    @include('Modals.add_teams')
    @include('Modals.edit_teams')

    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <div class="col-6">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#staticBackdrop">
                            Ajouter une nouvelle équipe
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <form method="GET" action="{{ route('teams.listes') }}" class="mb-4">
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <label for="search">Recherche par nom</label>
                                <input type="text" name="search" id="search" class="form-control" value="{{ request('search') }}" placeholder="Rechercher une équipe...">
                            </div>
                           
                            <div class="col-md-12 mt-2">
                                <button type="submit" class="btn btn-primary">Filtrer</button>
                                <a href="{{ route('teams.listes') }}" class="btn btn-secondary">Réinitialiser</a>
                            </div>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table class="table datanew">
                            <thead>
                                <tr>
                                    <th>Nom de l'équipe</th>
                                    <th>Business associé</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($teams as $team)
                                    <tr>
                                        <td>{{ $team->name }}</td>
                                        <td>
                                            @if ($team->business->isNotEmpty())
                                                @php
                                                    // Check if all businesses are soft-deleted
                                                    $allDeleted = $team->business->every(function ($business) {
                                                        return $business->deleted_at !== null;
                                                    });
                                                @endphp

                                                @if ($allDeleted)
                                                    <span class="badge badge-warning">No business linked</span>
                                                @else
                                                    <ul>
                                                        @foreach ($team->business as $business)
                                                            <li>

                                                                @if (!$business->deleted_at)
                                                                    <!-- Check if the business is soft-deleted -->
                                                                    {{--  <span class="badge badge-danger">(Deleted)</span> --}}
                                                                    {{ $business->name }}
                                                                @endif
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                @endif
                                            @else
                                                N/A
                                            @endif
                                        </td>



                                        <td>
                                            <!-- Edit Button -->
                                            <button type="button" data-id="{{ $team->id }}" class="btn btn-primary"
                                                id="edit-team-btn" data-bs-toggle="modal" data-bs-target="#editTeamModal">
                                                <i class="ti ti-pencil"></i>
                                            </button>
                                            <a href="{{ route('teams.delete', $team->id) }}"
                                                class="btn btn-warning"> <i class="ti ti-trash"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center">Aucune équipe trouvée.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="d-flex justify-content-center">
                        {{ $teams->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Fetch team data on Edit button click
        document.addEventListener('DOMContentLoaded', function() {
            // Get all edit buttons
            const editButtons = document.querySelectorAll('#edit-team-btn');

            // Loop through each button and add the event listener
            editButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    const teamId = button.getAttribute(
                        'data-id'); // Get the ID from the clicked button

                    // Make the AJAX request to get the team details
                    const xhr = new XMLHttpRequest();
                    xhr.open('GET', '/teams/' + teamId, true);
                    xhr.setRequestHeader('Content-Type', 'application/json');
                    xhr.onload = function() {
                        if (xhr.status === 200) {
                            const data = JSON.parse(xhr
                                .responseText); // Parse the JSON response

                            // Populate the modal with the team data
                            document.getElementById('team_id_edit').value = data.team.id;
                            document.getElementById('name_edit').value = data.team.name;

                            // Update the multi-select for associated businesses
                            let businessSelect = document.getElementById('business_id_edit');
                            // Clear previous selections:
                            //Array.from(businessSelect.options).forEach(option => option.selected = false);

                            // Loop through associated businesses and mark the corresponding options as selected
                            if (data.businesses) {
                                data.businesses.forEach(function(business) {
                                    //let option = businessSelect.querySelector('option[value="' + business.id + '"]');
                                    //if (option) {
                                    //    option.selected = true;
                                    //}
                                    let inputEyesOn = document.getElementById(
                                        "business_id_edit" + business.id);

                                    if(inputEyesOn){
                                        inputEyesOn.checked = true;
                                    }
                                });
                            }

                            // Set the form action for updating the team
                            let editTeamForm = document.getElementById('editTeamForm');
                            editTeamForm.action = `/teams/${data.team.id}/update`;
                            editTeamForm.method = 'POST';
                        } else {
                            alert('Failed to fetch team details.');
                        }
                    };
                    xhr.send();
                });
            });


            /*   // Handle the form submission to update team details
              const editTeamForm = document.getElementById('editTeamForm');
              if (editTeamForm) {
                  editTeamForm.addEventListener('submit', function(e) {
                      e.preventDefault();

                      const formData = new FormData(editTeamForm);
                      const teamId = document.getElementById('team_id_edit').value; // Get the team ID from the hidden input field

                      // Create an AJAX request for updating team details
                      const xhr = new XMLHttpRequest();
                      xhr.open('POST', '/teams/' + teamId + '/update', true);
                      xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

                      // Serialize the FormData
                      const data = new URLSearchParams(formData).toString();

                      xhr.onload = function() {
                          if (xhr.status === 200) {
                              const modal = document.getElementById('editTeamModal');
                              if (modal) {
                                  modal.classList.remove('show');
                              }
                              location.reload(); // Reload the page to reflect changes
                          } else {
                              alert('Failed to update team details.');
                          }
                      };

                      xhr.send(data);
                  });
              } */
        });
    </script>

@endsection
