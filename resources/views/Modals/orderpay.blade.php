<!-- Modal de Détails du Paiement -->
<div class="modal fade" id="paymentDetailsModal" tabindex="-1" aria-labelledby="paymentDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentDetailsModalLabel">Détails du Paiement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST">
                @csrf
                <div class="modal-body">
                    <!-- Table des détails de paiement -->
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col">Champ</th>
                                <th scope="col">Valeur</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Client</strong></td>
                                <td id="payment_client"></td>
                            </tr>
                            <tr>
                                <td><strong>Magasin</strong></td>
                                <td id="payment_magasin"></td>
                            </tr>
                            <tr>
                                <td><strong>Utilisateur</strong></td>
                                <td id="payment_user"></td>
                            </tr>
                            <tr>
                                <td><strong>Montant Total</strong></td>
                                <td id="payment_totalAmount"></td>
                            </tr>
                            <tr>
                                <td><strong>TVA</strong></td>
                                <td id="payment_tva"></td>
                            </tr>
                            <tr>
                                <td><strong>Statut de la Facture</strong></td>
                                <td id="payment_invoiceStatus"></td>
                            </tr>
                            <tr>
                                <td><strong>Mode de Paiement</strong></td>
                                <td id="payment_paymentMode"></td>
                            </tr>
                            <tr>
                                <td><strong>Montant Payé</strong></td>
                                <td id="payment_amountPaid"></td>
                            </tr>
                            <tr>
                                <td><strong>Restant à Payer</strong></td>
                                <td id="payment_restToPay"></td>
                            </tr>
                            <!-- Mobile Money Details -->
                            <tr id="payment_mobileMoneyDetails" class="payment-details-row d-none">
                                <td><strong>Numéro Mobile Money</strong></td>
                                <td id="payment_mobileNumber"></td>
                            </tr>
                            <tr id="payment_mobileMoneyRef" class="payment-details-row d-none">
                                <td><strong>Référence Mobile Money</strong></td>
                                <td id="payment_mobileReference"></td>
                            </tr>
                            <!-- Credit Card Details -->
                            <tr id="payment_creditCardDetailsType" class="payment-details-row d-none">
                                <td><strong>Type de Carte</strong></td>
                                <td id="payment_cardType"></td>
                            </tr>
                            <tr id="payment_creditCardDetailsRef" class="payment-details-row d-none">
                                <td><strong>Référence de Carte</strong></td>
                                <td id="payment_cardReference"></td>
                            </tr>
                            <!-- Bank Transfer Details -->
                            <tr id="payment_bankNameRow" class="payment-details-row d-none">
                                <td><strong>Nom de la Banque</strong></td>
                                <td id="payment_bankName"></td>
                            </tr>
                            <tr id="payment_bankReferenceRow" class="payment-details-row d-none">
                                <td><strong>Référence de Virement</strong></td>
                                <td id="payment_bankReference"></td>
                            </tr>
                            <!-- Cash Details -->
                            <tr id="payment_cashDetailsRow" class="payment-details-row d-none">
                                <td><strong>Référence du Reçu</strong></td>
                                <td id="payment_cashReference"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    <button type="submit" class="btn btn-primary">Mettre à jour le paiement</button>
                </div>
            </form>
        </div>
    </div>
</div>