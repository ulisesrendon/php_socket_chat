<?php

namespace Chatapp;

require __DIR__.'/vendor/autoload.php';

use Chatapp\Shared\LogHelper;
use Illuminate\Database\Capsule\Manager as Capsule;
use Dotenv\Dotenv;

(Dotenv::createImmutable(__DIR__))->load();

// $capsule = new Capsule;
// $capsule->addConnection([
//     "driver" => $_ENV['DB_CONNECTION'],
//     "host" => $_ENV['DB_HOST'],
//     "port" => $_ENV['DB_PORT'],
//     "database" => $_ENV['DB_DATABASE'],
//     "username" => $_ENV['DB_USERNAME'],
//     "password" => $_ENV['DB_PASSWORD'],
// ]);
// $capsule->setAsGlobal();
// $capsule->bootEloquent();

/* Allow the script to hang around waiting for connections. */
set_time_limit(0);

/* Turn on implicit output flushing so we see what comes in. */
ob_implicit_flush();

$host = 'localhost'; //host
$location = $_ENV['APP_PORT']; //domain name
$port = $_ENV['APP_PORT']; //port
$null = null;

//Create TCP/IP sream socket
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
//reuseable port
socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);

//bind socket to specified host
socket_bind($socket, 0, $port);

//listen to port
socket_listen($socket);

//create & add listning socket to the list
$clients = [$socket];
$rooms = [];
$roomsPerSID = [];

//start endless loop, so that our script doesn't stop
while (true) {
	//manage multipal connections
	$changed = $clients;
	//returns the socket resources in $changed array
	socket_select($changed, $null, $null, 0, 10);

	//check for new socket
	if (in_array($socket, $changed)) {
		$socket_new = socket_accept($socket); //accpet new socket
		$clients[] = $socket_new; //add socket to client array

		$header = socket_read($socket_new, 5242880); //read data sent by the socket
		perform_handshaking($header, $socket_new, $host, $port, $location); //perform websocket handshake

		//socket_getpeername($socket_new, $ip); //get ip address of connected socket

		//make room for new socket
		unset($changed[array_search($socket, $changed)]);
	}

	//loop through all connected sockets
	foreach ($changed as $changed_socket) {

        $sid = array_search($changed_socket, $clients);

		//check for any incomming data
		while(socket_recv($changed_socket, $buf, 5242880, 0) >= 1){
			$tst_msg = json_decode(unmask($buf), true);
            // Group clients per room / disconnect if no room
            if( !isset($tst_msg['room']) ){
                socket_close($clients[$sid]);
                unset($clients[$sid]);
                break 2;
            }
            if( !isset($rooms[$tst_msg['room']][$sid]) ){
                $rooms[$tst_msg['room']][$sid] = $sid;
                $roomsPerSID[$sid][] = $tst_msg['room'];
            }

			send_room_message($tst_msg);
			break 2;
		}

		$buf = @socket_read($changed_socket, 5242880, PHP_NORMAL_READ);
        // check for disconnected client
		if ($buf === false) {
            //socket_getpeername($changed_socket, $ip);
            // remove client from $rooms array
            if(isset($roomsPerSID[$sid])) {
				foreach ($roomsPerSID[$sid] as $room_id) unset($rooms[$room_id][$sid]);
				unset($roomsPerSID[$sid]);
			}
			// remove client from $clients array
			unset($clients[$sid]);
		}
	}
}
// close the listening socket
socket_close($socket);

//Unmask incoming framed message
function unmask($text)
{
	$length = ord($text[1]) & 127;
	if($length == 126) {
		$masks = substr($text, 4, 4);
		$data = substr($text, 8);
	}
	elseif($length == 127) {
		$masks = substr($text, 10, 4);
		$data = substr($text, 14);
	}
	else {
		$masks = substr($text, 2, 4);
		$data = substr($text, 6);
	}
	$text = "";
	for ($i = 0; $i < strlen($data); ++$i) {
		$text .= $data[$i] ^ $masks[$i%4];
	}
	return $text;
}

//Encode message for transfer to client.
function mask($text)
{
	$b1 = 0x80 | (0x1 & 0x0f);
	$length = strlen($text);

	if($length <= 125) $header = pack('CC', $b1, $length);
	elseif($length > 125 && $length < 65536) $header = pack('CCn', $b1, 126, $length);
	elseif($length >= 65536) $header = pack('CCNN', $b1, 127, $length);
	return $header.$text;
}

//handshake new client.
function perform_handshaking($receved_header,$client_conn, $host, $port, $location)
{
	$headers = [];
	$lines = preg_split("/\r\n/", $receved_header);
	foreach($lines as $line){
		$line = chop($line);
		if(preg_match('/\A(\S+): (.*)\z/', $line, $matches)) $headers[$matches[1]] = $matches[2];
	}

	$secKey = $headers['Sec-WebSocket-Key'] ?? 1;
	$secAccept = base64_encode(pack('H*', sha1($secKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
	//hand shaking header
	$upgrade  = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
	"Upgrade: websocket\r\n" .
	"Connection: Upgrade\r\n" .
	"WebSocket-Origin: $host\r\n" .
	"WebSocket-Location: $location\r\n".
	"Sec-WebSocket-Accept:$secAccept\r\n\r\n";
	socket_write($client_conn,$upgrade,strlen($upgrade));
}

function send_room_message(array $data)
{
    global $rooms;
    global $clients;

	$data['type'] ??= '';

	if ($data['type'] != 'keepalive'){
		$msg = mask(json_encode($data));
		foreach ($rooms[$data['room']] as $sid) {
			$total = count($rooms[$data['room']]);
			echo "Escribiendo en el room {$data['room']}, con {$total} participantes\n";
			if (isset($clients[$sid])) @socket_write($clients[$sid], $msg, strlen($msg));
		}
	}

    return true;
}
