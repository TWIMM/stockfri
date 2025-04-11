<!-- Modal pour ajouter un stock -->
<div class="modal fade" id="addQuantityStockModal" tabindex="-1" aria-labelledby="addStockModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="addStockForm" method="POST" action="{{ route('stock.add_up_quantity') }}"  enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addStockModalLabel">Augmenter la quantite</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Sélecteur d'entreprise -->
                    <div class="mb-3">
                        <label for="fournisseur_id" class="form-label">Fournisseurs</label>
                        <select name="fournisseur_id" id="fournisseur_id" class="form-control" required>
                            @foreach ($fournisseurs as $fournisseur)
                                <option value="{{ $fournisseur->id }}">{{ $fournisseur->name }}</option>
                            @endforeach
                        </select>
                    </div>


                    <div class="mb-3">
                        <label for="business_id" class="form-label">Produit</label>
                        <select name="stock_id" id="stock_id" class="form-control" required>
                            @foreach ($stocks as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantité</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" required>
                    </div>

                    <div class="mb-3">
                        <label for="prix_fournisseur" class="form-label">Prix fournisseur</label>
                        <input type="number" class="form-control" id="prix_fournisseur" name="prix_fournisseur" required>
                    </div>

                    <div class="mb-3">
                        <label for="factures_achat" class="form-label">Facture d'achat</label>
                        <input type="file" class="form-control" id="factures_achat" name="factures_achat[]" multiple required>
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
