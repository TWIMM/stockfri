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
                                <td><strong>Montant Total</strong></td>
                                <td id="totalAmount"></td>
                            </tr>
                            <tr>
                                <td><strong>Montant Payé</strong></td>
                                <td id="amountPaid"></td>
                            </tr>
                            <tr>
                                <td><strong>Mode de Paiement</strong></td>
                                <td id="paymentMode"></td>
                            </tr>
                            <tr id="mobileMoneyDetails" class="payment-details d-none">
                                <td><strong>Numéro Mobile Money</strong></td>
                                <td id="mobileNumber"></td>
                            </tr>
                            <tr id="mobileMoneyDetails" class="payment-details d-none">
                                <td><strong>Référence Mobile Money</strong></td>
                                <td id="mobileReference"></td>
                            </tr>
                            <tr id="creditCardDetails" class="payment-details d-none">
                                <td><strong>Type de Carte</strong></td>
                                <td id="cardType"></td>
                            </tr>
                            <tr id="creditCardDetails" class="payment-details d-none">
                                <td><strong>Référence de Carte</strong></td>
                                <td id="cardReference"></td>
                            </tr>
                            <tr id="bankTransferDetails" class="payment-details d-none">
                                <td><strong>Nom de la Banque</strong></td>
                                <td id="bankName"></td>
                            </tr>
                            <tr id="bankTransferDetails" class="payment-details d-none">
                                <td><strong>Référence de Virement</strong></td>
                                <td id="bankReference"></td>
                            </tr>
                            <tr id="cashDetails" class="payment-details d-none">
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

<script>
    // Fonction pour charger les détails du paiement dans le modal
    function loadPaymentDetails(paymentId) {
        // Exemple de données pour démonstration
        const paymentDetails = {
            totalAmount: "500 000 FCFA",
            amountPaid: "250 000 FCFA",
            paymentMode: "Mobile Money",
            mobileNumber: "0701234567",
            mobileReference: "ABC123456",
            cardType: "Visa",
            cardReference: "CARD12345",
            bankName: "Banque X",
            bankReference: "BANK123456",
            cashReference: "RECEIPT12345",
        };

        // Remplissage des informations dans le modal
        document.getElementById('totalAmount').innerText = paymentDetails.totalAmount;
        document.getElementById('amountPaid').innerText = paymentDetails.amountPaid;
        document.getElementById('paymentMode').innerText = paymentDetails.paymentMode;

        // Détails supplémentaires selon le mode de paiement
        if (paymentDetails.paymentMode === "Mobile Money") {
            document.getElementById('mobileMoneyDetails').classList.remove('d-none');
            document.getElementById('mobileNumber').innerText = paymentDetails.mobileNumber;
            document.getElementById('mobileReference').innerText = paymentDetails.mobileReference;
            
            document.getElementById('creditCardDetails').classList.add('d-none');
            document.getElementById('bankTransferDetails').classList.add('d-none');
            document.getElementById('cashDetails').classList.add('d-none');
        } else if (paymentDetails.paymentMode === "Carte de Crédit") {
            document.getElementById('creditCardDetails').classList.remove('d-none');
            document.getElementById('cardType').innerText = paymentDetails.cardType;
            document.getElementById('cardReference').innerText = paymentDetails.cardReference;

            document.getElementById('mobileMoneyDetails').classList.add('d-none');
            document.getElementById('bankTransferDetails').classList.add('d-none');
            document.getElementById('cashDetails').classList.add('d-none');
        } else if (paymentDetails.paymentMode === "Virement Bancaire") {
            document.getElementById('bankTransferDetails').classList.remove('d-none');
            document.getElementById('bankName').innerText = paymentDetails.bankName;
            document.getElementById('bankReference').innerText = paymentDetails.bankReference;

            document.getElementById('mobileMoneyDetails').classList.add('d-none');
            document.getElementById('creditCardDetails').classList.add('d-none');
            document.getElementById('cashDetails').classList.add('d-none');
        } else if (paymentDetails.paymentMode === "Espèces") {
            document.getElementById('cashDetails').classList.remove('d-none');
            document.getElementById('cashReference').innerText = paymentDetails.cashReference;

            document.getElementById('mobileMoneyDetails').classList.add('d-none');
            document.getElementById('creditCardDetails').classList.add('d-none');
            document.getElementById('bankTransferDetails').classList.add('d-none');
        }
    }

    // Initialisation de la fonction lors de l'ouverture du modal
    document.getElementById('paymentDetailsModal').addEventListener('show.bs.modal', function (event) {
        const paymentId = event.relatedTarget.getAttribute('data-payment-id');
        loadPaymentDetails(paymentId);
    });
</script>
