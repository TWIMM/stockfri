<div class="modal fade" id="addServiceModal" tabindex="-1" aria-labelledby="addServiceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="addServiceForm" method="POST" action="{{ route('services.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addServiceModalLabel">Ajouter un service</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="title_add" class="form-label">Titre</label>
                        <input type="text" class="form-control" id="title_add" name="title" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description_add" class="form-label">Description</label>
                        <textarea class="form-control" id="description_add" name="description" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="price_add" class="form-label">Prix (F CFA)</label>
                        <input type="number" class="form-control" id="price_add" name="price" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Business associé</label>
                        <div id="business-options">
                            <select name="business_id" id="business_id" class="form-control">
                                    
                            
                                @forelse($businesses as $business)
                                    <option value="{{$business->id}}" >{{$business->name}}</option>
                                @empty
                                    <p>Aucun Business disponible</p>
                                @endforelse
                           </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    <button type="submit" class="btn btn-primary">Ajouter service</button>
                </div>
            </div>
        </form>
    </div>
</div>
