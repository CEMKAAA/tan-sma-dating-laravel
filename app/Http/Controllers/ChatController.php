<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\ChatMessage;
use App\User;
use Auth;

class ChatController extends Controller
{

    public function userChatBox($username){
        if(Auth::user()['username']!=$username){
            return redirect('/');
        }
    	return view('users.chat')->with(compact('username'));
    }

    public function sendMessage(){
    	$username = Input::get('username');
    	$text = Input::get('text');
    	$chatMessage = new ChatMessage();
    	$chatMessage->sender_username = $username;
    	$chatMessage->message = $text;
    	$chatMessage->save();
    }

    public function isTyping(){
        $username = Input::get('username');
        $chat = User::where('username',$username)->first();
        if($chat->username == $username){
            User::where('username',$username)->update(['user_is_typing'=>1]);
        }
    }

    public function notTyping(){
        $username = Input::get('username');
        $chat = User::where('username',$username)->first();
        if($chat->username == $username){
            User::where('username',$username)->update(['user_is_typing'=>0]);
        }
    }

    public function retrieveChatMessages(){
        $username = Input::get('username');
        $message = ChatMessage::where('sender_username','!=',$username)->where('read',0)->first();
        if($message){
            $message->read = 1;
            $message->save();
            return "<strong>".$message->sender_username.": </strong>".$message->message;
        }
    }

    public function retrieveTypingStatus(){
        $username = Input::get('username');
        $chat = User::where('username',$username)->first();
        if($chat->username == $username){
            if($chat->user_is_typing){
                return $chat->username;
            }
        }
    }
}
