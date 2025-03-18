<div class="modal fade" id="editServiceModal" tabindex="-1" aria-labelledby="editServiceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="editServiceForm" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editServiceModalLabel">Edit Service</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="service_id_edit" name="service_id">
                    <div class="mb-3">
                        <label for="title_edit" class="form-label">Title</label>
                        <input type="text" class="form-control" id="title_edit" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="description_edit" class="form-label">Description</label>
                        <textarea class="form-control" id="description_edit" name="description" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="price_edit" class="form-label">Price (FCFA)</label>
                        <input type="number" class="form-control" id="price_edit" name="price" required>
                    </div>


                    <div class="mb-3">
                        <label class="form-label">Business associé</label>
                        <div id="business-options">
                            <select name="business_id" id="business_id_edit" class="form-control">
                                    
                            
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
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </div>
        </form>
    </div>
</div>
