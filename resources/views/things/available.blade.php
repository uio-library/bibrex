<html>
  <link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Open+Sans" />
  <head>
    <style>
  body{
margin: 0; padding: 0;
    background: #f9f9f9;
background:#000;
  }
* {
  font-family: Open Sans;
  letter-spacing: .05em;
}
div {
  text-align: center;
  font-size: 1.8vw;
}
.header {
   font-weight: bold;
}
.desc {
  color: #eee;
}
h1 {
 color: #8A9B0F;
 font-size: 3vw;
 letter-spacing: .2em;
 margin:3vw 4vw 1vw 4vw;
}
  </style>
  </head>
  <body>
   <h1>Ting</h1>
    <div style="display:flex; flex-wrap: wrap; margin-top: 1vw; margin-left:3vw;margin-right:3vw" id="things">
    </div>

  <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/jquery@1.12.4/dist/jquery.min.js"></script>

<script>
function update() {
    $.getJSON('{{ URL::action('ThingsController@getAvailableJson', $library_id) }}').then(function(res) {
      $('#things').empty();
      res.forEach(function(thing) {
          if (!thing.disabled && thing.num_items) {
            $('#things').append(`
            <div style="flex: 0 0 31vw;">
    <div style=" background: #8A9B0F; border-radius: 3px; color:#fff; padding: 1em; border: 1px solid #033649; margin: 1vw;">
              <div class="header">${thing.name}</div>
              <div class="desc">${thing.available_items} av ${thing.num_items} tilgjengelig</div>
            </div>
            `);
          }
      });
      setTimeout(update, 5000);
    });
}
update();
</script>
  </body>
</html>
