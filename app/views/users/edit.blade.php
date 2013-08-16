
@section('content')

  @if ($e = $errors->all('<li>:message</li>'))
    <div class="alert alert-info">
      <button type="button" class="close" data-dismiss="alert">&times;</button>  
      Kunne ikke lagre fordi:
      <ul>
      @foreach ($e as $msg)
        {{$msg}}
      @endforeach
      </ul>
    </div>
  @endif

  {{ Form::model($user, array(
      'action' => array('UsersController@postUpdate', $user->id),
      'class' => 'panel panel-primary',
      'method' => 'post'
  )) }}

    <div class="panel-heading">
      <h3 class="panel-title">Rediger bruker #{{ $user->id }}</h3>
    </div>

    <div class="form-group">
      {{ Form::label('ltid', 'LTID: ') }} (kan stå blankt hvis personen f.eks. ikke fått studiekort enda)
      {{ Form::text('ltid', $user->ltid, array('class' => 'form-control')) }}
    </div>

    <div class="form-group">
      {{ Form::label('lastname', 'Etternavn: ') }}
      {{ Form::text('lastname', $user->lastname, array('class' => 'form-control')) }}
    </div>

    <div class="form-group">
      {{ Form::label('firstname', 'Fornavn: ') }}
      {{ Form::text('firstname', $user->firstname, array('class' => 'form-control')) }}
    </div>

    <div class="form-group">
      {{ Form::label('phone', 'Tlf.: ') }}
      {{ Form::text('phone', $user->phone, array('class' => 'form-control')) }}
    </div>

    <div class="form-group">
      {{ Form::label('email', 'Epost: ') }}
      {{ Form::text('email', $user->email, array('class' => 'form-control')) }}
    </div>

    <div class="form-group">
      {{ Form::radio('lang', 'eng', false, array('id' => 'lang-eng')) }}
      {{ Form::label('lang-eng', 'engelsk') }}
      {{ Form::radio('lang', 'nor', true, array('id' => 'lang-nor')) }}
      {{ Form::label('lang-nor', 'norsk') }}
    </div>

    <div class="panel-footer">
      <a href="{{ URL::action('UsersController@getShow', $user->id) }}" class="btn">Avbryt</a>
      {{ Form::submit('Lagre', array('class' => 'btn btn-success')) }}
    </div>

  {{ Form::close() }}

@stop


@section('scripts')

<script type='text/javascript'>     
  $(document).ready(function() {
    $('#ltid').focus();              
  });
</script>

@stop