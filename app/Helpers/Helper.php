<?php
use App\ChatRoom;
//get chatRom
function getChatRom($sender_id, $reciver_id, $flag = 1, $status=1)
{
    $Rom =ChatRoom::where("status",$status)
        ->whereIn('first',[$sender_id,$reciver_id])
        ->whereIn("second",[$sender_id,$reciver_id])
        ->latest()->first();
//    dd($Rom);
    if(!$Rom && $flag)
    {
        $Rom                = new ChatRoom;
        $Rom["first"]       = $sender_id;
        $Rom["second"]      = $reciver_id;
        $Rom->save();
    }
    return $Rom;
}
?>