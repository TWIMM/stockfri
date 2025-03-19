<!-- Modal pour modifier un fournisseur -->
<div class="modal fade" id="editFournisseurModal" tabindex="-1" aria-labelledby="editFournisseurModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editFournisseurForm" action="" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editFournisseurModalLabel">Modifier un Fournisseur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label for="name_edit">Nom</label>
                        <input type="text" name="name" id="name_edit" class="form-control" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="email_edit">Email</label>
                        <input type="email" name="email" id="email_edit" class="form-control" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="phone_edit">Téléphone</label>
                        <input type="text" name="phone" id="phone_edit" class="form-control" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="phone_edit">IFU</label>
                        <input type="text" name="ifu" id="ifu_edit" class="form-control" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="address_edit">Adresse</label>
                        <input type="text" name="address" id="address_edit" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    <button type="submit" class="btn btn-primary">Mettre à jour le Fournisseur</button>
                </div>
            </form>
        </div>
    </div>
</div>
