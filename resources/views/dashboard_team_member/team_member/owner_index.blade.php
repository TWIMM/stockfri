@extends('layouts.team_member_layout')

@section('title', 'Team Members Page')

@section('content')

    @include('Modal_team_members.add_team_member')
    @include('Modal_team_members.edit_team_member')
    @include('Modal_team_members.linkmembertoteam')

    <div class="row">
        <div class="col-sm-12"> 
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTeamMemberModal">
                        Ajouter un nouveau membre d'équipe
                    </button>

                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#linkToTeam">
                        Lier membre à une équipe
                    </button>
                </div>


                <div class="card-body">
                    <form method="GET" action="{{ route('owner.team_member.listes') }}" class="mb-4">
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <label for="search">Recherche par nom</label>
                                <input type="text" name="search" id="search" class="form-control" value="{{ request('search') }}" placeholder="Rechercher un membre...">
                            </div>
                           
                            <div class="col-md-12 mt-2">
                                <button type="submit" class="btn btn-primary">Filtrer</button>
                                <a href="{{ route('owner.team_member.listes') }}" class="btn btn-secondary">Réinitialiser</a>
                            </div>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table class="table datanew">
                            <thead>
                                <tr>
                                    <th>Nom du membre</th>
                                    <th>Email</th>
                                    <th>Téléphone</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($teamMembers as $teamMember)
                                    <tr>
                                        <td>{{ $teamMember->name }}</td>
                                        <td>{{ $teamMember->email }}</td>
                                        <td>{{ $teamMember->tel }}</td>

                                        <td>
                                            <!-- Edit Button -->
                                            <button type="button" data-id="{{ $teamMember->id }}" class="btn btn-success"
                                                id="edit-team-member-btn" data-bs-toggle="modal"
                                                data-bs-target="#editTeamMemberModal">
                                                <i class="fas fa-pencil"></i>
                                            </button>
                                            <a href="{{ route('team_member.delete', $teamMember->id) }}"
                                                class="btn btn-warning"> <i class="fas fa-trash"></i> </a>
                                        </td>
                                    </tr>
                                    @include('Modals.permissions_modal', [
                                        'teamMember' => $teamMember,
                                        'teams' => $teamMember->teams,
                                    ])

                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">Aucun membre d'équipe trouvé.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="d-flex justify-content-center">
                        {{ $teamMembers->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const editButtons = document.querySelectorAll('#edit-team-member-btn');

            editButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    const memberId = button.getAttribute('data-id');

                    // Fetch team member details
                    const xhr = new XMLHttpRequest();
                    xhr.open('GET', `/team_member/${memberId}`, true);
                    xhr.setRequestHeader('Content-Type', 'application/json');
                    xhr.onload = function() {
                        if (xhr.status === 200) {
                            const data = JSON.parse(xhr.responseText);
                            document.getElementById('team_member_id_edit').value = data.id;
                            document.getElementById('name_edit').value = data.name;
                            document.getElementById('email_edit').value = data.email;
                            document.getElementById('tel_edit').value = data.tel;

                            // Update form action for submitting changes
                            const editTeamMemberForm = document.getElementById(
                                'editTeamMemberForm');
                            editTeamMemberForm.action = `/team_member/${data.id}/update`;
                            editTeamMemberForm.method = 'POST';

                            // Load associated teams for the member
                            loadTeamsForMember(data.id);
                        } else {
                            console.error('Failed to fetch team member details');
                        }
                    };
                    xhr.send();
                });
            });


            const modalElement = document.getElementById('editPermissionsModal');

            // Ensure Bootstrap properly manages the modal
            const permissionsModal = new bootstrap.Modal(modalElement);

            document.body.addEventListener('click', function(event) {
                if (event.target.closest('.open-permissions-modal')) {
                    const button = event.target.closest('.open-permissions-modal');
                    const memberId = button.getAttribute('data-member-id');
                    const teamId = button.getAttribute('data-team-id');
                    const teamName = button.getAttribute('data-team-name');

                    console.log("Clicked on permissions for:", teamName);

                    document.getElementById('team_member_id').value = memberId;
                    document.getElementById('team_id').value = teamId;
                    document.getElementById('modalTitle').textContent =
                        `Modifier les permissions pour ${teamName}`;

                        fetch(`/team_member/${memberId}/teams`)
                        .then(response => response.json())
                        .then(data => {
                            const container = document.getElementById('permissionsContainer');
                            container.innerHTML = ''; // Clear previous permissions

                            const team = data.teams.find(t => t.id == teamId);
                            if (team) {
                                // Show a loading message
                                container.innerHTML = '<p>Loading permissions...</p>';

                                // Fetch all permissions in one request
                                const permissionIds = team.permissions; // Array of permission IDs
                                fetch(`/team_member/${permissionIds.join(',')}/getperms`)
                                    .then(response => response.json())
                                    .then(permissionData => {
                                        if (!permissionData || !Array.isArray(permissionData
                                                .permissions)) {
                                            throw new Error("Invalid permissions data received");
                                        }

                                        // Generate the checkboxes dynamically
                                        const checkboxes = permissionData.permissionsAll.map(({
                                            id,
                                            name
                                        }) => {
                                            // Check if the user has this permission
                                            const isChecked = permissionData.permissions
                                                .some(userPerm => userPerm.id === id);

                                            const capitalizedName = name.charAt(0).toUpperCase() + name.slice(1);


                                            return `
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" name="permissions[]" 
                                                        value="${id}" id="perm_${id}" ${isChecked ? 'checked' : ''}>
                                                    <label class="form-check-label" for="perm_${id}">
                                                      ${capitalizedName} <!-- Display Permission Name -->
                                                    </label>
                                                </div>
                                            `;
                                        }).join('');

                                        // Insert all checkboxes into the container
                                        const container = document.getElementById(
                                            'permissionsContainer'); // Adjust the container ID
                                        container.innerHTML = checkboxes;

                                        // Show the modal once data is loaded
                                        const modal = new bootstrap.Modal(document.getElementById(
                                            'editPermissionsModal'));
                                        modal.show();
                                    })
                                    .catch(error => {
                                        console.error("Error fetching permissions:", error);
                                    });

                            }
                        })
                        .catch(error => console.error("Error fetching team permissions:", error));

                    permissionsModal.show();
                }
            });

            // Close modal properly when clicking on close button
            modalElement.addEventListener('hidden.bs.modal', function() {
                console.log("Permissions modal closed");

                // Ensure backdrop is removed
                document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());

                // Ensure body scrolling is re-enabled
                document.body.classList.remove('modal-open');
            });




            function loadTeamsForMember(memberId) {
                const xhr = new XMLHttpRequest();
                xhr.open('GET', `/team_member/${memberId}/teams`, true);
                xhr.setRequestHeader('Content-Type', 'application/json');
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        const response = JSON.parse(xhr.responseText);
                        const teamListContainer = document.getElementById('edit_team_list');
                        teamListContainer.innerHTML = ''; // Clear previous entries

                        if (response.teams.length > 0) {
                            response.teams.forEach(team => {

                                function myModalFunction() {
                                    console.log("ok");
                                }
                                const teamRow = `
                                <tr>
                                    <td>${team.business_id}</td>
                                    <td>${team.name}</td>
                                    <td>
                                        <a href="/remove.team.link?team_member_id=${memberId}&team_id=${team.id}" class="btn btn-danger">
                                            <i class="fas fa-times"></i> Retirer
                                        </a>
                                        <a href="#" class="btn btn-info text-white open-permissions-modal"  
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editPermissionsModal" 
                                            data-member-id="${memberId}" 
                                            data-team-id="${team.id}" 
                                            data-team-name="${team.name}">
                                            <i class="fas fa-eye"></i> Permissions
                                        </a>
                                    </td>
                                </tr>`;


                                teamListContainer.innerHTML += teamRow;
                            });


                        } else {
                            teamListContainer.innerHTML =
                                '<tr><td colspan="2" class="text-center">Aucune équipe associée</td></tr>';
                        }
                    } else {
                        console.error('Failed to load teams for member');
                    }
                };
                xhr.send();
            }
        });
    </script>

@endsection
