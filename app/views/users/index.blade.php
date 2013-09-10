@extends('layouts.master')

@section('content')

  <div class="panel panel-primary">

    <div class="panel-heading">
      <h3 class="panel-title">Brukere ({{ count($users) }})</h3>
    </div>

    <div class="panel-body">

      <form class="form-inline" role="form">

        <button id="flett" class="btn btn-success" disabled="disabled" style="float:right;">Flett</button>

        <div class="form-group">
          <input type="text" id="search" class="form-control" placeholder="Søk">
        </div>
        <div class="checkbox">
          <label>
            <input type="checkbox" id="onlyUsersWithLoans"> Vis bare folk med aktive lån
          </label>
        </div>
      </form>

    </div>

    <!-- List group -->
    <ul class="list-group">

    </ul>

  </div>

@stop

@section('scripts')

<script type='text/javascript'>
  $(document).ready(function() {

    var users = {{ json_encode($users) }},
        checked = [];


    function filter_users(users) {

      var onlyUsersWithLoans = $('#onlyUsersWithLoans').is(':checked'),
          search = $('#search').val().toLowerCase();

      return $.grep(users, function(user) {
        var n1 = (user.firstname + ' ' + user.lastname).toLowerCase(),
          n2 = (user.lastname + ', ' + user.firstname).toLowerCase();
        if (onlyUsersWithLoans && user.loancount === 0) return false;
        if (search !== '' && n1.indexOf(search) === -1 && n2.indexOf(search) === -1) return false;
        return true;
      });
    }

    function show_users(users) {
      console.log('Antall: ' + users.length)
      $('.list-group').html('');
      $.each(users, function(i, user) {
        $('.list-group').append('<li class="list-group-item" data-id="' + user.id + '" data-loanscount="' + user.loancount + '"> \
            <input type="checkbox" id="user' + user.id + '"> \
            <span class="badge">' + user.loancount + '</span> \
            <a href="/users/show/' + user.id + '">' + user.lastname + ', ' + user.firstname + '</a> \
          </li>');
      });
      $('.list-group input').on('change', check_checked);
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