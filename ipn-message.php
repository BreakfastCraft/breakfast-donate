<?php
namespace BreakfastCraft;

class IPNMessage
{

    public $ipn;
    public $sketchyIPN;
    public $message_status;
    public $paypalURL;
    
    public $correct_sender;
    public $mode;
    public $filename;
    public $logfile;
    public $ipnError;

    public function __construct($ipn)
    {
        $this->ipn = $ipn;

        //read config file
        $config = json_decode(file_get_contents('config.json'));
        $this->correct_sender = $config->correct_sender;
        $this->mode = $config->mode;
        $this->filename = $config->message_file;
        $this->logfile = $config->errorlog;
        
        if ($this->mode != 'sandbox') {
            $this->paypalURL = "https://www.paypal.com/cgi-bin/webscr";
        } else {
            $this->paypalURL = "https://www.sandbox.paypal.com/cgi-bin/webscr";
        }
    }

    public function isIPNValid()
    {
        //Rebuild POST Request and send back to paypal
        $this->sketchyIPN = 'cmd=_notify-validate'; //prepend validation command
        
        foreach ($this->ipn as $key => $value) {
            $value = urlencode(stripslashes($value));
            $this->sketchyIPN .= "&$key=$value";
        }

        $this->message_status = $this->postIPN();


        if ($this->message_status) {
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

        // if ($this->mode == 'sandbox') {
             curl_setopt($ch, CURLOPT_HEADER, 1);
             curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
        // }

        curl_setopt($ch, CURLOPT_CONNNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));

        $response = curl_exec($ch);
        if (curl_errno($ch) != 0) {
            $this->ipnError = "Can't connect to PayPal to validate IPN: " . curl_error($ch);
            curl_close($ch);
            exit;
        } else {

            list($headers, $response) = explode("\r\n\r\n", $response, 2);
            curl_close($ch);
        }
        

        if (strcmp($response, "VERIFIED") == 0) {
            return true;
        }

        $this->ipnError = "IPNMessage Not verified. Response: " . $response;
        return false;
    }

    public function writeMessage()
    {
        $message = array(
            'amount'    => $this->ipn['mc_gross'],
            'date'      => $this->ipn['payment_date'],
            'status'    => $this->ipn['payment_status'],
            'ign'       => $this->ipn['custom'],
            'rec_email' => $this->ipn['receiver_email'],
            'test_ipn'  => $this->ipn['test_ipn']
        );

        
        //Check for JSON file
        if (file_exists($this->filename)) {
            
            //if exists read file into array
            $json = json_decode(file_get_contents($this->filename), true);
            
            
            // Verify that ipn is from the correct sender and that the payment is complete
            if ($message['rec_email'] == $this->correct_sender && $message['status'] == 'complete') {
                //add $message to array
                array_push($json, $message);
            }

        } else {
            // create file
            try {
                $fh = fopen($this->filename, 'w');
                fclose($fh);
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
