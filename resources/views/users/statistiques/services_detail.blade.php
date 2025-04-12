@extends('layouts.app_layout')

@section('title', 'Stock fri')

@section('content')

    <div class="col-md-12">
       
        
        <div class="campaign-tab">
            <ul class="nav">
                

                <li>
                    <a href="javascript:void(0);" class="{{  'active'}}" id="tab-stat_commandes_par_client" data-target="card-stat_commandes_par_client">
                        Commandes par client(s)
                    </a>
                </li>
                
                
            </ul>
        </div>

        <div class="card" id="all-content">
            @include('appbranch_without_layout.stat_commandes_par_client_services')
        </div>

       
    </div>


@endsection
