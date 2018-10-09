// @todo 1.0: Setup this file for live chat to run on node js
//create websocket protocol via socket.io
var io = require('socket.io').listen(8080);
//send data to client
io.sockets.on('connection', function(socket) {
  socket.on('message_to_server', function(data) {
    io.sockets.emit("message_to_client",{ message: data["message"] });
  });
});
