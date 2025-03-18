<!-- Modal pour ajouter un stock -->
<div class="modal fade" id="addStockModal" tabindex="-1" aria-labelledby="addStockModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="addStockForm" method="POST" action="{{ route('stock.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addStockModalLabel">Ajouter un nouveau Stock</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Champ pour le nom du stock -->
                    <div class="mb-3">
                        <label for="name" class="form-label">Nom du Stock</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>

                    <!-- Champ pour la description du stock -->
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>

                    <!-- Champ pour la quantité -->
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantité</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" required>
                    </div>

                    <!-- Champ pour le prix -->
                    <div class="mb-3">
                        <label for="price" class="form-label">Prix</label>
                        <input type="number" class="form-control" id="price" name="price" required>
                    </div>

                    <!-- Sélecteur d'entreprise -->
                    <div class="mb-3">
                        <label for="business_id" class="form-label">Entreprise</label>
                        <select name="business_id" id="business_id" class="form-control" required>
                            @foreach($businesses as $business)
                                <option value="{{ $business->id }}">{{ $business->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    <button type="submit" class="btn btn-primary">Ajouter le stock</button>
                </div>
            </div>
        </form>
    </div>
</div>
