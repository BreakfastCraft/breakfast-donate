<?php
namespace BreakfastCraft;

class IPNMessage
{

    private $ipn;
    private $sketchyIPN;
    private $paypalURL = 'ssl://www.sandbox.paypal.com';
    private $mode;
    private $filename = 'messages.json';

    public function __construct($ipn, $mode = 'sandbox')
    {
        $this->ipn = $ipn;
        
        if ($mode != 'sandbox') {
            $this->paypalURL = 'ssl://www.paypal.com';
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

        $message_status = $this->postIPN();

        if (strcmp($message_status, "VERIFIED") == 0) {
            return true;
        }
        return false;

    }

    private function postIPN()
    {
        // POST validation request to PayPal
        $header = "POST /cgi-bin/webscr HTTP/1.1\r\n";
        $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $header .= "Content-Length: " . strlen($this->sketchyIPN) . "\r\n\r\n";

        $post = fsockopen($this->paypalURL, 443, $errno, $errstr, 30);
        fputs($post, $header . $this->sketchyIPN);

        while (!feof($post)) {
            $response = fgets($post, 1024);
            fclose($post);
            return $response;
        }
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

        echo "Check File";
        //Check for JSON file
        if (file_exists($this->filename)) {
            echo "Open/Read File";
            //if exists read file into array
            $json = json_decode(file_get_contents($this->filename), true);
            
            //add $message to array
            echo "Add data to array";
            array_push($json, $message);

        } else {
            // create file
            echo "Create File";
            try {
                $fh = fopen($this->filename, 'w');
                $fclose($fh);
            } catch (Exception $e) {
                echo "Error Writing File: " . $e;
            }
            

            // create $json array
            echo "Build Array";
            $json[0] = $message;
        }

        // Write $json to file
        echo "Write File";
        file_put_contents($this->filename, json_encode($json));

    }
}
