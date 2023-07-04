<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Session;
use Image;
use App\User;
use App\UsersDetail;
use App\Country;
use App\Language;
use App\Hobby;
use App\UsersPhoto;
use File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use App\Response;
use App\Friend;
use Cache;
use App\Favorite;

class UsersController extends Controller
{
    public function register(Request $request){
    	if($request->isMethod('post')){
    		$data = $request->all();
    		/*echo "<pre>"; print_r($data); die;*/

            // It will return back to Register if Captcha not selected
            /*$this->validate($request, [
                 'g-recaptcha-response' => 'required|captcha',
            ]);*/

    		$user = new User;
    		$user->name = $data['name'];
            $user->username = $data['username'];
    		$user->email = $data['email'];
    		$user->password = bcrypt($data['password']);
    		$user->save();
            if(Auth::attempt(['username'=>$data['username'],'password'=>$data['password'],'admin'=>'0'])){
                Session::put('frontSession',$data['username']);
                return redirect('/step/2');
            }

    	}
    	return view('users.register');
    }

    public function step2(Request $request){

        // Check if dating profile already exists and under review
        $userProfileCount = UsersDetail::where(['user_id'=>Auth::user()['id'],'status'=>0])->count();
        if($userProfileCount>0){
            return redirect('/review');
        }

        if($request->isMethod('post')){
            $data = $request->all();
            /*echo "<pre>"; print_r($data); die;*/
            if(empty($data['user_id'])){
                $userDetail = new UsersDetail;
                $userDetail->user_id = Auth::User()['id'];
            }else{
                $userDetail = UsersDetail::where('user_id',$data['user_id'])->first();
                $userDetail->status = 0;
            }

            $userDetail->username = Session::get('frontSession');
            $userDetail->dob = $data['dob'];
            $userDetail->gender = $data['gender'];
            $userDetail->height = $data['height'];
            $userDetail->marital_status = $data['marital_status'];

            if(empty($data['body_type'])){
                $data['body_type'] = '';
            }

            if(empty($data['city'])){
                $data['city'] = '';
            }

            if(empty($data['state'])){
                $data['state'] = '';
            }

            if(empty($data['country'])){
                $data['country'] = '';
            }

            if(empty($data['education'])){
                $data['education'] = '';
            }

            if(empty($data['occupation'])){
                $data['occupation'] = '';
            }

            if(empty($data['income'])){
                $data['income'] = '';
            }

            if(empty($data['complexion'])){
                $data['complexion'] = '';
            }

            $userDetail->complexion = $data['complexion'];
            $userDetail->body_type = $data['body_type'];
            $userDetail->city = $data['city'];
            $userDetail->state = $data['state'];
            $userDetail->country = $data['country'];
            $userDetail->education = $data['education'];
            $userDetail->occupation = $data['occupation'];
            $userDetail->income = $data['income'];
            $userDetail->about_myself = $data['about_myself'];
            $userDetail->about_partner = $data['about_partner'];

            $hobbies = "";
            if(!empty($data['hobbies'])){
                foreach($data['hobbies'] as $hobby){
                    $hobbies .= $hobby.', ';
                }    
            }
            $userDetail->hobbies = $hobbies;

            $languages = "";
            if(!empty($data['languages'])){
                foreach($data['languages'] as $language){
                    $languages .= $language.', ';
                }
            }
            $userDetail->languages = $languages;

            $userDetail->save();

            return redirect('/review');
        }

        // Get all Countries
        $countries = Country::get();

        // Get all Languages
        $languages = Language::orderBy('name','ASC')->get();

        // Get all Hobbies
        $hobbies = Hobby::orderBy('title','ASC')->get();

        return view('users.step2')->with(compact('countries','languages','hobbies'));
    }

