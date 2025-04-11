
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
            
            </div>

            <div class="card-body">
                <form method="GET" action="{{ route('commandes.listes') }}" class="mb-4">
                    <div class="row">
                        
                     
                        <div class="col-md-3 mb-2">
                            <label for="min_price">Prix minimum</label>
                            <input min=0 type="number" name="min_price" id="min_price" class="form-control"
                                value="{{ request('min_price') }}">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label for="max_price">Prix maximum</label>
                            <input min=0 type="number" name="max_price" id="max_price" class="form-control"
                                value="{{ request('max_price') }}">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label for="date_start">Date début</label>
                            <input type="date" name="date_start" id="date_start" class="form-control"
                                value="{{ request('date_start') }}">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label for="date_end">Date fin</label>
                            <input type="date" name="date_end" id="date_end" class="form-control"
                                value="{{ request('date_end') }}">
                        </div>

                        <div class="col-md-12 mt-2">
                            <button type="submit" class="btn btn-primary">Filtrer</button>
                            <a href="{{ route('commandes.listes') }}" class="btn btn-secondary">Réinitialiser</a>
                        </div>
                    </div>
                </form>
                <div class="table-responsive">
                    <table class="table table-view">
                        <thead>
                            <tr>
                                <th>Stock</th>
                                <th>Quantite</th>
                                <th>Type de mouvement</th>
                                <th>Prix Fournisseur</th>
                                <th>Date </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($mouvementDeStocksForPagination as $moves)
                                <tr>
                                    <td> <span
                                            class="badge badge-pill badge-status bg-blue">{{ $moves->stock_id }}</span>
                                    </td>
                                    <td>
                                        {{ number_format($moves->quantity) }}
                                    </td>
                                    <td>
                                        {{ $moves->type_de_mouvement }}
                                    </td>
                                    <td> {{ $moves->prix_fournisseur }}</td>
                                    <td> {{ $moves->created_at }}</td>

                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-footer">
                {{ $mouvementDeStocksForPagination->links() }}
            </div>
        </div>
    </div>
</div>
