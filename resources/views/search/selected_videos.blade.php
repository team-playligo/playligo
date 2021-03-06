<div class="section">
  <h5 class="section-title title">{{ trans('form.selected_videos') }} ({{ count($videos) }})
    <a href="{{ url('public_playlist/' . $playlist->pl_id) }}">{{ Form::button('View Live', ['type'=>'button', 'class'=>'btn btn-transparent pull-right']) }}</a>
  </h5>
  <div id="sort_list">
    <ul class="list-group playlist-scroll">
      @foreach ($videos as $video)
      <?php $video_snippet = unserialize($video->plv_snippet) ?>
      <li class="list-group-item row" id="{{ $video->plv_id }}">
        <div class="col-md-4 col-sm-2 col-xs-4">
          <div class="play_image_container">
            <a href="{{ url('search/preview/' . $video->plv_video_id) }}" class="btn-modal"><img src="{{ $video_snippet->thumbnails->medium->url }}" class="img-rounded" width="100%"></a>
            <div class="play_button"><i class="fa fa-play-circle-o"></i></div>
          </div>
        </div>
        <div class="col-md-6 col-sm-8 col-xs-6">
            <div class="selected_video_title">{{ $video_snippet->title }}</div>
            <!-- <div class="selected_video_published"><i class="fa fa-clock-o"></i> {{ $video_snippet->publishedAt }}</div> -->
        </div>
        <div class="col-md-2 col-sm-2 col-xs-2">
            <a plv_id="{{ $video->plv_id }}" id="{{ $video->plv_video_id }}" class="remove_video_button" href="{{ url('playlist/video/instant_delete') }}"><i class="fa fa-times-circle fa-4"></i></a>
        </div>
      </li>
      @endforeach
    </ul>
  </div>
</div>

<script>
// Sort selected videos
$("#sort_list ul").sortable({ opacity: 0.6, cursor: 'move',
	start: function(event, ui) {
				ui.item.startPos = ui.item.index();
		},
	update: function(event, ui) {
	var end_pos = ui.item.index() + 1;
	var start_pos = ui.item.startPos + 1;
  var pl_id = $('#pl_id').val();
	var id = ui.item.attr("id");
	$.ajax({
					data: {start_pos:start_pos, end_pos:end_pos, pl_id:pl_id, id:id, _token: "{{ csrf_token() }}"},
					type: 'POST',
					url: '{{url('playlist/sort_item')}}'
			});
}
});

</script>
