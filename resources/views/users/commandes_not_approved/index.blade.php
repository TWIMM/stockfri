@extends('layouts.app_layout')

@section('title', 'Stocks Page')

@section('content')

    @include('Modals.listes_des_produits')

    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">

                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-view">
                            <thead>
                                <tr>
                                    <th>Produit</th>
                                    <th>Client</th>
                                    <th>Mode de Paiement </th>
                                    <th>Total</th>
                                    <th>Reste a payer</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody`>
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
                                        <td>{{ $client->name }}</td>
                                        <td>{{ $eachcommandeNotApproved->payment_mode }}</td>
                                        <td>{{ $eachcommandeNotApproved->total_price }} FCFA</td>
                                        <td>{{ $eachcommandeNotApproved->rest_to_pay }}</td>

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
                            document.querySelector('#mmodalListeDeProduits select[name="client_id"]')
                                .value = data.commande.client_id;
                            document.querySelector('#mmodalListeDeProduits input[name="tva"]').value = data
                                .commande.tva || 0;
                            document.querySelector('#mmodalListeDeProduits select[name="payment_mode"]')
                                .value = data.commande.payment_mode;
                            document.querySelector('#mmodalListeDeProduits select[name="invoice_status"]')
                                .value = data.commande.invoice_status;

                            // Vider les produits existants (garder la première ligne)
                            const productsContainer = document.getElementById(
                                'productsContainer');
                            while (productsContainer.children.length > 1) {
                                productsContainer.removeChild(productsContainer.lastChild);
                            }

                            // Remplir avec le premier produit
                            if (data.products.length > 0) {
                                const firstProductSelect = document.querySelector(
                                    'select[name="products[0][product_id]"]');
                                const firstQuantityInput = document.querySelector(
                                    'input[name="products[0][quantity]"]');
                                const firstDiscountInput = document.querySelector(
                                    'input[name="products[0][discount]"]');
                                const firstPriceInput = document.querySelector(
                                    'input[name="products[0][price]"]');

                                firstProductSelect.value = data.products[0].stock_id;
                                firstQuantityInput.value = data.products[0].quantity;
                                firstDiscountInput.value = data.products[0].discount || 0;
                                firstPriceInput.value = data.products[0].price;
                            }

                            // Ajouter les produits supplémentaires
                            for (let i = 1; i < data.products.length; i++) {
                                createProductRow();

                                const productSelect = document.querySelector(
                                    `select[name="products[${i}][product_id]"]`);
                                const quantityInput = document.querySelector(
                                    `input[name="products[${i}][quantity]"]`);
                                const discountInput = document.querySelector(
                                    `input[name="products[${i}][discount]"]`);
                                const priceInput = document.querySelector(
                                    `input[name="products[${i}][price]"]`);

                                productSelect.value = data.products[i].stock_id;
                                quantityInput.value = data.products[i].quantity;
                                discountInput.value = data.products[i].discount || 0;
                                priceInput.value = data.products[i].price;
                            }

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

                            // Ouvrir le modal manuellement
                            const bsModal = new bootstrap.Modal(document.getElementById(
                                'mmodalListeDeProduits'));
                            bsModal.show();
                        } else {
                            alert('Impossible de récupérer les détails de la commande.');
                        }
                    };
                    xhr.send();
                });
            });
        });
    </script>

@endsection
