<?php
/**
 * @author Nicolas Colbert <nicolas@foonster.com>
 * @copyright 2005 Foonster Technology
 */
use \SendGrid as SendGrid;
use PHPMailer\PHPMailer\PHPMailer as PHPMailer;
/**
 * A class that may be used to compose and send e-mail messages using 
 * the sendmail function within PHP/Server
 *                                                                    
 */
class Email
{
    /**
     * Indentifying text for module sending email.
     * 
     * @access private
     * @var string
     */
    //private $_mailer = 'ZEEKEE EMAIL MODULE 6.5.3';

    protected $vars = array();
    /**
     * [$isHtml description]
     * @var boolean
     */
    protected $isHtml = true;
    /**
     * [$_replyto description]
     * @var [type]
     */
    protected $_replyto;    
    /**
     * value used in the TO field.
     * 
     * @access private
     * @var string
     */
    protected $_to;    
    /**
     * value used in the FROM field.
     * 
     * @access private
     * @var string
     */
    protected $_from;
    /**
     * The common name associated with the email address.
     * 
     * @access private
     * @var string
     */
    protected $_fromname; 
    /**
     * sender field value, also can be added in _headers.
     * 
     * @access private
     * @var string
     */
    protected $_sender;
    /**
     * array of email addresses to be used in CC field
     * 
     * @access private
     * @var array
     */
    protected $_cc = array();
    /**
     * array of email addresses to be used in BCC field
     * 
     * @access private
     * @var array
     */
    protected $_bcc = array(); // blind cc field.
    /**
     * array of files to be added in file attachment function.
     * 
     * @access private
     * @var array
     */    
    protected $_attachments = array();
    /**
     * priority level assigned to email 1 to 10
     * 
     * @access private
     * @var integer
     */
    protected $_priority = 3; // priority level
    /**
     * array of values to be used in the headers section of the email.
     * 
     * @access private
     * @var array
     */
    protected $_headers;
    /**
     * @access private
     * @var boolean
     */
    protected $_lCheckDns = false; // DNS widget
    /**
     * @access private
     * @var string
     */
    protected $_html_msg; // html version of email
    /**
     * @access private
     * @var string
     */
    protected $_text_msg; // plain-text version of email
    /**
     * @access private
     * @var string
     */
    protected $_html_content_transfer_encoding = '7bit';
    /**
     * @access private
     * @var string
     */
    protected $_html_charset = 'utf-8'; // UTF-8 - iso-8859-1
    /**
     * @access private
     * @var string
     */
    protected $_text_content_transfer_encoding = '7bit';
    /**
     * @access private
     * @var string
     */
    protected $_text_charset = 'utf-8'; // UTF-8 - iso-8859-1

    /**
     * @access private
     * @var string
     */
    protected $_mailer = 'PHPMAILER';

    /**
     * @access private
     * @var string
     */
    protected $constants;

    /**
     * @access private
     * @var string
     */
    public $error;
    /**
     * class constructor
     * 
     * @param string $lDNSCheck TRUE|FALSE on if DNS check is required for various applicable functions.
     */
    public function __construct($lDNSCheck = 'false')
    {
        $this->checkDns($lDNSCheck);
    }
    /**
     *  @ingore
     */
    public function __destruct()
    {
    }

    /**
     *  @ingore
     */
    public function __set($index, $value)
    {
        $this->vars[ $index ] = $value;
    }

    /**
     *  @ingore
     */
    public function __get($index)
    {
        return $this->vars[ $index ];
    }
    /**
     * Attach various files/ file uploads/ file paths for mail message.
     * 
     * @param  array $aArray array containing all information about file to upload.
     * 
     * @return Email
     */
    public function addFileAttachment($aArray = null)
    {
        $this->_attachments[] = $aArray;        
        return $this;
    }

    /**    
     * Add a single address or multiple addresses to the bcc field.
     * 
     * strings with comma's "," or semi-colons ";" are split and 
     * each address verified and then added or not included in the final message.
     * 
     * @param  string $cEmail  the email address or string of email addresses to 
     * be added.
     * @return Email
    */
    public function addBlindCopyRecipient($cEmail = null)
    {
        if (strpos($cEmail, ',') > 0 || strpos($cEmail, ';') > 0) {
            $aSplit = preg_split('/[,|;]/', $cEmail);
            foreach ($aSplit as $cValue) {
                $cValue = strtolower(trim($cValue));
                $this->isAddressValid($cValue) ? $this->_bcc[] = $cValue : false;
            }
        } else {
            $this->isAddressValid($cEmail) ? $this->_bcc[] = $cEmail : false;
        }

        return $this;
    }

