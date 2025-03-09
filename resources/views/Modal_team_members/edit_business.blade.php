<!-- Edit Business Modal -->
<div class="modal fade" id="editBusinessModal" tabindex="-1" aria-labelledby="editBusinessModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editBusinessModalLabel">Edit Business</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Modal content will be populated via AJAX -->
                <form id="editBusinessForm">
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="form-label">Nom</label>
                        <input type="text" class="form-control" id="name_edit" name="name">
                        <input type="hidden" class="form-control" id="business_id_edit" name="business_id_edit">
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description_edit" name="description" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="ifu" class="form-label">Ifu</label>
                        <input type="text" class="form-control" id="ifu_edit" name="ifu">
                    </div>
                    <div class="mb-3">
                        <label for="commercial_number" class="form-label">Num commercial</label>
                        <input type="text" class="form-control" id="commercial_number_edit" name="commercial_number">
                    </div>
                    <div class="mb-3">
                        <label for="business_email" class="form-label">Email professionnel</label>
                        <input type="email" class="form-control" id="business_email_edit" name="business_email">
                    </div>
                    <div class="mb-3">
                        <label for="number" class="form-label">Telephone</label>
                        <input type="text" class="form-control" id="number_edit" name="number">
                    </div>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>
