
@section('content')

  <div class="panel panel-success">

    <div class="panel-heading">
      <h3 class="panel-title">Nytt utlån</h3>
    </div>


    @if ($e = $errors->all('<li>:message</li>'))
      <div class="alert alert-danger">
        <button type="button" class="close" data-dismiss="alert">&times;</button>  
        Kunne ikke lagre fordi:
        <ul>
        @foreach ($e as $msg)
          {{$msg}}
        @endforeach
        </ul>
      </div>
    @endif

    {{ Form::model(new Loan(), array(
        'action' => 'LoansController@postStore',
        'class' => 'form-inline'
        )) }}

      LTID:
      {{ Form::text('ltid', null, array(
          'placeholder' => 'LTID', 
          'class' => 'form-control',
          'style' => 'width:120px'
      )) }}
      <!--
      eller navn
      {{ Form::text('navn', null, array(
          'placeholder' => 'Navn', 
          'class' => 'form-control',
          'style' => 'width:120px'
      )) }}
      -->

      <br />

      Hva? 
      {{ Form::select('thing', $things, null, array(
          'class' => 'form-control',
          'style' => 'width:180px'
      )) }}

      <span id="bibsysdok_extras">
        med DOKID:
        {{ Form::text('dokid', null, array(
            'placeholder' => 'DOKID',
            'class' => 'form-control',
            'style' => 'width:180px'
        )) }}
      </span>

      <span id="other_extras" style="display:none;">
        Antall:
        {{ Form::text('count', '1', array(
            'placeholder' => 'Antall',
            'class' => 'form-control',
            'style' => 'width:80px'
        )) }}
      </span>

      {{ Form::submit('Lån ut!', array(
          'class' => 'btn btn-success'
      )) }}

      <img src="/img/spinner2.gif" class="spinner" />

    {{ Form::close() }}

  </div>

  <div class="panel panel-success">

    <div class="panel-heading">
      <h3 class="panel-title">Utlån</h3>
    </div>


    <ul class="list-group">
    @foreach ($loans as $loan)

      <li class="list-group-item{{ in_array($loan->id, $loan_ids) ? ' added' : '' }}">
        <h4 class="list-group-item-heading">
          <a href="{{ URL::action('DocumentsController@getShow', $loan->document->id) }}">
            {{ $loan->representation() }}

          </a>
          utlånt til
          <a href="{{ URL::action('UsersController@getShow', $loan->user->id) }}">
            {{ $loan->user->lastname }},
            {{ $loan->user->firstname }}

          </a>
        </h4>
        <p class="list-group-item-text">
          Utlånt {{ $loan->created_at }}.
          <a href="{{ URL::action('LoansController@getDestroy', $loan->id) }}?returnTo=loans.index">
            Returnér
          </a>
        </p>
      </li>
    @endforeach

    </ul>

  </div>

@stop


@section('scripts')

<script type='text/javascript'>     
  $(document).ready(function() {
    var $ltid = $('input[name="ltid"]'),
      $dokid = $('input[name="dokid"]');

    $ltid.focus();
    $('.spinner').hide();
        
    var ltidLength = 0;
    function ltidChanged(e) {
      if ($ltid.val().length !== ltidLength) {
        ltidLength = $ltid.val().length;
        if (ltidLength == 10) {
          if ($('select[name="thing"]').val() === '1') {
            $dokid.focus();
          }
        }
      }
    };

    $ltid.on('keyup', ltidChanged);
    $ltid.on('paste', ltidChanged);   // IE, FF3  (http://stackoverflow.com/a/574971)
    $ltid.on('input', ltidChanged);   // FF, Opera, Chrome, Safari

    $('form').on('submit', function(e) {
      $('.spinner').show();
      $('input[type="button"]').prop('disabled', true);
      return true;
    });

    $('.list-group .added').addClass('focus');
    setTimeout(function() {
      $('.list-group .added').removeClass('added').removeClass('focus');
    }, 1000)

    $('select[name="thing"]').on('change', function(e) {
      if ($(e.target).val() === '1') {
        $('#bibsysdok_extras').show();
        $('#other_extras').show();
      } else {
        $('#bibsysdok_extras').hide();
        $('#other_extras').show();       
      }
    });

    // $('input[name="ltid"]').typeahead([
    //   {
    //   name: 'planets',
    //   local: [ "Mercury", "Venus", "Earth", "Mars", "Jupiter", "Saturn", "Uranus", "Neptune" ]
    //   }
    //   ]);

    $('')

  });
</script>

@stop