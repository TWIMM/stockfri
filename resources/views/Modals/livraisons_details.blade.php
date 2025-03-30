<!-- Livraison Details Modal -->
<div class="modal fade" id="livraisonDetailsModal" tabindex="-1" aria-labelledby="livraisonDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="livraisonDetailsModalLabel">Détails de la Livraison</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST">
                @csrf
                <div class="modal-body">
                    <!-- Table des détails de la livraison -->
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col">Champ</th>
                                <th scope="col">Valeur</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Date de livraison</strong></td>
                                <td id="livraisonDate"></td>
                            </tr>
                            <tr>
                                <td><strong>Statut de la livraison</strong></td>
                                <td id="livraisonStatus"></td>
                            </tr>
                            <tr>
                                <td><strong>Adresse de livraison</strong></td>
                                <td id="livraisonAddress"></td>
                            </tr>
                            <tr>
                                <td><strong>Notes de livraison</strong></td>
                                <td id="livraisonNotes"></td>
                            </tr>
                            <tr>
                                <td><strong>Livré par</strong></td>
                                <td id="deliveredBy"></td>
                            </tr>
                            <tr>
                                <td><strong>Reçu par</strong></td>
                                <td id="receivedBy"></td>
                            </tr>
                            <tr>
                                <td><strong>Numéro de suivi</strong></td>
                                <td id="trackingNumber"></td>
                            </tr>
                            <tr>
                                <td><strong>Méthode de livraison</strong></td>
                                <td id="shippingMethod"></td>
                            </tr>
                            <tr>
                                <td><strong>Coût de livraison</strong></td>
                                <td id="shippingCost"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    <button type="submit" class="btn btn-primary">Mettre à jour la livraison</button>
                </div>
            </form>
        </div>
    </div>
</div>