    public function step3(Request $request){
        if($request->isMethod('post')){
            $data = $request->all();
            /*echo "<pre>"; print_r($data); die;*/
            if($request->hasFile('photo')){
                $files = $request->file('photo');
                foreach($files as $file){

                    // Add photo at photos Folder

                    // Get photo extension
                    $extension = $file->getClientOriginalExtension();
                    // Give Random name to image and add its extension
                    $fileName = rand(111,99999).'.'.$extension;
                    // Set Image Path 
                    $image_path = 'images/frontend_images/photos/'.$fileName;
                    // Intervention Code for uploading Image
                    Image::make($file)->resize(600, 600)->save($image_path);

                    // Add photo at users_photos table

                    $photo = new UsersPhoto;
                    $photo->photo = $fileName;
                    $photo->user_id = $data['user_id'];
                    $photo->username = Session::get('frontSession');
                    $photo->save();
                }
            }
            return redirect('/step/3')->with('flash_message_success','Your photo(s) has been added successfully.');
        }
        $user_id = Auth::User()['id'];
        $user_photos = UsersPhoto::where('user_id',$user_id)->get();
        return view('users.step3')->with(compact('user_photos'));
    }

    public function deletePhoto($photo){
        $user_id = Auth::User()->id;
        UsersPhoto::where(['user_id'=>$user_id,'photo'=>$photo])->delete();

        // Delete from photos folder with PHP unlink function
        //unlink('images/frontend_images/photos/'.$photo);

        // Delete from photos folder with PHP unlink function
        File::delete('images/frontend_images/photos/'.$photo);
        return redirect()->back()->with('flash_message_success','Photo has been deleted Successfully!');
    }

    public function defaultPhoto($photo){
        $user_id = Auth::User()->id;
        // Set all photos as Non default
        UsersPhoto::where('user_id',$user_id)->update(['default_photo'=>'No']);
        // Make selected Photo default
        UsersPhoto::where(['user_id'=>$user_id,'photo'=>$photo])->update(['default_photo'=>'Yes']);
        return redirect()->back()->with('flash_message_success','Default Photo has been set Successfully!');
    }

    public function login(Request $request){
        if($request->isMethod('post')){
            $data = $request->input();
            /*echo "<pre>"; print_r($data); die;*/
            if(Auth::attempt(['username'=>$data['username'],'password'=>$data['password'],'admin'=>'0'])){
                /*echo "success"; die;*/

                if (preg_match("/contact/i", Session::get('current_url'))) {
                    Session::put('frontSession',$data['username']);
                    return redirect(Session::get('current_url'));
                } else if (preg_match("/add-new-friend/i", Session::get('current_url'))) {
                    Session::put('frontSession',$data['username']);
                    return redirect(Session::get('current_url'));
                } else if (preg_match("/add-new-favorite/i", Session::get('current_url'))) {
                    Session::put('frontSession',$data['username']);
                    return redirect(Session::get('current_url'));
                }else {
                    Session::put('frontSession',$data['username']);
                    return redirect('/step/2');
                }
            }else{
                /*echo "failed"; die;*/
                return redirect::back()->with('flash_message_error','Invalid Username or Password');
            }
        }
    }

    public function logout(){
        Cache::flush();
        Auth::logout();
        Session::forget('frontSession');
        Session::forget('current_url');
        return redirect()->action('IndexController@index');
    }

    public function checkEmail(Request $request){
        $data = $request->all(); 
        $usersCount = User::where('email',$data['email'])->count();
        if($usersCount>0){
            echo 'false';
        }else{
            echo 'true';
        }
    }

    public function checkUsername(Request $request){
        $data = $request->all(); 
        $usersCount = User::where('username',$data['username'])->count();
        if($usersCount>0){
            echo 'false';
        }else{
            echo 'true';
        }
    }

    public function review(){
        $user_id = Auth::User()['id'];
        $userStatus = UsersDetail::select('status')->where('user_id',$user_id)->first();
        if($userStatus->status == 1){
            return redirect('/step/2');
        }else{
            return view('users.review');
        }
    }

    public function viewUsers(){
        $users = User::with('details')->with('photos')->where('admin','!=','1')->get();
        $users = json_decode(json_encode($users),true);
        /*echo "<pre>"; print_r($users); die;*/
        return view('admin.users.view_users')->with(compact('users'));
    }

