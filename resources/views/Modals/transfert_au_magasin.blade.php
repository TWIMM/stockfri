<!-- Modal pour ajouter un stock -->
<div class="modal fade" id="addToMagasinsModal" tabindex="-1" aria-labelledby="addStockModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="addStockForm" method="POST" action="{{ route('magasins.magasins_add_produits') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addStockModalLabel">Envoyé au Magasin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">                    

                    <div class="mb-3">
                        <label for="stock_id" class="form-label">Produit</label>
                        <select name="stock_id" id="stock_id" class="form-control" required>
                            @foreach ($stocks as $stock)
                                <option value="{{ $stock->id }}">{{ $stock->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantité</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" required>
                    </div>

                    <div class="mb-3">
                        <label for="magasin" class="form-label">Magasin</label>
                        <select name="magasin_id" id="magasin_id" class="form-control" required>
                            @foreach ($magasins as $magasin)
                                <option value="{{ $magasin->id }}">{{ $magasin->name }}</option>
                            @endforeach
                        </select>                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    <button type="submit" class="btn btn-primary">Ajouter le stock</button>
                </div>
            </div>
        </form>
    </div>
</div>
