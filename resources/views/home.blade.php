@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="controler-status ">
                <h1>Controller Status</h1>
                <p><label>Online</label> <input type="radio" name="status" @if(auth()->user()->online == "online")  checked @endif value="online"/> </p>
                <p><label>Offline</label> <input type="radio" name="status" @if(auth()->user()->online == "offline") checked @endif value="offline"/> </p>
                <p><label>Busy</label> <input type="radio" name="status" @if(auth()->user()->online == "bys") checked @endif value="bys"/> </p>
                <p><label>Dnd</label> <input type="radio" name="status" @if(auth()->user()->online == "dnd")checked @endif  value="dnd"/> </p>
            </div>
            <div id="chat-sidebar">

                @foreach(App\User::all() as $user)
                 @if(auth()->user()->id != $user->id)
                    <?php
                        $number = 100;
                    ?>
                    <div id="sidebar-user-box" class="{{$user->id}}" >
                        <img src="{{asset('image/user.png')}}" />
                        <span id="slider-username">{{$user->name}} </span>
                        <span class="user_status {{$user->online}}" id="user_{{$user->id}}" data-id="{{$user->id}}">&nbsp;</span>
                    </div>
                    <?php $number++ ?>
                 @endif
               @endforeach

            </div>
            <button id="close" class="btn btn-danger">close</button>
            <video id="myvedio"></video>

        </div>
    </div>
