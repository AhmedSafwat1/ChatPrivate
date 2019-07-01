var db      = require("./database");
class User
{
    constructor()
    {
        this.db = db;
    }
    async updateStatus(userId,status)
    {
         return  await db.execute("update users set online=? where id = ? ",[status,userId]);
    }
    async saveMessage(sender,reciver,msg,status,rom)
    {
        return  await db.execute("insert into messages(sender_id,reciever_id,message,chatRoom_id,status) VALUES(?,?,?,?,?)  ",[sender,reciver,msg,rom,status]);
    }
}
module.exports = User;