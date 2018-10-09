var socketio = io.connect('http://localhost:8080');

socketio.on("message_to_client", function(data) {
    document.getElementById("chatlog").innerHTML = ("<hr/>" +
    data['message'] + document.getElementById("chatlog").innerHTML);
});
//submit and send data to server via enter key
document.onkeydown = function(e){
    var keyCode = (window.event) ? e.which : e.keyCode;
    if(keyCode == 13){
        var msg = document.getElementById("message_input").value;
        socketio.emit("message_to_server", { message : msg});
        document.getElementById("message_input").value = '';
    }
};