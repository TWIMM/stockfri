<!-- Modal de vente -->
<div class="modal fade" id="remboursementModal" tabindex="-1" aria-labelledby="modalVenteLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalVenteLabel">Remboursements de crédit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('finances.handle_dette') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">



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



                    <div id="alreadyPayIInput" class="mb-3 d-none">
                        <label for="invoiceStatusl" class="form-label">Montant payé</label>
                        <input type="number" name="amount" class="form-control">
                        <input type="hidden" id='put_id_in_there' value='' name='id'>
                    </div>

                    <!-- Champs conditionnels pour les détails de paiement -->
                    <div id="paymentDetailsContainer" class="d-none">
                        <!-- Pour Mobile Money -->
                        <div id="mobileMoneyDetails" class="payment-details d-none mb-3">
                            <h6 class="mb-3">Détails Mobile Money</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="mobileNumber" class="form-label">Numéro Mobile Money</label>
                                    <input type="text" class="form-control" id="mobileNumber" name="mobile_number">
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
                                    <input type="text" class="form-control" id="bankReference" name="bank_reference">
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
                                    <input type="text" class="form-control" id="cardReference" name="card_reference">
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

                         <div id="facture" class=" mb-3">
                            <h6 class="mb-3">Facture</h6>
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <input type="file" class="form-control" id="factureId"
                                        name="factures_remboursement" multiple>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    <button type="submit" class="btn btn-primary">Enregistrer le remboursement</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get the payment mode select element
        const paymentModeSelect = document.getElementById('paymentMode');

        // Get the payment details container and all specific details sections
        const paymentDetailsContainer = document.getElementById('paymentDetailsContainer');
        const alreadyPayInput = document.getElementById('alreadyPayIInput');
        const mobileMoneyDetails = document.getElementById('mobileMoneyDetails');
        const bankTransferDetails = document.getElementById('bankTransferDetails');
        const creditCardDetails = document.getElementById('creditCardDetails');
        const cashDetails = document.getElementById('cashDetails');

        // Function to hide all payment detail sections
        function hideAllPaymentDetails() {
            mobileMoneyDetails.classList.add('d-none');
            bankTransferDetails.classList.add('d-none');
            creditCardDetails.classList.add('d-none');
            cashDetails.classList.add('d-none');
        }

        // Function to display the correct payment details section based on the selected payment method
        function updatePaymentDetails() {
            // Get the selected payment method
            const selectedPaymentMode = paymentModeSelect.value;

            // Hide all details sections first
            hideAllPaymentDetails();

            // Show the corresponding section based on selected payment mode
            if (selectedPaymentMode === 'cash') {
                cashDetails.classList.remove('d-none');
            } else if (selectedPaymentMode === 'credit_card') {
                creditCardDetails.classList.remove('d-none');
            } else if (selectedPaymentMode === 'mobile_money') {
                mobileMoneyDetails.classList.remove('d-none');
            } else if (selectedPaymentMode === 'bank_transfer') {
                bankTransferDetails.classList.remove('d-none');
            }

            alreadyPayInput.classList.remove('d-none');

            // Display the payment details container if a payment method is selected
            if (selectedPaymentMode) {
                paymentDetailsContainer.classList.remove('d-none');
            }
        }

        // Attach event listener to the payment mode select element
        paymentModeSelect.addEventListener('change', updatePaymentDetails);

        // Initialize the payment details view when the page loads
        updatePaymentDetails();
    });
</script>
