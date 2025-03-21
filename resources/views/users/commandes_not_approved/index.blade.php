@extends('layouts.app_layout')

@section('title', 'Stocks Page')

@section('content')

    @include('Modals.listes_des_produits')
    @include('Modals.order_clients')

    @include('Modals.orderpay')

    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h4>Liste des commandes</h4>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-view">
                            <thead>
                                <tr>
                                    <th>Produit</th>
                                    <th>Client</th>
                                    <th>Details de Paiement </th>
                                    <th>Prix total </th>
                                    <th>Montant restant</th>
                                    <th>Nombre de produit(s)</th>

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
                                        <td><button type="button" data-client-id="{{ $client->id}}" data-id='{{ $eachcommandeNotApproved->id }}'
                                                id='precommande-btn' class="btn bg-violet" data-bs-toggle="modal"
                                                data-bs-target="#clientDetailsModal">
                                                <i style="color: white" class="ti ti-user"></i>
                                            </button></td>
                                        <td><button type="button" data-id='{{ $eachcommandeNotApproved->id }}'
                                                id='precommande-btn' class="btn bg-green" data-bs-toggle="modal"
                                                data-bs-target="#paymentDetailsModal">
                                                <i style="color: white" class="ti ti-receipt"></i>
                                            </button></td>
                                        <td>{{ $eachcommandeNotApproved->total_price }} FCFA</td>
                                        <td>{{ $eachcommandeNotApproved->total_price }} FCFA</td>
                                        <td>{{ count($eachcommandeNotApproved->commandeItems) }} </td>

                                        <td>
                                            <button type="button" data-id='{{ $eachcommandeNotApproved->id }}'
                                                id='edit-stock-btn' class="btn btn-secondary" data-bs-toggle="modal"
                                                data-bs-target="#editStockModal">
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
                            data.products.forEach((product, index) => {
                                // Pour le premier produit, utiliser la ligne existante
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
                                    firstQuantityInput.value = product.quantity;
                                    firstDiscountInput.value = product.discount || 0;
                                    firstPriceInput.value = product.unit_price;
                                } else {
                                    // Pour les produits suivants, créer de nouvelles lignes
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

                                    productSelect.value = product.stock_id;
                                    quantityInput.value = product.quantity;
                                    discountInput.value = product.discount || 0;
                                    priceInput.value = product.unit_price;
                                }
                            });

                            // Afficher les détails de paiement si nécessaire
                            togglePaymentDetails();

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

                        <select name="products[0][product_id]" class="form-control product-select" required>
                            <option value="">-- Sélectionner un produit --</option>
                            @foreach ($stocks as $product)
                            <option value="{{ $product->id }}" data-price="{{ $product->price }}">{{ $product->name }}</option>
                            @endforeach
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
        }

        // Fonction pour créer une nouvelle ligne de produit
        function createProductRow() {
            const productsContainer = document.getElementById('productsContainer');
            const rowCount = productsContainer.children.length;

            const newRow = document.createElement('div');
            newRow.className = 'product-row mb-3';
            newRow.innerHTML = `
                <div class="row">
                    <div class="col-md-4">
                       <label for="productSelect${rowCount}" class="form-label">Produit</label>

                        <select name="products[${rowCount}][product_id]" class="form-control product-select" required>
                            <option value="">-- Sélectionner un produit --</option>
                            @foreach ($stocks as $product)
                            <option value="{{ $product->id }}" data-price="{{ $product->price }}">{{ $product->name }}</option>
                            @endforeach
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
            });

            return newRow;
        }

        // Fonction pour réindexer les lignes de produits
        function reindexProductRows() {
            const productsContainer = document.getElementById('productsContainer');
            const rows = productsContainer.querySelectorAll('.product-row');

            rows.forEach((row, index) => {
                // Mettre à jour les attributs name avec le nouvel index
                row.querySelector('.product-select').name = `products[${index}][product_id]`;
                row.querySelector('.product-quantity').name = `products[${index}][quantity]`;
                row.querySelector('.product-discount').name = `products[${index}][discount]`;
                row.querySelector('.product-price').name = `products[${index}][price]`;
            });
        }

        // Fonction pour ajouter les event listeners aux champs d'une ligne
        function addProductRowEventListeners(row) {
            // Sélectionner les éléments pertinents
            const productSelect = row.querySelector('.product-select');
            const quantityInput = row.querySelector('.product-quantity');
            const discountInput = row.querySelector('.product-discount');
            const priceInput = row.querySelector('.product-price');

            // Ajouter les event listeners
            productSelect.addEventListener('change', updatePrice);
            quantityInput.addEventListener('input', updatePrice);
            discountInput.addEventListener('input', updatePrice);

            // Fonction pour mettre à jour le prix
            function updatePrice() {
                if (productSelect.value) {
                    const basePrice = parseFloat(productSelect.options[productSelect.selectedIndex].dataset.price);
                    const quantity = parseFloat(quantityInput.value) || 0;
                    const discount = parseFloat(discountInput.value) || 0;

                    const finalPrice = basePrice * quantity * (1 - discount / 100);
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
        document.getElementById('addProductBtn').addEventListener('click', function() {
            createProductRow();
        });

        // Function to toggle payment details based on payment mode
        function togglePaymentDetails() {
            const paymentMode = document.querySelector('#mmodalListeDeProduits select[name="payment_mode"]').value;

            // Hide all payment detail sections
            document.querySelectorAll('.payment-details').forEach(el => {
                el.style.display = 'none';
            });

            // Show the relevant payment detail section
            if (paymentMode !== '') {
                document.getElementById(`${paymentMode}Details`).style.display = 'block';
            }
        }

        // Add event listener for payment mode change
        document.querySelector('#mmodalListeDeProduits select[name="payment_mode"]').addEventListener('change',
            togglePaymentDetails);
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

        function loadClientDetails(clientData) {
            document.getElementById('clientName').innerText = clientData.name;
            document.getElementById('clientEmail').innerText = clientData.email;
            document.getElementById('clientPhone').innerText = clientData.phone;
            document.getElementById('clientAddress').innerText = clientData.address;
            document.getElementById('clientCreditScore').innerText = clientData.credit_score;
            document.getElementById('clientRiskLevel').innerText = clientData.risk_level;
            document.getElementById('clientAvailableCredit').innerText = clientData.available_credit;
        }
    </script>

@endsection