    public function updateUserStatus(Request $request){
        $data = $request->all();
        UsersDetail::where('user_id',$data['user_id'])->update(['status'=>$data['status']]);
    }

    public function updatePhotoStatus(Request $request){
        $data = $request->all();
        UsersPhoto::where('id',$data['photo_id'])->update(['status'=>$data['status']]);
    }

    public function viewProfile($username){
        $userCount = User::where('username',$username)->count();
        if($userCount>0){
            $userDetails = User::with('details')->with('photos')->where('username',$username)->first();
            $userDetails = json_decode(json_encode($userDetails));
            /*echo "<pre>"; print_r($userDetails); die;*/
            if(Auth::check()){
                // Check the user is friend or not
                $user_id = Auth::user()->id;
                $friend_id = User::getUserId($username);
                $friendCount = Friend::where(['user_id'=>$user_id,'friend_id'=>$friend_id])->count();
                if($friendCount>0){
                    $friendDetails = Friend::where(['user_id'=>$user_id,'friend_id'=>$friend_id])->first();
                    /*$friendDetails = json_decode(json_encode($friendDetails));
                    echo "<pre>"; print_r($friendDetails); die;*/
                    if($friendDetails->accept==1){
                        $friendrequest = "Unfriend";
                    }else{
                        $friendrequest = "Friend Request Sent";
                    }
                }else{
                    $friendRequestCount = Friend::where(['friend_id'=>$user_id,'user_id'=>$friend_id])->count();
                    if($friendRequestCount>0){
                        $friendRequest = Friend::where(['friend_id'=>$user_id,'user_id'=>$friend_id])->first();
                        if($friendRequest->accept == 1){
                            $friendrequest = "Unfriend";
                        }else{
                            $friendrequest = "Confirm Friend Request";
                        }
                    }else{
                        $friendrequest = "Add Friend";
                    }
                }
            }else{
                $friendrequest = "";
            }

            if(Auth::check()){
                $user_id = Auth::user()->id;
                $favorite_id = User::getUserId($username);
                
                // Check if already favorite 
                $favoriteCount = Favorite::where(['favorite_id'=>$favorite_id,'user_id'=>$user_id])->count();
                if($favoriteCount>0){
                    $favorite = "Favorite Profile";
                }else{
                    $favorite = "Add Favorite";   
                }

            }else{
                $favorite = "Add Favorite";
            }

        }else{
            abort(404);    
        } 

        // User Friends List
        $friend_id = User::getUserId($username);
        $friendsCount1 = Friend::where(['friend_id'=>$friend_id,'accept'=>1])->count();
        $friend_ids1 = array();
        if($friendsCount1>0){
            $friend_ids1 = Friend::select('user_id')->where(['friend_id'=>$friend_id,'accept'=>1])->get();
            $friend_ids1 = array_flatten(json_decode(json_encode($friend_ids1),true));
            /*echo "<pre>"; print_r($friend_ids1);*/
        }
        $friendsCount2 = Friend::where(['user_id'=>$friend_id,'accept'=>1])->count();
        $friend_ids2 = array();
        if($friendsCount2>0){
            $friend_ids2 = Friend::select('friend_id')->where(['user_id'=>$friend_id,'accept'=>1])->get();
            $friend_ids2 = array_flatten(json_decode(json_encode($friend_ids2),true));
            /*echo "<pre>"; print_r($friend_ids2); die;*/
        }
        $friends_ids = array();
        $friends_ids = array_merge($friend_ids1,$friend_ids2);
        /*echo "<pre>"; print_r($friends_ids); die;*/

        $friendsList = User::with('details')->with('photos')->whereIn('id',$friends_ids)->orderBy('id','Desc')->get();
        $friendsList = json_decode(json_encode($friendsList));


        // Get Favorite List of Opened Profile
        $profile_id = User::getUserId($username);
        $favorite_ids = Favorite::select('favorite_id')->where('user_id',$profile_id)->get();
        $favorite_ids = array_flatten(json_decode(json_encode($favorite_ids),true));

        $favoriteList = User::with('details')->with('photos')->whereIn('id',$favorite_ids)->orderBy('id','Desc')->get();
        $favoriteList = json_decode(json_encode($favoriteList));
        //echo "<pre>"; print_r($favoriteList); die;

        return view('users.profile')->with(compact('userDetails','friendrequest','friendsList','favorite','favoriteList'));
    }

