<div class="modal fade" id="linkToTeam" tabindex="-1" aria-labelledby="addTeamMemberModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Affilier un membre</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('team_member.assign') }}" method="POST">
                @csrf
                <div class="modal-body">

                      {{-- select rrole --}}
                    {{-- <div class="mb-3">
                        <label for="role_id" class="form-label">Rôle</label>
                        <select class="form-select" name="role_id" id="role_id" required>
                            <option value="">-- Sélectionner un rôle --</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}"
                                    data-permissions="{{ json_encode($role->permissions->pluck('id')) }}">
                                    {{ ucfirst($role->name) }}
                                </option>
                            @endforeach
                        </select>
                    </div> --}}
                    <div class="mb-3">
                        <input type="hidden" name="mode_admin" value="0">
                        <input type="checkbox" class="form-check-input" name="mode_admin" id="mode_admin" value="1">
                        <input type="hidden" class="form-control" id="team_member" value='there' name="team_member" required>

                        <label class="form-check-label" for="mode_admin">Mode Admin (Global Permissions)</label>
                    </div>


                    <!-- Permissions List -->
                    <div class="mb-3" id="permssions_id_container">
                        <label class="form-label">Permissions</label>
                        <div class="row">
                            @foreach ($permissions as $permission)
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input permission-checkbox"
                                            name="permissions[]" value="{{ $permission->id }}"
                                            id="perm_{{ $permission->id }}">
                                        <label class="form-check-label" for="perm_{{ $permission->id }}">
                                            {{ ucfirst($permission->name) }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <!-- Select Team -->
                    <div class="mb-3" id="team_id_container">
                        <label for="team_id" class="form-label">Équipe Affectée</label>
                        <select class="form-select" name="team_id" id="team_id" required>
                            <option value="">Sélectionnez une équipe</option>
                            @foreach ($teams as $team)
                                <option value="{{ $team->id }}">{{ $team->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Select Business (Initially Empty) -->
                    <div class="mb-3" id="business_id_container">
                        <label for="business_id" class="form-label">Business Affectée</label>
                        <select class="form-select" name="business_id" id="business_id" required>
                            <option value="">Sélectionnez une équipe d'abord</option>
                        </select>
                    </div>

                    <script>
                        document.addEventListener("DOMContentLoaded", function() {
                        const modeAdminCheckbox = document.getElementById('mode_admin');
                        const permissionsContainer = document.getElementById('permssions_id_container');
                        const businessContainer = document.getElementById('business_id_container');
                        const businessSelect = document.getElementById('business_id');
                        const teamContainer = document.getElementById('team_id_container');
                        const teamSelect = document.getElementById("team_id");

                        function toggleModeAdminFields() {
                            if (modeAdminCheckbox.checked) {
                                businessContainer.style.display = 'none';
                                businessSelect.removeAttribute('required'); // Remove required attribute
                                businessSelect.value = ''; // Clear business selection

                                teamContainer.style.display = 'none';
                                teamSelect.removeAttribute('required'); // Remove required attribute
                                teamSelect.value = ''; // Clear team selection

                                permissionsContainer.style.display = 'none';
                            } else {
                                businessContainer.style.display = 'block';
                                businessSelect.setAttribute('required', 'required'); // Re-add required attribute

                                teamContainer.style.display = 'block';
                                teamSelect.setAttribute('required', 'required'); // Re-add required attribute

                                permissionsContainer.style.display = 'block';
                            }
                        }

                        // Handle change event for mode_admin
                        modeAdminCheckbox.addEventListener('change', toggleModeAdminFields);

                        // Run on page load in case mode_admin is already checked
                        toggleModeAdminFields();
                    });

                    </script>

                    <script>
                    document.addEventListener("DOMContentLoaded", function() {
                        const teamSelect = document.getElementById("team_id");
                        const businessSelect = document.getElementById("business_id");

                        teamSelect.addEventListener("change", function() {
                            const teamId = this.value;
                            businessSelect.innerHTML = '<option value="">Chargement...</option>'; // Show loading state

                            if (teamId) {
                                fetch(`/team/${teamId}/businesses`) // Adjust route as needed
                                    .then(response => response.json())
                                    .then(data => {
                                        businessSelect.innerHTML = ''; // Clear previous options
                                        if (data.businesses.length > 0) {
                                            data.businesses.forEach(business => {
                                                let option = document.createElement("option");
                                                option.value = business.id;
                                                option.textContent = business.name;
                                                businessSelect.appendChild(option);
                                            });
                                        } else {
                                            businessSelect.innerHTML = '<option value="">Aucun Business Disponible</option>';
                                        }
                                    })
                                    .catch(error => {
                                        console.error("Error fetching businesses:", error);
                                        businessSelect.innerHTML = '<option value="">Erreur de chargement</option>';
                                    });
                            } else {
                                businessSelect.innerHTML = '<option value="">Sélectionnez une équipe d\'abord</option>';
                            }
                        });
                    });
                    </script>


                    <div class="mb-3">
                        <select class="form-select" name="team_member_id" id="teamMemberSelect">
                            <option value="">-- Select a Team Member --</option>
                            @foreach ($teamMembers as $teamMember)
                                <option value="{{ $teamMember->id }}">{{ $teamMember->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                 
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    <button type="submit" class="btn btn-primary">Ajouter</button>
                </div>
            </form>
        </div>
    </div>
</div>
{{-- 

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const roleSelect = document.getElementById("role_id");
        const permissionCheckboxes = document.querySelectorAll(".permission-checkbox");

        roleSelect.addEventListener("change", function() {
            // Get selected role's permissions
            let selectedRole = this.options[this.selectedIndex];
            let rolePermissions = JSON.parse(selectedRole.getAttribute("data-permissions") || "[]");

            // Uncheck all permissions first
            permissionCheckboxes.forEach(checkbox => {
                checkbox.checked = false;
            });

            // Check permissions assigned to the selected role
            rolePermissions.forEach(permissionId => {
                let checkbox = document.getElementById("perm_" + permissionId);
                if (checkbox) {
                    checkbox.checked = true;
                }
            });
        });
    });
</script>
 --}}