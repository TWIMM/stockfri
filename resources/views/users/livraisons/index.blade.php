@extends('layouts.app_layout')

@section('title', 'Stocks Page')

@section('content')

    @include('Modals.listes_des_produits_no_update')
    @include('Modals.livraisons_details')
    @include('Modals.risk_high')
    @include('Modals.orderpay')
    @include('Modals.risk_low')
    @include('Modals.pdf_viewer')

    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h4>Liste des commandes</h4>
                </div>

                <div class="card-body">
                    <form method="GET" action="{{ route('commandes.listes') }}" class="mb-4">
                        <div class="row">
                            <div class="col-md-3 mb-2">
                                <label for="client">Client</label>
                                <select name="client" id="client" class="form-control">
                                    <option value="">Tous les clients</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}" {{ request('client') == $client->id ? 'selected' : '' }}>
                                            {{ $client->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label for="status">Statut livraison</label>
                                <select name="status" id="status" class="form-control">
                                    <option value="">Tous les statuts</option>
                                    <option value="none" {{ request('status') == 'none' ? 'selected' : '' }}>Aucun</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                                    <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Livré</option>
                                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>En cours</option>
                                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Annulé</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label for="min_price">Prix minimum</label>
                                <input min=0 type="number" name="min_price" id="min_price" class="form-control" value="{{ request('min_price') }}">
                            </div>
                            <div class="col-md-3 mb-2">
                                <label for="max_price">Prix maximum</label>
                                <input min=0 type="number" name="max_price" id="max_price" class="form-control" value="{{ request('max_price') }}">
                            </div>
                            <div class="col-md-3 mb-2">
                                <label for="date_start">Date début</label>
                                <input type="date" name="date_start" id="date_start" class="form-control" value="{{ request('date_start') }}">
                            </div>
                            <div class="col-md-3 mb-2">
                                <label for="date_end">Date fin</label>
                                <input type="date" name="date_end" id="date_end" class="form-control" value="{{ request('date_end') }}">
                            </div>
                            
                            <div class="col-md-12 mt-2">
                                <button type="submit" class="btn btn-primary">Filtrer</button>
                                <a href="{{ route('commandes.listes') }}" class="btn btn-secondary">Réinitialiser</a>
                            </div>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table class="table table-view">
                            <thead>
                                <tr>
                                    <th>Tracking number</th>
                                    <th>Commande</th>
                                    <th>Details de la livraion</th>
                                    <th>Statut livraison </th>
                                    <th>Prix livraison </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($livraisons as $eachLiv)
                                    <tr>
                                        <td> <span class="badge badge-pill badge-status bg-blue">{{$eachLiv->tracking_number}}</span></td>
                                        <td> 
                                            <button type="button" data-id='{{ $eachLiv->commande_id }}'
                                                id='precommande-btn' data-bs-toggle="modal"
                                                data-bs-target="#mmodalListeDeProduits" class="btn btn-sm btn-secondary"><i class="ti ti-eye"></i></button>
                                        </td>
                                        <td>
                                            <button type="button" data-id='{{ $eachLiv->commande_id }}'
                                                id='precommande-btn' data-bs-toggle="modal"
                                                data-bs-target="#livraisonDetailsModal" class="btn btn-sm bg-violet"><i class="ti ti-eye"></i></button>
                                        </td>
                                        <td> {{$eachLiv->delivery_status}}</td>
                                        <td> {{number_format($eachLiv->shipping_cost)}}</td>

                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer">
                    {{ $livraisons->links() }}
                </div>
            </div>
        </div>
    </div>

    <script>
        const precommandesee = document.querySelectorAll('#precommande-btn');

        precommandesee.forEach(function(button) {
                button.addEventListener('click', function() {
                    const serviceId = button.getAttribute(
                        'data-id'); // Get the ID from the clicked button

                    // Make the AJAX request to get the service details
                    const xhr = new XMLHttpRequest();
                    xhr.open('GET', '/precommande/' + serviceId, true);
                    xhr.setRequestHeader('Content-Type', 'application/json');
                    xhr.onload = function() {
                        if (xhr.status === 200) {
                            const data = JSON.parse(xhr.responseText);

                            // Mettre le modal en mode édition
                            const modal = document.getElementById('mmodalListeDeProduits');
                            modal.setAttribute('data-mode', 'edit');

                            // Mettre à jour le titre du modal
                            document.getElementById('modalVenteLabel').textContent =
                                'Détails de la Commande';

                            // Remplir le formulaire avec les données
                            document.querySelector(
                                    '#mmodalListeDeProduits select[name="client_id"]')
                                .value = data.commande.client_id;
                            document.querySelector('#mmodalListeDeProduits input[name="tva"]')
                                .value = data
                                .commande.tva || 0;
                            document.querySelector(
                                    '#mmodalListeDeProduits select[name="payment_mode"]')
                                .value = data.commande.payment_mode;
                            document.querySelector(
                                    '#mmodalListeDeProduits select[name="invoice_status"]')
                                .value = data.commande.invoice_status;

                            // Vider complètement le conteneur de produits
                            resetProductsContainer();

                            // Remplir avec les produits de la commande
                            // In the AJAX request success handler where you update form data
                            // Modify this part of the code in the first script section:

                            data.products.forEach((product, index) => {
                                // For the first product, utilize the existing row
                                if (index === 0) {
                                    const firstProductSelect = document.querySelector(
                                        'select[name="products[0][product_id]"]');
                                    const firstQuantityInput = document.querySelector(
                                        'input[name="products[0][quantity]"]');
                                    const firstDiscountInput = document.querySelector(
                                        'input[name="products[0][discount]"]');
                                    const firstPriceInput = document.querySelector(
                                        'input[name="products[0][price]"]');

                                    firstProductSelect.value = product.stock_id;

                                    // Make sure the option is visible in the dropdown
                                    // Find the option with the matching value and ensure it's available
                                    let optionExists = false;
                                    for (let i = 0; i < firstProductSelect.options
                                        .length; i++) {
                                        if (firstProductSelect.options[i].value ==
                                            product.stock_id) {
                                            optionExists = true;
                                            break;
                                        }
                                    }

                                    // If the option doesn't exist, add it
                                    if (!optionExists) {
                                        // Find the product name from the original options
                                        let productName = "Unknown Product";
                                        const originalSelect = document.querySelector(
                                            '#mmodalListeDeProduits select.productSelect'
                                        );
                                        if (originalSelect && originalSelect
                                            .originalOptions) {
                                            for (let opt of originalSelect
                                                    .originalOptions) {
                                                if (opt.value == product.stock_id) {
                                                    productName = opt.text;
                                                    break;
                                                }
                                            }
                                        }

                                        const newOption = new Option(productName,
                                            product.stock_id);
                                        newOption.dataset.price = product.unit_price;
                                        firstProductSelect.add(newOption);
                                    }

                                    firstProductSelect.value = product.stock_id;
                                    firstQuantityInput.value = product.quantity;
                                    firstDiscountInput.value = product.discount || 0;
                                    firstPriceInput.value = product.unit_price;
                                } else {
                                    // For subsequent products, create new rows
                                    createProductRow();

                                    const productSelect = document.querySelector(
                                        `select[name="products[${index}][product_id]"]`
                                    );
                                    const quantityInput = document.querySelector(
                                        `input[name="products[${index}][quantity]"]`
                                    );
                                    const discountInput = document.querySelector(
                                        `input[name="products[${index}][discount]"]`
                                    );
                                    const priceInput = document.querySelector(
                                        `input[name="products[${index}][price]"]`);

                                    // Same fix as above for new rows
                                    let optionExists = false;
                                    for (let i = 0; i < productSelect.options
                                        .length; i++) {
                                        if (productSelect.options[i].value == product
                                            .stock_id) {
                                            optionExists = true;
                                            break;
                                        }
                                    }

                                    if (!optionExists) {
                                        let productName = "Unknown Product";
                                        const originalSelect = document.querySelector(
                                            '#mmodalListeDeProduits select.productSelect'
                                        );
                                        if (originalSelect && originalSelect
                                            .originalOptions) {
                                            for (let opt of originalSelect
                                                    .originalOptions) {
                                                if (opt.value == product.stock_id) {
                                                    productName = opt.text;
                                                    break;
                                                }
                                            }
                                        }

                                        const newOption = new Option(productName,
                                            product.stock_id);
                                        newOption.dataset.price = product.unit_price;
                                        productSelect.add(newOption);
                                    }

                                    productSelect.value = product.stock_id;
                                    quantityInput.value = product.quantity;
                                    discountInput.value = product.discount || 0;
                                    priceInput.value = product.unit_price;
                                }
                            });
                            // Update all product dropdowns after populating
                            updateAllProductDropdowns();

                            // Afficher les détails de paiement si nécessaire

                            // Remplir les champs de détails de paiement si disponibles
                            if (data.payment_details) {
                                switch (data.commande.payment_mode) {
                                    case 'mobile_money':
                                        document.getElementById('mobileNumber').value = data
                                            .commande.mobile_number || '';
                                        document.getElementById('mobileReference').value = data
                                            .commande.mobile_reference || '';
                                        break;
                                    case 'bank_transfer':
                                        document.getElementById('bankName').value = data
                                            .commande.bank_name || '';
                                        document.getElementById('bankReference').value = data
                                            .commande.bank_reference || '';
                                        break;
                                    case 'credit_card':
                                        document.getElementById('cardType').value = data
                                            .commande.card_type || '';
                                        document.getElementById('cardReference').value = data
                                            .commande.card_reference || '';
                                        break;
                                    case 'cash':
                                        document.getElementById('cashReference').value = data
                                            .commande.cash_reference || '';
                                        break;
                                }
                            }

                            // Ensure payment mode is properly formatted
                            const paymentMode = data.commande.payment_mode;
                            console.log("Payment mode:", paymentMode);

                            // First hide all payment details
                            const allPaymentRows = document.querySelectorAll(
                                '.payment-details');
                            allPaymentRows.forEach(row => row.classList.add('d-none'));

                            document.getElementById('paymentDetailsContainer').classList.remove(
                                'd-none');

                            // Détails supplémentaires selon le mode de paiement
                            if (paymentMode === "mobile_money") {
                                document.getElementById('mobileMoneyDetails').classList.remove(
                                    'd-none');
                                /*  // document.getElementById('mobileMoneyRef').classList.remove(
                                      'd-none'); */
                                document.getElementById('mobileNumber').innerText =
                                    data.commande.mobile_number || 'N/A';
                                document.getElementById('mobileReference').innerText =
                                    data.commande.mobile_reference || 'N/A';
                            } else if (paymentMode === "credit_card") {
                                document.getElementById('creditCardDetailsType').classList
                                    .remove('d-none');
                                document.getElementById('creditCardDetailsRef').classList
                                    .remove('d-none');
                                document.getElementById('cardType').innerText = data.commande
                                    .card_type || 'N/A';
                                document.getElementById('cardReference').innerText =
                                    data.commande.card_reference || 'N/A';
                            } else if (paymentMode === "bank_transfer") {
                                document.getElementById('bankNameRow').classList.remove(
                                    'd-none');
                                document.getElementById('bankReferenceRow').classList.remove(
                                    'd-none');
                                document.getElementById('bankName').innerText = data.commande
                                    .bank_name || 'N/A';
                                document.getElementById('bankReference').innerText =
                                    data.commande.bank_reference || 'N/A';
                            } else if (paymentMode === "cash") {
                                document.getElementById('cashDetailsOrderPay').classList.remove(
                                    'd-none');
                                document.getElementById('cashReference').innerText =
                                    data.commande.cash_reference || 'N/A';
                            } else {
                                console.log("Unknown payment mode:", paymentMode);
                            }

                            // Modifier l'action du formulaire pour la mise à jour
                            const form = document.querySelector('#mmodalListeDeProduits form');
                            form.action = `/commandes/${serviceId}/update`;

                            // Changer le texte du bouton de soumission
                            const submitButton = document.querySelector(
                                '#mmodalListeDeProduits button[type="submit"]');
                            submitButton.textContent = 'Mettre à jour la commande';
                        } else {
                            alert('Impossible de récupérer les détails de la commande.');
                        }
                    };
                    xhr.send();
                });
            });
            // Fonction pour réinitialiser complètement le conteneur des produits
        function resetProductsContainer() {
            const productsContainer = document.getElementById('productsContainer');

            // Vider complètement le conteneur
            productsContainer.innerHTML = '';

            // Créer la première ligne par défaut
            const template = document.createElement('div');
            template.className = 'product-row mb-3';
            template.innerHTML = `
                <div class="row">
                    <div class="col-md-4">
                        <label for="productSelect0" class="form-label">Produit</label>
    
                        <select readonly disabled name="products[0][product_id]" class="form-control productSelect" required>
                            <option value="">-- Sélectionner un produit --</option>
                            @foreach ($stocks as $product)
                            <option value="{{ $product->id }}" data-price="{{ $product->price }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="quantity0" class="form-label">Quantité</label>
    
                        <input readonly disabled type="number" name="products[0][quantity]" class="form-control product-quantity" placeholder="Quantité" required>
                    </div>
                    <div class="col-md-2">
                        <label for="discount${0}" class="form-label">Remise (%)</label>
                        <input readonly disabled type="number" name="products[0][discount]" class="form-control product-discount" placeholder="Remise %" value="0">
                    </div>
                    <div class="col-md-3">
                        <label for="price${0}" class="form-label">Prix Unitaire</label>
                        <input readonly disabled type="number" name="products[0][price]" class="form-control product-price" placeholder="Prix" readonly>
                    </div>
                    <div class="col-md-1">
                        <button disabled type="button" class="btn btn-danger remove-product-btn">
                            <i class="ti ti-trash"></i>
                        </button>
                    </div>
                </div>
            `;

            productsContainer.appendChild(template);

            // Ajouter les event listeners nécessaires pour cette première ligne
            addProductRowEventListeners(template);

            // Add event listener for the remove button on the first row
            template.querySelector('.remove-product-btn').addEventListener('click', function() {
                // Don't remove if it's the only row
                if (productsContainer.children.length > 1) {
                    productsContainer.removeChild(template);
                    // Reindex the fields
                    reindexProductRows();
                    // Update dropdowns
                    updateAllProductDropdowns();
                }
            });
        }

        // Fonction pour créer une nouvelle ligne de produit
        function createProductRow() {
            const productsContainer = document.getElementById('productsContainer');
            const rowCount = productsContainer.children.length;

            // Get all currently selected products
            const selectedProducts = [];
            document.querySelectorAll('.productSelect').forEach(select => {
                if (select.value) {
                    selectedProducts.push(select.value);
                }
            });

            const newRow = document.createElement('div');
            newRow.className = 'product-row mb-3';
            newRow.innerHTML = `
                <div class="row">
                    <div class="col-md-4">
                       <label for="productSelect${rowCount}" class="form-label">Produit</label>
    
                        <select readonly disabled name="products[${rowCount}][product_id]" class="form-control productSelect" required>
                            <option value="">-- Sélectionner un produit --</option>
                            @foreach ($stocks as $product)
                            <option value="{{ $product->id }}" data-price="{{ $product->price }}" >
                                {{ $product->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="quantity${rowCount}" class="form-label">Quantité</label>
    
                        <input readonly disabled type="number" name="products[${rowCount}][quantity]" class="form-control product-quantity" placeholder="Quantité" required>
                    </div>
                    <div class="col-md-2">
                        <label for="discount${rowCount}" class="form-label">Remise (%)</label>
                        <input readonly disabled type="number" name="products[${rowCount}][discount]" class="form-control product-discount" placeholder="Remise %" value="0">
                    </div>
                    <div class="col-md-3">
                        <label for="price${rowCount}" class="form-label">Prix Unitaire</label>
    
                        <input readonly disabled type="number" name="products[${rowCount}][price]" class="form-control product-price" placeholder="Prix" readonly>
                    </div>
                    <div class="col-md-1">
                        <button  disabled type="button" class="btn btn-danger remove-product-btn">
                            <i class="ti ti-trash"></i>
                        </button>
                    </div>
                </div>
            `;

            productsContainer.appendChild(newRow);

            // Ajouter les event listeners pour cette nouvelle ligne
            addProductRowEventListeners(newRow);

            // Ajouter l'event listener pour le bouton de suppression
            newRow.querySelector('.remove-product-btn').addEventListener('click', function() {
                productsContainer.removeChild(newRow);
                // Réindexer les champs
                reindexProductRows();
                // Update all dropdowns to reflect the change
                updateAllProductDropdowns();
            });

            return newRow;
        }

        // Fonction pour réindexer les lignes de produits
        function reindexProductRows() {
            const productsContainer = document.getElementById('productsContainer');
            const rows = productsContainer.querySelectorAll('.product-row');

            rows.forEach((row, index) => {
                // Mettre à jour les attributs name avec le nouvel index
                row.querySelector('.productSelect').name = `products[${index}][product_id]`;
                row.querySelector('.product-quantity').name = `products[${index}][quantity]`;
                row.querySelector('.product-discount').name = `products[${index}][discount]`;
                row.querySelector('.product-price').name = `products[${index}][price]`;
            });
        }

        function getSelectedProductIds() {
            const selectedIds = [];
            document.querySelectorAll('.productSelect').forEach(select => {
                if (select.value) {
                    selectedIds.push(select.value);
                }
            });
            return selectedIds;
        }

        // New function to update product selection in all dropdowns
        function updateAllProductDropdowns() {
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

                // After updating options, make sure the current value is still selected
                if (currentValue) {
                    select.value = currentValue;
                }
            });
        }

        // Fonction pour ajouter les event listeners aux champs d'une ligne
        function addProductRowEventListeners(row) {
            // Sélectionner les éléments pertinents
            const productSelect = row.querySelector('.productSelect');
            const quantityInput = row.querySelector('.product-quantity');
            const discountInput = row.querySelector('.product-discount');
            const priceInput = row.querySelector('.product-price');
            updateAllProductDropdowns()
            // Ajouter les event listeners
            productSelect.addEventListener('change', function() {
                updatePrice();
                updateAllProductDropdowns();
            });
            quantityInput.addEventListener('input', updatePrice);
            discountInput.addEventListener('input', updatePrice);

            // Fonction pour mettre à jour le prix
            function updatePrice() {
                if (productSelect.value) {
                    const basePrice = parseFloat(productSelect.options[productSelect.selectedIndex].dataset.price);
                    const quantity = parseFloat(quantityInput.value) || 0;
                    const discount = parseFloat(discountInput.value) || 0;

                    const finalPrice = basePrice;
                    priceInput.value = finalPrice.toFixed(2);

                    // Mettre à jour le total
                    updateTotalPrice();
                }
            }
        }

        // Fonction pour mettre à jour le prix total
        function updateTotalPrice() {
            const priceInputs = document.querySelectorAll('.product-price');
            let total = 0;

            priceInputs.forEach(input => {
                total += parseFloat(input.value) || 0;
            });

            // Si vous avez un champ pour afficher le total
            if (document.getElementById('totalPrice')) {
                document.getElementById('totalPrice').textContent = total.toFixed(2);
            }
        }

    </script>
    
    
@endsection
