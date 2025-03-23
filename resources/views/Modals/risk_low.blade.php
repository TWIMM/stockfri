<!-- Modal de Confirmation pour Excellent Score de Crédit -->
<div class="modal fade" id="excellentCreditModal" tabindex="-1" aria-labelledby="excellentCreditModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{route('commandes.approveClientOrder')}}" method="POST">
            @csrf
            <input type="hidden" name="commande_id" id="commande_id_risk_low" value=''>

            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="excellentCreditModalLabel">
                        <i class="ti ti-award me-2"></i>Client Privilégié Détecté
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-success">
                        <p><strong>Opportunité de vente spéciale!</strong></p>
                        <p>Ce client dispose selon vous d'un excellent profil financier</p>
                        <ul>
                        </ul>
                        <p>Ce client est <strong>éligible pour une vente sans dépôt initial</strong> jusqu'à concurrence de sa limite de crédit.</p>
                    </div>
                    
                    <div class="form-group mt-3">
                        <label for="credit_offer_amount" class="form-label">Credit achat</label>
                        <div class="input-group">
                            <span class="input-group-text">FCFA</span>
                            <input type="number" id="available_credit_limit" class="form-control" readonly>
                        </div>
                    </div>
                    
                    <div class="form-group mt-3">
                        <label for="current_debt" class="form-label">Dette actuelle</label>
                        <div class="input-group">
                            <span class="input-group-text">FCFA</span>
                            <input type="number" id="current_debt" class="form-control" readonly>
                        </div>
                    </div>
                   
                    
                    <input type="hidden" id="excellent_client_id" value="">
                    <input type="hidden" id="excellent_commande_id" value="">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Ignorer cette opportunité</button>
                    <button type="submit" id="confirmCreditOffer" class="btn btn-success">
                        <i class="ti ti-check me-1"></i>Accepter la commande
                    </button>
                </div>
            </div>
        </form>
        
    </div>
</div>