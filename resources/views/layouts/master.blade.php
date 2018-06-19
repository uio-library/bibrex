<!DOCTYPE html>
<html lang="nb">
<head>
  <title>BIBREX</title>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="stylesheet" href="{{ mix('/css/app.css') }}">
  <link href='//fonts.googleapis.com/css?family=Open+Sans&subset=latin,latin-ext' rel='stylesheet' type='text/css'>

  <link rel="stylesheet" href="//cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css" rel='stylesheet' type='text/css' crossorigin="anonymous">

  <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.0.13/css/all.css" integrity="sha384-oi8o31xSQq8S0RpBcb4FaLB8LJi9AT8oIdmS1QldR8Ui7KUQjNAnDlJjp55Ba8FG" crossorigin="anonymous">


</head>
<body>
    @section('sidebar')


    @if (Auth::check())

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">

      <div class="container">

        <a class="navbar-brand" href="/" title="Spiser alle tingene dine.">BIBREX</a>

        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">

            <ul class="navbar-nav mr-auto">
              <li class="nav-item {{ strpos($_SERVER['REQUEST_URI'], '/loans') === 0 ? ' active':'' }}">
                <a class="nav-link" href="{{ URL::action('LoansController@getIndex') }}">Utlån</a>
              </li>

              <li class="nav-item {{ strpos($_SERVER['REQUEST_URI'], '/users') === 0 ? ' active':'' }}">
                <a class="nav-link" href="{{ URL::action('UsersController@getIndex') }}">Brukere</a>
              </li>

              <li class="nav-item {{ strpos($_SERVER['REQUEST_URI'], '/things') === 0 ? ' active':'' }}">
                <a class="nav-link" href="{{ URL::action('ThingsController@getIndex') }}">Ting</a>
              </li>

              <li class="nav-item {{ strpos($_SERVER['REQUEST_URI'], '/items') === 0 ? ' active':'' }}">
                <a class="nav-link" href="{{ URL::action('ItemsController@getIndex') }}">Eksemplarer</a>
              </li>

              <li class="nav-item {{ strpos($_SERVER['REQUEST_URI'], '/logs') === 0 ? ' active':'' }}">
                <a class="nav-link" href="{{ URL::action('LogsController@getIndex') }}">Logg</a>
              </li>
            </ul>

            <ul class="navbar-nav ml-auto">

              <li class="nav-item {{ strpos($_SERVER['REQUEST_URI'], '/about') === 0 ? ' active':'' }}">
               <a class="nav-link" href="/about">Hjelp</a>
             </li>

             <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Innlogget som {{ Auth::user()->name }}
                </a>
                <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                    <a class="dropdown-item" href="{{ URL::action('LibrariesController@getMyAccount') }}">Kontoinnstillinger</a>
                    <a class="dropdown-item" href="{{ URL::action('LibrariesController@getMyIps') }}">Autopålogging</a>
                    <a class="dropdown-item" href="{{ URL::action('LibrariesController@getIndex') }}">Biblioteksoversikt</a>
                    @if (Session::get('iplogin') != true)
                        <a class="dropdown-item" href="/logout">Logg ut</a>
                    @endif
                </div>
            </li>
          </ul>
        </div>
    </div>
    </nav>
    @endif

  <div class="container" id="app">
    @show

    @if (Session::has('status'))
      <div class="alert alert-info" style="display:none;">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        {!! Session::get('status') !!}
      </div>
    @endif

    @if (Session::has('error'))
        <div class="alert alert-danger" style="display:none;">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {!! Session::get('error') !!}
        </div>
    @endif

    @if ($e = $errors->all('<li>:message</li>'))
      <div class="alert alert-danger" style="display:none;">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        Kunne ikke lagre fordi:
        <ul>
        @foreach ($e as $msg)
          {!!  $msg !!}
        @endforeach
        </ul>
      </div>

    @endif

    {{--
    <div class="well" style="border: 1px solid #aaa; border-radius:3px; background: white; padding: 10px;margin-bottom: 20px; background: #ffffef;">
      17.1.2014: BIBSYS har stengt beta-versjonen av
      <a href="http://www.ncip.info/">NCIP</a>-tjenesten sin, fordi den var usikret,
      men uten at noen ny versjon er på plass.
        Inntil videre får vi dermed ikke hentet ut låntakerinformasjon, og vik kan ikke bruke Bibrex til å
        låne ut dokumenter på ikke-importerte studentkort.
        <hr>
      25.1.2014: Utlån av dingser fungerer nå igjen, men låntakerinformasjon må skrives inn manuelt første gang for hver bruker.
    </div>
    --}}

    @yield('content')

  </div>

<!--  <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/jquery@1.12.4/dist/jquery.min.js"></script>-->

  <script type="text/javascript" src="{{ mix('js/app.js') }}"></script>
<!--
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/handlebars@1.3.0/dist/handlebars.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/typeahead.js@0.11.1/dist/typeahead.bundle.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.10.16/js/dataTables.bootstrap4.min.js"></script>
-->
  <!--
  <script src="//cdnjs.cloudflare.com/ajax/libs/css3finalize/3.4.0/jquery.css3finalize.min.js"></script>
  <script src="//cdnjs.cloudflare.com/ajax/libs/underscore.js/1.4.4/underscore-min.js"></script>
  <script src="//cdnjs.cloudflare.com/ajax/libs/backbone.js/1.0.0/backbone-min.js"></script>
  -->
  @yield('scripts')

  <script type="text/javascript">

    $(document).ready(function() {

      if ($('.container > .alert').length != 0) {
        $('.container > .alert').hide().slideDown();
      }

      //parent.postMessage("Hello","*");

      var idleStart;
      function resetIdleTime() {
        idleStart = new Date();
      }
      function tick() {
        var now = new Date();
        // 5 minutter
        if (now - idleStart > 1000 * 60 * 5) {
          location.reload();
        }

        setTimeout(tick, 1000);
      }
      resetIdleTime();
      document.body.addEventListener('mousemove', resetIdleTime, true);
      document.body.addEventListener('keydown', resetIdleTime, true);
      setTimeout(tick, 1000);
    });
  </script>

</body>
</html>
