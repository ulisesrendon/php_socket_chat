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

-----------------------------------------------------------------

Add cronjob to restart service to avoid some problems

0 0 * * * sudo service chat restart

------------------------------------------------------------------

Configure port for web socket listening

sudo ufw allow 7000

-------------------------------------------------------------

```
map $http_upgrade $connection_upgrade {
    default upgrade;
    '' close;
}
server {

    # Log files for Debugging
    access_log /var/log/nginx/chat.mydomain.com-access.log;
    error_log /var/log/nginx/chat.mydomain.com-error.log;

    root /var/www/chat/public;
    index index.php index.html index.htm;

    # Your Domain Name
    server_name chat.mydomain.com;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP-FPM Configuration Nginx
    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_read_timeout 500;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location /websocket {
        proxy_pass http://chat.mydomain.com:7000;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection $connection_upgrade;
        proxy_set_header Host $host;
    }



    listen 443 ssl; # managed by Certbot
    ssl_certificate /etc/letsencrypt/live/chat.mydomain.com/fullchain.pem; # managed by Certbot
    ssl_certificate_key /etc/letsencrypt/live/chat.mydomain.com/privkey.pem; # managed by Certbot
    include /etc/letsencrypt/options-ssl-nginx.conf; # managed by Certbot
    ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem; # managed by Certbot

}

server {

    server_name chat.mydomain.com;
    listen 80;

    access_log /var/log/nginx/chat-access.log;
    error_log /var/log/nginx/chat-error.log;

    root /var/www/chat/public;
    index index.php index.html index.htm;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP-FPM Configuration Nginx
    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_read_timeout 500;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

}

```