    /**
     * add value to CC list
     * 
     * strings with comma's "," or semi-colons ";" are split and each 
     * address verified and then added or not included in the final message.
     * 
     * @created 03/13/2014
     * @modified 03/13/2014
     * @param  string $cEmail string with email address to be added.
     *
     * @return Email
     */
    public function addCarbonCopyRecipient($cEmail = null)
    {
        if (strpos($cEmail, ',') > 0 || strpos($cEmail, ';') > 0) {
            $aSplit = preg_split('/[,|;]/' , $cEmail);
            foreach ($aSplit AS $cValue) {
                $this->isAddressValid ($cValue) ? $this->_cc[] = $cValue : false;
            }
        } else {
            $this->isAddressValid ($cEmail) ? $this->_cc[] = $cEmail : false;
        }
        return $this;
    }
    /**    
     * Add a single address or multiple addresses to the bcc field.
     * 
     * strings with comma's "," or semi-colons ";" are split and 
     * each address verified and then added or not included in the final message.
     * 
     * @param  string $cEmail  the email address or string of email addresses to 
     * be added.
     * @return Email
    */
    public function addRecipient($cEmail = null)
    {
        if (strpos($cEmail, ',') > 0 || strpos($cEmail, ';') > 0) {
            $aSplit = preg_split('/[,|;]/', $cEmail);
            foreach ($aSplit as $cValue) {
                $cValue = strtolower(trim($cValue));
                $this->isAddressValid($cValue) ? $this->_to[] = $cValue : false;
            }
        } else {
            $this->isAddressValid($cEmail) ? $this->_to[] = $cEmail : false;
        }

        return $this;
    }

    /**
     * set the bcc field of the email message - only one email address allowed.
     * 
     * @return Email
    */
    public function bcc($emailAddress)
    {
        $this->_bcc = array();
        if (strpos($emailAddress, ',') > 0 || strpos($emailAddress, ';') > 0) {
            $aSplit = preg_split('/[,|;]/' , $emailAddress);
            foreach ($aSplit AS $cValue) {
                $this->isAddressValid ($cValue) ? $this->_bcc[] = $cValue : false;
            }
        } else {
            $this->isAddressValid ($emailAddress) ? $this->_bcc[] = $emailAddress : false;
        }
        return $this;
    }
    /**
     * set the bcc field of the email message - only one email address allowed.
     * 
     * @return Email
    */
    public function cc($emailAddress)
    {
        $this->_cc = array();
        if (strpos($emailAddress, ',') > 0 || strpos($emailAddress, ';') > 0) {
            $aSplit = preg_split('/[,|;]/' , $emailAddress);
            foreach ($aSplit AS $cValue) {
                $this->isAddressValid ($cValue) ? $this->_cc[] = $cValue : false;
            }
        } else {
            $this->isAddressValid ($emailAddress) ? $this->_cc[] = $emailAddress : false;
        }
        return $this;
    }

    /**
     * set the check DNS variable.
     */ 
    public function checkDns ($lCheck = false)
    {

        ($lCheck == true || $lCheck == 1 || strtoupper(trim($lCheck)) == 'YES') ? $this->_lCheckDns = true : $this->_lCheckDns = false;

    }

    /**
     * To replace any restricted characters from various header fields.
     */
    public function checkVar (&$cVariable)
    {

        $cVariable = preg_replace('/(;|\||`|>|<|&|^|"|'."\t|\n|\r|'".'|{|}|[|]|\)|\()/i' , '' , $cVariable);

        $spam = strtolower($cVariable);

        (preg_match("/bcc: /i" , $spam) || preg_match("/cc: /i" , $spam) || preg_match("/subject: /i", $spam)) ? $cVariable = '' : false;

    }

    /**
     * 
    * @ignore
    */
    public function csrfguard_generate_token($unique_form_name)
    {

        if (function_exists("hash_algos") and in_array("sha512",hash_algos())) {
            $token = hash("sha512",mt_rand(0,mt_getrandmax()));
        } else {
            $token=' ';
            for ($i=0;$i<128;++$i) {
                $r=mt_rand(0,35);
                if ($r<26) {
                    $c=chr(ord('a')+$r);
                } else {
                    $c=chr(ord('0')+$r-26);
                }
                $token.=$c;
            }
        }

        $this->store_in_session($unique_form_name,$token);

        return $token;

    }

    /**
    * @ignore
    */
   public function csrfguard_validate_token($unique_form_name,$token_value)
    {
        $token = $this->get_from_session($unique_form_name);
        if ($token===false) {
            return true;
        } elseif ($token===$token_value) {
            $result=true;
        } else {
            $result=false;
        }

        $this->unset_session($unique_form_name);

        return $result;
    }

