@extends('layouts.app_layout')

@section('title', 'Stock fri')

@section('content')

    <div class="col-md-12">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-4">
                    <h4 class="page-title">
                        Vous gérez <span class="count-title">{{ $countBusiness }}</span> business
                    </h4>
                </div>
                <div class="col-8 text-end">
                    <div class="head-icons">
                        <a href="{{ route('dashboard') }}" data-bs-toggle="tooltip" data-bs-placement="top"
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
                    <h2>{{ $countClients }}</h2>
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
                    <h2>{{ $countTeams }}</h2>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6">
                <div class="campaign-box bg-purple-light">
                    <div class="campaign-img">
                        <span>
                            <i class="ti ti-brand-feedly"></i>
                        </span>
                        <p>Coéquipiers</p>
                    </div>
                    <h2>{{ $countTeamMembers }}</h2>
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
                    <h2>{{ $countBusiness }}</h2>
                </div>
            </div>
        </div>

        <div class="campaign-tab">
            <ul class="nav">
                <li>
                    <a href="javascript:void(0);" class="{{ session('active_tab', 'service') == 'service' ? 'active' : '' }}" id="tab-service" data-target="card-service">
                        Service (s) vendu (s) <span>{{ $countApprovedSelledServices }}</span>
                    </a>
                </li>
                <li>
                    <a href="javascript:void(0);" class="{{ session('active_tab') == 'product' ? 'active' : '' }}" id="tab-product" data-target="card-product">
                        Produit (s) vendu (s) <span>{{ $countApprovedSelledProduct }}</span>
                    </a>
                </li>
                <li>
                    <a href="javascript:void(0);" class="{{ session('active_tab') == 'delivery' ? 'active' : '' }}" id="tab-delivery" data-target="card-delivery">
                        Livraison en cours <span>{{ $countApprovedSelledProduct }}</span>
                    </a>
                </li>
                <li>
                    <a href="javascript:void(0);" class="{{ session('active_tab') == 'payment' ? 'active' : '' }}" id="tab-payment" data-target="card-payment">
                        Paiements <span>{{ $countApprovedSelledProduct }}</span>
                    </a>
                </li>
            </ul>
        </div>

        <div class="card" id="all-content">
            @if(session('active_tab', 'service') == 'service')
                @include('appbranch_without_layout.approved')
            @elseif(session('active_tab') == 'product')
                @include('appbranch_without_layout.prod_approved')
            @elseif(session('active_tab') == 'delivery')
                <div>kjjj</div>
            @elseif(session('active_tab') == 'payment')
                <div>jojoo</div>
            @endif
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const tabs = document.querySelectorAll('.nav li a');
                
                tabs.forEach(tab => {
                    tab.addEventListener('click', function(e) {
                        e.preventDefault();
                        
                        // Remove active class from all tabs
                        tabs.forEach(t => t.classList.remove('active'));
                        
                        // Add active class to clicked tab
                        this.classList.add('active');
                        
                        // Get the target content type from data attribute
                        const contentType = this.getAttribute('data-target').replace('card-', '');
                        
                        // Use fetch or axios to update session and reload the content
                        fetch('/update-tab-session?tab=' + contentType, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            }
                        }).then(response => window.location.reload());
                    });
                });
            });
        </script>
    </div>


@endsection
