<!-- Modal de Confirmation de Risque -->
<div class="modal fade" id="riskConfirmationModal" tabindex="-1" aria-labelledby="riskConfirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="riskConfirmationModalLabel">
                    <i class="ti ti-alert-triangle me-2"></i>Attention: Risque Élevé
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <p><strong>Niveau de risque élevé détecté!</strong></p>
                    <p>Cette commande présente un risque important pour les raisons suivantes:</p>
                    <ul>
                        <li>Score de crédit client: <span id="risk_credit_score" class="fw-bold text-danger"></span></li>
                        <li>Niveau de risque: <span id="risk_level" class="fw-bold text-danger"></span></li>
                    </ul>
                    <p class="mb-0">Vous devrez activer le Trust et ajouter un credit au portefeuille du client pour effectuer cette action !
                    </p>
                </div>
                
                <div class="form-group mt-3">
                    <tr class="mt-3">
                        <td><strong>Trust client</strong></td>
                        <td>
                            <select class="form-select" name="trust_status">
                                <option value="0">Désactiver</option>
                                <option value="1">Activer</option>
                            </select>
                        </td>
                    </tr>
                    <tr  class="mt-3">
                        <td><strong>Crédit en FCFA</strong></td>
                        <td><input type="number" class="form-control" name="credit"
                                placeholder="Montant du crédit en FCFA" required></td>
                    </tr>
                   <div class="invalid-feedback">
                        Veuillez activer le Trust et ajouter un credit au portefeuille du client .
                    </div>
                </div>

                
                
                <input type="hidden" id="risk_commande_id" value="">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" id="confirmRiskApproval" class="btn btn-danger">
                    <i class="ti ti-check me-1"></i>Confirmer l'approbation
                </button>
            </div>
        </div>
    </div>
</div>