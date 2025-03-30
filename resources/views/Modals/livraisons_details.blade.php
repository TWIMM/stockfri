<!-- Client Details Modal -->
<div class="modal fade" id="livraisonDetailsModal" tabindex="-1" aria-labelledby="clientDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="clientDetailsModalLabel">Détails du Client</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST">
                @csrf
                <div class="modal-body">
                    <!-- Table des détails du client -->
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col">Champ</th>
                                <th scope="col">Valeur</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Nom</strong></td>
                                <td id="clientName"></td>
                            </tr>
                            <tr>
                                <td><strong>Email</strong></td>
                                <td id="clientEmail"></td>
                            </tr>
                            <tr>
                                <td><strong>Numéro de téléphone</strong></td>
                                <td id="clientPhone"></td>
                            </tr>
                            <tr>
                                <td><strong>Adresse</strong></td>
                                <td id="clientAddress"></td>
                            </tr>
                            
                            <!-- Additional client data fields -->
                            <tr>
                                <td><strong>Score de crédit</strong></td>
                                <td id="clientCreditScore"></td>
                            </tr>
                            <tr>
                                <td><strong>Niveau de risque</strong></td>
                                <td > <span id='clientRiskLevelBadge' style="color: white" class="badge"></span></td>
                            </tr>
                            <tr>
                                <td><strong>Crédit disponible</strong></td>
                                <td id="clientAvailableCredit"></td>
                            </tr>
                            <tr>
                                <td><strong>Dette actuelle</strong></td>
                                <td id="clientDebtActual"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    <button type="submit" class="btn btn-primary">Mettre à jour le client</button>
                </div>
            </form>
        </div>
    </div>
</div>

