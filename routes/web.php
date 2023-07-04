<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Front Routes
Route::get('/','IndexController@index');
Route::any('/register','UsersController@register');

/*Route::get('/chat/{username}', function($username){
	return view('users.chat')->with(compact('username'));
});*/



Route::group(['middleware'=>['frontlogin']],function(){

	Route::get('/chat/{username}','ChatController@userChatBox');
	Route::post('/chat/sendMessage','ChatController@sendMessage');
	Route::post('/chat/isTyping','ChatController@isTyping');
	Route::post('/chat/notTyping','ChatController@notTyping');
	Route::post('/chat/retrieveChatMessages','ChatController@retrieveChatMessages');
	Route::post('/chat/retrieveTypingStatus','ChatController@retrieveTypingStatus');

	Route::any('/step/2','UsersController@step2');
	Route::any('/step/3','UsersController@step3');
	Route::get('/review','UsersController@review');
	Route::get('/responses','UsersController@responses');
	Route::get('/friends-requests','UsersController@friendsRequests');
	Route::get('/friends','UsersController@friends');
	Route::get('/accept-friend-request/{id}','UsersController@acceptFriendRequest');
	Route::get('/confirm-friend-request/{id}','UsersController@confirmFriendRequest');
	Route::get('/reject-friend-request/{id}','UsersController@rejectFriendRequest');
	Route::post('/update-response','UsersController@updateResponse');
	Route::get('/delete-response/{id}','UsersController@deleteResponse');
	Route::get('/sent-messages','UsersController@sentMessages');
	Route::get('/delete-photo/{photo}','UsersController@deletePhoto');
	Route::get('/default-photo/{photo}','UsersController@defaultPhoto');
	Route::match(['get','post'],'/contact/{username}','UsersController@contactProfile');
	Route::match(['get','post'],'/add-friend/{username}','UsersController@addFriend');
	Route::match(['get','post'],'/add-new-friend/{username}','UsersController@addNewFriend');
	Route::match(['get','post'],'/add-new-favorite/{username}','UsersController@addNewFavorite');
	Route::match(['get','post'],'/remove-friend/{username}','UsersController@removeFriend');
});

Route::any('/login','UsersController@login');
Route::get('/logout','UsersController@logout');

Route::get('/check-email','UsersController@checkEmail');
Route::get('/check-username','UsersController@checkUsername');

Route::match(['get', 'post'], '/admin','AdminController@login');

Route::get('/admin/logout','AdminController@logout');

Route::any('/profile/search','UsersController@searchProfile');
Route::get('/profile/{username}','UsersController@viewProfile');

Route::group(['middleware' => ['adminlogin']],function(){
	Route::get('/admin/dashboard','AdminController@dashboard');	
	Route::get('/admin/settings','AdminController@settings');
	Route::get('/admin/check-pwd','AdminController@chkPassword');
	Route::match(['get','post'],'/admin/update-pwd','AdminController@updatePassword');

	// Users Routes
	Route::get('admin/view-users','UsersController@viewUsers');
	Route::post('admin/update-user-status','UsersController@updateUserStatus');
	Route::post('admin/update-photo-status','UsersController@updatePhotoStatus');

});
