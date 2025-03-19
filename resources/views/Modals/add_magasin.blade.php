<!-- Modal pour ajouter un magasin -->
<div class="modal fade" id="addMagasinsModal" tabindex="-1" aria-labelledby="addMagasinsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="addMagasinsForm" method="POST" action="{{ route('magasins.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addMagasinsModalLabel">Ajouter un nouveau Magasin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Champ pour le nom du magasin -->
                    <div class="mb-3">
                        <label for="name" class="form-label">Nom du magasin</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>

                    <!-- Champ pour la description du magasin -->
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>

                    <!-- Champ pour l'adresse du magasin -->
                    <div class="mb-3">
                        <label for="address" class="form-label">Adresse</label>
                        <input type="text" class="form-control" id="address" name="address">
                    </div>

                    <!-- Champ pour l'email -->
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email">
                    </div>

                    <!-- Champ pour le téléphone -->
                    <div class="mb-3">
                        <label for="tel" class="form-label">Téléphone</label>
                        <input type="text" class="form-control" id="tel" name="tel">
                    </div>

                    <!-- Sélecteur d'entreprise -->
                    <div class="mb-3">
                        <label for="business_id" class="form-label">Entreprise</label>
                        <select name="business_id" id="business_id" class="form-control" required>
                            @foreach($businesses as $business)
                                <option value="{{ $business->id }}">{{ $business->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    <button type="submit" class="btn btn-primary">Ajouter le magasin</button>
                </div>
            </div>
        </form>
    </div>
</div>
