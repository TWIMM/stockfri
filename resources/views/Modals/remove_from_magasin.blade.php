<!-- Modal pour ajouter un stock -->
<div class="modal fade" id="remove_from_magasin" tabindex="-1" aria-labelledby="addStockModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="addStockForm" method="POST" action="{{ route('stock.retrait_magasin') }}">
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
                        <select name="magasin_id" id="magasin_id" class="form-control" required>
                           
                           <option value=""></option>
                            @foreach ($magasins as $magasin)
                                <option value="{{ $magasin->id }}">{{ $magasin->name }}</option>
                            @endforeach
                        </select>
                    </div>


                    <div class="mb-3">
                        <label for="business_id" class="form-label">Produit</label>
                        <select name="stock_id" id="stockIdiD" class="form-control" required>
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
<script>
    document.getElementById('magasin_id').addEventListener('change', function () {
        let magasinId = this.value;
        let stockSelect = document.getElementById('stockIdiD');

        // Vider la liste actuelle
        stockSelect.innerHTML = '<option value="">Chargement...</option>';

        // Faire appel à la route Laravel
        fetch(`/get-stocks/${magasinId}`)
            .then(response => response.json())
            .then(data => {
                stockSelect.innerHTML = ''; // Vider les anciennes options
                console.log(data);
                if (data.length > 0) {
                    data.forEach(stock => {
                        console.log(stock);
                        let option = document.createElement('option');
                        option.value = stock.id;
                        option.text = stock.name;
                        stockSelect.appendChild(option);
                    });
                } else {
                    stockSelect.innerHTML = '<option value="">Aucun stock trouvé</option>';
                }
            })
            .catch(error => {
                console.error('Erreur lors de la récupération des stocks:', error);
                stockSelect.innerHTML = '<option value="">Erreur de chargement</option>';
            });
    });
</script>
