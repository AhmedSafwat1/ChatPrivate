 const express      = require("express"),
        http        = require("http"),
        socket      = require("socket.io"),
        socketServer= require("./socket") ;
 class Server
 {
     constructor()
     {
         this.port  = "8890"
         this.host  = "localhost"
         this.app   = express()
         this.http  = http.Server(this.app)
         this.socket=socket(this.http)
     }
     runServer()
     {


         this.http.listen(this.port,this.host,()=>{
             console.log(`server runing in http://${this.host}:${this.port}`)
         })
         new socketServer(this.socket).socketConnection();
     }
 }

 const app = new Server();
 app.runServer();