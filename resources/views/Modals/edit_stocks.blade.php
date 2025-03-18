<!-- Modal pour éditer un stock -->
<div class="modal fade" id="editStockModal" tabindex="-1" aria-labelledby="editStockModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="editStockForm" method="POST" action="{{ route('stock.update', 'id') }}">
            @csrf
            @method('POST')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editStockModalLabel">Modifier un Stock</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Champ pour le nom du stock -->
                    <div class="mb-3">
                        <label for="name_edit" class="form-label">Nom du Stock</label>
                        <input type="text" class="form-control" id="name_edit" name="name" required>
                    </div>

                    <!-- Champ pour la description du stock -->
                    <div class="mb-3">
                        <label for="description_edit" class="form-label">Description</label>
                        <textarea class="form-control" id="description_edit" name="description" rows="3"></textarea>
                    </div>

                    <!-- Champ pour la quantité -->
                    <div class="mb-3">
                        <label for="quantity_edit" class="form-label">Quantité</label>
                        <input type="number" class="form-control" id="quantity_edit" name="quantity" required>
                    </div>

                    <!-- Champ pour le prix -->
                    <div class="mb-3">
                        <label for="price_edit" class="form-label">Prix</label>
                        <input type="number" class="form-control" id="price_edit" name="price" required>
                    </div>

                    <!-- Sélecteur d'entreprise -->
                    <div class="mb-3">
                        <label for="business_id_edit" class="form-label">Entreprise</label>
                        <select name="business_id" id="business_id_edit" class="form-control" required>
                            @foreach($businesses as $business)
                                <option value="{{ $business->id }}">{{ $business->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                </div>
            </div>
        </form>
    </div>
</div>
