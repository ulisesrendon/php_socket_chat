Running:


1. Running the socket process

php -q server.php


For docker:

docker compose exec php-fpm php -q /var/www/html/chat/server.php


3. Go website

Visit http://chat.localhost


-------------------------------------------------------------------

sudo nano /etc/systemd/system/chat.service

----------------------------------------------------------------

[Unit]
Description=WS Chat PHP Server
 
[Service]
ExecStart=sudo php -q /var/www/chat/server.php &
 
[Install]
WantedBy=default.target

-----------------------------------------------------------------

chmod 644 /etc/systemd/system/chat.service

systemctl enable chat.service
