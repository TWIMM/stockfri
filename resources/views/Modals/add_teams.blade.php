<!-- Modal for creating a business -->
<div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">Creer une equipe</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('teams.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nom de l'equipe</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                        <input type="hidden" value="{{$user->id}}" class="form-control" id="user_id" name="user_id" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Business associé</label>
                        <div id="business-options">
                            @forelse($businesses as $business)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="business_ids[]" id="business_{{ $business->id }}" value="{{ $business->id }}">
                                    <label class="form-check-label" for="business_{{ $business->id }}">
                                        {{ $business->name }} - {{ $business->type == 'business_physique' ? 'Business Physique' : 'Prestation de service' }}
                                    </label>
                                </div>
                            @empty
                                <p>Aucun Business disponible</p>
                            @endforelse
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Créer équipe</button>
                </div>
            </form>
        </div>
    </div>
</div>
