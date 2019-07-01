const User = require("./util/helper")
class Socket
{
    constructor(socket)
    {
        this.io          = socket
        this.clientsIds  = []
        this.status  = ""
        this.User    = new User
    }
    // io config socket id be constant user_id
    ioConfig()
    {
        this.io.use((socket, next)=>{
            if(socket.handshake.query.myfreind != "" && socket.handshake.query.myfreind != undefined)
            {
                socket["myfriend"] =socket.handshake.query.myfreind.split(",")
            }
            else
            {
                socket["myfriend"] = []
            }
            if(socket.handshake.query.status != "" && socket.handshake.query.status != undefined)
            {
                socket["status"] =socket.handshake.query.status
            }
            else
            {
                socket["status"] = "online"
            }
            if(socket.handshake.query.username != "" && socket.handshake.query.username != undefined)
            {
                socket["username"] =socket.handshake.query.username
            }
            else
            {
                socket["username"] = "Anonmouse"

            }

            socket["id"]  = socket.handshake.query.user_id;
            console.log(socket.myfriend);
            next()
        })
    }
    // socket connection new user connect =========================
    socketConnection()
    {
        this.ioConfig()
        this.io.on("connection", async (socket)=>{
            this.clientsIds = Object.keys(this.io.sockets.connected)
            console.log("new user login id => "+socket.id)
            //check friend online
            this.chekOnline(socket)
            //change status
            this.ChangeStatus(socket)
            // // send message
            this.SendMessage(socket)
            // procate rtiting
            this.PrivatBroadCast(socket)
            // ============
            this.sockitDisconnect(socket); //disconnect
        })

    }
    // socket disconnect user disconnect
    sockitDisconnect(socket)
    {
        socket.on("disconnect",async (data)=>{
            console.log("user disonnect"+data)
            await  this.User.updateStatus(socket.id, socket.status).catch(e=>console.log(e))
            socket.myfriend.forEach((uid)=>{
                if(this.clientsIds.indexOf(uid.toString()) !=- 1)
                    socket.broadcast.to(uid).emit("offline_user",socket.id)
            })

            // offline user
            let index = this.clientsIds.indexOf(socket.id);
            this.clientsIds.splice(index, 1)
            socket.broadcast.emit("offline_user",socket.id)
            socket.disconnect()
        })
    }
    // chek if freind online or no
    chekOnline(socket)
    {
        socket.on("check_online",(uid)=>{
            let status = "online"
            if(this.clientsIds.indexOf(uid.toString()) !=- 1)
            {
                status = "online"
                socket.broadcast.to(uid).emit("is_online",{
                    status:socket.status,
                    uid:socket.id
                })
                console.log("hi"+socket.status)
                status = this.io.sockets.connected[uid].status
            }
            else
            {
                status = "offline"
            }

            socket.emit("is_online",{
                status,
                uid
            })
        })
    }
    // chnage status
    ChangeStatus(socket)
    {
        socket.on("change-status",async (status)=>{

            try {

                await  this.User.updateStatus(socket.id, status)
                socket.status = status
                socket.myfriend.forEach((uid)=> {
                    if (this.clientsIds.indexOf(uid.toString()) != -1) {
                        socket.broadcast.to(uid).emit("is_online", {status, uid: socket.id})
                    }
                })
            }
            catch (e) {
                socket.emit("error_message",{
                    code   : e.code,
                    message:e.message,
                    type   : "error in connection database",
                    sql    : e.sqlMessage,
                    errno  :e.errno
                })
            }
        })
    }
    //send message
    SendMessage(socket)
    {
        socket.on("sendmessages", async (data)=>{
            try {
                let status = 0;
                if (this.clientsIds.indexOf(data.reciver_id) != -1) {
                    status = 1;
                    this.io.sockets.connected[data.reciver_id].emit("reciveMessage", {
                        ...data,
                        name: socket.username,
                        sender_id: socket.id
                    })
                }
                this.User.saveMessage(socket.id, data.reciver_id, data.msg, status, data.rom)
                socket.emit("reciveMyMessage", {...data, name: socket.username, sender_id: socket.id})
            }catch (e) {
                socket.emit("error_message",{
                    code   : e.code,
                    message:e.message,
                    type   : "error in connection database",
                    sql    : e.sqlMessage,
                    errno  :e.errno
                })
            }
        })

    }
    // private broad cast
    PrivatBroadCast(socket)
    {
        socket.on("privat_broad",data=>{
            if(this.clientsIds.indexOf(data.uid.toString()) !=- 1)
            {
                data.to = socket.id
                this.io.sockets.connected[data.uid].emit("writeing",data)
            }
        })
    }



}
module.exports = Socket;