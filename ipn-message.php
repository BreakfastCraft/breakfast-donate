<?php
namespace BreakfastCraft;

class IPNMessage
{

    //TODO: Switch these back to private after everything works

    private $ipn;
    private $sketchyIPN;
    private $message_status;
    private $paypalURL;
    private $mode;
    private $filename = 'messages.json';
    private $logfile = 'ipn.log';

    public function __construct($ipn, $mode = 'sandbox')
    {
        $this->ipn = $ipn;
        
        if ($mode != 'sandbox') {
            $this->paypalURL = "https://www.paypal.com/cgi-bin/webscr";
        } else {
            $this->paypalURL = "https://www.sandbox.paypal.com/cgi-bin/webscr";
        }

        $this->mode = $mode;
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
        $ch = curl_init($this->paypalURL);
        
        if ($ch == false) {
            return false;
        }

        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->sketchyIPN);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);

        if ($this->mode == 'sandbox') {
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
        }

        curl_setopt($ch, CURLOPT_CONNNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, arra('Connection: Close'));

        $response = curl_exec($ch);

        curl_close($ch);

        if (strcmp($response, "VERIFIED") === 0) {
            return true;
        }

        return false;

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
