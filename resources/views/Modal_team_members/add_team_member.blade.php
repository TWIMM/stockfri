<!-- Modal for creating a team member -->
<div class="modal fade" id="addTeamMemberModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">Ajouter un Membre d'Équipe</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('team_member.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    {{-- select rrole --}}
                   {{--  <div class="mb-3">
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


                    <!-- Permissions List -->
                    {{-- <div class="mb-3">
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
                    </div> --}}
                    <!-- Team Member Name -->
                    <div class="mb-3">
                        <label for="name" class="form-label">Nom du Membre</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                        <input type="hidden" class="form-control" id="team_member" value='there' name="team_member" required>

                    </div>

                    <!-- Email -->
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>

                    <!-- Phone Number -->
                    <div class="mb-3">
                        <label for="tel" class="form-label">Téléphone</label>
                        <input type="text" class="form-control" id="tel" name="tel" required>
                    </div>

                    <!-- Team Selection with Business Name -->
                   {{--  <div class="mb-3">
                        <label for="team_id" class="form-label">Équipe et Business Associé</label>
                        <select class="form-select" name="team_id" id="team_id" required>
                            @forelse($teams as $team)
                                <option value="{{ $team->id }}">
                                    {{ $team->name }} (
                                    @if ($team->business->isNotEmpty())
                                        @foreach ($team->business as $business)
                                            {{ $business->name }}{{ !$loop->last ? ', ' : '' }}
                                        @endforeach
                                    @else
                                        Aucun Business
                                    @endif
                                    )
                                </option>
                            @empty
                                <option value="">Aucune équipe disponible</option>
                            @endforelse
                        </select>
                    </div> --}}

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    <button type="submit" class="btn btn-primary">Ajouter Membre</button>
                </div>
            </form>

          {{--   <script>
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
        </div>
    </div>
</div>
