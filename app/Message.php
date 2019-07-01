<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    //
    protected $fillable = ['sender_id',"reciever_id","message"];
    public function Sender()
    {
        return $this->belongsTo("App\User","sender_id");
    }
    public function Reciever()
    {
        return $this->belongsTo("App\User","reciever_id");
    }
}
