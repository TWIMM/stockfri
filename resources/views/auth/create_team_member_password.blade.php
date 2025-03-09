<!DOCTYPE html>
<html lang="en">

<head>


    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Streamline your business with our advanced CRM template. Easily integrate and customize to manage sales, support, and customer interactions efficiently. Perfect for any business size">
    <meta name="keywords" content="Advanced CRM template, customer relationship management, business CRM, sales optimization, customer support software, CRM integration, customizable CRM, business tools, enterprise CRM solutions">
    <meta name="author" content="Dreams Technologies">
    <meta name="robots" content="index, follow">

    <title>Inscription - StockFri</title>

    <script src="assets/js/theme-script.js" type="d49464235a86cf7c053a2086-text/javascript"></script>

    <link rel="apple-touch-icon" sizes="180x180" href="{{asset('assets/img/apple-touch-icon.png')}}">

    <link rel="icon" href="{{asset('assets/img/favicon.png')}}" type="image/x-icon">
    <link rel="shortcut icon" href="{{asset('assets/img/favicon.png')}}" type="image/x-icon">

    <link rel="stylesheet" href="{{asset('assets/css/bootstrap.min.css')}}">

    <link rel="stylesheet" href="{{asset('assets/plugins/tabler-icons/tabler-icons.css')}}">

    <link rel="stylesheet" href="{{asset('assets/css/dataTables.bootstrap5.min.css')}}">

    <link rel="stylesheet" href="{{asset('assets/plugins/fontawesome/css/fontawesome.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/plugins/fontawesome/css/all.min.css')}}">

    <link rel="stylesheet" href="{{asset('assets/plugins/daterangepicker/daterangepicker.css')}}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notiflix@3.0.2/dist/notiflix-3.0.2.min.css" />

    <link rel="stylesheet" href="{{asset('assets/css/style.css')}}">
</head>

<body class="account-page">



    <div class="main-wrapper">
        <div class="account-content">
            <div class="d-flex flex-wrap w-100 vh-100 overflow-hidden account-bg-02">
                <div class="d-flex align-items-center justify-content-center flex-wrap vh-100 overflow-auto p-4 w-50 bg-backdrop">
                    <form method="POST" action="{{ route('register_member.submit'  , $teamM->id ) }}" class="flex-fill">
                        @csrf
                        <div class="mx-auto mw-450">
                           {{--  <div class="text-center mb-4">
                                <img src="assets/img/logo.svg" class="img-fluid" alt="Logo">
                            </div> --}}
                            <div class="mb-4">
                                <h4 class="mb-2 fs-20">Activez votre compte</h4>
                            </div>
                           
                            <div class="mb-3">
                                <label class="col-form-label">Email </label>
                                <div class="position-relative">
                                    <span class="input-icon-addon">
                                        <i class="ti ti-mail"></i>
                                    </span>
                                    <input type="email" name="email" id="email" value='{{$teamM->email}}' class="form-control" readonly>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="col-form-label">Password</label>
                                <div class="pass-group">
                                    <input type="password" name="password" id="password" class="pass-input form-control">
                                    <span class="ti toggle-password ti-eye-off"></span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="col-form-label">Confirmer Password</label>
                                <div class="pass-group">
                                    <input type="password" name="password_confirmation" id="password_confirmation" class="pass-inputs form-control">
                                    <span class="ti toggle-passwords ti-eye-off"></span>
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="form-check form-check-md d-flex align-items-center">
                                    <input class="form-check-input" type="checkbox" value id="checkebox-md" checked>
                                    <label class="form-check-label" for="checkebox-md">
                                        Je suis d'accord <a href="javascript:void(0);" class="text-primary link-hover"> avec les Termes & Conditions</a>
                                    </label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <button type="submit" class="btn btn-primary w-100">S'inscrire</button>
                            </div>
                           
                            <div class="text-center">
                                <p class="fw-medium text-gray">Copyright &copy; 2024 - CRMS</p>
                            </div>
                        </div>
                    </form>
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
    @if ($errors->any())
        <script>
           
            @foreach ($errors->all() as $error)
                Notiflix.Notify.failure("{{ $error }}");
            @endforeach
        </script>
    @endif
</html>