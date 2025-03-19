<!-- Modal pour ajouter un fournisseur -->
<div class="modal fade" id="addFournisseurModal" tabindex="-1" aria-labelledby="addFournisseurModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('fournisseurs.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addFournisseurModalLabel">Ajouter un Fournisseur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label for="name">Nom</label>
                        <input type="text" name="name" id="name" class="form-control" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="email">IFU</label>
                        <input type="text" name="ifu" id="ifu" class="form-control" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="email">Email</label>
                        <input type="email" name="email" id="email" class="form-control" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="phone">Téléphone</label>
                        <input type="text" name="phone" id="phone" class="form-control" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="address">Adresse</label>
                        <input type="text" name="address" id="address" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    <button type="submit" class="btn btn-primary">Ajouter Fournisseur</button>
                </div>
            </form>
        </div>
    </div>
</div>
