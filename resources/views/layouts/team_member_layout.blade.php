<!DOCTYPE html>
<html lang="en">


<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="Streamline your business with our advanced CRM template. Easily integrate and customize to manage sales, support, and customer interactions efficiently. Perfect for any business size">
    <meta name="keywords"
        content="Advanced CRM template, customer relationship management, business CRM, sales optimization, customer support software, CRM integration, customizable CRM, business tools, enterprise CRM solutions">
    <meta name="author" content="Dreams Technologies">
    <meta name="robots" content="index, follow">

    <title>Dashboard - StockFri</title>

    <script src="assets/js/theme-script.js" type="d49464235a86cf7c053a2086-text/javascript"></script>

    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/img/apple-touch-icon.png') }}">

    <link rel="icon" href="{{ asset('assets/img/favicon.png') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('assets/img/favicon.png') }}" type="image/x-icon">

    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/plugins/tabler-icons/tabler-icons.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/css/dataTables.bootstrap5.min.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/plugins/fontawesome/css/fontawesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/fontawesome/css/all.min.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/plugins/daterangepicker/daterangepicker.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notiflix@3.0.2/dist/notiflix-3.0.2.min.css" />

</head>

<body>



    <body>

        <div class="main-wrapper">
            <div class="preloader">
                <span class="loader"></span>
            </div>

            <div class="header">

                <div class="header-left active">
                    <a href="/dashboard_team_member" class="logo logo-normal">
                        <img src="/assets/img/logo.svg" alt="Logo">
                        <img src="/assets/img/white-logo.svg" class="white-logo" alt="Logo">
                    </a>
                    <a href="/dashboard_team_member" class="logo-small">
                        <img src="/assets/img/logo-small.svg" alt="Logo">
                    </a>
                    <a id="toggle_btn" href="javascript:void(0);">
                        <i class="ti ti-arrow-bar-to-left"></i>
                    </a>
                </div>

                <a id="mobile_btn" class="mobile_btn" href="#sidebar">
                    <span class="bar-icon">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                </a>
                <div class="header-user">
                    <ul class="nav user-menu">

                        <li class="nav-item nav-search-inputs me-auto">
                            <div class="top-nav-search">
                                @if (request()->routeIs('dashboard.*'))
                                    <a href="javascript:void(0);" class="responsive-search">
                                        <i class="fa fa-search"></i>
                                    </a>
                                    <form action="#" class="dropdown">
                                        <div class="searchinputs" id="dropdownMenuClickable">
                                            <input type="text" placeholder="Search">
                                            <div class="search-addon">
                                                <button type="submit"><i class="ti ti-command"></i></button>
                                            </div>
                                        </div>
                                    </form>
                                @endif
                            </div>
                        </li>



                        <li class="nav-item dropdown nav-item-box">
                            <a href="javascript:void(0);" class="nav-link" data-bs-toggle="dropdown">
                                <i class="ti ti-bell"></i>
                                <span class="badge rounded-pill">0</span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end notification-dropdown">
                                <div class="topnav-dropdown-header">
                                    <h4 class="notification-title">Notifications</h4>
                                </div>
                                <div class="noti-content">
                                    <ul class="notification-list">
                                        {{--  <li class="notification-message">
                                            <a href="activities.html">
                                                <div class="media d-flex">
                                                    <span class="avatar flex-shrink-0">
                                                        <img src="{{ $user->profile_image ? asset('/storage/profiles/' . $user->profile_image) : asset('/assets/img/profiles/' . 'default.jfif') }}" alt="Profile">
                                                        <span class="badge badge-info rounded-pill"></span>
                                                    </span>
                                                    <div class="media-body flex-grow-1">
                                                        <p class="noti-details">Ray Arnold left 6 comments on Isla Nublar SOC2 compliance report</p>
                                                        <p class="noti-time">Last Wednesday at 9:42 am</p>
                                                    </div>
                                                </div>
                                            </a>
                                        </li> --}}


                                    </ul>
                                </div>
                                <div class="topnav-dropdown-footer">
                                    <a href="activities.html" class="view-link">View all</a>
                                    <a href="javascript:void(0);" class="clear-link">Clear all</a>
                                </div>
                            </div>
                        </li>
                        
                        <li class="nav-item dropdown nav-item-box">
                            <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Logout">
                                <i class="ti ti-logout"></i>
                            </a>
                            
                            <form id="logout-form" action="{{ route('logout') }}" method="GET" style="display: none;">
                                @csrf
                            </form>
                            
                        </li>
                        
                        <li class="nav-item dropdown nav-item-box">
                            <a href="/dashboard_team_member" onclick="event.preventDefault(); window.location.href = '/dashboard_team_member'  " data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Dashboard">
                                <i class="ti ti-home"></i>
                            </a>
                            
                            <form id="dash-form" action="/dashboard_team_member" method="GET" style="display: none;">
                                @csrf
                            </form>
                            
                        </li>


                        <li class="nav-item dropdown has-arrow main-drop" >
                            <a href="javascript:void(0);" onclick="event.preventDefault(); window.location.href = '/profile_page' " class="nav-link userset" data-bs-toggle="dropdown">
                                <span class="user-info">
                                    <span class="user-letter">
                                        <img src="{{ $user->profile_image ? asset('/storage/profiles/' . $user->profile_image) : asset('/assets/img/profiles/' . 'default.jfif') }}" alt="Profile">

                                    </span>
                                    <span class="badge badge-success rounded-pill"></span>
                                </span>
                            </a>
                            
                        </li>

                    </ul>
                </div>

                <div class="dropdown mobile-user-menu">
                    <a href="javascript:void(0);" class="nav-link dropdown-toggle" data-bs-toggle="dropdown"
                        aria-expanded="false"><i class="fa fa-ellipsis-v"></i></a>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="/dashboard_team_member">
                            <i class="ti ti-layout-2"></i> Dashboard
                        </a>
                        <a class="dropdown-item" href="/profile_page">
                            <i class="ti ti-user-pin"></i> My Profile
                        </a>
                        <a class="dropdown-item" href="login.html">
                            <i class="ti ti-lock"></i> Logout
                        </a>
                    </div>
                </div>

            </div>


            <div class="sidebar" id="sidebar">
                <div class="sidebar-inner slimscroll">
                    <div id="sidebar-menu" class="sidebar-menu">
                        <ul>
                            <li class="clinicdropdown">
                                <a href="/profile_page">
                                    <img src="{{ $user->profile_image ? asset('/storage/profiles/' . $user->profile_image) : asset('/assets/img/profiles/' . 'default.jfif')}}" class="img-fluid" alt="Profile">
                                    <div class="user-names">
                                        <h5>{{ $user->name }}</h5>
                                        <h6>Coequipier</h6>

                                    </div>
                                </a>
                            </li>
                        </ul>
                        <ul>
                            <li>
                                <h6 class="submenu-hdr">Main Menu</h6>
                                <ul>

                                    {{-- @if ($isUserAdminQuestionMark) --}}
                                        <li class="submenu">
                                            <a href="javascript:void(0);"
                                                class="{{ request()->routeIs('owner.business.*') ? 'subdrop active' : '' }}"
                                                class="subdrop active">
                                                <i class="ti ti-layout-2"></i><span>Business</span><span
                                                    class="menu-arrow"></span>
                                            </a>
                                            <ul>
                                                <li><a class="{{ request()->routeIs('owner.business.listes') ? 'active' : '' }}"
                                                        href="{{ route('owner.business.listes') }}">Gerer business</a>
                                                </li>
                                            </ul>
                                        </li>


                                        <li class="submenu">
                                            <a href="javascript:void(0);"
                                                class="{{ request()->routeIs('owner.teams.*') || request()->routeIs('owner.team_member.*') ? 'subdrop active' : '' }}"><i
                                                    class="ti ti-brand-airtable"></i><span>Equipes</span><span
                                                    class="menu-arrow"></span></a>
                                            <ul>
                                                <li><a href="{{ route('owner.teams.listes') }}"
                                                        class="{{ request()->routeIs('owner.teams.listes') ? 'active' : '' }}">Gerer
                                                        les equipes</a></li>
                                                <li><a href="{{ route('owner.team_member.listes') }}"
                                                        class="{{ request()->routeIs('owner.team_member.listes') ? 'active' : '' }}">Membres
                                                        d'equipes</a></li>

                                            </ul>
                                        </li>
                                    {{-- @endif --}}


                                    @if ($hasPhysique || $hasPrestation)
                                        <li class="submenu">
                                            <a class="{{ request()->routeIs('finances.*') ? 'subdrop active' : '' }}"
                                                href="javascript:void(0);"><i class="ti ti-brand-airtable"></i>
                                                <span>Finances</span>
                                                <span class="menu-arrow"></span>
                                            </a>
                                            <ul>
                                                <li><a href="{{ route('finances.dettes') }}"
                                                        class="{{ request()->routeIs('finances.dettes') ? 'active' : '' }}">Gestion
                                                        des dettes</a></li>
                                                <li><a href="{{ route('finances.paiement') }}"
                                                        class="{{ request()->routeIs('finances.paiement') ? 'active' : '' }}">Gestion
                                                        des paiements</a></li>

                                            </ul>
                                        </li>
                                    @endif




                                    @if ($hasPhysique)
                                        <li class="submenu">
                                            <a href="javascript:void(0);"
                                                class="{{ request()->routeIs('stock.*') || request()->routeIs('cat_prod.*') ? 'subdrop active' : '' }}"><i
                                                    class="ti ti-brand-airtable"></i>
                                                <span>Stock</span>
                                                <span class="menu-arrow"></span>
                                            </a>
                                            <ul>
                                                <li><a href="{{ route('cat_prod.listes') }}"
                                                        class="{{ request()->routeIs('cat_prod.listes') ? 'active' : '' }}">Categorie
                                                        de produits</a></li>

                                                <li><a href="{{ route('stock.listes') }}"
                                                        class="{{ request()->routeIs('stock.listes') ? 'active' : '' }}">Gerer
                                                        stock</a></li>

                                                <li><a href="{{ route('stock.moves') }}"
                                                        class="{{ request()->routeIs('stock.moves') ? 'active' : '' }}">Mouvements
                                                        stock</a></li>
                                            </ul>
                                        </li>

                                        <li class="submenu">
                                            <a href="javascript:void(0);"
                                                class="{{ request()->routeIs('team_member.fournisseurs.*') ? 'subdrop active' : '' }}"><i
                                                    class="ti ti-brand-airtable"></i>
                                                <span>Fournisseurs</span>
                                                <span class="menu-arrow"></span>
                                            </a>
                                            <ul>
                                                <li><a href="{{ route('team_member.fournisseurs.listes') }}"
                                                        class="{{ request()->routeIs('team_member.fournisseurs.listes') ? 'active' : '' }}">Gerer
                                                        Fournisseurs</a></li>

                                            </ul>
                                        </li>

                                        <li class="submenu">
                                            <a class="{{ request()->routeIs('magasins.*') || request()->routeIs('clients.*') || request()->routeIs('livraisons.*') || request()->routeIs('pre_commandes.*') || request()->routeIs('commandes.*') ? 'subdrop active' : '' }}"
                                                href="javascript:void(0);"><i class="ti ti-brand-airtable"></i>
                                                <span>Magasins</span>
                                                <span class="menu-arrow"></span>
                                            </a>
                                            <ul>
                                                <li><a href="{{ route('magasins.listes') }}"
                                                        class="{{ request()->routeIs('magasins.listes') ? 'active' : '' }}">Gestion
                                                        des magasins</a></li>
                                                <li><a href="{{ route('clients.listes') }}"
                                                        class="{{ request()->routeIs('clients.listes') ? 'active' : '' }}">Gestion
                                                        des clients</a></li>
                                                <li><a href="{{ route('pre_commandes.listes') }}"
                                                        class="{{ request()->routeIs('pre_commandes.listes') ? 'active' : '' }}">Approuver
                                                        des commandes</a></li>
                                                <li><a href="{{ route('commandes.listes') }}"
                                                        class="{{ request()->routeIs('commandes.listes') ? 'active' : '' }}">
                                                        Commandes actives</a></li>
                                                <li><a href="{{ route('livraisons.listes') }}"
                                                        class="{{ request()->routeIs('livraisons.listes') ? 'active' : '' }}">Gestion
                                                        des Livraisons</a></li>


                                            </ul>
                                        </li>
                                    @endif

                                    @if ($hasPrestation)
                                        <li class="submenu">
                                            <a href="javascript:void(0);"
                                                class="{{ request()->routeIs('services.*') || request()->routeIs('pre_commandes_s.*') || request()->routeIs('commandes_s.*') ? 'subdrop active' : '' }}"><i
                                                    class="ti ti-brand-airtable"></i>
                                                <span>Services Offert</span>
                                                <span class="menu-arrow"></span>
                                            </a>
                                            <ul>
                                                <li><a class="{{ request()->routeIs('services.listes') ? 'active' : '' }}"
                                                        href="{{ route('services.listes') }}">Gerer services</a></li>
                                                <li><a href="{{ route('clients.listes') }}"
                                                        class="{{ request()->routeIs('clients.listes') ? 'active' : '' }}">Gestion
                                                        des clients</a></li>
                                                <li><a href="{{ route('pre_commandes_s.services') }}"
                                                        class="{{ request()->routeIs('pre_commandes_s.services') ? 'active' : '' }}">Approuver
                                                        des commandes</a></li>
                                                <li><a href="{{ route('commandes_s.services') }}"
                                                        class="{{ request()->routeIs('commandes_s.services') ? 'active' : '' }}">
                                                        Commandes actives</a></li>
                                            </ul>
                                        </li>
                                    @endif

        
                                </ul>
                            </li>

                            {{--  <li>
                                <h6 class="submenu-hdr">Rapports</h6>
                                <ul>
                                    <li class="submenu">
                                        <a href="javascript:void(0);">
                                            <i class="ti ti-file-invoice"></i><span>Rapports</span><span
                                                class="menu-arrow"></span>
                                        </a>
                                        <ul>
                                            <li><a href="lead-reports.html">Rapports de ventes</a></li>
                                            <li><a href="deal-reports.html">Rapports de clients</a></li>

                                        </ul>
                                    </li>
                                </ul>
                            </li>

                            <li>
                                <h6 class="submenu-hdr">Settings</h6>
                                <ul>
                                    <li class="submenu">
                                        <a href="javascript:void(0);">
                                            <i class="ti ti-settings-cog"></i><span> Settings</span><span
                                                class="menu-arrow"></span>
                                        </a>
                                        <ul>
                                            <li><a href="/profile_page">Profile</a></li>
                                            <li><a href="security.html">securite</a></li>
                                            <li><a href="invoice-settings.html">Invoice Settings</a></li>

                                        </ul>
                                    </li>




                                </ul>
                            </li> --}}


                        </ul>
                    </div>
                </div>
            </div>

            <div class="page-wrapper">
                <div class="content">
                    <div class="row">
                        @yield('content')
                    </div>
                </div>
            </div>


        </div>



        <script src="{{ asset('assets/js/jquery-3.7.1.min.js') }}" type="text/javascript"></script>
        <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}" type="text/javascript"></script>
        <script src="{{ asset('assets/js/feather.min.js') }}" type="text/javascript"></script>
        <script src="{{ asset('assets/js/jquery.slimscroll.js') }}" type="text/javascript"></script>
        <script src="{{ asset('assets/js/jquery.slimscroll.min.js') }}" type="text/javascript"></script>
        <script src="{{ asset('assets/js/moment.min.js') }}" type="text/javascript"></script>
        <script src="{{ asset('assets/plugins/daterangepicker/daterangepicker.js') }}" type="text/javascript"></script>
        <script src="{{ asset('assets/plugins/apexchart/apexcharts.min.js') }}" type="text/javascript"></script>
        <script src="{{ asset('assets/plugins/apexchart/chart-data.js') }}" type="text/javascript"></script>
        <script src="{{ asset('assets/js/jquery.dataTables.min.js') }}" type="text/javascript"></script>
        <script src="{{ asset('assets/js/dataTables.bootstrap5.min.js') }}" type="text/javascript"></script>
        <script src="{{ asset('assets/js/jsonscript.js') }}" type="text/javascript"></script>
        <script src="{{ asset('assets/js/script.js') }}" type="text/javascript"></script>
        <script src="https://cdn.jsdelivr.net/npm/notiflix@3.0.2/dist/notiflix-3.0.2.min.js"></script>

        <!-- Cloudflare Rocket Loader (if needed) -->
        <script src="../../cdn-cgi/scripts/7d0fa10a/cloudflare-static/rocket-loader.min.js"
            data-cf-settings="d49464235a86cf7c053a2086-|49" defer></script>

        @if ($errors->any())
            <script>
                // Display error messages using Notiflix
                @foreach ($errors->all() as $error)
                    Notiflix.Notify.failure("{{ $error }}", {
                        timeout: 10000, // Timeout in milliseconds
                        zindex: 10000, // Ensure the notification appears above other elements
                    });
                @endforeach
            </script>
        @endif

        @if (session('error'))
            <script>
                Notiflix.Notify.failure("{{ session('error') }}", {
                    timeout: 100000, // Timeout in milliseconds (optional)
                    zindex: 10000, // Adjust the z-index if needed
                });
            </script>
        @endif

        @if (session('success'))
            <script>
                Notiflix.Notify.success("{{ session('success') }}", {
                    timeout: 100000, // Timeout in milliseconds (optional)
                    zindex: 10000, // Adjust the z-index if needed
                });
            </script>
        @endif
    </body>

</html>
