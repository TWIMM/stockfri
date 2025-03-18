<!-- Modal for creating a business -->
<div class="modal fade" id="makeInventory" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">Ajouter a l'inventaire</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="inventaire_en_cours" class="form-label">Quantite</label>
                        <input type="number" class="form-control" id="inventaire_en_cours" name="inventaire_en_cours" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Pre enregistrer l'inventaire</button>
                </div>
            </form>
        </div>
    </div>
</div>
