@extends('layouts.team_member_layout')

@section('title', 'Gestion des Catégories De Produits')

@section('content')

    @include('Modals.add_category_produits')  <!-- Modal pour ajouter une catégorie -->
    @include('Modals.edit_category_produits') <!-- Modal pour éditer une catégorie -->

    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                        Ajouter une Catégorie
                    </button>
                </div>

                <div class="card-body">
                    <form method="GET" action="{{ route('cat_prod.listes') }}" class="mb-4">
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label for="search">Nom de la catégorie</label>
                                <input type="text" name="search" id="search" class="form-control" value="{{ request('search') }}" placeholder="Rechercher par nom de catégorie">
                            </div>
                            <div class="col-md-12 mt-2">
                                <button type="submit" class="btn btn-primary">Filtrer</button>
                                <a href="{{ route('cat_prod.listes') }}" class="btn btn-secondary">Réinitialiser</a>
                            </div>
                        </div>
                    </form>
                    
                    <div class="table-responsive">
                        <table class="table table-view">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($categories as $category)
                                    <tr>
                                        <td>{{ $category->name }}</td>
                                        <td>
                                            <!-- Modifier la catégorie -->
                                            <button type="button" class="btn btn-secondary edit-btn"
                                                data-id="{{ $category->id }}" data-name="{{ $category->name }}"
                                                data-bs-toggle="modal" data-bs-target="#editCategoryModal">
                                                <i class="ti ti-pencil"></i>
                                            </button>
                                            
                                            <!-- Supprimer la catégorie -->
                                            <form action="{{ route('categories.destroy', $category->id) }}"
                                                method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger"><i class="ti ti-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center">Aucune catégorie trouvée.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="d-flex justify-content-center">
                        {{ $categories->links() }}  <!-- Pagination des catégories -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Ajouter un événement pour les boutons "Modifier"
            const editButtons = document.querySelectorAll('.edit-btn');
    
            editButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    // Récupérer les données de la catégorie à partir des attributs de l'élément cliqué
                    const categoryId = button.getAttribute('data-id');
                    const categoryName = button.getAttribute('data-name');
    
                    // Remplir le formulaire avec les données de la catégorie
                    document.getElementById('name_edit').value = categoryName;
    
                    // Mettre à jour l'action du formulaire pour qu'il soumette la mise à jour de la catégorie
                    const form = document.getElementById('editCategoryForm');
                    form.action = '/categories/' + categoryId;
                });
            });
        });
    </script>
    
@endsection
