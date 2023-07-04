<?php
use App\User;
?>
@extends('layouts.frontLayout.front_design')
@section('content')
  <div id="right_container">
        <div style="padding:20px 15px 30px 15px;">
          <h1>{{ $userDetails->name }}</h1>
          @if(Session::has('flash_message_success'))
              <div class="alert alert-success alert-block">
                  <button type="button" class="close" data-dismiss="alert">×</button> 
                      <strong>{!! session('flash_message_success') !!}</strong>
              </div>
          @endif
          @foreach($userDetails->photos as $key => $photo)
            @if($photo->default_photo == "Yes")
              <?php $user_photo = $userDetails->photos[$key]->photo; ?>
            @else
              <?php $user_photo = $userDetails->photos[0]->photo; ?>
            @endif
          @endforeach
          <div>
            @if(!empty($user_photo))
              <img src="{{ asset('images/frontend_images/photos/'.$user_photo) }}" alt="" width="210" class="aboutus-img" />
            @else
              <img src="{{ asset('images/frontend_images/photos/default.jpg') }}" alt="" width="210" class="aboutus-img" />
            @endif  
            <strong>Profile ID:</strong> {{ $userDetails->username }}<br>
            <strong>Name:</strong> {{ $userDetails->name }}<br>
            <strong>Gender:</strong> {{ $userDetails->details->gender }}<br>
            <strong>Marital Status:</strong> {{ $userDetails->details->marital_status }}<br>
            <strong>Age:</strong> <?php
                      echo $diff = date('Y') - date('Y',strtotime($userDetails->details->dob)); 
                    ?> Yrs.<br>
            <strong>Height:</strong> {{ $userDetails->details->height }}<br>
            <strong>Body Type:</strong> {{ $userDetails->details->body_type }}<br>
            <strong>Complexion:</strong> {{ $userDetails->details->complexion }}<br>
            <strong>Languages:</strong> {{ $userDetails->details->languages }}<br>
            <strong>Hobbies:</strong> {{ $userDetails->details->hobbies }}<br>
            <strong>City:</strong> {{ $userDetails->details->city }}<br>
            <strong>State:</strong> {{ $userDetails->details->state }}<br>
            <strong>Country:</strong> {{ $userDetails->details->country }}<br>
            <strong style="float:right;">
              <script type="text/javascript">
                  var viewer = new PhotoViewer();
                  @foreach($userDetails->photos as $key => $photo)
                    viewer.add('/images/frontend_images/photos/<?php echo $userDetails->photos[$key]->photo ?>');
                  @endforeach
              </script>
                <a href="javascript:void(viewer.show(0))">Photo Album</a>
            </strong><br>
            <strong style="float:right;">
              @if(Auth::check())
                @if(Auth::User()->username == $userDetails->username)
                  <a href="{{ url('/step/2') }}" style="color: red"><i class="fa fa-comment" aria-hidden="true" style="color: red"></i>&nbsp;Edit Profile</a>
                @else
                  <a href="{{ url('/contact/'.$userDetails->username) }}" style="color: red"><i class="fa fa-comment" aria-hidden="true" style="color: red"></i>&nbsp;Contact Profile</a>
                @endif
              @else
                <a href="{{ url('/contact/'.$userDetails->username) }}" style="color: red"><i class="fa fa-comment" aria-hidden="true" style="color: red"></i>&nbsp;Contact Profile</a>
              @endif
            </strong><br>
            <strong style="float:right;">
              
              <?php 
                  $isOnline = User::isOnline($userDetails->id); 
                  if($isOnline){ ?>
                  <i class="fa fa-user" aria-hidden="true" style="color: green"></i>
                  <font color='green'><strong>Online</strong></font>
              <?php }else{ ?>
                  <i class="fa fa-user-o" aria-hidden="true" style="color: grey"></i>
                  <font color='grey'><strong>Offline</strong></font>
              <?php } ?>
            </strong>
            <br>
            @if(!empty($friendrequest))
              @if(Auth::check() && Auth::User()->username != $userDetails->username)
              <strong style="float:right;">
                @if($friendrequest=="Add Friend")
                  <a href="{{ url('/add-friend/'.$userDetails->username) }}" style="color: green"><i class="fa fa-user-plus" aria-hidden="true"></i>&nbsp;{{ $friendrequest }}</a>
                @elseif($friendrequest=="Unfriend")
                  <a href="{{ url('/remove-friend/'.$userDetails->username) }}" style="color: green"><i class="fa fa-minus-circle" aria-hidden="true"></i>&nbsp;{{ $friendrequest }}</a>
                @elseif($friendrequest=="Confirm Friend Request")
                  <a href="{{ url('/confirm-friend-request/'.$userDetails->username) }}" style="color: green"><i class="fa fa-minus-circle" aria-hidden="true"></i>&nbsp;{{ $friendrequest }}</a>  
                @else
                  <span style="color: green"><i class="fa fa-user" aria-hidden="true"></i>&nbsp;{{ $friendrequest }}</span>
                @endif  
              </strong>
              @endif
              <br />
              <br />
              <div class="clear"></div>
            @else
              <strong style="float:right;"><a onclick="return loginuser();" style="color: green">Add Friend</a></strong>  
            @endif
            @if(!Auth::check()) <br> @endif
              <strong style="float:right;"><a @if($favorite!="Favorite Profile") onclick="return favoriteuser();" @endif style="color: green; cursor: pointer;">{{ $favorite }}</a></strong>  
          </div>
          <div class="clear"></div>
          <div>
            <h6 class="inner">Education & Career</h6>
            <div>
              <strong>Highest Education:</strong> {{ $userDetails->details->hobbies }}<br>
              <strong>Occupation:</strong> {{ $userDetails->details->hobbies }}<br>
              <strong>Income:</strong> {{ $userDetails->details->hobbies }}<br>
            </div>
          </div>
          <div class="clear"></div>
          <div class="aboutcolumnzone">
            <div class="aboutcolumn1">
              <div>
                <h5 class="inner">About Myself</h5>
                <div>{{ $userDetails->details->about_myself }}</div>
              </div>
            </div>
            <div class="aboutcolumn2">
              <div>
                <h5 class="inner">About My Preferred Partner</h5>
                <div>{{ $userDetails->details->about_partner }}</div>
              </div>
            </div>
          </div>
          <div class="clear"></div>
          <div>
          <h6 class="inner" style="margin-top: 10px">My Friends</h6>
          <div class="recent_add_prifile">
            @if(count($friendsList)>0)
            <?php $count=1; ?>
              @foreach($friendsList as $user)
                @if(!empty($user->details) && $user->details->status == 1)
                  @if($count<=4)
                    @if(Auth::check()) 
                      
                        <div class="profile_box">
                          @foreach($user->photos as $key => $photo)
                            @if($photo->default_photo == "Yes")
                              <?php $user_photo = $user->photos[$key]->photo; ?>
                            @else
                              <?php $user_photo = $user->photos[0]->photo; ?>
                            @endif
                          @endforeach
                          @if(!empty($user_photo))
                            <span class="photo"><a href="{{ url('profile/'.$user->username) }}"><img src="{{ asset('images/frontend_images/photos/'.$user_photo) }}" alt="" /></a></span>
                          @else
                            <span class="photo"><a href="{{ url('profile/'.$user->username) }}"><img src="{{ asset('images/frontend_images/photos/default.jpg') }}" alt="" /></a></span>
                          @endif
                        <p class="left">Name:</p>
                        <p class="right">{{ $user->name }}</p>
                        <p class="left">Age:</p>
                        <p class="right">
                          <?php
                            echo $diff = date('Y') - date('Y',strtotime($user->details->dob)); 
                          ?> Yrs.
                        </p>
                        <p class="left">Location:</p>
                        <p class="right">@if(!empty($user->details->city)) {{ $user->details->city }} @endif</p>
                        <a href="#"><img src="{{ asset('images/frontend_images/more_btn.gif') }}" alt="" class="more_1" /></a> </div>
                    @else
                    <div class="profile_box">
                      @foreach($user->photos as $key => $photo)
                        @if($photo->default_photo == "Yes")
                          <?php $user_photo = $user->photos[$key]->photo; ?>
                        @else
                          <?php $user_photo = $user->photos[0]->photo; ?>
                        @endif
                      @endforeach
                      @if(!empty($user_photo))
                        <span class="photo"><a href="{{ url('profile/'.$user->username) }}"><img src="{{ asset('images/frontend_images/photos/'.$user_photo) }}" alt="" /></a></span>
                      @else
                        <span class="photo"><a href="{{ url('profile/'.$user->username) }}"><img src="{{ asset('images/frontend_images/photos/default.jpg') }}" alt="" /></a></span>
                      @endif
                    <p class="left">Name:</p>
                    <p class="right">{{ $user->name }}</p>
                    <p class="left">Age:</p>
                    <p class="right">
                      <?php
                        echo $diff = date('Y') - date('Y',strtotime($user->details->dob)); 
                      ?> Yrs.
                    </p>
                    <p class="left">Location:</p>
                    <p class="right">@if(!empty($user->details->city)) {{ $user->details->city }} @endif</p>
                    <a href="#"><img src="{{ asset('images/frontend_images/more_btn.gif') }}" alt="" class="more_1" /></a> </div>
                    @endif
                    <?php $count = $count+1; ?>
                  @endif
                @endif
              @endforeach
            @else
              No Friends
            @endif
          </div> 
          <div>
          <h6 class="inner" style="margin-top: 10px">My Favorites</h6>
          <div class="recent_add_prifile">
            @if(count($favoriteList)>0)
            <?php $count=1; ?>
              @foreach($favoriteList as $user)
                @if(!empty($user->details) && $user->details->status == 1)
                  @if($count<=4)
                    @if(Auth::check()) 
                      
                        <div class="profile_box">
                          @foreach($user->photos as $key => $photo)
                            @if($photo->default_photo == "Yes")
                              <?php $user_photo = $user->photos[$key]->photo; ?>
                            @else
                              <?php $user_photo = $user->photos[0]->photo; ?>
                            @endif
                          @endforeach
                          @if(!empty($user_photo))
                            <span class="photo"><a href="{{ url('profile/'.$user->username) }}"><img src="{{ asset('images/frontend_images/photos/'.$user_photo) }}" alt="" /></a></span>
                          @else
                            <span class="photo"><a href="{{ url('profile/'.$user->username) }}"><img src="{{ asset('images/frontend_images/photos/default.jpg') }}" alt="" /></a></span>
                          @endif
                        <p class="left">Name:</p>
                        <p class="right">{{ $user->name }}</p>
                        <p class="left">Age:</p>
                        <p class="right">
                          <?php
                            echo $diff = date('Y') - date('Y',strtotime($user->details->dob)); 
                          ?> Yrs.
                        </p>
                        <p class="left">Location:</p>
                        <p class="right">@if(!empty($user->details->city)) {{ $user->details->city }} @endif</p>
                        <a href="#"><img src="{{ asset('images/frontend_images/more_btn.gif') }}" alt="" class="more_1" /></a> </div>
                    @else
                    <div class="profile_box">
                      @foreach($user->photos as $key => $photo)
                        @if($photo->default_photo == "Yes")
                          <?php $user_photo = $user->photos[$key]->photo; ?>
                        @else
                          <?php $user_photo = $user->photos[0]->photo; ?>
                        @endif
                      @endforeach
                      @if(!empty($user_photo))
                        <span class="photo"><a href="{{ url('profile/'.$user->username) }}"><img src="{{ asset('images/frontend_images/photos/'.$user_photo) }}" alt="" /></a></span>
                      @else
                        <span class="photo"><a href="{{ url('profile/'.$user->username) }}"><img src="{{ asset('images/frontend_images/photos/default.jpg') }}" alt="" /></a></span>
                      @endif
                    <p class="left">Name:</p>
                    <p class="right">{{ $user->name }}</p>
                    <p class="left">Age:</p>
                    <p class="right">
                      <?php
                        echo $diff = date('Y') - date('Y',strtotime($user->details->dob)); 
                      ?> Yrs.
                    </p>
                    <p class="left">Location:</p>
                    <p class="right">@if(!empty($user->details->city)) {{ $user->details->city }} @endif</p>
                    <a href="#"><img src="{{ asset('images/frontend_images/more_btn.gif') }}" alt="" class="more_1" /></a> </div>
                    @endif
                    <?php $count = $count+1; ?>
                  @endif
                @endif
              @endforeach
            @else
              No Friends
            @endif
          </div>
          </div>        
        </div>
      </div>
@endsection

<script>
  function loginuser(){
    alert("Please login to Add Friend");
    window.location = "/add-new-friend/<?php echo $userDetails->username; ?>";
  }
  function favoriteuser(){
    @if(!Auth::check())
      alert("Please login to add this user as Favorite");
    @endif
    window.location = "/add-new-favorite/<?php echo $userDetails->username; ?>";
  }
</script>