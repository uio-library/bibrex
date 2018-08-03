<!DOCTYPE html>
<html lang="nb">
<head>

  <title>BIBREX</title>

  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <link rel="stylesheet" type="text/css" href="{{ mix('/css/app.css') }}">
  <link rel="stylesheet" type="text/css" href="{{ mix('/css/dataTables.bootstrap4.min.css') }}">

  <link rel="shortcut icon" href="/images/favicon.ico">
  <link rel="apple-touch-icon-precomposed" href="/images/apple-touch-icon.png">

</head>
<body>
  @section('sidebar')


  @if (Auth::check())

  <nav class="navbar navbar-expand-md navbar-dark bg-dark mb-4">

    <div class="container">

      <a class="navbar-brand" href="/" title="Spiser alle tingene dine.">
        <em class="far fa-heart-rate"></em>
        BIBREX
      </a>

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
            <a class="nav-link" href="{{ URL::action('ThingsController@index') }}">Ting</a>
          </li>

          <li class="nav-item {{ strpos($_SERVER['REQUEST_URI'], '/items') === 0 ? ' active':'' }}">
            <a class="nav-link" href="{{ URL::action('ItemsController@index') }}">Eksemplarer</a>
          </li>

          <li class="nav-item {{ strpos($_SERVER['REQUEST_URI'], '/logs') === 0 ? ' active':'' }}">
            <a class="nav-link" href="{{ URL::action('LogsController@getIndex') }}">Logg</a>
          </li>
        </ul>

        <ul class="navbar-nav ml-auto">

          <li class="nav-item {{ strpos($_SERVER['REQUEST_URI'], '/about') === 0 ? ' active':'' }}">
           <a class="nav-link" href="/about">
            <em class="far fa-question-circle"></em> Hjælp
          </a>
         </li>

         <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <em class="far fa-university"></em>
            {{ Auth::user()->name }}
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
    <alert variant="success" v-cloak>
      {!! Session::get('status') !!}
    </alert>
  @endif

  @if (Session::has('error'))
    <alert variant="danger" v-cloak>
      {!! Session::get('error') !!}
    </alert>
  @endif

  @if ($e = $errors->all('<li>:message</li>'))
    <alert variant="danger" v-cloak>
      Kunne ikke lagre fordi:
      <ul>
        @foreach ($e as $msg)
        {!!  $msg !!}
        @endforeach
      </ul>
    </alert>
  @endif

  @yield('content')
</div>

<script type="text/javascript" src="{{ mix('/js/app.js') }}"></script>

@yield('scripts')

</body>
</html>
