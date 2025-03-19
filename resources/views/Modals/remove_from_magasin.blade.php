<!-- Modal pour ajouter un stock -->
<div class="modal fade" id="remove_from_magasin" tabindex="-1" aria-labelledby="addStockModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="addStockForm" method="POST" action="{{ route('stock.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addStockModalLabel">Retour de magasin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Sélecteur d'entreprise -->
                    <div class="mb-3">
                        <label for="business_id" class="form-label">Magasin</label>
                        <select name="business_id" id="business_id" class="form-control" required>
                            @foreach ($businesses as $business)
                                <option value="{{ $business->id }}">{{ $business->name }}</option>
                            @endforeach
                        </select>
                    </div>


                    <div class="mb-3">
                        <label for="business_id" class="form-label">Produit</label>
                        <select name="business_id" id="business_id" class="form-control" required>
                            @foreach ($businesses as $business)
                                <option value="{{ $business->id }}">{{ $business->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantité</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" required>
                    </div>

                    <div class="mb-3">
                        <label for="quantity" class="form-label">Motif</label>
                        <input type="text" class="form-control" id="motif" name="motif" required>
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
