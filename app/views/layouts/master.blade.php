<!DOCTYPE html>
<html lang="nb">
<head>
  <title>BIBREX</title>

  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Complete CSS (Responsive, With Icons) -->
  <link rel="stylesheet" type="text/css" href="{{ URL::to('/components/bootstrap/dist/css/bootstrap.css') }}">
  <link rel="stylesheet" type="text/css" href="{{ URL::to('/site.css') }}">
  <link href='//fonts.googleapis.com/css?family=Open+Sans&subset=latin,latin-ext' rel='stylesheet' type='text/css'>
  <link rel="stylesheet" type="text/css" href="{{ URL::to('/halflings.css') }}">
</head>
<body>

  <div class="container">

    @section('sidebar')

    @if (Auth::check())

    <nav class="navbar navbar-default" role="navigation">

      <!-- Brand and toggle get grouped for better mobile display -->
      <div class="navbar-header">
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
          <span class="sr-only">Toggle navigation</span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand" href="#">BIBREX</a>
      </div>

      <!-- Navigation links -->
      <div class="collapse navbar-collapse navbar-ex1-collapse">

        <ul class="nav navbar-nav">

          <li{{ strpos(getenv('REQUEST_URI'), '/loans') === 0 ? ' class="active"':'' }}>
            <a href="{{ URL::action('LoansController@getIndex') }}">Utlån</a>
          </li>

          <li{{ strpos(getenv('REQUEST_URI'), '/users') === 0 ? ' class="active"':'' }}>
            <a href="{{ URL::action('UsersController@getIndex') }}">Brukere</a>
          </li>

          <li{{ strpos(getenv('REQUEST_URI'), '/documents') === 0 ? ' class="active"':'' }}>
            <a href="{{ URL::action('DocumentsController@getIndex') }}">Dokumenter</a>
          </li>

          <li{{ strpos(getenv('REQUEST_URI'), '/things') === 0 ? ' class="active"':'' }}>
            <a href="{{ URL::action('ThingsController@getIndex') }}">Ting</a>
          </li>

          <li{{ strpos(getenv('REQUEST_URI'), '/logs') === 0 ? ' class="active"':'' }}>
            <a href="{{ URL::action('LogsController@getIndex') }}">Logg</a>
          </li>

        </ul>

        <ul class="nav navbar-nav navbar-right">

          <li{{ strpos(getenv('REQUEST_URI'), '/about') === 0 ? ' class="active"':'' }}>
            <a href="/about">Hjelp</a>
          </li>

            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                Innlogget som {{ Auth::user()->name }}
                <b class="caret"></b>
              </a>
              <ul class="dropdown-menu">
                <li>
                	<a href="{{ URL::action('LibrariesController@myAccount') }}">Kontoinnstillinger</a>
                </li>
                <li>
                	<a href="{{ URL::action('LibrariesController@myIps') }}">Autopålogging</a>
                </li>
                <li>
                	<a href="{{ URL::action('LibrariesController@getIndex') }}">Biblioteksoversikt</a>
                </li>
                @if (Session::get('iplogin') != true)
                  <li><a href="/logout">Logg ut</a></li>
                @endif
              </ul>
            </li>

        </ul>

    </nav>
    @endif

    @show

    @if (!empty($status))
      <div class="alert alert-info" style="display:none;">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        {{$status}}
      </div>
    @endif

    @if ($e = $errors->all('<li>:message</li>'))
      <div class="alert alert-danger" style="display:none;">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        Kunne ikke lagre fordi:
        <ul>
        @foreach ($e as $msg)
          {{$msg}}
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

  <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/jquery@1.12.4/dist/jquery.min.js"></script>
  <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/handlebars@1.3.0/dist/handlebars.min.js"></script>
  <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/typeahead.js@0.11.1/dist/typeahead.bundle.min.js"></script>

  <script type="text/javascript" src="{{ URL::to('/components/bootstrap/dist/js/bootstrap.min.js') }}"></script>

  <script type="text/javascript" src="{{ URL::to('/components/select2/select2.js') }}"></script>
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