    public function contactProfile(Request $request,$username){
        $userCount = User::where('username',$username)->count();
        if($userCount>0){
            $userDetails = User::with('details')->with('photos')->where('username',$username)->first();
            $userDetails = json_decode(json_encode($userDetails));
            if($request->isMethod('post')){
            $data = $request->all();
            // Add Response in responses table
            $response = new Response;
            $response->sender_id = Auth::user()->id;
            $response->receiver_id = $userDetails->id;
            $response->message = $data['message'];
            $response->save();
            return redirect()->back()->with('flash_message_success','Your response has been sent to this dating profile.');
            }
        }else{
            abort(404);    
        } 
        return view('users.contact')->with(compact('userDetails'));
    }

    public function addFriend($username){
        $userCount = User::where('username',$username)->count();
        if($userCount>0){
            $user_id = Auth::user()->id;
            $friend_id = User::getUserId($username);
            $friend = new Friend;
            $friend->user_id = $user_id;
            $friend->friend_id = $friend_id;
            $friend->save();
            return redirect()->back();    
        }else{
            abort(404);    
        } 
    }

    public function addNewFriend($username){
        $userCount = User::where('username',$username)->count();
        if($userCount>0){
            $user_id = Auth::user()->id;
            $friend_id = User::getUserId($username);
            

            // Check if already friends or friend request sent
            $friendsCount1 = Friend::where(['friend_id'=>$friend_id,'user_id'=>$user_id])->count();
            if($friendsCount1>0){
                return redirect('profile/'.$username);
            }
            $friendsCount2 = Friend::where(['user_id'=>$friend_id,'friend_id'=>$user_id])->count();
            if($friendsCount2>0){
                return redirect('profile/'.$username);
            }

            $friend = new Friend;
            $friend->user_id = $user_id;
            $friend->friend_id = $friend_id;
            $friend->save();
            return redirect('profile/'.$username);    
        }else{
            abort(404);    
        } 
    }

    public function addNewFavorite($username){
        $userCount = User::where('username',$username)->count();
        if($userCount>0){
            $user_id = Auth::user()->id;
            $favorite_id = User::getUserId($username);
            
            // Check if already favorite 
            $favoriteCount = Favorite::where(['favorite_id'=>$favorite_id,'user_id'=>$user_id])->count();
            if($favoriteCount>0){
                return redirect('profile/'.$username);
            }

            $favorite = new Favorite;
            $favorite->user_id = $user_id;
            $favorite->favorite_id = $favorite_id;
            $favorite->save();
            return redirect('profile/'.$username)->with('flash_message_success','User has been added as Favorite!');    
        }else{
            abort(404);    
        } 
    }

    public function confirmFriendRequest($username){
        $user_id = Auth::user()->id;
        $friend_id = User::getUserId($username);
        $friendCount = Friend::where(['friend_id'=>$user_id,'user_id'=>$friend_id])->count();
        if($friendCount>0){
            Friend::where(['friend_id'=>$user_id,'user_id'=>$friend_id])->update(['accept'=>1]);
            return redirect()->back();    
        }else{
            abort(404);
        }
    }

    public function removeFriend($username){
        $userCount = User::where('username',$username)->count();
        if($userCount>0){
            $user_id = Auth::user()->id;
            $friend_id = User::getUserId($username);
            Friend::where(['user_id'=>$user_id,'friend_id'=>$friend_id])->delete();
            return redirect()->back();    
        }else{
            abort(404);    
        } 
    }

