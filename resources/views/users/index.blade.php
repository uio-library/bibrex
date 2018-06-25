@extends('layouts.master')

@section('content')

  <div class="card">

    <h5 class="card-header">
      Brukere (<span id="usercount">{{ count($users) }}</span>)
    </h5>

    <div class="card-body">

      <form class="form" role="form">
        <div class="row px-3 align-items-center">
            <input type="text" id="search" class="form-control col-sm-3" placeholder="Søk" autocomplete="off">
            <div class="col col">
            </div>
            <div class="col col-auto" v-b-tooltip.hover title="Merk to brukere som du ønsker å slå sammen.">
                <button id="flett" class="btn btn-success" style="width:100%;" disabled="disabled">
                    <i class="far fa-compress-alt"></i>
                    Flett valgte
                </button>
            </div>
        </div>
      </form>

    </div>

    <!-- List group -->
    <ul class="list-group list-group-flush">

    </ul>

  </div>

@stop

@section('scripts')

<script type='text/javascript'>
  $(document).ready(function() {

    var users = {!! json_encode($users) !!},
        checked = [];


    function filter_users(users) {

      var search = $('#search').val().toLowerCase();

      return $.grep(users, function(user) {
        var n1 = (user.name).toLowerCase();
        if (search !== '' && n1.indexOf(search) === -1 ) return false;
        return true;
      });
    }

    function show_users(users) {
      console.log('Antall: ' + users.length)
      $('.list-group').html('');
      $.each(users, function(i, user) {
        $('.list-group').append('<li class="list-group-item" data-id="' + user.id + '"> \
            <input type="checkbox" id="user' + user.id + '"> \
            <a href="/users/' + user.id + '">' + user.name + '</a> \
            ' + (user.barcode ? '(' + user.barcode + ')' : '') + ' \
          </li>');
      });
      $('.list-group input').on('change', check_checked);
      $('#usercount').text(users.length);
    }

    function check_checked() {
      checked = [];
      $('.list-group-item').each(function(idx, item) {
        var chk = $(item).find('input[type="checkbox"]');
        if (chk.is(':checked')) {
          checked.push($(item).data('id'));
        }
        $('#flett').prop('disabled', checked.length != 2);
      });

    }

    function update_users() {
      show_users(filter_users(users));
    }

    $('#flett').on('click', function(e) {
      e.preventDefault();
      window.location.href = '/users/merge/' + checked[0] + '/' + checked[1];
    });
    $('form input').on('keyup', update_users);
    $('form input').on('change', update_users);
    update_users();

  });
</script>

@stop
