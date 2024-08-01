<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ChatMessage; 
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth; 

class ChatController extends Controller
{
    public function SendMessage(Request $request){
        
        $request->validate([
            'msg' => 'required'
        ]);

        ChatMessage::create([
            'sender_id' => Auth::user()->id,
            'receiver_id' => $request->receiver_id,
            'msg' => $request->msg,
            'created_at' => Carbon::now(),
        ]);

        return response()->json(['message' => 'Message Send Successfully']);

    } // End Method 

    public function GetAllUsers(){

        $chats = ChatMessage::orderBy('id','DESC')
                ->where('sender_id',auth()->id())
                ->orWhere('receiver_id',auth()->id())
                ->get();

        $users = $chats->flatMap(function($chat){
            if ($chat->sender_id === auth()->id()) {
               return [$chat->serder, $chat->receiver];
            }
            return [$chat->receiver, $chat->serder];
        })->unique();       

        return $users;   

    }// End Method 

    public function UserMsgById($userId){

        $user = User::find($userId);

        if ($user) {
           $messages = ChatMessage::where(function($q) use ($userId){
                     $q->where('sender_id',auth()->id());
                     $q->where('receiver_id',$userId);
                     })->orWhere(function($q) use ($userId){
                        $q->where('sender_id',$userId);
                        $q->where('receiver_id',auth()->id());
                     })->with('user')->get();

                     return response()->json([
                        'user' => $user,
                        'messages' => $messages, 
                     ]);
        }else {
            abort(404);
        }

    }// End Method 





}
 