@extends('layouts.app_layout')

@section('title', 'Gestion des Coéquipiers')

@section('content')

    @include('Modals.add_coequipier_member')
    @include('Modals.edit_coequipier_member')

    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTeamMemberModal">
                        Ajouter un Coéquipier
                    </button>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-view">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Email</th>
                                    <th>Téléphone</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($coequipiers as $coequipier)
                                    <tr>
                                        <td>{{ $coequipier->name }}</td>
                                        <td>{{ $coequipier->email }}</td>
                                        <td>{{ $coequipier->tel }}</td>
                                        <td>
                                            <button type="button" class="btn btn-primary edit-btn"
                                                data-id="{{ $coequipier->id }}" data-name="{{ $coequipier->name }}"
                                                data-email="{{ $coequipier->email }}" data-tel="{{ $coequipier->tel }}"
                                                data-bs-toggle="modal" data-bs-target="#editTeamMemberModal">
                                                Modifier
                                            </button>
                                            <form action="{{ route('coequipiers.destroy', $coequipier->id) }}"
                                                method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger">Supprimer</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">Aucun coéquipier trouvé.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="d-flex justify-content-center">
                        {{ $coequipiers->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
