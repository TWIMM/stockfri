<div class="modal fade" id="editPermissionsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Modifier les permissions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="permissionsForm" method="POST">
                @csrf
                <input type="hidden" name="team_member_id" id="team_member_id">
                <input type="hidden" name="team_id" id="team_id">

                <div class="modal-body" id="permissionsContainer">
                    <!-- Permissions checkboxes will be inserted here dynamically -->
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Enregistrer</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </form>
        </div>
    </div>
</div>
