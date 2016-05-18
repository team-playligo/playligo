@extends('layouts.app')

@section('content')
{{ Form::open(['url'=> url(''), 'method'=>'post']) }}
<div class="container">
  <div class="page-breadcrumbs">
    <h1 class="section-title">{{ $poll->pol_title }}</h1>
  </div>
  <div class="section">
    <h4>Vote for your favorite destination or playlist by  clicking on the “love” button…</h4>
    <div class="row">
      <div class="col-md-8">
        <ul class="video-post-list">
        @foreach($poll_playlists as $playlist)
          <?php $snippet = unserialize($playlist->vc_snippet); ?>
          <li class="poll_playlist_group">
            <div class="post video-post small-post">
              <div class="entry-header">
                <img src="https://i.ytimg.com/vi/Ni_xpBhfriI/mqdefault.jpg" width="100%">
                <!-- <div class="vote_count">{{ number_format($playlist->polp_vote) }}</div>
                <a href="{{ url('/pollplaylist/'.$playlist->polp_id.'/vote') }}" class="vote_playlist">{{ Form::button('<i class="fa fa-heart fa-1"></i>', ['class'=>'btn btn-default']) }}</a> -->
              </div>
              <!-- <div class="col-md-2"><a href="{{ url('search/preview/' . $playlist->vc_id) }}" class="btn-modal"><img src="{{ $snippet->thumbnails->medium->url }}" class="img-rounded" width="100%"></a></div> -->
              <div class="post-content">
                <a href="{{ url('load_playlist/' . $playlist->pl_id) }}" class="load_playlist">{{ $playlist->pl_title  }}</a>
                <div class="progress">
                  <div class="progress-bar progress-bar-success progress-bar-striped" role="progressbar" aria-valuenow="40"
                  aria-valuemin="0" aria-valuemax="100" style="width:{{ $poll->pol_votes > 0 ? round(($playlist->polp_vote / $poll->pol_votes) * 100) : 0 }}%">
                    {{ $poll->pol_votes > 0 ? round(($playlist->polp_vote / $poll->pol_votes) * 100) : 0 }}%
                  </div>
                </div>
                <a href="{{ url('/pollplaylist/'.$playlist->polp_id.'/vote') }}" class="vote_playlist">{{ Form::button('<i class="fa fa-heart fa-1"></i>', ['class'=>'btn btn-default']) }}</a>
                <span class="vote_count">{{ number_format($playlist->polp_vote) }}</span>

              </div>
            </div>
          </li>
        @endforeach
        </ul>
        <div class="fb-comments hidden-sm hidden-xs" data-href="{{ request()->url() }}" data-numposts="5" data-width="100%"></div>

      </div>
      <div class="col-md-4">
        <ul class="list-group">
        @foreach ($voters as $voter)
        <li class="list-group-item">
          <div class="row">
            <div class="col-md-4 col-sm-4 col-xs-4">
              <div class="image-cropper">
                <img class="rounded" src="{{ url($voter->avatar) }}" >
              </div>
            </div>
            <div class="col-md-8 col-sm-8 col-xs-8">{{ $voter->name }}
              <div class="voter_description">Voted {{ $pl_titles[$voter->pov_poll_playlist] or '' }}</div>
              <!-- <div class="date_time"><i class="fa fa-clock-o"></i> {{ $voter->created_at }}</div> -->
            </div>
          </div>
        </li>
        @endforeach
        </ul>
        <div id="playlist_videos">
        </div>
      </div>
    </div>
  </div>
</div>
{{ Form::close() }}
@endsection

@section('script')
<script>

$(document).ready(function() {
	$('body').on('click', '.load_playlist', function (event) {
			event.preventDefault();
			$.ajax({
					url: $(this).attr('href'),
					type: 'GET',
			}).done(function( data ) {
        $('#playlist_videos').html(data);
    });
			return false;
	});

  $('body').on('click', '.vote_playlist', function (event) {
			event.preventDefault();
      $.ajax({
          url: $(this).attr('href'),
          type: 'POST',
          dataType: 'json',
          data: {_token: $("input[name='_token']").val()},
          success: function (data) {
              // $('#basicModal').find('.modal-content').html('');
              // $('#basicModal').modal('show');
              // $('#basicModal').find('.modal-content').load($(this).attr('href'));
              location.reload();
          },
          error: function(data){
            $('#basicModal').find('.modal-content').html('');
            $('#basicModal').modal('show');
            $('#basicModal').find('.modal-content').html(data.responseText);
    			}
      });
			return false;
	});
});

</script>
@endsection

@section('meta')
  <meta property="og:url"           content="{{ Request::url() }}" />
	<meta property="og:type"          content="website" />
	<meta property="og:title"         content="{{ $poll->pol_title }}" />
	<meta property="og:description"   content="{{ $poll->pol_description }}" />
	<!-- <meta property="og:image"         content="http://www.your-domain.com/path/image.jpg" /> -->
@endsection
