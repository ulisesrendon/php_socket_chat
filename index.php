<?php
require 'vendor/autoload.php';

use Dotenv\Dotenv;

(Dotenv::createImmutable(__DIR__))->load();
?>
<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style type="text/css">
        .chat-wrapper {
            font: bold 11px/normal 'lucida grande', tahoma, verdana, arial, sans-serif;
            background: #00a6bb;
            padding: 20px;
            margin: 20px auto;
            box-shadow: 2px 2px 2px 0px #00000017;
            max-width: 700px;
            min-width: 500px;
        }

        #message-box {
            width: 97%;
            display: inline-block;
            height: 300px;
            background: #fff;
            box-shadow: inset 0px 0px 2px #00000017;
            overflow: auto;
            padding: 10px;
        }

        .user-panel {
            margin-top: 10px;
        }

        input[type=text] {
            border: none;
            padding: 5px 5px;
            box-shadow: 2px 2px 2px #0000001c;
        }

        input[type=text]#name {
            width: 20%;
        }

        input[type=text]#message {
            width: 60%;
        }

        button#send-message {
            border: none;
            padding: 5px 15px;
            background: #11e0fb;
            box-shadow: 2px 2px 2px #0000001c;
        }
    </style>
</head>

<body>

    <div class="chat-wrapper">
        <div id="message-box"></div>
        <div class="user-panel">
            <input type="text" name="name" id="name" placeholder="Your Name" maxlength="15" />
            <input type="text" name="message" id="message" placeholder="Type your message here..." maxlength="100" />
            <button id="send-message">Send</button>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script language="javascript" type="text/javascript">
        //create a new WebSocket object.
        const msgBox = $('#message-box');
        const wsUri = "ws://<?php echo $_ENV['APP_WS_SERVER_DOMAIN'] ?>:<?php echo $_ENV['APP_PORT'] ?>/server.php";
        websocket = new WebSocket(wsUri);
        websocket.onopen = function(ev) {
            msgBox.append('<div class="system_msg" style="color:#bbbbbb">Connected! - Welcome to my the Chat room</div>');
        }
        websocket.onerror = function(ev) {
            msgBox.append('<div class="system_error">Error Occurred - ' + ev.data + '</div>');
        };
        websocket.onclose = function(ev) {
            msgBox.append('<div class="system_msg">Connection Closed</div>');
        };
        websocket.onmessage = function(ev) {
            const response = JSON.parse(ev.data);
            const res_type = response.type ?? 'usermsg'; //message type
            const user_message = response.message; //message text
            const user_name = response.name; //user name

            switch (res_type) {
                case 'usermsg':
                    msgBox.append('<div><span class="user_name" style="color:purple">' + user_name + '</span> : <span class="user_message">' + user_message + '</span></div>');
                    break;
                case 'system':
                    msgBox.append('<div style="color:#bbbbbb">' + user_message + '</div>');
                    break;
            }
            msgBox[0].scrollTop = msgBox[0].scrollHeight; //scroll message
        };

        //Message send button
        $('#send-message').click(function() {
            send_message();
        });

        //User hits enter key
        $("#message").on("keydown", function(event) {
            if (event.which == 13) {
                send_message();
            }
        });

        //Send message
        function send_message() {
            var message_input = $('#message'); //user message text
            var name_input = $('#name'); //user name

            if (message_input.val() == "") { //empty name?
                alert("Enter your Name please!");
                return;
            }
            if (name_input.val() == "") { //emtpy message?
                alert("Enter Some message Please!");
                return;
            }

            websocket.send(JSON.stringify({
                message: message_input.val(),
                name: name_input.val(),
                room: window.room ?? 1
            }));
            message_input.val(''); //reset message input
        }
    </script>
</body>

</html>
