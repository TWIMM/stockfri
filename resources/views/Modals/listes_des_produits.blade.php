<!-- Modal de vente -->
<div class="modal fade" id="mmodalListeDeProduits" tabindex="-1" aria-labelledby="modalVenteLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalVenteLabel">Nouvelle Vente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('stock.stock_fri_order_stock') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <!-- Sélectionner un client -->
                    <div class="mb-3">
                        <label for="clientSelect" class="form-label">Sélectionner un Client</label>
                        <input type="hidden" value="" name="magasin_id">
                        <select class="form-select" id="clientSelect" name="client_id" required>
                            <option value="" disabled selected>Choisir un client</option>
                            @foreach ($clients as $client)
                                <option value="{{ $client->id }}">{{ $client->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    @if(!isset($stocks))
                    <!-- Produits -->
                    <div id="productsContainer">
                        <!-- Initial product row -->
                        <div class="productRow mb-3">
                            <div class="d-flex">
                                <!-- Produit -->
                                <div class="flex-fill">
                                    <label for="productSelect" class="form-label">Produit</label>
                                    <select class="form-select productSelect" name="products[0][product_id]" required readonly disabled>
                                        <option value="" disabled selected>Choisir un produit</option>
                                        @foreach ($services as $stock)
                                            <option value="{{ $stock->id }}" data-price="{{ $stock->price }}">
                                                {{ $stock->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Quantité -->
                                <div class="flex-fill ms-2">
                                    <label for="quantity" class="form-label">Quantité</label>
                                    <input type="number" class="form-control" name="products[0][quantity]"
                                        min="1" required readonly>
                                </div>

                                <!-- Remise -->
                                <div class="flex-fill ms-2">
                                    <label for="discount" class="form-label">Remise (%)</label>
                                    <input type="number" class="form-control" name="products[0][discount]"
                                        min="0" max="100" readonly>
                                </div>

                                <!-- Prix Unitaire -->
                                <div class="flex-fill ms-2">
                                    <label for="price" class="form-label">Prix Unitaire</label>
                                    <input type="number" class="form-control" name="products[0][price]" required
                                        readonly>
                                </div>

                                <!-- Supprimer Produit -->
                                <div class="flex-fill ms-2">
                                    <button type="button" class="btn btn-danger removeProduct"
                                        aria-label="Supprimer produit" disabled>
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif


                    @if(!isset($services))
                    <!-- Produits -->
                    <div id="productsContainer">
                        <!-- Initial product row -->
                        <div class="productRow mb-3">
                            <div class="d-flex">
                                <!-- Produit -->
                                <div class="flex-fill">
                                    <label for="productSelect" class="form-label">Produit</label>
                                    <select class="form-select productSelect" name="products[0][product_id]" required readonly disabled>
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
                                    <input type="number" class="form-control" name="products[0][quantity]"
                                        min="1" required readonly>
                                </div>

                                <!-- Remise -->
                                <div class="flex-fill ms-2">
                                    <label for="discount" class="form-label">Remise (%)</label>
                                    <input type="number" class="form-control" name="products[0][discount]"
                                        min="0" max="100" readonly>
                                </div>

                                <!-- Prix Unitaire -->
                                <div class="flex-fill ms-2">
                                    <label for="price" class="form-label">Prix Unitaire</label>
                                    <input type="number" class="form-control" name="products[0][price]" required
                                        readonly>
                                </div>

                                <!-- Supprimer Produit -->
                                <div class="flex-fill ms-2">
                                    <button type="button" class="btn btn-danger removeProduct"
                                        aria-label="Supprimer produit" disabled>
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
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

                    <div id="alreadyPayIInput" class="mb-3 d-none">
                        <label for="invoiceStatusl" class="form-label">Montant payé</label>
                        <input type="number" name="already_paid" class="form-control">
                    </div>
                    <div id="facture" class=" mb-3">
                        <h6 class="mb-3">Facture</h6>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <input type="file" class="form-control" id="factureId"
                                    name="factureName">
                            </div>
                        </div>
                    </div>
                    <!-- Champs conditionnels pour les détails de paiement -->
                    <div id="paymentDetailsContainer" class="d-none">
                        <!-- Pour Mobile Money -->
                        <div id="mobileMoneyDetails" class="payment-details d-none mb-3">
                            <h6 class="mb-3">Détails Mobile Money</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="mobileNumber" class="form-label">Numéro Mobile Money</label>
                                    <input type="text" class="form-control" id="mobileNumber"
                                        name="mobile_number">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="mobileReference" class="form-label">Référence de transaction</label>
                                    <input type="text" class="form-control" id="mobileReference"
                                        name="mobile_reference">
                                </div>
                            </div>
                        </div>

                        <!-- Pour Virement bancaire -->
                        <div id="bankTransferDetails" class="payment-details d-none mb-3">
                            <h6 class="mb-3">Détails du virement bancaire</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="bankName" class="form-label">Nom de la banque</label>
                                    <input type="text" class="form-control" id="bankName" name="bank_name">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="bankReference" class="form-label">Référence du virement</label>
                                    <input type="text" class="form-control" id="bankReference"
                                        name="bank_reference">
                                </div>
                            </div>
                        </div>

                        <!-- Pour Carte de crédit -->
                        <div id="creditCardDetails" class="payment-details d-none mb-3">
                            <h6 class="mb-3">Détails de la carte de crédit</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="cardType" class="form-label">Type de carte</label>
                                    <select class="form-select" id="cardType" name="card_type">
                                        <option value="" disabled selected>Choisir le type de carte</option>
                                        <option value="visa">Visa</option>
                                        <option value="mastercard">Mastercard</option>
                                        <option value="other">Autre</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="cardReference" class="form-label">Référence de transaction</label>
                                    <input type="text" class="form-control" id="cardReference"
                                        name="card_reference">
                                </div>
                            </div>
                        </div>

                        <!-- Pour Espèces (Cash) -->
                        <div id="cashDetails" class="payment-details d-none mb-3">
                            <h6 class="mb-3">Détails du paiement en espèces</h6>
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="cashReference" class="form-label">Référence du reçu</label>
                                    <input type="text" class="form-control" id="cashReference"
                                        name="cash_reference">
                                </div>
                            </div>
                        </div>

                        
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
    // Function to get all currently selected product IDs
    function getSelectedProductIds() {
        const selectedIds = [];
        document.querySelectorAll('.productSelect').forEach(select => {
            if (select.value) {
                selectedIds.push(select.value);
            }
        });
        return selectedIds;
    }

    // Function to update available products in all dropdowns
    function updateAvailableProducts() {
        const selectedIds = getSelectedProductIds();

        // Update each product dropdown
        document.querySelectorAll('.productSelect').forEach(select => {
            const currentValue = select.value;

            // Store all options first (clone the original options)
            if (!select.originalOptions) {
                const options = Array.from(select.options);
                select.originalOptions = options;
            }

            // Clear current options except the first one (placeholder)
            while (select.options.length > 1) {
                select.remove(1);
            }

            // Add back options that aren't selected elsewhere (except the current selection)
            select.originalOptions.forEach(option => {
                if (option.value === "" || option.value === currentValue || !selectedIds.includes(option
                        .value)) {
                    // Skip the first empty option since we kept it
                    if (option.value !== "" || select.options.length === 0) {
                        select.add(option.cloneNode(true));
                    }
                }
            });
        });
    }

    // Function to update price based on selected product
    function updatePrice(productSelect) {
        const selectedOption = productSelect.options[productSelect.selectedIndex];
        const rowContainer = productSelect.closest('.productRow');
        const priceInput = rowContainer.querySelector('input[name*="[price]"]');

        if (selectedOption && selectedOption.getAttribute('data-price')) {
            priceInput.value = selectedOption.getAttribute('data-price');
        } else {
            priceInput.value = '';
        }
    }

    // Function to create a new product row dynamically
    let productIndex = 1;

    function createProductRow() {
        const productRow = document.createElement('div');
        productRow.classList.add('productRow', 'mb-3');

        const currentIndex = productIndex;

        productRow.innerHTML = `
            <div class="d-flex">
                <!-- Produit -->
                <div class="flex-fill">
                    <label for="productSelect${currentIndex}" class="form-label">Produit</label>
                    <select class="form-select productSelect" id="productSelect${currentIndex}" name="products[${currentIndex}][product_id]" required>
                        <option value="" disabled selected>Choisir un produit</option>
                        @if(isset($stocks))
                            @foreach ($stocks as $stock)
                                <option value="{{ $stock->id }}" data-price="{{ $stock->price }}">
                                    {{ $stock->name }}
                                </option>
                            @endforeach
                        @endif

                        @if(isset($services))
                            @foreach ($services as $stock)
                                <option value="{{ $stock->id }}" data-price="{{ $stock->price }}">
                                    {{ $stock->title }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <!-- Quantité -->
                <div class="flex-fill ms-2">
                    <label for="quantity${currentIndex}" class="form-label">Quantité</label>
                    <input type="number" class="form-control" id="quantity${currentIndex}" name="products[${currentIndex}][quantity]" min="1" required>
                </div>

                <!-- Remise -->
                <div class="flex-fill ms-2">
                    <label for="discount${currentIndex}" class="form-label">Remise (%)</label>
                    <input type="number" class="form-control" id="discount${currentIndex}" name="products[${currentIndex}][discount]" min="0" max="100">
                </div>

                <!-- Prix Unitaire -->
                <div class="flex-fill ms-2">
                    <label for="price${currentIndex}" class="form-label">Prix Unitaire</label>
                    <input type="number" class="form-control" id="price${currentIndex}" name="products[${currentIndex}][price]" required readonly>
                </div>

                <!-- Supprimer Produit -->
                <div class="flex-fill ms-2">
                    <button type="button" class="btn btn-danger removeProduct" aria-label="Supprimer produit">
                        <i class="ti ti-trash"></i>
                    </button>
                </div>
            </div>
            `;

        // Add to DOM first
        document.getElementById('productsContainer').appendChild(productRow);

        // Then add event listener to the new product select
        const productSelect = productRow.querySelector(`#productSelect${currentIndex}`);
        productSelect.addEventListener('change', function() {
            updatePrice(this);
            updateAvailableProducts();
        });

        // Make sure to store the original options for this new select
        const options = Array.from(productSelect.options);
        productSelect.originalOptions = options;

        // Update available products in all dropdowns
        updateAvailableProducts();

        productIndex++;
    }

    // Event listener to add a new product row
    document.getElementById('addProductButton').addEventListener('click', function() {
        createProductRow();
    });

    // Event listener to remove a product row
    document.addEventListener('click', function(e) {
        if (e.target && (e.target.classList.contains('removeProduct') || e.target.closest('.removeProduct'))) {
            const productRow = e.target.closest('.productRow');
            if (productRow) {
                productRow.remove();
                // Update available products after removal
                updateAvailableProducts();
            }
        }
    });

   

    // Initialize event listeners for product selects on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Add event listeners for the existing product selects
        document.querySelectorAll('.productSelect').forEach(select => {
            select.addEventListener('change', function() {
                updatePrice(this);
                updateAvailableProducts();
            });
        });

        // Initialize price fields based on selected products
        document.querySelectorAll('.productSelect').forEach(select => {
            if (select.value) {
                updatePrice(select);
            }
        });

        // Initialize available products
        updateAvailableProducts();

        
    });
</script>