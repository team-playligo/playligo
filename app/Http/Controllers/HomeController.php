<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Poll;
use App\PollVoter;
use App\Playlist;
use App\PlaylistRating;
use App\Subscriber;
use App\LogEmail;
use Illuminate\Http\Request;
use App\Http\Requests\AddSubscriber;
use Mail;
use Carbon\Carbon;

class HomeController extends Controller
{
    /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {
    // session()->put('last_page', request()->url());
    parent::__construct();
  }

  /**
   * Show the application dashboard.
   *
   * @return \Illuminate\Http\Response
   */
  public function index()
  {
    $page_title = trans('meta_data.search_funnel_title');

    $page_desc = trans('meta_data.search_funnel_desc');

    $page_img = asset('img/playligo_home_background_glacier.jpg');

    return view('home', compact('page_title', 'page_desc', 'page_img'));
  }

  // Poll page
  public function poll(Poll $poll)
  {
    $poll->increment('pol_view');

    $poll_playlists = $poll->playlists;

    $voters = $poll->voters->take(5);

    $owner = $poll->owner;

    $povRepo = new PollVoter;

    $voted = $povRepo->voted($poll->pol_id);

    $pl_titles = array_column($poll_playlists->toArray(), 'pl_title', 'polp_id');

    $page_title = $poll->pol_title;

    $page_desc = $poll->pol_desc;

    $url_refresh = md5(date('YmdHis'));

    if ($poll_playlists->count() > 0) {
      return view('public.poll_page', compact('poll', 'voters', 'poll_playlists', 'pl_titles', 'page_title', 'page_desc', 'voted', 'owner', 'url_refresh'));
    } else {
      return view('public.poll_page_blank', compact('poll', 'page_title', 'page_desc', 'voted', 'owner'));
    }
  }

  public function allPlaylist()
  {
    $plRepo = new Playlist;

    $latest = $plRepo->latest()->getPaginated(8);

    $mostViewed = $plRepo->mostViewed()->getPaginated(8);

    $page_title = 'Latest Playlists, Most Viewed Playlists | Playligo';

    $page_desc = 'Latest Playlists, Most Viewed Playlists';

    return view('public.playlist', compact('latest', 'mostViewed', 'page_title', 'page_desc'));
  }

  public function allPoll()
  {
    $polRepo = new Poll;

    $latest = $polRepo->withOwner()->latest()->getPaginated(8);

    $mostVoted = $polRepo->mostVoted()->withOwner()->getPaginated(8);

    $page_title = 'Latest Polls, Most Voted Polls | Playligo';

    $page_desc = 'Latest Polls, Most Voted Polls';

    return view('public.poll', compact('latest', 'mostVoted', 'page_title', 'page_desc'));
  }

  public function latestPlaylist(Request $request)
  {
    $page = $request->input('page');

    $plRepo = new Playlist;

    $latest = $plRepo->latest()->getPaginated(8);

    return view('public.playlist.latest_more', compact('latest', 'page'));
  }

  public function mostViewedPlaylist(Request $request)
  {
    $page = $request->input('page');

    $plRepo = new Playlist;

    $mostViewed = $plRepo->mostViewed()->getPaginated(8);

    return view('public.playlist.mostviewed_more', compact('mostViewed', 'page'));
  }

  public function latestPoll(Request $request)
  {
    $page = $request->input('page');

    $polRepo = new Poll;

    $latest = $polRepo->withOwner()->latest()->getPaginated(8);

    return view('public.poll.latest_more', compact('latest', 'page'));
  }

  public function mostVotedPoll(Request $request)
  {
    $page = $request->input('page');

    $polRepo = new Poll;

    $mostVoted = $polRepo->mostVoted()->withOwner()->getPaginated(8);

    return view('public.poll.mostvoted_more', compact('mostVoted', 'page'));
  }

