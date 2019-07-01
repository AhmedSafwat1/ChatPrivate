<?php

namespace App\Http\Controllers;

use function foo\func;
use Illuminate\Http\Request;
use Validator;
class ChatRoomController extends Controller
{
    //
    public function responseMessage($rom)
    {
        $messages = $rom->Messages;
        $res = $messages->map(function ($message){
            $data["id"]         = $message->id;
            $data["sender_id"]  = $message->Sender->id;
            $data["reeciver_id"]= $message->Reciever->id;
            $data["msg"]        =$message->message;
            return $data;
        });
        return $res->toArray();
    }
    public function getRom(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'sender_id'      => 'required|exists:users,id',
            'reciever_id'    => 'required|exists:users,id',
        ]);
        if ($validator->passes()) {
            $rom = getChatRom($request["sender_id"], $request["reciever_id"]);
            $msg = $request['lang'] == 'ar' ? 'تم  الحصول على الغرفه بنجاح.' : ' sucessfull get rom message.';
            return response()->json(['key'=>'sucess','value'=>'1',"rom"=>$rom->id,"message"=>$this->responseMessage($rom), 'msg'=>$msg]);
        }
        else{
            foreach ((array)$validator->errors() as $key => $value){
                foreach ($value as $msg){
                    return response()->json(['key' => 'fail','value' => 0, 'msg' => $msg[0]]);
                }
            }
        }
    }
}
