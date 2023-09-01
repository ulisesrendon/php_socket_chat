<?php
require '../vendor/autoload.php';

use Dotenv\Dotenv;

(Dotenv::createImmutable(__DIR__.'/..'))->load();
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="main.css">
    <title>El farmachat</title>
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

    <script>
        window.SERVER_DOMAIN = "<?php echo $_ENV['APP_WS_SERVER_DOMAIN'] ?>";
        window.SERVER_PORT = "<?php echo $_ENV['APP_PORT'] ?>";
    </script>
    <script src="main.js" type="text/javascript"></script>
</body>

</html>
