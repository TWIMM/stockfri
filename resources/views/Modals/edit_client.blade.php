<div class="modal fade" id="editClientModal" tabindex="-1" aria-labelledby="editClientModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editClientModalLabel">Modifier un Client</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" method="POST" action="" >
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <input type="hidden" id="edit-client-id" name="id"> <!-- Hidden input to store client id -->

                    <div class="mb-3">
                        <label for="edit-name" class="form-label">Nom</label>
                        <input type="text" id="edit-name" class="form-control" name="name" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit-email" class="form-label">Email</label>
                        <input type="email" id="edit-email" class="form-control" name="email" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit-tel" class="form-label">Téléphone</label>
                        <input type="text" id="edit-tel" class="form-control" name="tel" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit-address" class="form-label">Adresse</label>
                        <input type="text" id="edit-address" class="form-control" name="address" required>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Modifier</button>
                </div>
            </form>
        </div>
    </div>
</div>
