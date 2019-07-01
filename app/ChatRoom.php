<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ChatRoom extends Model
{
    //
    public function First()
    {
        return $this->belongsTo("App\User","first");
    }
    public function Second()
    {
        return $this->belongsTo("App\User","second");
    }
    //
    public  function Messages()
    {
        return $this->hasMany("App\Message", "chatRoom_id");
    }

}
