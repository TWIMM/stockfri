<div class="modal fade" id="editTeamMemberModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Modifier le membre de l'équipe</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editTeamMemberForm" method="POST">
                @csrf
                <input type="hidden" name="team_member_id" id="team_member_id_edit">

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name_edit" class="form-label">Nom</label>
                        <input type="text" class="form-control" id="name_edit" name="name">
                    </div>

                    <div class="mb-3">
                        <label for="email_edit" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email_edit" name="email">
                    </div>

                    <div class="mb-3">
                        <label for="tel_edit" class="form-label">Téléphone</label>
                        <input type="text" class="form-control" id="tel_edit" name="tel">
                    </div>

                    <h6>Équipes Associées</h6>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Business</th>

                                <th>Équipe</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="edit_team_list">
                            <tr>
                                <td colspan="2" class="text-center">Chargement...</td>
                            </tr>
                        </tbody>
                    </table>

                    
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Enregistrer</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </form>
        </div>
    </div>
</div>
