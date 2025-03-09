<!-- Modal for creating a business -->
<div class="modal fade" id="editTeamModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">Mofifier une equipe</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id='editTeamForm'>
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nom de l'equipe</label>
                        <input type="text" class="form-control" id="name_edit" name="name_edit" required>
                        <input type="hidden" class="form-control" id="team_id_edit" name="team_id_edit" required>

                    </div>
                   
                    <div class="mb-3">
                        <label for="business_id" class="form-label">Business associé</label>
                        <select class="form-select" name="business_ids[]" id="business_id_edit" multiple>
                            @forelse($businesses as $business)
                                <option value="{{ $business->id }}">{{ $business->name }} - {{ $business->type == 'business_physique' ? 'Business Physique' : 'Prestation de service' }}</option>
                            @empty
                                <option value="">Aucun Business</option>
                            @endforelse
                        </select>
                    </div>
                    
                    
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Edit Equipe</button>
                </div>
            </form>
        </div>
    </div>
</div>
