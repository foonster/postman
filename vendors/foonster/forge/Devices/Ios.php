<?php
/**
 * @author Nicolas Colbert <nicolas@foonster.com>
 * @copyright 2005 Foonster Technology
 */
class Ios
{
    /**
     * @ignore
     */
    private $vars = array(); 
    private $apnsHost = 'gateway.push.apple.com';
    private $apnsPort = 2195;
    private $apnsCert = '';   
    private $apns = '';   
    /**
     * @ignore
     */
    public function __construct()
    {

    }
    /**
     * @ignore
     */
    public function __destruct()
    {
    }
    /**
     * @ignore
     */
    public function __get($index)
    {
        return $this->vars[$index];
    }
    /**
     * @ignore
     */
    public function __set($index, $value)
    {
        $this->vars[$index] = $value;
    }
    /**
     * [connect description]
     * @return [type] [description]
     */
    private function connect()
    {
        $this->streamContext = stream_context_create();
        stream_context_set_option($this->streamContext, 'ssl', 'local_cert', 
            $this->apnsCert);
        $this->apns = stream_socket_client('ssl://' . $this->apnsHost . ':' . $this->apnsPort, $this->error, $this->errorString, 2, STREAM_CLIENT_CONNECT, $this->streamContext);        

        try {
            if (!$this->apns) {
                throw new Exception("Failed to connect $err $errstr" . PHP_EOL);
            }       
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
    /**
     * [getError description]
     * @return [type] [description]
     */
    public function getError()
    {
        return $this->error . ' : ' . $this->errorString;
    }
    /**
     * [sendMessage description]
     * @return [type] [description]
     */
    public function sendMessage()
    {
        $this->connect();

        return true;

    }    
    /**
     * [set the message]
     * @param string $message [the content of the message to send]
     */
    public function setMessage($message = '')
    {
        $this->message = $message;
        return $this;
    }
    /**
     * [set the path to the certificaiton file]
     * @param string $file [file path to the certification file]
     */
    public function setCertFile($file) 
    {
        $this->apnsCert = $file;
        return $this;
    }    
    /**
     * [set the port number]
     * @param string $port [port number]
     */
    public function setPort($port = 2195)
    {
        $this->apnsPort = $port;
        return $this;
    }
    /**
     * [set the Apple.com server to post ]
     * @param string $server [the domain to post the transaction]
     */
    public function setServer($server = 'PROD')
    {
        if (strtoupper(trim($server)) == 'SANDBOX') { 
            $this->apnsHost = 'gateway.sandbox.push.apple.com';
        } else { 
            $this->apnsHost = 'gateway.push.apple.com';
        }
        return $this;
    }
}