  // Playlist page
  public function playlistPage(Playlist $playlist)
  {
    $owner = $playlist->owner;

    $playlist->increment('pl_view');

    // $mostViewed = $playlist->mostViewed([$playlist->pl_id])->limit(5)->get();
    // $random = $playlist->random([$playlist->pl_id])->limit(5)->get();
    $latest = $playlist->latest([$playlist->pl_id])->limit(3)->get();

    $videos = $playlist->videos;

    // $pvRepo = new PollVoter;
    $polRepo = new Poll;

    $plrRepo = new PlaylistRating;

    // $recent_votes = $pvRepo->withPublicPoll()->withUser()->withPlaylist()->take(5)->get();

    $latest_polls = $polRepo->withOwner()->latest()->limit(3)->get();

    $my_rating = $plrRepo->myRating($playlist->pl_id, auth()->check() ? auth()->user()->id : 0);

    $page_title = $playlist->pl_title;

    $page_desc = $playlist->pl_desc;

    $playlist_keys = implode(', ', array_column($playlist->keys()->get()->toArray(), 'plk_key'));

    // $page_img = unserialize($videos[0]->vc_snippet)->thumbnails->high->url;
    $page_img = null;
    if (count($videos) > 0) {
      $page_img = unserialize($videos[0]->plv_snippet)->thumbnails->high->url;
    }

    $url_refresh = md5(date('YmdHis'));

    return view('public.playlist_page', compact('playlist', 'videos', 'owner', 'latest', 'page_title', 'page_desc', 'page_img', 'latest_polls', 'my_rating', 'playlist_keys', 'url_refresh'));
  }

  public function playlistPopUp(Playlist $playlist)
  {
    $playlist->increment('pl_view');

    $videos = $playlist->videos;

    $title = $playlist->pl_title;

    return view('public.playlist_popup', compact('playlist', 'videos', 'title'));
  }

  // Process new subscriber
  public function subscribe(AddSubscriber $request)
  {
    $susbcriberObj = new Subscriber;

    $input = $request->except('_token');

    // Add to database
    $subscriber = $susbcriberObj->create($input);

    // Send to Sendgrid
    $susbcriberObj->sendSendgrid($subscriber);

    // Email notification
    $email = new LogEmail;

    $email->sendNewSusbcriber($subscriber);

    if ($request->ajax() || $request->wantsJson()) {
      return response()->json(['message' => trans('messages.subscribe_successful')]);
    } else {
      return back()->with('message', trans('messages.subscribe_successful'));
    }
  }

  // Explainer video pop up
  public function explainerPopUp()
  {
    return view('explainer_popup');
  }

  // Teaser page
  public function welcome(Request $request)
  {
    // $play = $request->input('play');
    $play = 1;

    return view('welcome', compact('play'));
  }

  // Prelaunch page
  public function prelaunch(Request $request)
  {
    $play = 1;

    return view('prelaunch', compact('play'));
  }

  public function searchPlaylist(Request $request)
  {
    $plRepo = new Playlist;

    $q = $request->input('q');

    if ($q) {
      $result = $plRepo->search($q)->getPaginated(20);
      $result->setPath('search?q=' . $q);
    } else {
      $result = null;
    }

    $page_title = $q . ' playlists search result | Playligo';

    $page_desc = $q . ' playlists search result';

    return view('public.playlist_result', compact('result', 'q', 'page_title', 'page_desc'));
  }

  public function searchPoll(Request $request)
  {
    $polRepo = new Poll;

    $q = $request->input('q');

    if ($q) {
      $result = $polRepo->search($q)->withOwner()->getPaginated(20);
      $result->setPath('search?q=' . $q);
    } else {
      $result = null;
    }

    $page_title = $q . ' polls search result | Playligo';

    $page_desc = $q . ' polls search result';

    return view('public.poll_result', compact('result', 'q', 'page_title', 'page_desc'));
  }

  public function invite($code)
  {
    $play = 1;

    return view('prelaunch', compact('play', 'code'));
  }

}
