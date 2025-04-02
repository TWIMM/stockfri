@extends('layouts.team_member_layout')

@section('title', 'Stocks Page')

@section('content')

    @include('Modals.listes_des_produits')
    @include('Modals.order_clients')
    @include('Modals.risk_high')
    @include('Modals.orderpay')
    @include('Modals.risk_low')

    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h4>Liste des Pre-commandes</h4>
                </div>

                <div class="card-body">
                    <form method="GET" action="{{ route('commandes_s.services') }}" class="mb-4">
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
                                <a href="{{ route('commandes_s.services') }}" class="btn btn-secondary">Réinitialiser</a>
                            </div>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table class="table table-view">
                            <thead>
                                <tr>
                                    <th>Services</th>
                                    <th>Client</th>
                                    <th>Details de Paiement </th>
                                    <th>Prix total </th>
                                    <th>Montant restant</th>
                                    <th>Nombre de service(s)</th>

                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($commandeNotApproved as $eachcommandeNotApproved)
                                    <tr>
                                        <td><button type="button" data-id='{{ $eachcommandeNotApproved->id }}'
                                                id='precommande-btn' class="btn btn-secondary" data-bs-toggle="modal"
                                                data-bs-target="#mmodalListeDeProduits">
                                                <i class="ti ti-eye"></i>
                                            </button> </td>
                                        @php
                                            $client = $getClientFromId($eachcommandeNotApproved->client_id);
                                        @endphp
                                        <td><button type="button" data-client-id="{{ $client->id }}"
                                                data-id='{{ $eachcommandeNotApproved->id }}' id='precommande-btn'
                                                class="btn bg-violet" data-bs-toggle="modal"
                                                data-bs-target="#clientDetailsModal">
                                                <i style="color: white" class="ti ti-user"></i>
                                            </button></td>
                                        <td><button type="button" data-payment-id='{{ $eachcommandeNotApproved->id }}'
                                                data-id='{{ $eachcommandeNotApproved->id }}' id='precommande-btn'
                                                class="btn bg-green" data-bs-toggle="modal"
                                                data-bs-target="#paymentDetailsModal">
                                                <i style="color: white" class="ti ti-receipt"></i>
                                            </button></td>
                                            <td>{{ number_format($eachcommandeNotApproved->total_price) }} FCFA</td>
                                            <td>{{ number_format($eachcommandeNotApproved->rest_to_pay) }} FCFA</td>
                                        <td>{{ count($eachcommandeNotApproved->commandeItems) }} </td>

                                        <td>
                                            <button type="button" data-command-id="{{$eachcommandeNotApproved->id}}" data-client-id="{{ $client->id }}"
                                                data-id='{{ $eachcommandeNotApproved->id }}' id='validate-order-btn'
                                                class="btn btn-secondary">
                                                <i class="ti ti-check"></i>
                                            </button>
                                            <form action="{{ route('stock.delete', $eachcommandeNotApproved->id) }}"
                                                method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger"><i
                                                        class="ti ti-trash"></i></button>
                                            </form>

                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer">
                    {{ $commandeNotApproved->links() }}
                </div>
            </div>
        </div>
    </div>
    <script>
        // Fetch service data on Edit button click
        document.addEventListener('DOMContentLoaded', function() {
            // Get all edit buttons
            const precommandesee = document.querySelectorAll('#precommande-btn');

            const validatesbUTTONS = document.querySelectorAll('#validate-order-btn');

            validatesbUTTONS.forEach(function(button) {
                button.addEventListener('click', function() {
                    const clientId = button.getAttribute(
                        'data-client-id');
                    const commandId = button.getAttribute(
                            'data-command-id');

                    // Fetch client data from the API
                    fetch(`/clients_data/${clientId}`)
                        .then(response => response.json())
                        .then(data => {
                            const clientData = data.clientData;
                            document.getElementById('risk_credit_score').innerText = clientData
                                .credit_score;
                            document.getElementById('risk_level').innerText = clientData
                                .risk_level;
                            console.log(clientData)
                            if (clientData.trusted == 0) {
                                if (clientData.risk_level === 'Très élevé' || clientData
                                    .risk_level === 'Élevé') {
                                    document.getElementById('client_id_risk_high').value = clientId;
                                    const riskModal = new bootstrap.Modal(document
                                        .getElementById('riskConfirmationModal'));
                                    riskModal.show();
                                } else {
                                    document.getElementById('commande_id_risk_low').value = commandId;
                                    const riskModal = new bootstrap.Modal(document
                                        .getElementById('excellentCreditModal'));
                                    riskModal.show();
                                }
                            } else if(clientData.trusted == 1){
                                document.getElementById('available_credit_limit').value = clientData
                                .limit_credit_for_this_user;
                                document.getElementById('commande_id_risk_low').value = commandId;
                                document.getElementById('current_debt').value = data.clientData.current_debt;
                                const riskModal = new bootstrap.Modal(document
                                        .getElementById('excellentCreditModal'));
                                    riskModal.show();
                            }

                        })
                        .catch(error => {
                            console.error('Error fetching client data:', error);
                        });
                })
            });

            // Loop through each button and add the event listener
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

                                console.log('checking');
                                // For the first product, utilize the existing row
                                if (index === 0) {
                                    console.log('stp 1');
                                    const firstProductSelect = document.querySelector(
                                        'select[name="products[0][product_id]"]');
                                    const firstQuantityInput = document.querySelector(
                                        'input[name="products[0][quantity]"]');
                                    const firstDiscountInput = document.querySelector(
                                        'input[name="products[0][discount]"]');
                                    const firstPriceInput = document.querySelector(
                                        'input[name="products[0][price]"]');

                                    firstProductSelect.value = product.service_id;

                                    // Make sure the option is visible in the dropdown
                                    // Find the option with the matching value and ensure it's available
                                    let optionExists = false;
                                    for (let i = 0; i < firstProductSelect.options
                                        .length; i++) {
                                        if (firstProductSelect.options[i].value ==
                                            product.service_id) {
                                            optionExists = true;
                                            break;
                                        }
                                    }

                                    // If the option doesn't exist, add it
                                    if (!optionExists) {
                                        console.log('! Exist');
                                        // Find the product name from the original options
                                        let productName = "Unknown Product";
                                        const originalSelect = document.querySelector(
                                            '#mmodalListeDeProduits select.productSelect'
                                        );
                                        if (originalSelect && originalSelect
                                            .originalOptions) {
                                            for (let opt of originalSelect
                                                    .originalOptions) {
                                                if (opt.value == product.service_id) {
                                                    productName = opt.text;
                                                    break;
                                                }
                                            }
                                        }

                                        const newOption = new Option(productName,
                                            product.service_id);
                                        newOption.dataset.price = product.unit_price;
                                        firstProductSelect.add(newOption);
                                    }

                                    firstProductSelect.value = product.service_id;
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
                                            .service_id) {
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
                                                if (opt.value == product.service_id) {
                                                    productName = opt.text;
                                                    break;
                                                }
                                            }
                                        }

                                        const newOption = new Option(productName,
                                            product.service_id);
                                        newOption.dataset.price = product.unit_price;
                                        productSelect.add(newOption);
                                    }

                                    productSelect.value = product.service_id;
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

            // Initialize the product dropdowns
            updateAllProductDropdowns();
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
    
                        <select name="products[0][product_id]" class="form-control productSelect" required>
                            <option value="">-- Sélectionner un produit --</option>
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
                    <div class="col-md-2">
                        <label for="quantity0" class="form-label">Quantité</label>
    
                        <input type="number" name="products[0][quantity]" class="form-control product-quantity" placeholder="Quantité" required>
                    </div>
                    <div class="col-md-2">
                        <label for="discount${0}" class="form-label">Remise (%)</label>
                        <input type="number" name="products[0][discount]" class="form-control product-discount" placeholder="Remise %" value="0">
                    </div>
                    <div class="col-md-3">
                        <label for="price${0}" class="form-label">Prix Unitaire</label>
                        <input type="number" name="products[0][price]" class="form-control product-price" placeholder="Prix" readonly>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-danger remove-product-btn">
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
    
                        <select name="products[${rowCount}][product_id]" class="form-control productSelect" required>
                            <option value="">-- Sélectionner un produit --</option>
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
                    <div class="col-md-2">
                        <label for="quantity${rowCount}" class="form-label">Quantité</label>
    
                        <input type="number" name="products[${rowCount}][quantity]" class="form-control product-quantity" placeholder="Quantité" required>
                    </div>
                    <div class="col-md-2">
                        <label for="discount${rowCount}" class="form-label">Remise (%)</label>
                        <input type="number" name="products[${rowCount}][discount]" class="form-control product-discount" placeholder="Remise %" value="0">
                    </div>
                    <div class="col-md-3">
                        <label for="price${rowCount}" class="form-label">Prix Unitaire</label>
    
                        <input type="number" name="products[${rowCount}][price]" class="form-control product-price" placeholder="Prix" readonly>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-danger remove-product-btn">
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

        // Réinitialiser le modal lors de la fermeture pour nouvelle commande
        document.getElementById('mmodalListeDeProduits').addEventListener('hidden.bs.modal', function() {
            // Réinitialiser à 'create' pour la prochaine ouverture
            this.setAttribute('data-mode', 'create');

            // Réinitialiser le titre
            document.getElementById('modalVenteLabel').textContent = 'Nouvelle Vente';

            // Réinitialiser l'action du formulaire
            const form = document.querySelector('#mmodalListeDeProduits form');
            form.action = "{{ route('stock.stock_fri_order_stock') }}";

            // Réinitialiser le texte du bouton
            const submitButton = document.querySelector('#mmodalListeDeProduits button[type="submit"]');
            submitButton.textContent = 'Enregistrer la commande';

            // Réinitialiser les champs du formulaire
            form.reset();

            // Réinitialiser complètement le conteneur des produits
            resetProductsContainer();

            // Réinitialiser les détails de paiement
            const paymentDetailsContainers = document.querySelectorAll('.payment-details');
            paymentDetailsContainers.forEach(container => {
                container.style.display = 'none';

                // Vider les champs
                const inputs = container.querySelectorAll('input');
                inputs.forEach(input => {
                    input.value = '';
                });
            });
        });

        // Event listener pour le bouton "Ajouter un produit"
        const addProductBtn = document.getElementById('addProductBtn');
        if (addProductBtn) {
            addProductBtn.addEventListener('click', function() {
                createProductRow();
            });
        }
    </script>
    <script>
        // Assuming the modal is triggered by a button with the 'data-client-id' attribute
        document.getElementById('clientDetailsModal').addEventListener('show.bs.modal', function(event) {
            const clientId = event.relatedTarget.getAttribute('data-client-id');

            // Fetch client data from the API
            fetch(`/clients_data/${clientId}`)
                .then(response => response.json())
                .then(data => {
                    const clientData = data.clientData;
                    loadClientDetails(clientData);
                })
                .catch(error => {
                    console.error('Error fetching client data:', error);
                });
        });


        // Fonction pour charger les détails du paiement dans le modal
        function loadPaymentDetails(paymentId) {
            // Make an AJAX request to fetch the payment details
            fetch(`/commande_data/${paymentId}`)
                .then(response => response.json())
                .then(data => {
                    const paymentDetails = data.commande; // Get the payment data from the response

                    // Remplissage des informations dans le modal
                    document.getElementById('payment_client').innerText = paymentDetails.client || 'N/A';
                    document.getElementById('payment_magasin').innerText = paymentDetails.magasin || 'N/A';
                    document.getElementById('payment_user').innerText = paymentDetails.user || 'N/A';
                    //document.getElementById('payment_totalAmount').innerText = paymentDetails.total_price || 'N/A';
                    document.getElementById('payment_tva').innerText = paymentDetails.tva || 'N/A';
                    document.getElementById('payment_invoiceStatus').innerText = paymentDetails.invoice_status || 'N/A';
                    document.getElementById('payment_paymentMode').innerText = paymentDetails.payment_mode || 'N/A';
                    const amountPaidFormatted = new Intl.NumberFormat().format(paymentDetails.already_paid || 0.00);
                    const restToPayFormatted = new Intl.NumberFormat().format(paymentDetails.rest_to_pay || 0.00);
                    const payment_totalAmount = new Intl.NumberFormat().format(paymentDetails.total_price || 0.00);

                    // Set the formatted values
                    document.getElementById('payment_amountPaid').innerText = amountPaidFormatted;
                    document.getElementById('payment_restToPay').innerText = restToPayFormatted;
                    document.getElementById('payment_totalAmount').innerText = payment_totalAmount;
                    // Ensure payment mode is properly formatted
                    const paymentMode = (paymentDetails.payment_mode || "").trim();
                    console.log("Payment mode:", paymentMode);

                    // First hide all payment details
                    const allPaymentRows = document.querySelectorAll('.payment-details-row');
                    allPaymentRows.forEach(row => row.classList.add('d-none'));

                    // Détails supplémentaires selon le mode de paiement
                    if (paymentMode === "mobile_money") {
                        document.getElementById('payment_mobileMoneyDetails').classList.remove('d-none');
                        //document.getElementById('payment_mobileMoneyRef').classList.remove('d-none');
                        document.getElementById('payment_mobileNumber').innerText = paymentDetails.mobile_number ||
                            'N/A';
                        document.getElementById('payment_mobileReference').innerText = paymentDetails
                            .mobile_reference || 'N/A';
                    } else if (paymentMode === "credit_card") {
                        document.getElementById('payment_creditCardDetailsType').classList.remove('d-none');
                        document.getElementById('payment_creditCardDetailsRef').classList.remove('d-none');
                        document.getElementById('payment_cardType').innerText = paymentDetails.card_type || 'N/A';
                        document.getElementById('payment_cardReference').innerText = paymentDetails.card_reference ||
                            'N/A';
                    } else if (paymentMode === "bank_transfer") {
                        document.getElementById('payment_bankNameRow').classList.remove('d-none');
                        document.getElementById('payment_bankReferenceRow').classList.remove('d-none');
                        document.getElementById('payment_bankName').innerText = paymentDetails.bank_name || 'N/A';
                        document.getElementById('payment_bankReference').innerText = paymentDetails.bank_reference ||
                            'N/A';
                    } else if (paymentMode === "cash") {
                        document.getElementById('payment_cashDetailsRow').classList.remove('d-none');
                        document.getElementById('payment_cashReference').innerText = paymentDetails.cash_reference ||
                            'N/A';
                    } else {
                        console.log("Unknown payment mode:", paymentMode);
                    }
                })
                .catch(error => console.error('Error fetching payment details:', error));
        }

        // Initialisation de la fonction lors de l'ouverture du modal
        document.getElementById('paymentDetailsModal').addEventListener('show.bs.modal', function(event) {
            // Get the paymentId from the button that triggered the modal
            const paymentId = event.relatedTarget.getAttribute('data-payment-id');

            // Load payment details using the paymentId
            loadPaymentDetails(paymentId);
        });

        function loadClientDetails(clientData) {
            document.getElementById('clientName').innerText = clientData.name;
            document.getElementById('clientEmail').innerText = clientData.email;
            document.getElementById('clientPhone').innerText = clientData.phone;
            document.getElementById('clientAddress').innerText = clientData.address;
            document.getElementById('clientCreditScore').innerText = clientData.credit_score;
            document.getElementById('clientRiskLevelBadge').innerText = clientData.risk_level;

            // Assuming you want to set a badge based on the risk level
            const riskLevelBadge = document.getElementById('clientRiskLevelBadge');

            if (clientData.risk_level === 'Faible') {
                riskLevelBadge.className = 'badge bg-success';
                riskLevelBadge.innerText = 'Faible';
            } else if (clientData.risk_level === 'Très faible') {
                riskLevelBadge.className = 'badge bg-warning';
                riskLevelBadge.innerText = 'Très faible';
            } else if (clientData.risk_level === 'Moyen') {
                riskLevelBadge.className = 'badge bg-danger';
                riskLevelBadge.innerText = 'Moyen';
            } else if (clientData.risk_level === 'Très Élevé') {
                riskLevelBadge.className = 'badge bg-secondary';
                riskLevelBadge.innerText = 'Très Élevé';
            } else {
                riskLevelBadge.className = 'badge bg-violet';
                riskLevelBadge.innerText = 'Élevé';
            }

// Format the credit limit and debt with comma separators
            const creditFormatted = new Intl.NumberFormat().format(clientData.limit_credit_for_this_user);
            const debtFormatted = new Intl.NumberFormat().format(clientData.current_debt);

            // Set the formatted values
            document.getElementById('clientAvailableCredit').innerText = creditFormatted;
            document.getElementById('clientDebtActual').innerText = debtFormatted;
        }


        // Function to toggle payment details visibility based on invoice status and payment mode
        function togglePaymentDetails() {
            const invoiceStatus = document.getElementById('invoiceStatus').value;
            const paymentMode = document.getElementById('paymentMode').value;
            const paymentDetailsContainer = document.getElementById('paymentDetailsContainer');

            // Hide all payment detail fields first
            document.querySelectorAll('.payment-details').forEach(detail => {
                detail.classList.add('d-none');
            });

            // Only show payment details if invoice status is "paid" or "partially_paid"
            if (invoiceStatus === 'paid' || invoiceStatus === 'partially_paid') {
                paymentDetailsContainer.classList.remove('d-none');

                // Show the specific payment detail fields based on payment mode
                if (paymentMode === 'mobile_money') {
                    document.getElementById('mobileMoneyDetails').classList.remove('d-none');
                } else if (paymentMode === 'bank_transfer') {
                    document.getElementById('bankTransferDetails').classList.remove('d-none');
                } else if (paymentMode === 'credit_card') {
                    document.getElementById('creditCardDetails').classList.remove('d-none');
                } else if (paymentMode === 'cash') {
                    document.getElementById('cashDetails').classList.remove('d-none');
                }
            } else {
                paymentDetailsContainer.classList.add('d-none');
            }

            // Toggle the "already paid" input based on invoice status
            if (invoiceStatus === 'paid' || invoiceStatus === 'unpaid') {
                document.getElementById('alreadyPayIInput').classList.add('d-none');
            } else if (invoiceStatus === 'partially_paid') {
                document.getElementById('alreadyPayIInput').classList.remove('d-none');
            }
        }

        // Event listeners for invoice status and payment mode changes
        document.getElementById('invoiceStatus').addEventListener('change', togglePaymentDetails);
        document.getElementById('paymentMode').addEventListener('change', togglePaymentDetails);
    </script>
@endsection
