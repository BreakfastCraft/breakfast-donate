<?php
require('ipn-message.php');

//Process IPN
$message = new BreakfastCraft\IPNMessage($_POST);

if ($message->isIPNValid()) {
    $message->writeMessage();
} else {
    $error = array(
        'error'      => 'Bad stuff has happend!',
    );
    file_put_contents('messages.json', json_encode($error));
}
