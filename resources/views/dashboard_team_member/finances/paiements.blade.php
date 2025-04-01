@extends('layouts.team_member_layout')

@section('title', 'Gestion des Coéquipiers')

@section('content')

    @include('Modals.remboursement')
    @include('Modals.commande_owned')
    @include('Modals.listes_des_produits_no_update')


    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h5>Paiements</h5>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-view">
                            <thead>
                                <tr>
                                    <th>ID Transaction</th>
                                    <th>Commande</th>
                                    <th>Montant</th>
                                    <th>Date</th>
                                    <th>Methode de payement</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($paiements as $pay)
                                    <tr>
                                        <td>{{ $pay->transaction_id }}</td>
                                        <td>{{ $pay->commande_id }} <span><button type="button"
                                                    class="btn btn-success btn-sm mark-paid-btn precommande-btn"  data-id='{{$pay->commande_id}}' data-debt-id="${debt.id}"
                                                    data-bs-toggle="modal" data-bs-target="#mmodalListeDeProduits">
                                                    <i class="ti ti-eye"></i>
                                                </button></span></td>
                                        <td> <span class="badge badge-pill badge-status bg-blue">{{ number_format($pay->amount) }} FCFA</span> </td>
                                        <td> {{ $pay->created_at }}</td>
                                        <td> {{ $pay->payment_method }} </td>
                                        <td>
                                            <button type="button" data-client-id="{{ $pay->id }}"
                                                data-id='{{ $pay->id }}' id='validate-order-btn'
                                                class="btn btn-secondary">
                                                <i class="ti ti-send"></i>
                                            </button>
                                            <button type="button" data-payment-id='{{ $pay->id }}'
                                                data-id='{{ $pay->id }}' id='invoice_viewer-btn' class="btn bg-blue"
                                                {{-- data-bs-target="#pdfViewerModal" --}}>
                                                <i style="color: white" class="ti ti-folder"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">Aucune créence trouvée.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="d-flex justify-content-center">
                        {{ $paiements->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        // JavaScript to handle the client debts modal
        document.addEventListener('DOMContentLoaded', function() {
            // Event listener for the CommandeImpayes modal buttons

            document.querySelectorAll('.precommande-btn').forEach(function(button) {
                    button.addEventListener('click', function() {
                        const serviceId = this.getAttribute('data-id');

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
                            template.querySelector('.remove-product-btn').addEventListener('click',
                                function() {
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
                            newRow.querySelector('.remove-product-btn').addEventListener('click',
                                function() {
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
                                row.querySelector('.productSelect').name =
                                    `products[${index}][product_id]`;
                                row.querySelector('.product-quantity').name =
                                    `products[${index}][quantity]`;
                                row.querySelector('.product-discount').name =
                                    `products[${index}][discount]`;
                                row.querySelector('.product-price').name =
                                    `products[${index}][price]`;
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
                                    if (option.value === "" || option.value ===
                                        currentValue || !selectedIds.includes(option
                                            .value)) {
                                        // Skip the first empty option since we kept it
                                        if (option.value !== "" || select.options
                                            .length === 0) {
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
                                    const basePrice = parseFloat(productSelect.options[productSelect
                                        .selectedIndex].dataset.price);
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
                                document.getElementById('totalPrice').textContent = total.toFixed(
                                    2);
                            }
                        }

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
                                document.querySelector(
                                        '#mmodalListeDeProduits input[name="tva"]')
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
                                data.products.forEach((product, index) => {
                                    // For the first product, utilize the existing row
                                    if (index === 0) {
                                        const firstProductSelect = document
                                            .querySelector(
                                                'select[name="products[0][product_id]"]'
                                            );
                                        const firstQuantityInput = document
                                            .querySelector(
                                                'input[name="products[0][quantity]"]');
                                        const firstDiscountInput = document
                                            .querySelector(
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
                                            const originalSelect = document
                                                .querySelector(
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
                                            newOption.dataset.price = product
                                                .unit_price;
                                            firstProductSelect.add(newOption);
                                        }

                                        firstProductSelect.value = product.stock_id;
                                        firstQuantityInput.value = product.quantity;
                                        firstDiscountInput.value = product.discount ||
                                            0;
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
                                            `input[name="products[${index}][price]"]`
                                        );

                                        // Same fix as above for new rows
                                        let optionExists = false;
                                        for (let i = 0; i < productSelect.options
                                            .length; i++) {
                                            if (productSelect.options[i].value ==
                                                product
                                                .stock_id) {
                                                optionExists = true;
                                                break;
                                            }
                                        }

                                        if (!optionExists) {
                                            let productName = "Unknown Product";
                                            const originalSelect = document
                                                .querySelector(
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
                                            newOption.dataset.price = product
                                                .unit_price;
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
                                            document.getElementById('mobileReference').value =
                                                data
                                                .commande.mobile_reference || '';
                                            break;
                                        case 'bank_transfer':
                                            document.getElementById('bankName').value = data
                                                .commande.bank_name || '';
                                            document.getElementById('bankReference').value =
                                                data
                                                .commande.bank_reference || '';
                                            break;
                                        case 'credit_card':
                                            document.getElementById('cardType').value = data
                                                .commande.card_type || '';
                                            document.getElementById('cardReference').value =
                                                data
                                                .commande.card_reference || '';
                                            break;
                                        case 'cash':
                                            document.getElementById('cashReference').value =
                                                data
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

                                document.getElementById('paymentDetailsContainer').classList
                                    .remove(
                                        'd-none');

                                // Détails supplémentaires selon le mode de paiement
                                if (paymentMode === "mobile_money") {
                                    document.getElementById('mobileMoneyDetails').classList
                                        .remove(
                                            'd-none');
                                    document.getElementById('mobileNumber').innerText =
                                        data.commande.mobile_number || 'N/A';
                                    document.getElementById('mobileReference').innerText =
                                        data.commande.mobile_reference || 'N/A';
                                } else if (paymentMode === "credit_card") {
                                    document.getElementById('creditCardDetailsType').classList
                                        .remove('d-none');
                                    document.getElementById('creditCardDetailsRef').classList
                                        .remove('d-none');
                                    document.getElementById('cardType').innerText = data
                                        .commande
                                        .card_type || 'N/A';
                                    document.getElementById('cardReference').innerText =
                                        data.commande.card_reference || 'N/A';
                                } else if (paymentMode === "bank_transfer") {
                                    document.getElementById('bankNameRow').classList.remove(
                                        'd-none');
                                    document.getElementById('bankReferenceRow').classList
                                        .remove(
                                            'd-none');
                                    document.getElementById('bankName').innerText = data
                                        .commande
                                        .bank_name || 'N/A';
                                    document.getElementById('bankReference').innerText =
                                        data.commande.bank_reference || 'N/A';
                                } else if (paymentMode === "cash") {
                                    document.getElementById('cashDetailsOrderPay').classList
                                        .remove(
                                            'd-none');
                                    document.getElementById('cashReference').innerText =
                                        data.commande.cash_reference || 'N/A';
                                } else {
                                    console.log("Unknown payment mode:", paymentMode);
                                }

                                // Modifier l'action du formulaire pour la mise à jour
                                const form = document.querySelector(
                                    '#mmodalListeDeProduits form');
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
            document.querySelectorAll('.edit-btn').forEach(button => {
                button.addEventListener('click', function() {
                    // Check if this button is for the CommandeImpayes modal
                    if (this.getAttribute('data-bs-target') === '#CommandeImpayes') {
                        const clientId = this.getAttribute('data-id');
                        const clientName = this.getAttribute('data-name');

                        // Update modal title with client name
                        document.getElementById('modalVenteLabel').textContent =
                            `Commandes impayées - ${clientName}`;

                        // Store client ID for later use
                        document.getElementById('put_id_in_there').value = clientId;

                        // Show loading indicator
                        const tableBody = document.querySelector('#CommandeImpayes table tbody');
                        tableBody.innerHTML =
                            '<tr><td colspan="6" class="text-center">Chargement des données...</td></tr>';

                        // Fetch client debts data
                        fetch(`/clients/${clientId}/debts`)
                            .then(response => {
                                if (!response.ok) {
                                    throw new Error('Erreur lors du chargement des données');
                                }
                                return response.json();
                            })
                            .then(debts => {
                                // Clear the loading message
                                tableBody.innerHTML = '';

                                if (debts.length === 0) {
                                    // No debts found
                                    tableBody.innerHTML =
                                        '<tr><td colspan="6" class="text-center">Aucune commande impayée trouvée.</td></tr>';
                                    return;
                                }

                                // Populate the table with debts
                                debts.forEach(debt => {
                                    const dueDate = new Date(debt.due_date);
                                    const now = new Date();
                                    const isLate = now > dueDate;

                                    const row = document.createElement('tr');
                                    row.innerHTML = `
                                        <td>${debt.commande_id}</td>
                                        <td>${Number(debt.amount).toLocaleString()} FCFA</td>
                                        <td>${formatDate(debt.due_date)}</td>
                                        <td>
                                            ${isLate 
                                                ? '<span class="badge bg-danger">En retard</span>' 
                                                : '<span class="badge bg-success">À temps</span>'}
                                        </td>
                                        <td>
                                            <button type="button" class="btn reveal-modal-btn btn-sm btn-secondary edit-btn"
                                                data-id="${debt.commande_id}" 
                                                data-bs-toggle="modal" data-bs-target="#remboursementModal">
                                                <i class="ti ti-receipt"></i>
                                            </button>
                                            <button type="button" class="btn reveal-modal-btn btn-sm btn-info"
                                                data-id="${debt.commande_id}" 
                                                data-bs-toggle="modal" data-bs-target="#mmodalListeDeProduits">
                                                <i class="ti ti-eye"></i>
                                            </button>
                                        </td>
                                    `;
                                    tableBody.appendChild(row);
                                });

                                // Add event listeners to remboursement buttons
                                setupRemboursementButtons();
                                // Add event listeners to precommande buttons
                                setupPrecommandeButtons();
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                tableBody.innerHTML =
                                    `<tr><td colspan="6" class="text-center text-danger">${error.message}</td></tr>`;
                            });
                    }
                });
            });

            // Function to set up the precommande buttons
            function setupPrecommandeButtons() {
               
            }

            // Function to set up the remboursement buttons
            function setupRemboursementButtons() {
                document.querySelectorAll('.reveal-modal-btn').forEach(button => {
                    if (button.getAttribute('data-bs-target') === '#remboursementModal') {
                        button.addEventListener('click', function() {
                            const commandId = this.getAttribute('data-id');
                            document.getElementById('setcommandid').value = commandId;
                        });
                    }
                });
            }

            // Function to mark a debt as paid
            function markDebtAsPaid(debtId) {
                // Get CSRF token from meta tag
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                fetch(`/client-debts/${debtId}/pay`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Erreur lors du traitement de la demande');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            // Get client ID from hidden input
                            const clientId = document.getElementById('put_id_in_there').value;

                            // Reload client debts
                            fetch(`/clients/${clientId}/debts`)
                                .then(response => response.json())
                                .then(debts => {
                                    const tableBody = document.querySelector(
                                        '#CommandeImpayes table tbody');
                                    tableBody.innerHTML = '';

                                    if (debts.length === 0) {
                                        tableBody.innerHTML =
                                            '<tr><td colspan="6" class="text-center">Aucune commande impayée trouvée.</td></tr>';
                                        return;
                                    }

                                    // Re-populate the table with updated debts
                                    debts.forEach(debt => {
                                        const dueDate = new Date(debt.due_date);
                                        const now = new Date();
                                        const isLate = now > dueDate;

                                        const row = document.createElement('tr');
                                        row.innerHTML = `
                                            <td>${debt.commande_id}</td>
                                            <td>${Number(debt.amount).toLocaleString()} FCFA</td>
                                            <td>${debt.payment_method}</td>
                                            <td>${formatDate(debt.due_date)}</td>
                                            <td>
                                                ${isLate 
                                                    ? '<span class="badge bg-danger">En retard</span>' 
                                                    : '<span class="badge bg-success">À temps</span>'}
                                            </td>
                                            <td>
                                                <a href="/commandes/${debt.commande_id}" class="btn btn-info btn-sm">
                                                    <i class="ti ti-eye"></i>
                                                </a>
                                                <button type="button" class="btn btn-success btn-sm mark-paid-btn" data-debt-id="${debt.id}">
                                                    <i class="ti ti-check"></i>
                                                </button>
                                            </td>
                                        `;
                                        tableBody.appendChild(row);
                                    });

                                    // Re-add event listeners
                                    setupRemboursementButtons();
                                    setupPrecommandeButtons();
                                });

                            // Show success message
                            showNotification('Succès', 'Dette marquée comme payée avec succès', 'success');
                        } else {
                            showNotification('Erreur', data.message || 'Une erreur est survenue', 'danger');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification('Erreur', error.message, 'danger');
                    });
            }

            // Helper function to format dates
            function formatDate(dateString) {
                const date = new Date(dateString);
                const day = date.getDate().toString().padStart(2, '0');
                const monthNames = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov',
                    'Déc'
                ];
                const month = monthNames[date.getMonth()];
                const year = date.getFullYear();
                return `${day} ${month} ${year}`;
            }

            // Function to show notifications
            function showNotification(title, message, type) {
                // Check if you have a notification library like Toastify or SweetAlert
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: title,
                        text: message,
                        icon: type === 'success' ? 'success' : 'error',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                } else if (typeof toastr !== 'undefined') {
                    toastr[type === 'success' ? 'success' : 'error'](message, title);
                } else {
                    // Fallback to alert
                    alert(`${title}: ${message}`);
                }
            }
        });

        // Listen for click events on "Edit" buttons for client editing
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.edit-btn').forEach(button => {
                if (button.hasAttribute('data-email')) { // Check if this is a client edit button
                    button.addEventListener('click', function() {
                        // Get the data-id from the clicked button
                        const clientId = this.getAttribute('data-id');
                        document.getElementById('put_id_in_there').value = clientId;

                        // Use AJAX to get the client data based on the ID
                        fetch(`/clients/${clientId}`)
                            .then(response => response.json())
                            .then(data => {
                                // Populate the modal fields with the retrieved data
                                document.getElementById('edit-client-id').value = data.id;
                                document.getElementById('edit-name').value = data.name;
                                document.getElementById('edit-email').value = data.email;
                                document.getElementById('edit-tel').value = data.tel;
                                document.getElementById('edit-address').value = data.address;

                                // Set the form action to the correct route for the update
                                document.getElementById('editForm').action =
                                    `/clients/update/${data.id}`;
                            })
                            .catch(error => console.error('Error fetching client data:', error));
                    });
                }
            });
        });
    </script>

@endsection