    /**
     * 
     */ 
    public function csrfguard_replace_forms($form_data_html)
    {

        $count=preg_match_all("/<form(.*?)>(.*?)<\\/form>/is",$form_data_html,$matches,PREG_SET_ORDER);
        if (is_array($matches)) {
            foreach ($matches as $m) {
                if (strpos($m[1],"nocsrf")!==false) { continue; }
                $name="CSRFGuard_".mt_rand(0,mt_getrandmax());
                $token = $this->csrfguard_generate_token($name);
                $form_data_html=str_replace($m[0],
                "<form{$m[1]}>
                <input type='hidden' name='CSRFName' value='{$name}' />
                <input type='hidden' name='CSRFToken' value='{$token}' />{$m[2]}</form>",$form_data_html);
            }
        }

        return $form_data_html;
    }
    /**
     * 
     */ 
    public function csrfguard_inject()
    {
        $data = ob_get_clean();
        $data = $this->csrfguard_replace_forms($data);
        echo $data;
    }
    /**
     * 
     */ 
    public function csrfguard_start()
    {
        if (count($_POST)) {
            if (!isset($_POST['CSRFName']) or !isset($_POST['CSRFToken'])) {
                trigger_error("No CSRFName found, probable invalid request.",E_USER_ERROR);
            }
            $name =$_POST['CSRFName'];
            $token=$_POST['CSRFToken'];

            if (!$this->csrfguard_validate_token($name, $token)) {
                trigger_error("Invalid CSRF token.",E_USER_ERROR);
            }
        }
        ob_start();
        /* adding double quotes for "csrfguard_inject" to prevent:
          Notice: Use of undefined constant csrfguard_inject - assumed 'csrfguard_inject' */
        register_shutdown_function($this->csrfguard_inject);
    }
    /**
     * post data in fields to url
     * 
     * @param  string $cUrl     [URL to post]
     * @param  mixed string|array $cFields  [array or string of values to post]
     * @param  string &$cResult [string reference to pass back any errors]
     * @return boolean 
     */
    public static function curlPOST($cUrl, $cFields, &$cResult)
    {

        $ch = curl_init();                  // URL of gateway for cURL to post to
        curl_setopt($ch, CURLOPT_URL, $cUrl);
        curl_setopt($ch, CURLOPT_HEADER, 0); // set to 0 to eliminate header info from response
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // Returns response data instead of TRUE(1)
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $cFields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        if (strtoupper(substr(PHP_OS, 0, 3) == 'WIN')) {
            curl_setopt($ch, CURLOPT_CAINFO, 'C:\WINNT\curl-ca-bundle.crt');
        }
        $cResult = curl_exec($ch);

        if (!$cResult) {
            $cResult = curl_error($ch) . '::' . curl_errno($ch);
            curl_close($ch);

            return false;

        } else {
            return true;
        }
    }
    /**
     * set the from address
     * @param  string $cEmail [the valid email address example@mail.com]
     * @param  string $cName  [the proper name to be associated, if needed]
     * @return Email
     */
    public function from($cEmail = null , $cName = null)
    {
        $this->isAddressValid ($cEmail) ? $this->_from = $cEmail : false;
        !empty($cName) ? $this->_fromname = $cName : false;
        return $this;
    }
    /**
     * 
     */ 
    public function getError()
    {
        return $this->error;
    }
    /**
     * 
     */ 
    public function get_from_session($key)
    {

        if (isset($_SESSION)) {
            return $_SESSION[$key];
        } else {  return false; } //no session data, no CSRF risk
    }
    /**
     * 
     */ 
    public function isHtml($true = true)
    {
        (!$true) ? $this->isHtml = false : $this->isHtml = true;
    }
    /**
     * test if email address follows valid form
     * 
     * @param  string  $email
     * @return boolean
     */
    public function isAddressValid($email = null)
    {
        $this->checkVar($email);
        $email = strtolower(trim($email));
        if (preg_match('/[\x00-\x1F\x7F-\xFF]/', $email)) {
            return false;
        }

        if (!preg_match('/^[^@]{1,64}@[^@]{1,255}$/', $email)) {
            return false;
        }
        // Split it into sections to make life easier
        $email_array = explode("@", $email);
        $local_array = explode(".", $email_array[0]);
        // CHECK LOCAL ARRAY
        foreach ($local_array as $local_part) {
            if (!preg_match('/^(([A-Za-z0-9!#$%&\'*+\/=?^_`{|}~-]+)|("[^"]+"))$/', $local_part)) {
                return false;
            }
        }
        if (!preg_match('/^\[?[0-9\.]+\]?$/',  $email_array[1])) { // Check if domain is IP. If not, it should be valid domain name
            $domain_array = explode('.', $email_array[1]);
            if (sizeof($domain_array) < 2) {
                return false; // Not enough parts to domain
            }
            foreach ($domain_array AS $domain_part) {

                if (!preg_match('/^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$/',  $domain_part)) {
                    return false;
                }
            }
            if ($this->_lCheckDns) {
                if (checkdnsrr($email_array[1])) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return true;
            }
        }
    }
    /**
     * 
     */
    public function mta_sendgrid()
    {
        $msg = new \SendGrid\Mail\Mail(); 
        $msg->setFrom($this->_from, $this->_fromname);
        $msg->setSubject($this->_subject);
        foreach ($this->_to as $k => $v) { 
            $msg->addTo($v);
        }
        foreach ($this->_cc as $k => $v) { 
            $msg->addCc($v);
        }
        foreach ($this->_bcc as $k => $v) { 
            $msg->addBcc($v);
        }
        $msg->addContent("text/plain", $this->_text_msg);
        $msg->addContent("text/html", $this->_html_msg);  
        if (sizeof($this->_attachments) > 0) { 
            foreach ($this->_attachments as $n => $attachment) { 
               if ($attachment['error'] == UPLOAD_ERR_OK) { 
                    $msgAttachment = new SendGrid\Mail\Attachment();
                    $msgAttachment->setContent(base64_encode(file_get_contents($attachment['tmp_name'])));
                    $msgAttachment->setType($attachment['type']);   
                    $msgAttachment->setDisposition("attachment");
                    $msgAttachment->setFilename($attachment['name']);
                    $msg->addAttachment($msgAttachment);
                }
            }
        }
        // --
        $sendgrid = new \SendGrid($this->constants['mta']['api_key']);
        try {
            $response = $sendgrid->send($msg);
            if ($response->statusCode() == '202') { 
                return true;
            } else { 
                $error = json_decode($response->body());
                $this->error = $error->errors[0]->message;
                return false;
            }
        } catch (Exception $e) {
            $this->error = 'Caught exception: ' . $e->getMessage();
            return false;
        }
    }
    /**
     */
    private function mta_phpmailer()
    {
        $mail = new PHPMailer(); // defaults to using php "mail()"
        $mail->IsHTML(true);
        $mail->SetFrom($this->_from);   
        foreach ($this->_to as $k => $v) { 
            $mail->AddAddress($v);
        }
        foreach ($this->_cc as $k => $v) { 
            $mail->AddCC($v);
        }
        foreach ($this->_bcc as $k => $v) { 
            $mail->addBCC($v);
        } 
        $mail->Subject = $this->_subject;
        $mail->AltBody = $this->_text_msg;  
        $mail->MsgHTML($this->_html_msg);
        if (sizeof($this->_attachments) > 0) { 
            foreach ($this->_attachments as $n => $attachment) { 
               if ($attachment['error'] == UPLOAD_ERR_OK) { 
                    $mail->addAttachment($attachment['tmp_name'], $attachment['name']);
                }
            }
        }
        if(!$mail->Send()) {
            $this->error = 'Error: ' . $mail->ErrorInfo;
            return false;
        } else { 
            return true;
        }                        
    }
    /**
     * 
     * 
     */
    private function mta_internal()
    {
        $eol;
        // set end of line marker
        if (strtoupper(substr(PHP_OS , 0 , 3) == 'WIN')) {
            $eol = "\r\n";
        } elseif (strtoupper(substr(PHP_OS , 0 , 3) == 'MAC')) {
            $eol = "\r";
        } else {
            $eol = "\n";
        }
        $headers = 'X-Priority: ' . $this->_priority . $eol;
        $headers .= 'X-Mailer: ' . $this->_mailer . $eol;
        if (empty($this->_fromname)) { 
            $headers .= 'From: ' . $this->_from . $eol;
            $headers .= 'Reply-To: ' . $this->_from . $eol;
        } else { 
            $headers .= 'From: ' . $this->_fromname . ' <' . $this->_from . '>' . $eol;
            $headers .= 'Reply-To: ' . $this->_fromname . ' <' . $this->_from . '>' . $eol;
        }
        if (is_array($this->_cc)) { 
            if (sizeof($this->_cc) > 0) {
                $headers .= 'Cc: '. implode(' ,' , $this->_cc) . $eol;            
            }
        } else { 
            if (strlen($this->_cc) > 0) { 
                $headers .= 'Cc: '. $this->_cc . $eol;            
            }
        }
        if (is_array($this->_bcc)) { 
            if (sizeof($this->_bcc) > 0) {
                $headers .= 'Bcc: '. implode(' ,' , $this->_bcc) . $eol;            
            }
        } else { 
            if (strlen($this->_bcc) > 0) { 
                $headers .= 'Bcc: '. $this->_bcc . $eol;            
            }
        }
        $headers .= 'Return-Path: ' . $this->_from . $eol;
        !empty($this->_sender) ? $headers .= 'Sender: ' . $this->_sender . $eol : $headers .= 'Sender: ' . $this->_from . $eol;
        !empty($this->_sender) ? $headers .= 'X-Sender: ' . $this->_sender . $eol : $headers .= 'X-Sender: ' . $this->_from . $eol;
        $headers .= 'X-Originating-Email: ' . $this->_from . $eol;

        if (is_array($this->_headers)) {
            foreach ($this->_headers AS $c => $v) {
                if (!empty($v)) {
                $headers .= $c . ': ' . $v . $eol;
                }
            }
        }
        $headers .= 'MIME-Version: 1.0' . $eol; // here is the line in question
        $msg = '';
        $cSemiRand = strtoupper(substr(md5(rand(0 , 500) . 'x' . rand(100 , 200)) , 0 , 15));
        $cMiMeBoundry = "==Multipart_Boundary_x{$cSemiRand}x";
        if (sizeof ($this->_attachments) > 0) {
            $headers .= "Content-type: multipart/mixed; boundary=\"$cMiMeBoundry\"" . $eol;
            $msg .= "--$cMiMeBoundry" . $eol;
            $msg .= "Content-type: multipart/alternative; boundary=\"alt--$cMiMeBoundry\"" . $eol . $eol;
            # -=-=-=- TEXT EMAIL PART
            if (!empty($this->_text_msg)) {
                $msg .= "--alt--$cMiMeBoundry" . $eol;
                $msg .= 'Content-Type: text/plain; charset=' . $this->_text_charset . $eol;
                $msg .= 'Content-Transfer-Encoding: ' . $this->_text_content_transfer_encoding . $eol;
                $msg .= 'Content-Disposition: inline;' . $eol . $eol;
                if (strtolower($this->_text_content_transfer_encoding) == 'base64') {
                    $msg .= chunk_split(base64_encode($this->_text_msg)) . $eol;
                } else {
                    $msg .= $this->_text_msg . $eol;
                }
                $msg .= $eol .$eol;
            }
            # -=-=-=- HTML EMAIL PART
            if (!empty($this->_html_msg)) {
                $msg .= "--alt--$cMiMeBoundry" . $eol;
                $msg .= 'Content-Type: text/html; charset=' . $this->_html_charset . $eol;
                $msg .= 'Content-Transfer-Encoding: ' . $this->_html_content_transfer_encoding . $eol;
                $msg .= 'Content-Disposition: inline;' . $eol . $eol;
                if (strtolower($this->_html_content_transfer_encoding) == 'base64') {
                    $msg .= chunk_split(base64_encode($this->_html_msg)) . $eol;
                } else {
                    $msg .= $this->_html_msg . $eol;
                }
                $msg .= "--alt--$cMiMeBoundry--" . $eol;
            }
            foreach ($this->_attachments AS $nKey => $aValue) {
                if (is_uploaded_file($aValue['tmp_name'])) {
                    $bin = @ fopen($aValue['tmp_name'] , 'rb');
                    $data = @ fread($bin , $aValue['size']);
                    @ fclose($bin);
                    $msg .= "--$cMiMeBoundry" . $eol;
                    $msg .= 'Content-Type: ' . $aValue['type'] . ';';
                    $msg .= " name=\"$aValue[name]\"" . $eol;
                    $msg .= 'Content-Transfer-Encoding: base64' . $eol;
                    $msg .= 'Content-Disposition: attachment;' . $eol . $eol;
                    $msg .= chunk_split(base64_encode($data)) . $eol . $eol;
                } else {
                    if (is_array($aValue)) { 
                        if (file_exists($aValue['path'])) {
                            $bin = @ fopen($aValue['path'] , 'rb');
                            $data = @ fread($bin , filesize($aValue['path']));
                            @ fclose($bin);
                            $msg .= "--$cMiMeBoundry" . $eol;
                            $msg .= 'Content-Type: application/octet-stream;';
                            !empty($aValue['name']) ? $msg .= ' name="' . $aValue['name'] . '"' . $eol : $msg .= ' name="' . basename($aValue['path']) . '"' . $eol;
                            $msg .= 'Content-Transfer-Encoding: base64' . $eol;
                            $msg .= 'Content-Disposition: attachment;' . $eol . $eol;
                            $msg .= chunk_split(base64_encode($data)) . $eol . $eol;
                        }
                    } else { 
                        if (file_exists($aValue)) {
                            $bin = @ fopen($aValue , 'rb');
                            $data = @ fread($bin , filesize($aValue));
                            @ fclose($bin);
                            $msg .= "--$cMiMeBoundry" . $eol;
                            $msg .= 'Content-Type: application/octet-stream;';
                            $msg .= ' name="' . basename($aValue) . '"' . $eol;
                            $msg .= 'Content-Transfer-Encoding: base64' . $eol;
                            $msg .= 'Content-Disposition: attachment;' . $eol . $eol;
                            $msg .= chunk_split(base64_encode($data)) . $eol . $eol;
                        }
                    }
                }
            }
            $msg .= $eol . "--$cMiMeBoundry--" . $eol . $eol;
        } else {
            if (!empty($this->_html_msg) && !empty($this->_text_msg)) {
                # -=-=-=- MAIL HEADERS
                $headers .= "Content-Type: multipart/alternative; boundary=\"$cMiMeBoundry\"" . $eol;
                # -=-=-=- TEXT EMAIL PART
                $msg .= "--$cMiMeBoundry" . $eol;
                $msg .= 'Content-Type: text/plain; charset=' . $this->_text_charset . $eol;
                $msg .= 'Content-Transfer-Encoding: ' . $this->_text_content_transfer_encoding . $eol;
                $msg .= 'Content-Disposition: inline;' . $eol . $eol;
                if (strtolower($this->_text_content_transfer_encoding) == 'base64') {
                    $msg .= chunk_split(base64_encode($this->_text_msg)) . $eol;
                } else {
                    $msg .= $this->_text_msg . $eol;
                }
                $msg .= $eol .$eol;
                # -=-=-=- HTML EMAIL PART
                $msg .= "--$cMiMeBoundry" . $eol;
                $msg .= 'Content-Type: text/html; charset=' . $this->_html_charset . $eol;
                $msg .= 'Content-Transfer-Encoding: ' . $this->_html_content_transfer_encoding . $eol;
                $msg .= 'Content-Disposition: inline;' . $eol . $eol;
                if (strtolower($this->_html_content_transfer_encoding) == 'base64') {
                    $msg .= chunk_split(base64_encode($this->_html_msg)) . $eol;
                } else {
                    $msg .= $this->_html_msg . $eol;
                }
                $msg .= $eol .$eol;
                # -=-=-=- FINAL BOUNDARY
                $msg .= $eol . "--$cMiMeBoundry--" . $eol . $eol;
            } elseif (!empty($this->_html_msg)) {
                $headers .= 'Content-Type: text/html; charset=' . $this->_html_charset . $eol;
                $msg .= 'Content-Transfer-Encoding: ' . $this->_html_content_transfer_encoding . $eol;
                $msg .= 'Content-Disposition: inline;' . $eol . $eol;
                if (strtolower($this->_html_content_transfer_encoding) == 'base64') {
                    $msg .= chunk_split(base64_encode($this->_html_msg)) . $eol;
                } else {
                    $msg .= $this->_html_msg . $eol;
                }
                $msg .= $eol .$eol;
            } else {
                $headers .= 'Content-Type: text/plain; charset=' . $this->_text_charset . $eol;
                $msg .= 'Content-Transfer-Encoding: ' . $this->_text_content_transfer_encoding . $eol;
                $msg .= 'Content-Disposition: inline;' . $eol . $eol;
                if (strtolower($this->_text_content_transfer_encoding) == 'base64') {
                    $msg .= chunk_split(base64_encode($this->_text_msg)) . $eol;
                } else {
                    $msg .= $this->_text_msg . $eol;
                }
                $msg .= $eol .$eol;
            }
        }
        ini_set(sendmail_from , $this->_from);
        if (strtoupper(substr(PHP_OS , 0 , 3) != 'WIN')) {
            $headers .= $msg;
            $msg = '';
        }
        // need to add imap mail function
        if (mail($this->_to, $this->_subject, $msg, $headers, "-f " . $this->_from)) {
            return true;
        } else {
            if (!$this->isAddressValid($this->_to)) {
                $this->error = 'Invalid TO address : ' . $this->_to;
            } elseif (!$this->isAddressValid($this->_from)) {
                $this->error = 'Invalid FROM address : ' . $this->_from;
            } else {
                $this->error = 'Unable to send email message';
            }
            return false;
        }
    } 
    /**
     * set the message 
     * 
     * @param $cValue $cValue 
     * @param $cType [TEXT|HTML - determine if content will be set to HTML or plain-text]
     * @param $cChar [change character set for message] 
     * @param $cContent $cValue 
     * 
     * @return Email
     */ 
    public function message($cValue = NULL, $cType = 'HTML', $cChar = NULL, $cContent = NULL)
    {
        if ($cContent != NULL) {
            if (strtoupper(trim($cType)) == 'HTML') {
                $this->_html_content_transfer_encoding = $cContent;
            } else {
                $this->_text_content_transfer_encoding = $cContent;
            }
        }
        if ($cChar != NULL) {
            if (strtoupper(trim($cType)) == 'HTML') {
                $this->_html_charset = $cChar;
            } else {
                $this->_text_charset = $cChar;
            }
        }
        if (strtoupper(trim($cType)) == 'HTML') {
            if ($this->_html_content_transfer_encoding == 'quoted-printable') {
                $this->_html_msg = quoted_printable_encode($cValue);
            } else {
                $this->_html_msg = $cValue;
            }
        } else {
            if ($this->_text_content_transfer_encoding == 'quoted-printable') {
                $this->_text_msg = quoted_printable_encode($cValue);
            } else {
                $this->_text_msg = $cValue;
            }
        }
        return $this;
    }
    /**
     * To set the priority of the message
     * 
     * @param integer $nValue [1 (for highest priority), 3 (normal) and 5 (lowest).]
     */ 
    public function priority($nValue = 3)
    {
        if (is_numeric($nValue) && $nValue >= 1 && $nValue <= 5) {
            $this->_priority = $nValue;
        }
        return $this;
    }
    /**
     * 
     */ 
    public function quoted_printable_encode($input = null , $line_max = 75)
    {
        if (version_compare(PHP_VERSION , '5.3.0' , '<')) {
            trim ($input);
            $hex = array('0','1','2','3','4','5','6','7', '8','9','A','B','C','D','E','F');
            $lines = preg_split("/(?:\r\n|\r|\n)/", $input);
            $linebreak = "=0D=0A=\r\n";
            /* the linebreak also counts as characters in the mime_qp_long_line
            * rule of spam-assassin */
            $line_max = $line_max - strlen($linebreak);
            $escape = "=";
            $output = "";
            $cur_conv_line = "";
            $length = 0;
            $whitespace_pos = 0;
            $addtl_chars = 0;

            // iterate lines
            for ($j=0; $j<count($lines); $j++) {
                $line = $lines[$j];
                $linlen = strlen($line);
                // iterate char
                for ($i = 0; $i < $linlen; $i++) {
                    $c = substr($line, $i, 1);
                    $dec = ord($c);
                    $length++;

                    if ($dec == 32) {
                        // space occurring at end of line, need to encode
                        if (($i == ($linlen - 1))) {
                            $c = "=20";
                            $length += 2;
                        }
                        $addtl_chars = 0;
                        $whitespace_pos = $i;
                    } elseif (($dec == 61) || ($dec < 32) || ($dec > 126)) {
                        $h2 = floor($dec/16); $h1 = floor($dec%16);
                        $c = $escape . $hex["$h2"] . $hex["$h1"];
                        $length += 2;
                        $addtl_chars += 2;
                    } else {
                        // dead
                    }
                    // length for wordwrap exceeded, get a newline into the text
                    if ($length >= $line_max) {
                        $cur_conv_line .= $c;
                        // read only up to the whitespace for the current line
                        $whitesp_diff = $i - $whitespace_pos + $addtl_chars;

                        /* the text after the whitespace will have to be read
                        * again (+ any additional characters that came into
                        * existence as a result of the encoding process after the whitespace)
                        *
                        * Also, do not start at 0, if there was *no* whitespace in
                        * the whole line */
                        if (($i + $addtl_chars) > $whitesp_diff) {
                            $output .= substr($cur_conv_line, 0, (strlen($cur_conv_line) - $whitesp_diff)) . $linebreak;
                            $i =  $i - $whitesp_diff + $addtl_chars;
                        } else {
                            $output .= $cur_conv_line . $linebreak;
                        }
                        $cur_conv_line = "";
                        $length = 0;
                        $whitespace_pos = 0;
                    } else {
                        // length for wordwrap not reached, continue reading
                        $cur_conv_line .= $c;
                    }
                } // end of for
                $length = 0;
                $whitespace_pos = 0;
                $output .= $cur_conv_line;
                $cur_conv_line = "";
                if ($j<=count($lines)-1) {
                    $output .= $linebreak;
                }
            }
        } else {
            $output = quoted_printable_encode($input);
        }
        return trim($output);
    }
    /**
     * [replyto description]
     * @param  [type] $emailAddress [description]
     * @return [type]               [description]
     */
    public function replyto($emailAddress)
    {        
        $this->isAddressValid ($emailAddress) ? $this->_replyto[] = $emailAddress : false;        
        return $this;
    }
    /**
     * reset all variables.
     */ 
    public function reset()
    {
        $this->_to = '';
        $this->_from = '';
        $this->_fromname = '';
        $this->_cc = array();
        $this->_bcc = array();
        $this->_subject = '';
        $this->_text_msg = '';
        $this->_html_msg = '';
        $this->_priority = 3;
        $this->_content_transfer_encoding = '7bit';
        $this->_charset = 'iso-8859-1'; // UTF-8
        $this->attachments = array();
        $this->_headers = array();
    }
    /**
    *   remove characters from string that veriifies a string and removes
    *   characters that to match the requested scrubing.
    *
    *   @param string $value - string that is being scrubbed.
    *
    *   @param string $cType - The type of scrubbing that needs to be completed.
    *   ALPHA - Only characters from A-Z and spaces.
    *   ALPHA_NUM - Only characters from A-Z & 0-9 and spaces.
    *   SIMPLE - Only characters found on the keyboard no special characters..
    *   EMAIL - Only characters that are part of a well-formed email address.
    *   HYPERLINK - A string that has been properly formatted as a URL.
    *   WHOLE_NUM - A whole number example 1000000 valid 1,000,000 invalid
    *   FLOAT_NUM - A float point number
    *   FORMAT_NUM - A properly formatted number
    *   SQL_INJECT - Only allow characters that are valid in value SQL calls.
    *   REMOVE_SPACES - Remove all spaces from the string.
    *   REMOVE_DOUBLESPACE - Remove double space and replace with single spaces.
    *   BASIC - Only characters found on the keyboard no special characters.
    *
    *   @param string $cWordFile [csv string for words that should be considered restricted]
    * 
    *   @return string
    *
    */
    public function scrubVar($value , $cType = 'BASIC' , $cWordFile = '')
    {
        $cType = strtoupper(trim($cType));
        if ($cType == 'ALPHA') {
            return preg_replace('/[^A-Za-z\s]/' , '' , $value);
        } elseif ($cType == 'ALPHA_NUM') {
            return preg_replace('/[^A-Za-z0-9]/' , '' , $value);
        } elseif ($cType == 'SIMPLE') {
            $cPattern = '[^A-Za-z0-9\s\-\_\.\ \,\@\!\#\$\%\^\&\*\(\)\[\]\{\}\?]';
            return preg_replace("/$cPattern/", '' , $value);
        } elseif ($cType == 'EMAIL') {
            $cPattern = '/(;|\||`|>|<|&|^|"|'."\t|\n|\r|'".'|{|}|[|]|\)|\()/i';
            return preg_replace($cPattern , '' , $value);
        } elseif ($cType == 'HYPERLINK') {
            // match protocol://address/path/file.extension?some=variable&another=asf%
            $value = preg_replace("/\s([a-zA-Z]+:\/\/[a-z][a-z0-9\_\.\-]*[a-z]{2,6}[a-zA-Z0-9\/\*\-\?\&\%]*)([\s|\.|\,])/i",'', $value);
            // match www.something.domain/path/file.extension?some=variable&another=asf%
            return preg_replace("/\s(www\.[a-z][a-z0-9\_\.\-]*[a-z]{2,6}[a-zA-Z0-9\/\*\-\?\&\%]*)([\s|\.|\,])/i",'', $value);
        } elseif ($cType == 'WHOLE_NUM') {
            return preg_replace('/[^0-9]/' , '' , $value);
        } elseif ($cType == 'FLOAT_NUM') {
            return preg_replace('/[^0-9\-\+]/' , '' , $value);
        } elseif ($cType == 'FORMAT_NUM') {
            return preg_replace('/[^0-9\.\,\-]/' , '' , $value);
        } elseif ($cType == 'SQL_INJECT') {
            $cPattern = '[^A-Za-z0-9\s\-\_\.\ \,\@\!\#\$\%\^\&\*\(\)\[\]\{\}\?]';
            $aRestrictedWords = array(
                '/\bcmd\b/i',
                '/\badmin\b/i',
                '/\bhaving\b/i',
                '/\broot\b/i',
                '/\bexec\b/i',
                '/\bdelete\b/i',
                '/\bCOLLATE\b/i',
                '/\bupdate\b/i',
                '/\bunion\b/i',
                '/\binsert\b/i',
                '/\bdrop\b/i',
                '/\bhttp\b/i',
                '/\bhttps\b/i',
                '/\b--\b/i'
            );
            return preg_replace("/$cPattern/", '' , preg_replace($aRestrictedWords , '' , $value));
        } elseif ($cType == 'REMOVE_SPACES') {
            return preg_replace("/\s/" , '' , trim($value));
        } elseif ($cType == 'REMOVE_DOUBLESPACE') {
            return preg_replace("/\s+/" , ' ' , trim($value));
        } else {
            $cPattern = '[^A-Za-z0-9\s\-\_\.\ \,\@\!\#\$\%\^\&\*\(\)\[\]\{\}\?]';
            return preg_replace("/$cPattern/", '' , strip_tags(trim($value)));
        }
    }
    /**
    * @created 03/13/2004
    * @modified 07/22/2006
    * @return boolean
    */
    public function send()
    {
        if ($this->_mailer == 'SENDGRID') {
            return $this->mta_sendgrid();
        } else if ($this->_mailer == 'PHPMAILER') { 
            return $this->mta_phpmailer();
        } else { 
            return $this->mta_internal();
        }
    }
    // =============================================================================
    // =============================================================================
    //           NAME: sender
    //   DATE CREATED: 03/13/2004
    //  DATE MODIFIED: 07/22/2006
    //         USAGE :
    //       PURPOSE : set the sender address if needed.
    //        RETURNS: none
    //      COMMENTS :
    //
    //
    // =============================================================================
    // =============================================================================
    public function sender($cEmail = null)
    {    
        $this->isAddressValid ($cEmail) ? $this->_sender = $cEmail : $this->_sender = '';
        return $this;
    }
    /**
     * [setMTA description]
     * @param string $mailer [description]
     */
    public function setMTA($mailer = 'PHPMAILER')
    {
        $mtas = array('PHPMAILER','MANDRILL','LOCALHOST','SMTP', 'SENDGRID');
        $mailer = strtoupper(trim($mailer));
        if (in_array($mailer, $mtas)) { 
            $this->_mailer = $mailer;
        } else { 
            $this->_mailer = 'PHPMAILER';
        }
    }
    /**
     * set the character set variable for the message
     */ 
    public function setCharacterSet($cValue, $cType = 'TEXT')
    {
        if (strtoupper(trim($cType)) == 'HTML') {
            $this->_html_charset = strtolower(trim($cValue));
        } else {
            $this->_text_charset = strtolower(trim($cValue));
        }

    }
    /**
     * [setConstants description]
     * @param [type] $variable [description]
     */
    public function setConstants($variable)
    {
        $this->constants = $variable;
    }
    /**
     * set the content type variable for the message
     */ 
    public function setContentType($cValue, $cType = 'TEXT')
    {
        if (strtoupper(trim($cType)) == 'HTML') {
            $this->_html_content_transfer_encoding = strtolower(trim($cValue));
        } else {
            $this->_text_content_transfer_encoding = strtolower(trim($cValue));
        }
    }
    // =============================================================================
    // =============================================================================
    //           NAME: setHeader
    //   DATE CREATED:
    //  DATE MODIFIED:
    //         USAGE :
    //       PURPOSE :
    //        RETURNS:
    //      COMMENTS :
    //
    //
    // =============================================================================
    // =============================================================================
    public function setHeader($cValue, $cKey = null)
    {
        $this->_headers[ $cKey ] = $cValue;
    }
    /**
     * 
     */ 
    public function store_in_session($key,$value)
    {
        if (isset($_SESSION)) {
            $_SESSION[$key]=$value;
        }
    }
    // =============================================================================
    // =============================================================================
    //           NAME: slurp
    //   DATE CREATED: 02/13/2005
    //  DATE MODIFIED: 04/22/2007
    //         USAGE : $this->slurp(PathtoFile);
    //       PURPOSE : To read contents of file into single string or execute if
    //                 needed.
    //        RETURNS: string
    //      COMMENTS :
    //
    //
    // =============================================================================
    // =============================================================================
    public function slurp($f = null , $aOutput = array() , $lDynamic = 1)
    {
        $cReturn = '';
        is_array($aOutput) ? $output = (object) $aOutput : $output = $aOutput;
        if (file_exists($f)) {
            ob_start();
            if (strtolower(substr (stripslashes($f), strrpos (stripslashes($f) ,'.'))) == '.php' && $lDynamic) {
                include($f);
                $cReturn = ob_get_contents();
            } else {
                $retval = readfile($f);
                if (false !== $retval) { // no readfile error
                    $cReturn = ob_get_contents();
                }
            }
            ob_end_clean();
        } else {
            if (substr(trim(strtolower($f)) , 0 , 4) == 'http') {
                $cReturn = file_get_contents($f);
            }
        }
        return $cReturn;
    }
    // =============================================================================
    // =============================================================================
    //           NAME: stripWhiteSpace
    //   DATE CREATED:
    //  DATE MODIFIED:
    //         USAGE :
    //       PURPOSE :
    //        RETURNS:
    //      COMMENTS :
    //
    //
    // =============================================================================
    // =============================================================================
    public static function stripWhiteSpace($cStr)
    {
        return preg_replace("/\s\s+/", ' ', $cStr);

    }
    /**
     * @ignore
     */
    public function stopText($cStr)
    {
        $cReturn = '';
        !empty($cStr) ? $nLen = strlen($cStr) : $nLen = 4;
        $nLen = rand(2, $nLen);
        for ($i = 1; $i <= $nLen; $i++) {
            $cReturn .= '*';
        }
        return $cReturn;
    }
    /**
     * set the subject of the message
     * 
     * @return Email 
     */ 
    public function subject($cValue = null)
    {
        $this->_subject = $cValue;
        return $this;
    }
    /**
     * set the to field of the email message - only one email address allowed.
     * 
     * @return Email
    */
    public function to($emailAddress)
    {
        $this->_to = array();
        if (strpos($emailAddress, ',') > 0 || strpos($emailAddress, ';') > 0) {
            $aSplit = preg_split('/[,|;]/' , $emailAddress);
            foreach ($aSplit AS $cValue) {
                $this->isAddressValid($cValue) ? $this->_to[] = $cValue : false;
            }
        } else {
            $this->isAddressValid($emailAddress) ? $this->_to[] = $emailAddress : false;
        }
        return $this;
    }
    /**
     * 
     */ 
    public function unset_session($key)
    {
        $_SESSION[$key]=' ';
        unset($_SESSION[$key]);
    }
}
// -that's all folks
