<?php
require('ipn-message.php');

//Process IPN
$message = new BreakfastCraft\IPNMessage($_POST);

if ($message->isIPNValid()) {
    $message->writeMessage();
} else {
    $error = array(
        'error'          => $message->ipnError,
        'ipn'            => $message->ipn,
	    'msgStatus'      => $message->message_status,
	    'paypalurl'      => $message->paypalURL,
	    'correct_sender' => $message->correct_sender,
	    'mode'           => $message->mode,
	    'filename'       => $message->filename,
	    'logfile'        => $message->logfile
    );

    file_put_contents('messages.json', json_encode($error));
}