</div>
@endsection
@section('scripts')
    <script>
       window.onload = function () {
           var id   = '{{auth()->user()->id}}',
               name = "{{auth()->user()->name}}";
           var myfreind = []
           $(".user_status").each(function(){
               let uid = $(this).data("id")
               myfreind.push(uid)
           })
           // ajex function ==============
           function changeStataus(status)
           {
               $.ajax({
                   type:'get',
                   url:'/api/changeStatus',
                   data:{status:status, user_id:id},
                   success:function(data){
                       if(data.value == "1")
                       {
                           socket.emit("change-status",status)
                       }
                       else
                       {
                           console.log(data)
                       }

                   }
               });
           }
           // get Rom
           function getRom(sender,reciver,username,msgNew=0)
           {

               $.ajax({
                   type:'post',
                   url:'/api/getRom',
                   data:{sender_id:sender, reciever_id:reciver},
                   success:function(data){
                       if(data.value == "1")
                       {
                           if(msgNew !=0)
                           {
                               data.message.push({sender_id:sender, reciever_id:reciver,msg:msgNew});
                               private_chat(reciver, username,data.rom,data.message)
                           }
                           else
                           {
                               private_chat(reciver, username,data.rom,data.message)
                           }

                           console.log(data);
                       }
                       else
                       {
                           console.log(data)
                       }

                   }
               });
           }
           //=======================
           // change status
           var status = "{{auth()->user()->online}}";
           var socket = io.connect('http://localhost:8890',{
               query:`user_id=${id}&username=${name}&status=${status}&myfreind=${myfreind.join(",")}`,
           });
           //connect
           socket.on('connect',  ()=>{
               console.log("conenct")
               //chek freiend online
               $(".user_status").each(function(){
                   let uid = $(this).data("id")
                   socket.emit("check_online",uid);
               })
               //========
               //isonline
               socket.on("is_online",(data)=>{
                   $(`#user_${data.uid}`).removeAttr("class").attr("class",`user_status ${data.status}`)
               })
               //=========
               //online
               socket.on("online_user", (id)=>{
                   $(`#user_${id}`).removeAttr("class").attr("class","user_status online");
               })
               //user offline
               socket.on("offline_user", (id)=>{
                   $(`#user_${id}`).removeAttr("class").attr("class","user_status offline");
               })
               //chang status
               $(document).on("change","input[name='status']",function () {
                   let x  = $(this).val()
                   socket.emit("change-status",x)
                   // changeStataus(x)
               })
           });
           // error event
           socket.on("error_message", (data)=>{
               let msg = `
                    ------------ error message-----
                    message : ${data.type}
                    ==============================
                      code: ${data.errno}
               `
               alert(msg)
           })
           // revivee message
           socket.on("reciveMessage",(data)=>{
               console.log(data)
               $(`.chat_${data.sender_id} .broadCast `).html("")
               console.log($(`.chat_${data.sender_id}`));
               if($(`.chat_`+data.sender_id).length == 0)
               {
                   getRom(id,data.sender_id,data.name)
                   // private_chat(data.sender_id,data.name)
               }
               else
               {
                   console.log("hi");
                   console.log(data+"kkk")
                   $('<div class="msg-right">'+data.msg+'</div>').insertBefore('[rel="'+data.sender_id+'"] .msg_push');
                   $(`.chat_${data.sender_id} .msg_body`).scrollTop($(`.chat_${data.sender_id} .msg_body`)[0].scrollHeight);
               }

           })
           //my message
           socket.on("reciveMyMessage",(data)=>{
               if($(`.chat_${data.reciver_id}`).length == 0)
               {
                   private_chat(data.reciver_id,data.name)
               }
               $('<div class="msg-left">'+data.msg+'</div>').insertBefore('[rel="'+data.reciver_id+'"] .msg_push');
               $(`.chat_${data.reciver_id} .msg_body`).scrollTop($(`.chat_${data.reciver_id} .msg_body`)[0].scrollHeight);
           })
           //==========
           function private_chat(userID, username,rom=0,messages=[])
           {
               if ($.inArray(userID, arr) != -1)
               {
                   arr.splice($.inArray(userID, arr), 1);
               }

               arr.unshift(userID);
               chatPopup =  '<div data-rom="'+rom+'"class="msg_box chat_'+userID +' " style="right:270px" rel="'+ userID+'">'+
                   '<div class="msg_head">'+username +
                   '<div class="close">x</div> </div>'+
                   '<div class="msg_wrap"> <div class="msg_body">	<div class="msg_push"></div> </div>'+
                   '<div class="msg_footer"><span class="broadCast"></span><textarea class="msg_input" rows="4"></textarea></div> 	</div> 	</div>' ;
               $("body").append(  chatPopup  );
               messages.forEach((msg)=> {
                       if (msg.sender_id == userID) {
                           $('<div class="msg-right">'+msg.msg+'</div>').insertBefore('[rel="'+userID+'"] .msg_push');
                       }
                       else
                       {
                           $('<div class="msg-left">'+msg.msg+'</div>').insertBefore('[rel="'+userID+'"] .msg_push');
                       }
                   }
               )
               $(`.chat_${userID} .msg_body`).scrollTop($(`.chat_${userID} .msg_body`)[0].scrollHeight);
               displayChatBox();
           }
           //==============================================================================
           var arr = []; // List of users
           //sockit

           //===================================
           $(document).on('click', '.msg_head', function() {
               var chatbox = $(this).parents().attr("rel") ;
               $('[rel="'+chatbox+'"] .msg_wrap').slideToggle('slow');
               return false;
           });
           $(document).on('click', '.close', function() {
               var chatbox = $(this).parents().parents().attr("rel") ;
               $('[rel="'+chatbox+'"]').hide();
               arr.splice($.inArray(chatbox, arr), 1);
               displayChatBox();
               return false;
           });
           $(document).on('click', '#sidebar-user-box', function() {
               var userID = $(this).attr("class");
               var username = $(this).children().text() ;

               if($(`.chat_${userID}`).length == 0)
               {
                   // */private_chat(userID, username)
                   getRom(id,userID,username);
               }else {
                   $('[rel="'+userID+'"] .msg_wrap').slideToggle('slow');
               }

           });


           $(document).on('keypress', 'textarea' , function(e) {
               var chatbox = $(this).parents().parents().parents().attr("rel") ;
               if (e.keyCode == 13 ) {
                   var msg = $(this).val();
                   $(this).val('');
                   if(msg.trim().length != 0){

                       var romId =  $(this).parents().parents().parents().data("rom")
                       socket.emit("sendmessages",{
                           reciver_id:chatbox,
                           msg    :msg,
                           rom    :romId
                       })
                       console.log("send")
                   }
               }
               else
               {
                   socket.emit("privat_broad",{
                       uid:chatbox,
                       "name":name
                   })
               }
           });


        // writing
           socket.on("writeing" , data=>{
               // image =
              let img = "{{asset("image/tenor.gif")}}";
              let myImage = `<img src='${img}' width="20" height="20"> ${data.name} `
               $(`.chat_${data.to} .broadCast `).html(myImage)
                setTimeout(function () {
                    $(`.chat_${data.to} .broadCast `).html("")
                },5000)
           })
        //  ===============
           function displayChatBox(){
               i = 270 ; // start position
               j = 260;  //next position

               $.each( arr, function( index, value ) {
                   if(index < 4){
                       $('[rel="'+value+'"]').css("right",i);
                       $('[rel="'+value+'"]').show();
                       i = i+j;
                   }
                   else{
                       $('[rel="'+value+'"]').hide();
                   }
               });
           }

       }
       // // vedio chat
       // function getPemision()
       // {
       //     return new Promise((res,re)=>{
       //         const  constraints = { video: {
       //                 width: 640,
       //                 height: 480,
       //                 facingMode: "environment"
       //             }, audio: true }
       //         // navigator.mediaDevices.getUserMedia = (navigator.getUserMedia ||
       //         //     navigator.webkitGetUserMedia ||
       //         //     navigator.mozGetUserMedia ||
       //         //     navigator.msGetUserMedia);
       //         navigator.mediaDevices.getUserMedia(constraints).then(stream => res(stream))
       //             .catch(err=> {throw new Error("unable to featch stream"+err)})
       //
       //     })
       //
       // }
       // getPemision().then((s)=>{
       //     let v = document.getElementById("myvedio");
       //     try {
       //          v.srcObject = s;
       //         document.getElementById('close').addEventListener('click', function () {
       //             stopStream(s);
       //         });
       //     }catch (e) {
       //
       //         v.src = URL.createObjectURL(s);
       //     }
       //
       //     v.play()
       // })
       // function stopStream(stream) {
       //     console.log('stop called');
       //     stream.getVideoTracks().forEach(function (track) {
       //         track.stop();
       //     });}
    </script>
@endsection