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
                                <td id="client"></td>
                            </tr>
                            <tr>
                                <td><strong>Magasin</strong></td>
                                <td id="magasin"></td>
                            </tr>
                            <tr>
                                <td><strong>Utilisateur</strong></td>
                                <td id="user"></td>
                            </tr>
                            <tr>
                                <td><strong>Montant Total</strong></td>
                                <td id="totalAmount"></td>
                            </tr>
                            <tr>
                                <td><strong>TVA</strong></td>
                                <td id="tva_ORDER"></td>
                            </tr>
                            <tr>
                                <td><strong>Statut de la Facture</strong></td>
                                <td id="invoiceStatusoRDER"></td>
                            </tr>
                            <tr>
                                <td><strong>Mode de Paiement</strong></td>
                                <td id="paymentModeOrderDet"></td>
                            </tr>
                            <tr>
                                <td><strong>Montant Payé</strong></td>
                                <td id="amountPaid"></td>
                            </tr>
                            <tr>
                                <td><strong>Restant à Payer</strong></td>
                                <td id="restToPay"></td>
                            </tr>
                            <!-- Mobile Money Details -->
                            <tr id="mobileMoneyDetails" class="payment-details-oder_pay d-none">
                                <td><strong>Numéro Mobile Money</strong></td>
                                <td id="mobileNumber"></td>
                            </tr>
                            <tr id="mobileMoneyRef" class="payment-details-oder_pay d-none">
                                <td><strong>Référence Mobile Money</strong></td>
                                <td id="mobileReference"></td>
                            </tr>
                            <!-- Credit Card Details -->
                            <tr id="creditCardDetailsType" class="payment-details-oder_pay d-none">
                                <td><strong>Type de Carte</strong></td>
                                <td id="cardType"></td>
                            </tr>
                            <tr id="creditCardDetailsRef" class="payment-details-oder_pay d-none">
                                <td><strong>Référence de Carte</strong></td>
                                <td id="cardReference"></td>
                            </tr>
                            <!-- Bank Transfer Details -->
                            <tr id="bankNameRow" class="payment-details-oder_pay d-none">
                                <td><strong>Nom de la Banque</strong></td>
                                <td id="bankName"></td>
                            </tr>
                            <tr id="bankReferenceRow" class="payment-details-oder_pay d-none">
                                <td><strong>Référence de Virement</strong></td>
                                <td id="bankReference"></td>
                            </tr>
                            <!-- Cash Details -->
                            <tr id="cashDetailsOrderPay" class="payment-details-oder_pay d-none">
                                <td><strong>Référence du Reçu</strong></td>
                                <td id="cashReference"></td>
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
