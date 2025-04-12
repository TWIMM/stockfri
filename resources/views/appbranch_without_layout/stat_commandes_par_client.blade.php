
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
            
            </div>

            <div class="card-body">
               
                <div class="table-responsive">
                    <table class="table table-view">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Quantite totale</th>
                                <th>Total (F CFA)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($clients as $client)
                                <tr>
                                    <td> <span
                                            class="badge badge-pill badge-status bg-blue">{{ $client->name }}</span>
                                    </td>
                                    <td>
                                        {{ $commandeTotalPerStock($client->id , $stock, 'bought') }}
                                    </td>
                                    <td>
                                        {{ $commandeTotalPerStock($client->id , $stock, 'price') }}
                                    </td>

                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-footer">
                {{ $clients->links() }}
            </div>
        </div>
    </div>
</div>
