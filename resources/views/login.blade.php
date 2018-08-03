<!DOCTYPE html>
<html lang="nb">
<head>
  <title>BIBREX</title>

  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Complete CSS (Responsive, With Icons) -->
  <link rel="stylesheet" type="text/css" href="{{ mix('/css/app.css') }}">
  <!--
  <link href='//fonts.googleapis.com/css?family=Open+Sans&subset=latin,latin-ext' rel='stylesheet' type='text/css'>
  -->

  <link rel="shortcut icon" href="/images/favicon.ico">
  <link rel="apple-touch-icon-precomposed" href="/images/apple-touch-icon.png">

  <style type="text/css">
  html,body {
    height: 100%;
  }
  body {
    /* Thanks to http://www.colorzilla.com/gradient-editor/ :) */
background: rgb(254,252,234); /* Old browsers */
background: -moz-radial-gradient(center, ellipse cover, rgba(254,252,234,1) 0%, rgba(241,218,54,1) 100%); /* FF3.6+ */
background: -webkit-gradient(radial, center center, 0px, center center, 100%, color-stop(0%,rgba(254,252,234,1)), color-stop(100%,rgba(241,218,54,1))); /* Chrome,Safari4+ */
background: -webkit-radial-gradient(center, ellipse cover, rgba(254,252,234,1) 0%,rgba(241,218,54,1) 100%); /* Chrome10+,Safari5.1+ */
background: -o-radial-gradient(center, ellipse cover, rgba(254,252,234,1) 0%,rgba(241,218,54,1) 100%); /* Opera 12+ */
background: -ms-radial-gradient(center, ellipse cover, rgba(254,252,234,1) 0%,rgba(241,218,54,1) 100%); /* IE10+ */
background: radial-gradient(ellipse at center, rgba(254,252,234,1) 0%,rgba(241,218,54,1) 100%); /* W3C */
filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#fefcea', endColorstr='#f1da36',GradientType=1 ); /* IE6-9 fallback on horizontal gradient */

  }
.card {
  display: inline-block;
  vertical-align: middle;
  width: 300px;
}

.block {
  text-align: center;
  height: 100%;
}

/* The ghost, nudged to maintain perfect centering */
.block:before {
  content: '';
  display: inline-block;
  height: 100%;
  vertical-align: middle;
  margin-right: -0.25em; /* Adjusts for spacing */
}

input {
    margin: 1em 0;
}

  </style>
</head>
<body>

    <div class="block">

        <div class="card card-body bg-light">
            <i class="halflings-icon lock"></i>
            <p>
              Logg inn for å bruke BIBREX.
            </p>
            @if (Session::has('loginfailed'))
            <p style="color:red;">
                Beklager, det var nok ikke helt riktig. Prøv igjen, dette klarer du!
            </p>
            @endif
            <form method="POST" action="{{ action('LibrariesController@postLogin') }}">
                {{ csrf_field() }}
              <div class="form-group">
                <label class="sr-only" for="email">Brukernavn (e-post)</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="Brukernavn (e-post)" value="{{ old('email') }}">
              </div>
              <div class="form-group">
                <label class="sr-only" for="password">Passord</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Passord">
              </div>
              <button type="submit" class="btn btn-success btn-lg">Logg inn</button
            </form>
        </div>

    </div>

</body>
</html>