    public function searchProfile(Request $request){
        if($request->isMethod('post')){
            $data = $request->all();
            /*echo "<pre>"; print_r($data); die;*/
            $searched_users = User::with('details')->with('photos')
                ->join('users_details','users_details.user_id','=','users.id')
                ->where('users_details.gender',$data['gender'])
                ->where('users.username','!=','admin');
            if(!empty($data['country'])){
                $searched_users = $searched_users->where('users_details.country',$data['country']);    
            }
            $searched_users = $searched_users->orderBy('users.id','Desc')->get();
            $searched_users = json_decode(json_encode($searched_users));
            /*echo "<pre>"; print_r($searched_users); die;*/
            $minAge = $data['minAge'];
            $maxAge = $data['maxAge'];
            return view('users.search')->with(compact('searched_users','minAge','maxAge'));
        }
    }

    public function responses(){
        $receiver_id = Auth::user()->id;
        $responses = Response::where('receiver_id',$receiver_id)->orderBy('id','Desc')->get();
        /*$responses = json_decode(json_encode($responses));
        echo "<pre>"; print_r($responses); die;*/
        return view('users.responses')->with(compact('responses'));
    }

    public function friendsRequests(){
        $user_id = Auth::user()->id;
        $friendsRequests = Friend::where(['friend_id'=>$user_id,'accept'=>0])->get();
        $friendsRequests = json_decode(json_encode($friendsRequests));
        /*echo "<pre>"; print_r($friendsRequests); die;*/
        return view('users.friends_requests')->with(compact('friendsRequests'));
    }

    public function friends(){
        // User Friends List
        $friend_id = Auth::user()->id;
        $friendsCount1 = Friend::where(['friend_id'=>$friend_id,'accept'=>1])->count();
        $friend_ids1 = array();
        if($friendsCount1>0){
            $friend_ids1 = Friend::select('user_id')->where(['friend_id'=>$friend_id,'accept'=>1])->get();
            $friend_ids1 = array_flatten(json_decode(json_encode($friend_ids1),true));
            /*echo "<pre>"; print_r($friend_ids1);*/
        }
        $friendsCount2 = Friend::where(['user_id'=>$friend_id,'accept'=>1])->count();
        $friend_ids2 = array();
        if($friendsCount2>0){
            $friend_ids2 = Friend::select('friend_id')->where(['user_id'=>$friend_id,'accept'=>1])->get();
            $friend_ids2 = array_flatten(json_decode(json_encode($friend_ids2),true));
            /*echo "<pre>"; print_r($friend_ids2); die;*/
        }
        $friends_ids = array();
        $friends_ids = array_merge($friend_ids1,$friend_ids2);

        $friendsList = User::with('details')->with('photos')->whereIn('id',$friends_ids)->orderBy('id','Desc')->get();
        $friendsList = json_decode(json_encode($friendsList));
        /*echo "<pre>"; print_r($friendsList); die;*/
        return view('users.friends')->with(compact('friendsList'));
    }

    public function acceptFriendRequest($sender_id){
        $receiver_id = Auth::user()->id;
        Friend::where(['user_id'=>$sender_id,'friend_id'=>$receiver_id])->update(['accept'=>1]);
        return redirect()->back()->with('flash_message_success','Friend Request successfully Accepted!');
    }

    public function rejectFriendRequest($sender_id){
        $receiver_id = Auth::user()->id;
        Friend::where(['user_id'=>$sender_id,'friend_id'=>$receiver_id])->delete();
        Friend::where(['user_id'=>$receiver_id,'friend_id'=>$sender_id])->delete();
        return redirect()->back()->with('flash_message_success','Friend Request successfully Rejected!');
    }

    public function updateResponse(Request $request){
        if($request->isMethod('post')){
            $data = $request->all();
            /*echo "<pre>"; print_r($data); die;*/
            Response::where('id',$data['response_id'])->update(['seen'=>1]);
            echo Response::newResponsesCount();
        }
    }

    public function deleteResponse($id){
        Response::where('id',$id)->delete();
        return redirect()->back()->with('flash_message_success','Response has been deleted successfully!');
    }

    public function sentMessages(){
        $sender_id = Auth::user()->id;
        $sent_msg = Response::where('sender_id',$sender_id)->orderBy('id','Desc')->get();
        return view('users.sent_messages')->with(compact('sent_msg'));
    }
}
