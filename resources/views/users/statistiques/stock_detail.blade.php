@extends('layouts.app_layout')

@section('title', 'Stock fri')

@section('content')

    <div class="col-md-12">
       
        
        <div class="campaign-tab">
            <ul class="nav">
                

                <li>
                    <a href="javascript:void(0);" class="{{ session('active_tab_stock_stat') == 'commandes' ? 'active' : '' }}" id="tab-commandes" data-target="card-commandes">
                        Commandes en cours(s) <span>0</span>
                    </a>
                </li>

                
            </ul>
        </div>

        <div class="card" id="all-content">
            @if(session('active_tab_stock_stat') == 'commandes')
                @include('appbranch_without_layout.stat_commandes')
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
                        fetch('/update-tab-stat-session?tab=' + contentType, {
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
