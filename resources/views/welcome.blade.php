@extends('layouts.app_layout')

@section('title', 'Stock fri')

@section('content')

    <div class="col-md-12">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-4">
                    <h4 class="page-title">
                        Commande en attente<span class="count-title">{{$countOrderNotApproved}}</span>
                    </h4>
                </div>
                <div class="col-8 text-end">
                    <div class="head-icons">
                        <a href="campaign.html" data-bs-toggle="tooltip" data-bs-placement="top"
                            data-bs-original-title="Refresh">
                            <i class="ti ti-refresh-dot"></i>
                        </a>
                        <a href="javascript:void(0);" data-bs-toggle="tooltip" data-bs-placement="top"
                            data-bs-original-title="Collapse" id="collapse-header">
                            <i class="ti ti-chevrons-up"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-3 col-lg-6">
                <div class="campaign-box bg-danger-light">
                    <div class="campaign-img">
                        <span>
                            <i class="ti ti-brand-campaignmonitor"></i>
                        </span>
                        <p>Clients</p>
                    </div>
                    <h2>{{$countClients}}</h2>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6">
                <div class="campaign-box bg-warning-light">
                    <div class="campaign-img">
                        <span>
                            <i class="ti ti-send"></i>
                        </span>
                        <p>Equipe</p>
                    </div>
                    <h2>{{$countTeams}}</h2>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6">
                <div class="campaign-box bg-purple-light">
                    <div class="campaign-img">
                        <span>
                            <i class="ti ti-brand-feedly"></i>
                        </span>
                        <p>Coequipiers</p>
                    </div>
                    <h2>{{$countTeamMembers}}</h2>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6">
                <div class="campaign-box bg-success-light">
                    <div class="campaign-img">
                        <span>
                            <i class="ti ti-brand-pocket"></i>
                        </span>
                        <p>Business</p>
                    </div>
                    <h2>{{$countBusiness}}</h2>
                </div>
            </div>
        </div>

        <div class="campaign-tab">
            <ul class="nav">
                <li>
                    <a href="campaign.html" class="active">Commandes approuvée<span>{{$countOrderApproved}}</span></a>
                </li>
                <li>
                    <a href="campaign-complete.html">Factures en attente<span>{{$countOrderApproved}}</span></a>
                </li>
                <li>
                    <a href="campaign-archieve.html">Commandes non approuvée<span>{{$countOrderNotApproved}}</span></a>
                </li>
                <li>
                    <a href="campaign-archieve.html">Paiements<span>{{$countOrderNotApproved}}</span></a>
                </li>
            </ul>
        </div>
    </div>


@endsection

