<!-- Modal de vente -->
<div class="modal fade" id="modalVente" tabindex="-1" aria-labelledby="modalVenteLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalVenteLabel">Nouvelle Vente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/vendre" method="POST">
                <div class="modal-body">
                    <!-- Sélectionner un client -->
                    <div class="mb-3">
                        <label for="clientSelect" class="form-label">Sélectionner un Client</label>
                        <select class="form-select" id="clientSelect" name="client_id" required>
                            <option value="" disabled selected>Choisir un client</option>
                            @foreach ($clients as $client)
                                <option value="{{ $client->id }}">{{ $client->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Produits -->
                    <div id="productsContainer">
                        <!-- Initial product row -->
                        <div class="productRow mb-3">
                            <div class="d-flex">
                                <!-- Produit -->
                                <div class="flex-fill">
                                    <label for="productSelect" class="form-label">Produit</label>
                                    <select class="form-select productSelect" name="products[][product_id]" required>
                                        <option value="" disabled selected>Choisir un produit</option>
                                        @foreach ($stocks as $stock)
                                            <option value="{{ $stock->id }}" data-price="{{ $stock->price }}">
                                                {{ $stock->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Quantité -->
                                <div class="flex-fill ms-2">
                                    <label for="quantity" class="form-label">Quantité</label>
                                    <input type="number" class="form-control" name="products[][quantity]"
                                        min="1" required>
                                </div>

                                <!-- Remise -->
                                <div class="flex-fill ms-2">
                                    <label for="discount" class="form-label">Remise (%)</label>
                                    <input type="number" class="form-control" name="products[][discount]"
                                        min="0" max="100">
                                </div>

                                <!-- Prix Unitaire -->
                                <div class="flex-fill ms-2">
                                    <label for="price" class="form-label">Prix Unitaire</label>
                                    <input type="number" class="form-control" name="products[][price]" required
                                        readonly>
                                </div>

                                <!-- Supprimer Produit -->
                                <div class="flex-fill ms-2">
                                    <button type="button" class="btn btn-danger removeProduct"
                                        aria-label="Supprimer produit">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Ajouter un produit -->
                    <button type="button" class="btn btn-primary" id="addProductButton">Ajouter un produit</button>

                    <!-- TVA -->
                    <div class="mb-3 mt-4">
                        <label for="tva" class="form-label">TVA (%)</label>
                        <input type="number" class="form-control" id="tva" name="tva" min="0"
                            required>
                    </div>

                    <!-- Mode de règlement -->
                    <div class="mb-3">
                        <label for="paymentMode" class="form-label">Mode de règlement</label>
                        <select class="form-select" id="paymentMode" name="payment_mode" required>
                            <option value="" disabled selected>Choisir un mode de règlement</option>
                            <option value="cash">Espèces</option>
                            <option value="credit_card">Carte de crédit</option>
                            <option value="mobile_money">Mobile Money</option>
                            <option value="bank_transfer">Virement bancaire</option>
                        </select>
                    </div>

                    <!-- Statut de la facture -->
                    <div class="mb-3">
                        <label for="invoiceStatus" class="form-label">Statut de la facture</label>
                        <select class="form-select" id="invoiceStatus" name="invoice_status" required>
                            <option value="" disabled selected>Choisir un statut</option>
                            <option value="paid">Payée</option>
                            <option value="partially_paid">Partiellement payée</option>
                            <option value="unpaid">Non payée</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    <button type="submit" class="btn btn-primary">Enregistrer la vente</button>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
    // Function to create a new product row dynamically
    function createProductRow() {
        const productRow = document.createElement('div');
        productRow.classList.add('productRow', 'mb-3');

        productRow.innerHTML = `
        <div class="d-flex">
            <!-- Produit -->
            <div class="flex-fill">
                <label for="productSelect" class="form-label">Produit</label>
                <select class="form-select productSelect" name="products[][product_id]" required>
                    <option value="" disabled selected>Choisir un produit</option>
                    @foreach ($stocks as $stock)
                        <option value="{{ $stock->id }}" data-price="{{ $stock->price }}">
                            {{ $stock->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Quantité -->
            <div class="flex-fill ms-2">
                <label for="quantity" class="form-label">Quantité</label>
                <input type="number" class="form-control" name="products[][quantity]" min="1" required>
            </div>

            <!-- Remise -->
            <div class="flex-fill ms-2">
                <label for="discount" class="form-label">Remise (%)</label>
                <input type="number" class="form-control" name="products[][discount]" min="0" max="100">
            </div>

            <!-- Prix Unitaire -->
            <div class="flex-fill ms-2">
                <label for="price" class="form-label">Prix Unitaire</label>
                <input type="number" class="form-control" name="products[][price]" required readonly>
            </div>

            <!-- Supprimer Produit -->
            <div class="flex-fill ms-2">
                <button type="button" class="btn btn-danger removeProduct" aria-label="Supprimer produit">
                    <i class="ti ti-trash"></i>
                </button>
            </div>
        </div>
    `;

        // Add event listener to the product select to update the price
        const productSelect = productRow.querySelector('.productSelect');
        productSelect.addEventListener('change', function() {
            const selectedOption = productSelect.options[productSelect.selectedIndex];
            const price = selectedOption.getAttribute('data-price');
            const priceInput = productRow.querySelector('input[name="products[][price]"]');
            priceInput.value = price; // Update the price input field
        });

        return productRow;
    }

    // Function to update the default (first) row's price field
    function updateFirstRowPrice() {
        const firstProductSelect = document.querySelector('.productSelect');
        if (firstProductSelect) {
            const selectedOption = firstProductSelect.options[firstProductSelect.selectedIndex];
            const price = selectedOption.getAttribute('data-price');
            const priceInput = firstProductSelect.closest('.productRow').querySelector(
                'input[name="products[][price]"]');
            priceInput.value = price; // Update the price input field for the first row
        }
    }

    // Event listener to add a new product row
    document.getElementById('addProductButton').addEventListener('click', function() {
        const productRow = createProductRow();
        document.getElementById('productsContainer').appendChild(productRow);
    });

    // Event listener to remove a product row
    document.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('removeProduct')) {
            const productRow = e.target.closest('.productRow');
            productRow.remove();
        }
    });

    // Initial call to update the price for the first product row when the page is loaded
    updateFirstRowPrice();

    // Add event listener to the first product select to update price on change
    document.querySelector('.productSelect').addEventListener('change', updateFirstRowPrice);
</script>
