<!DOCTYPE html>
<html lang="nb">
<head>
  <title>BIBREX</title>

  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
 
  <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
  <!--[if lt IE 9]>
  <script src="//cdnjs.cloudflare.com/ajax/libs/html5shiv/3.6/html5shiv.min.js"></script>
  <![endif]-->
 
  <!-- Complete CSS (Responsive, With Icons) -->
  <link rel="stylesheet" type="text/css" href="/components/bootstrap/dist/css/bootstrap.css">
  <link rel="stylesheet" type="text/css" href="/site.css">
  <link href='//fonts.googleapis.com/css?family=Open+Sans&subset=latin,latin-ext' rel='stylesheet' type='text/css'>
  <link rel="stylesheet" type="text/css" href="/halflings.css">
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
                Logget inn som {{ Auth::user()->name }}
                <b class="caret"></b>
              </a>
              <ul class="dropdown-menu">
                <li><a href="/libraries/my">Kontoinnstillinger</a></li>
                @if (Session::get('iplogin') != true)
                  <li><a href="/libraries/logout">Logg ut</a></li>
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

    @yield('content')
  </div>

  <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script> 

  <script type="text/javascript" src="/components/bootstrap/dist/js/bootstrap.min.js"></script>

  <script type="text/javascript" src="/hogan-2.0.0.js"></script>
  <script type="text/javascript" src="/typeahead.js/typeahead.min.js"></script>
  <script type="text/javascript" src="/components/select2/select2.js"></script>
  <!--
  <script src="//cdnjs.cloudflare.com/ajax/libs/css3finalize/3.4.0/jquery.css3finalize.min.js"></script>
  <script src="//cdnjs.cloudflare.com/ajax/libs/underscore.js/1.4.4/underscore-min.js"></script>
  <script src="//cdnjs.cloudflare.com/ajax/libs/backbone.js/1.0.0/backbone-min.js"></script>
  -->
  @yield('scripts')

  <script type="text/javascript">

    $(document).ready(function() {

      if ($('.alert').length != 0) {
        $('.alert').hide().slideDown();
      }

      //parent.postMessage("Hello","*");

    });
  </script>

</body>
</html>
