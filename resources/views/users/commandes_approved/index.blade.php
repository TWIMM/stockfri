@extends('layouts.app_layout')

@section('title', 'Stocks Page')

@section('content')

    @include('Modals.listes_des_produits_no_update')
    @include('Modals.order_clients')
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
                    <div class="table-responsive">
                        <table class="table table-view">
                            <thead>
                                <tr>
                                    <th>Produit</th>
                                    <th>Client</th>
                                    <th>Details de Paiement </th>
                                    <th>Statut livraison </th>
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

                                        <td>
                                            <select class="form-control" name="status_livraison" id="status_livraison">
                                                <option value="none"
                                                    {{ !$eachcommandeNotApproved->livraison ? 'selected' : '' }}>
                                                    Aucun
                                                </option>
                                                <option value="pending"
                                                    {{ $eachcommandeNotApproved->livraison && $eachcommandeNotApproved->livraison->delivery_status == 'pending' ? 'selected' : '' }}>
                                                    En attente
                                                </option>
                                                <option value="delivered"
                                                    {{ $eachcommandeNotApproved->livraison && $eachcommandeNotApproved->livraison->delivery_status == 'delivered' ? 'selected' : '' }}>
                                                    Livré
                                                </option>
                                                <option value="in_progress"
                                                    {{ $eachcommandeNotApproved->livraison && $eachcommandeNotApproved->livraison->delivery_status == 'in_progress' ? 'selected' : '' }}>
                                                    En cours
                                                </option>
                                                <option value="cancelled"
                                                    {{ $eachcommandeNotApproved->livraison && $eachcommandeNotApproved->livraison->delivery_status == 'cancelled' ? 'selected' : '' }}>
                                                    Annulé
                                                </option>
                                            </select>
                                        </td>
                                        <td>{{ $eachcommandeNotApproved->total_price }} FCFA</td>
                                        <td>{{ $eachcommandeNotApproved->rest_to_pay }} FCFA</td>
                                        <td>{{ count($eachcommandeNotApproved->commandeItems) }} </td>

                                        <td>
                                            <button type="button" data-client-id="{{ $client->id }}"
                                                data-id='{{ $eachcommandeNotApproved->id }}' id='validate-order-btn'
                                                class="btn btn-secondary">
                                                <i class="ti ti-send"></i>
                                            </button>
                                            <button type="button" data-payment-id='{{ $eachcommandeNotApproved->id }}'
                                                data-id='{{ $eachcommandeNotApproved->id }}' id='invoice_viewer-btn'
                                                class="btn bg-blue" {{-- data-bs-target="#pdfViewerModal" --}}>
                                                <i style="color: white" class="ti ti-folder"></i>
                                            </button>

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

            const invoice_viewer_buttons = document.querySelectorAll('#invoice_viewer-btn');

            // Find all select elements with name="status_livraison"
            function initializeStatusSelects() {
                const statusSelects = document.querySelectorAll('select[name="status_livraison"]');

                // Add onchange event listener to each select
                statusSelects.forEach(select => {
                    // Remove existing event listeners first to prevent duplicates
                    select.removeEventListener('change', handleStatusChange);
                    // Add the event listener
                    select.addEventListener('change', handleStatusChange);
                });
            }

            // Handler function for the change event
            function handleStatusChange() {
                // Get the selected value
                const status = this.value;
                console.log(status);

                // Get the commande ID from the closest row
                const row = this.closest('tr');
                const commandeId = row.querySelector('#precommande-btn')
                    .getAttribute('data-id');

                // Store the original HTML and the select element for later use
                const originalHtml = this.closest('td').innerHTML;
                const tdElement = this.closest('td');

                // Check if a livraison already exists
                checkLivraisonExists(commandeId).then(exists => {
                    console.log('checking');
                    if (exists) {
                        // Just update the status
                        showLoadingIndicator(tdElement);
                        updateLivraisonStatus(commandeId, status, tdElement, originalHtml);
                    } else {
                        // Show the modal to get additional information
                        showLivraisonModal(commandeId, status, tdElement, originalHtml);
                    }
                });
            }

            // Function to check if a livraison exists
            function checkLivraisonExists(commandeId) {
                return fetch(`/check-livraison-exists/${commandeId}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        return data.exists;
                    })
                    .catch(error => {
                        console.error('Error checking livraison:', error);
                        return false;
                    });
            }

            // Function to show loading indicator
            function showLoadingIndicator(element) {
                element.innerHTML =
                    '<div class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">Loading...</span></div>';
            }

            // Function to show the livraison modal
            function showLivraisonModal(commandeId, status, tdElement, originalHtml) {
                // First, fetch delivery personnel list to populate the select
                fetch('/get-delivery-personnel', {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Create options for delivery personnel select
                        let personnelOptions = '<option value="">Sélectionner un livreur</option>';
                        data.personnel.forEach(person => {
                            personnelOptions +=
                                `<option value="${person.id}">${person.name}</option>`;
                        });

                        // Remove any existing modal to avoid duplicates
                        const existingModal = document.getElementById('livraisonModal');
                        if (existingModal) {
                            existingModal.remove();
                        }

                        // Create modal HTML
                        const modalHtml = `
                <div class="modal fade" id="livraisonModal" tabindex="-1" aria-labelledby="livraisonModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="livraisonModalLabel">Détails de livraison</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="livraisonForm">
                                    <input type="hidden" name="commande_id" value="${commandeId}">
                                    <input type="hidden" name="delivery_status" value="${status}">
                                    <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').getAttribute('content')}">
                                    
                                    <div class="mb-3">
                                        <label for="delivery_address" class="form-label">Adresse de livraison</label>
                                        <textarea class="form-control" id="delivery_address" name="delivery_address" rows="3" required></textarea>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="delivered_by" class="form-label">Livreur</label>
                                        <select class="form-select" id="delivered_by" name="delivered_by" required>
                                            ${personnelOptions}
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="shipping_method" class="form-label">Méthode d'expédition</label>
                                        <select class="form-select" id="shipping_method" name="shipping_method" required>
                                            <option value="Standard">Standard</option>
                                            <option value="Express">Express</option>
                                            <option value="Premium">Premium</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="delivery_date" class="form-label">Date de livraison prévue</label>
                                        <input type="date" class="form-control" id="delivery_date" name="delivery_date" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Frais de livraison payés?</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="is_shipping_paid" id="shipping_paid_yes" value="1">
                                            <label class="form-check-label" for="shipping_paid_yes">
                                                Oui
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="is_shipping_paid" id="shipping_paid_no" value="0" checked>
                                            <label class="form-check-label" for="shipping_paid_no">
                                                Non
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div id="shipping_cost_container" class="mb-3" style="display: none;">
                                        <label for="shipping_cost" class="form-label">Montant des frais de livraison</label>
                                        <input type="number" class="form-control" id="shipping_cost" name="shipping_cost" step="0.01" min="0">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="delivery_notes" class="form-label">Notes de livraison</label>
                                        <textarea class="form-control" id="delivery_notes" name="delivery_notes" rows="2"></textarea>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                <button type="button" class="btn btn-primary" id="saveLivraisonBtn">Enregistrer</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

                        // Add modal to the document
                        const modalContainer = document.createElement('div');
                        modalContainer.innerHTML = modalHtml;
                        document.body.appendChild(modalContainer);

                        // Initialize the modal
                        const livraisonModal = new bootstrap.Modal(document.getElementById('livraisonModal'));

                        // Set default delivery date to 3 days from now
                        const deliveryDateInput = document.getElementById('delivery_date');
                        const threeBusinessDays = new Date();
                        threeBusinessDays.setDate(threeBusinessDays.getDate() + 3);
                        deliveryDateInput.valueAsDate = threeBusinessDays;

                        // Show/hide shipping cost field based on payment selection
                        const shippingPaidRadios = document.querySelectorAll('input[name="is_shipping_paid"]');
                        shippingPaidRadios.forEach(radio => {
                            radio.addEventListener('change', function() {
                                const shippingCostContainer = document.getElementById(
                                    'shipping_cost_container');
                                if (this.value === '1') {
                                    shippingCostContainer.style.display = 'block';
                                    document.getElementById('shipping_cost').setAttribute(
                                        'required', 'required');
                                } else {
                                    shippingCostContainer.style.display = 'none';
                                    document.getElementById('shipping_cost').removeAttribute(
                                        'required');
                                }
                            });
                        });

                        // Handle save button click
                        document.getElementById('saveLivraisonBtn').addEventListener('click', function() {
                            const form = document.getElementById('livraisonForm');

                            // Check form validity
                            if (form.checkValidity()) {
                                // Get form data
                                const formData = new FormData(form);

                                // Hide modal
                                livraisonModal.hide();

                                // Show loading indicator
                                showLoadingIndicator(tdElement);

                                // Send form data
                                createLivraison(formData, tdElement, originalHtml);
                            } else {
                                // Trigger form validation
                                form.reportValidity();
                            }
                        });

                        // Show the modal
                        livraisonModal.show();

                        // Clean up when the modal is hidden
                        document.getElementById('livraisonModal').addEventListener('hidden.bs.modal',
                            function() {
                                // Restore original select
                                tdElement.innerHTML = originalHtml;

                                // Ensure the select has the correct value
                                const select = tdElement.querySelector('select');
                                if (select) {
                                    select.value = status;
                                }

                                // Reinitialize the event listeners
                                initializeStatusSelects();

                                // Remove the modal from the DOM
                                this.remove();
                            });
                    })
                    .catch(error => {
                        console.error('Error fetching delivery personnel:', error);
                        // Restore original select
                        tdElement.innerHTML = originalHtml;

                        // Reinitialize the event listeners
                        initializeStatusSelects();

                        // Show error message
                        const toast = new bootstrap.Toast(document.getElementById('liveToast'));
                        document.querySelector('#liveToast .toast-body').textContent =
                            'Error fetching delivery personnel';
                        toast.show();
                    });
            }

            // Function to create a new livraison
            function createLivraison(formData, tdElement, originalHtml) {
                fetch('/create-livraison', {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        // Restore select with updated value
                        tdElement.innerHTML = originalHtml;

                        // Set the correct option as selected
                        const select = tdElement.querySelector('select');
                        select.value = formData.get('delivery_status');

                        // Reinitialize the event listeners
                        initializeStatusSelects();

                        // Show success message
                        Notiflix.Notify.success("Creation de la livraison reussie", {
                            timeout: 100000, // Timeout in milliseconds (optional)
                            zindex: 10000, // Adjust the z-index if needed
                        });
                    })
                    .catch(error => {
                        // Restore original select on error
                        tdElement.innerHTML = originalHtml;

                        // Reinitialize the event listeners
                        initializeStatusSelects();

                        // Show error message
                        Notiflix.Notify.failure("Erreur lors de la creation de la livraison", {
                            timeout: 100000, // Timeout in milliseconds (optional)
                            zindex: 10000, // Adjust the z-index if needed
                        });
                    });
            }


            // Function to update an existing livraison status
            function updateLivraisonStatus(commandeId, status, tdElement, originalHtml) {
                // Create form data
                const formData = new FormData();
                formData.append('commande_id', commandeId);
                formData.append('delivery_status', status);
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute(
                    'content'));

                // Send POST request
                fetch('/update-livraison-status', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        // Restore select with updated value
                        tdElement.innerHTML = originalHtml;

                        // Set the correct option as selected
                        const select = tdElement.querySelector('select');
                        select.value = status;

                        // Reinitialize the event listeners
                        initializeStatusSelects();

                        // Show success message
                        const toast = new bootstrap.Toast(document.getElementById('liveToast'));
                        document.querySelector('#liveToast .toast-body').textContent = data.message ||
                            'Status updated successfully';
                        toast.show();
                    })
                    .catch(error => {
                        // Restore original select on error
                        tdElement.innerHTML = originalHtml;

                        // Reinitialize the event listeners
                        initializeStatusSelects();

                        // Show error message
                        const toast = new bootstrap.Toast(document.getElementById('liveToast'));
                        document.querySelector('#liveToast .toast-body').textContent = 'Error updating status';
                        toast.show();

                        console.error('Error:', error);
                    });
            }

            initializeStatusSelects();

            // Re-initialize whenever the table content changes (if using AJAX to update the table)
            // If you have an event that triggers table content refresh, add initializeStatusSelects() there too

            // Function to load PDF from the input URL
            function loadPdfFromUrl() {
                const pdfUrl = document.getElementById('invoices_url').value;

                if (pdfUrl) {
                    // Show loading state
                    document.getElementById('pdfLoading').classList.remove('d-none');
                    document.getElementById('pdfContainer').classList.add('d-none');
                    document.getElementById('pdfError').classList.add('d-none');

                    // Load the PDF
                    currentPdfUrl = pdfUrl;
                    const viewer = document.getElementById('pdfViewerFrame');
                    viewer.src = pdfUrl;

                    // Hide loading, show PDF viewer
                    document.getElementById('pdfLoading').classList.add('d-none');
                    document.getElementById('pdfContainer').classList.remove('d-none');

                    // Simulate page count update
                    simulatePageCount();
                } else {
                    showError('URL du document introuvable');
                }
            }

            invoice_viewer_buttons.forEach(function(button) {
                button.addEventListener('click', function() {
                    const commandeId = button.getAttribute(
                        'data-id');

                    // Fetch client data from the API
                    fetch(`/invoices/${commandeId}`)
                        .then(response => response.json())
                        .then(data => {
                            const invoicesurl = data.invoicesurl;
                            document.getElementById('invoices_url').value = invoicesurl;
                            console.log(invoicesurl)
                            const pdfViewer = new bootstrap.Modal(document
                                .getElementById('pdfViewerModal'));
                            pdfViewer.show();

                            loadPdfFromUrl();
                        })
                        .catch(error => {
                            console.error('Error fetching client invoice:', error);
                        });
                });
            });

            validatesbUTTONS.forEach(function(button) {
                button.addEventListener('click', function() {
                    const clientId = button.getAttribute(
                        'data-client-id');

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
                                    const riskModal = new bootstrap.Modal(document
                                        .getElementById('riskConfirmationModal'));
                                    riskModal.show();
                                } else {
                                    const riskModal = new bootstrap.Modal(document
                                        .getElementById('excellentCreditModal'));
                                    riskModal.show();
                                }
                            } else if (clientData.trusted == 1) {
                                document.getElementById('available_credit_limit').value =
                                    clientData
                                    .limit_credit_for_this_user;
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
                    document.getElementById('payment_totalAmount').innerText = paymentDetails.total_price || 'N/A';
                    document.getElementById('payment_tva').innerText = paymentDetails.tva || 'N/A';
                    document.getElementById('payment_invoiceStatus').innerText = paymentDetails.invoice_status || 'N/A';
                    document.getElementById('payment_paymentMode').innerText = paymentDetails.payment_mode || 'N/A';
                    document.getElementById('payment_amountPaid').innerText = paymentDetails.already_paid || '0.00';
                    document.getElementById('payment_restToPay').innerText = paymentDetails.rest_to_pay || '0.00';

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

            document.getElementById('clientAvailableCredit').innerText = clientData.limit_credit_for_this_user;
            document.getElementById('clientDebtActual').innerText = clientData.current_debt;

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
