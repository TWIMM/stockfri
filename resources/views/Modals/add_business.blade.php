<!-- Modal for creating a business -->
<div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">Create a Business</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('business.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Business Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="type" class="form-label">Type de Business</label>
                        <select class="form-select" name="type" id="type">
                            <option value="prestation_de_service">Prestation de service</option>
                            <option value="business_physique">Business Physique</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="ifu" class="form-label">IFU</label>
                        <input type="text" class="form-control" id="ifu" name="ifu" required>
                    </div>
                    <div class="mb-3">
                        <label for="commercial_number" class="form-label">Commercial Number</label>
                        <input type="text" class="form-control" id="commercial_number" name="commercial_number" required>
                    </div>
                    <div class="mb-3">
                        <label for="number" class="form-label">Phone Number</label>
                        <input type="text" class="form-control" id="number" name="number" required>
                    </div>
                    <div class="mb-3">
                        <label for="business_email" class="form-label">Business Email</label>
                        <input type="email" class="form-control" id="business_email" name="business_email" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Create Business</button>
                </div>
            </form>
        </div>
    </div>
</div>
