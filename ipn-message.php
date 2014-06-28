<?php
namespace BreakfastCraft;

class IPNMessage
{

    public $ipn;
    public $sketchyIPN;
    public $message_status;
    public $fsockerr = array(
        'errno'  => '',
        'errstr' => ''
    );

    private $paypalURL = 'www.sandbox.paypal.com';
    private $mode;
    private $filename = 'messages.json';

    public function __construct($ipn, $mode = 'sandbox')
    {
        $this->ipn = $ipn;
        
        if ($mode != 'sandbox') {
            $this->paypalURL = 'www.paypal.com';
        }

    }

    public function isIPNValid()
    {
        //Rebuild POST Request and send back to paypal
        $this->sketchyIPN = 'cmd=_notify-validate'; //prepend validation command
        echo 'in valid 1';
        foreach ($this->ipn as $key => $value) {
            $value = urlencode(stripslashes($value));
            $this->sketchyIPN .= "&$key=$value";
        }

        $this->message_status = $this->postIPN();

        if (strcmp($this->message_status, "VERIFIED") == 0) {
            return true;
        }
        return false;

    }

    private function postIPN()
    {
        // POST validation request to PayPal
        $header = "POST /cgi-bin/webscr HTTP/1.1\r\n";
        $header .= "Host: " . $this->paypalURL ."\r\n";
        $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $header .= "Content-Length: " . strlen($this->sketchyIPN) . "\r\n";
        $header .= "Connection: Close\r\n\r\n";

        $post = fsockopen("ssl://" . $this->paypalURL . "/", 443, $errno, $errstr, 30);
        fputs($post, $header . $this->sketchyIPN);

        
        $response = stream_get_contents($post, 1024);
        return $response;
        
    }

    public function writeMessage()
    {
        $message = array(
            'amount'    => $this->ipn['mc_gross'],
            'date'      => $this->ipn['payment_date'],
            'status'    => $this->ipn['payment_status'],
            'custom'    => $this->ipn['custom'],
            'rec_email' => $this->ipn['receiver_email'],
            'test_ipn'  => $this->ipn['test_ipn']
        );

        
        //Check for JSON file
        if (file_exists($this->filename)) {
            
            //if exists read file into array
            $json = json_decode(file_get_contents($this->filename), true);
            
            //add $message to array
            array_push($json, $message);

        } else {
            // create file
            try {
                $fh = fopen($this->filename, 'w');
                $fclose($fh);
            } catch (Exception $e) {
                echo "Error Writing File: " . $e;
            }
            

            // create $json array
            $json[0] = $message;
        }

        // Write $json to file
        
        file_put_contents($this->filename, json_encode($json));

    }
}
