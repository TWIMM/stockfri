@extends('layouts.app_layout')

@section('title', 'Stock Fri')

@section('content')

<div class="col-md-12">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-sm-4">
                <h4 class="page-title">Paramètres</h4>
            </div>
            <div class="col-sm-8 text-sm-end">
                <div class="head-icons">
                    <a href="profile.html" data-bs-toggle="tooltip" data-bs-placement="top"
                        data-bs-original-title="Actualiser"><i class="ti ti-refresh-dot"></i></a>
                    <a href="javascript:void(0);" data-bs-toggle="tooltip" data-bs-placement="top"
                        data-bs-original-title="Réduire" id="collapse-header"><i class="ti ti-chevrons-up"></i></a>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body pb-0 pt-2">
            <ul class="nav nav-tabs nav-tabs-bottom">
                <li class="nav-item me-3">
                    <a href="/profile_page" class="nav-link px-0 active">
                        <i class="ti ti-settings-cog me-2"></i>Paramètres généraux
                    </a>
                </li>
                
                
            </ul>
        </div>
    </div> 

    <div class="row">
        <div class="col-xl-12 col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="fw-semibold mb-3">Paramètres du profil</h4>
                    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT') 
                        
                        <div class="border-bottom mb-3 pb-3">
                            <h5 class="fw-semibold mb-1">Informations de l'utilisateur</h5>
                            <p>Fournissez vos informations de profil ci-dessous</p>
                        </div>
                        
                        <div class="mb-3">
                            <div class="profile-upload">
                                <div class="profile-upload-img" style='background:transparent'>
                                    <span><i class="ti ti-photo"></i></span>
                                    <img id="ImgPreview" 
                                        src="{{ $user->profile_image ? asset('storage/profiles/' . $user->profile_image) : asset('assets/img/profiles/avatar-02.jpg') }}" 
                                        alt="Profile" class="preview1" />
                                    <button type="button" id="removeImage1" class="profile-remove">
                                        <i class="feather-x"></i>
                                    </button>
                                </div>
                                <div class="profile-upload-content">
                                    <label class="profile-upload-btn">
                                        <i class="ti ti-file-broken"></i> Télécharger une photo
                                        <input type="file" id="imag" name="profile_image" class="input-img" />
                                    </label>
                                    <p>JPG, GIF ou PNG. Taille maximale de 800K</p>
                                </div>
                            </div>
                        </div>

                        
                        <div class="border-bottom mb-3">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">
                                           Prénom <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control" required />
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            Téléphone <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" name="phone" value="{{ old('phone', $user->tel) }}" class="form-control" required />
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            E-mail <span class="text-danger">*</span>
                                        </label>
                                        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control" required />
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="border-bottom mb-3 pb-3">
                            <h5 class="fw-semibold mb-1">Adresse</h5>
                            <p>Veuillez entrer vos informations d'adresse</p>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">
                                        Adresse <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="address" value="{{ old('address', $user->address) }}" class="form-control" required />
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">
                                        Pays <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="country" value="{{ old('country', $user->country) }}" class="form-control" required />
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">
                                        Ville <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="city" value="{{ old('city', $user->city) }}" class="form-control" required />
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <a href="{{ route('profile.page') }}" class="btn btn-light me-2">Annuler</a>
                            <button type="submit" class="btn btn-primary">Sauvegarder les modifications</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
<script type="text/javascript">
    document.getElementById('imag').addEventListener('change', function(event) {
        const file = event.target.files[0]; // Get the selected file
        const preview = document.getElementById('ImgPreview'); // Get the image element
        
        if (file) {
            const reader = new FileReader(); // Create a FileReader object

            reader.onload = function(e) {
                preview.src = e.target.result; // Set the preview image source to the file's data URL
            };

            reader.readAsDataURL(file); // Read the file as a data URL
        }
    });

    // Optional: Clear the preview and reset the input when the user clicks the remove button
    document.getElementById('removeImage1').addEventListener('click', function() {
        document.getElementById('imag').value = ''; // Reset the file input
        document.getElementById('ImgPreview').src = '{{ asset('assets/img/profiles/avatar-02.jpg') }}'; // Reset the preview image to default
    });
</script>

<script src="{{ asset('assets/plugins/theia-sticky-sidebar/ResizeSensor.js') }}" type="text/javascript"></script>
<script src="{{ asset('assets/plugins/theia-sticky-sidebar/theia-sticky-sidebar.js') }}" type="text/javascript"></script>

@endsection
