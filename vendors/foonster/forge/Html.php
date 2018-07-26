<?php
/**
 * @author Nicolas Colbert <nicolas@foonster.com>
 * @copyright 2005 Foonster Technology
 */
/**
 * The standard HTML rendering class
 */
class Html
{
    /**
     * array of values used to fill the attributes section of an html element
     * 
     * @access private
     * @var array
     */
    private $_attributes = array();

    /**
    * An array to hold the options available to html elments that support 
    * options as possible values. 
    * 
    * @access private
    * @var array
    */
    private $_options = array();

    /**
     * array of html tags that are self-closing
     * 'br', 'col', 'command', 'embed', 'hr', 'img', 'input', 'link', 'meta', 'param', 'source'
     * 
     * @access private
     * @var array
     */
    private $_singletonTags = array(
        'br',
        'col',
        'command',
        'embed',
        'hr',
        'img',
        'input',
        'link',
        'meta',
        'param',
        'source');

    private $booleanAttributes = array(
        'checked', 
        'selected', 
        'disabled', 
        'readonly', 
        'multiple', 
        'ismap', 
        'defer', 
        'declare', 
        'noresize', 
        'nowrap', 
        'noshade', 
        'compact'          
       );

    /**
     * the string to be used to identify the html element
     * 
     * @access private
     * @var string
     */
    private $_tag = '';
    /**
     * value to be used for value call
     * 
     * @access private
     * @var array
     */
    private $_value = '';
    /**
     * @ignore
     */ 
    public function __construct()
    {

    }

    private $_name;

    private $_id;

    /**
     * @ignore
     */ 
    public function __destruct()
    {
    }
    /**
     * [attribute description]
     * @param  string $name  the name of the attribute
     * @param  value  $value the value of the attribute
     * @return none
     */
    public function attribute($name = null, $value = null)
    {
        if (is_array($value)) { 
            $this->_attributes[$name] = implode(' ', $value);
        } else { 
            !empty($name) ? $this->_attributes[$name] = $value : false;
        }
        return $this;
    }
    /**
     * set the tag value
     * 
     */ 
    public function tag($name = null)
    {
        $this->_tag = strtolower($name);
        return $this;
    }
    /**
     * 
     * clear all the protected variables
     * 
     * @return Html
     */
    private function clearVariables()
    {
        $this->_options = array();
        $this->_attributes = array();
        $this->_tag = '';
        $this->_text = '';
        $this->_value = '';

    }

    /**
     * return the closing html tag for _tag variable.
     * 
     * @return  string
     */
    public function closeTag()
    {
        return '</' . $this->_tag . '>';
    }
    /**
     * return the long or short message associated with current http response codes.
     * 
     * @param string $nRecord [the http response code to be evaluated]
     * @param boolean $lExpanded [true|false]
     * 
     *     TRUE  - the long version of text will be returned
     *     FALSE - the short version of text will be returned
     * 
     * @return  string
     *  
     */
    public static function httpResponse($nRecord = '', $lExpanded = false)
    {
        $status_reason = array(
            100 => 'Continue',
            101 => 'Switching Protocols',
            102 => 'Processing',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            207 => 'Multi-Status',
            226 => 'IM Used',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            306 => 'Reserved',
            307 => 'Temporary Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            422 => 'Unprocessable Entity',
            423 => 'Locked',
            424 => 'Failed Dependency',
            426 => 'Upgrade Required',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            506 => 'Variant Also Negotiates',
            507 => 'Insufficient Storage',
            510 => 'Not Extended',
            999 => 'False Positive'
       );
        $status_msg = array(
            400 => "Your browser sent a request that this server could not understand.",
            401 => "This server could not verify that you are authorized to access the document requested.",
            402 => 'The server encountered an internal error or misconfiguration and was unable to complete your request.',
            403 => "You don't have permission to access %U% on this server.",
            404 => "We couldn't find <acronym title='%U%'>that uri</acronym> on our server, though it's most certainly not your fault.",
            405 => "The requested method is not allowed for the URL %U%.",
            406 => "An appropriate representation of the requested resource %U% could not be found on this server.",
            407 => "An appropriate representation of the requested resource %U% could not be found on this server.",
            408 => "Server timeout waiting for the HTTP request from the client.",
            409 => 'The server encountered an internal error or misconfiguration and was unable to complete your request.',
            410 => "The requested resource %U% is no longer available on this server and there is no forwarding address. Please remove all references to this resource.",
            411 => "A request of the requested method GET requires a valid Content-length.",
            412 => "The precondition on the request for the URL %U% evaluated to false.",
            413 => "The requested resource %U% does not allow request data with GET requests, or the amount of data provided in the request exceeds the capacity limit.",
            414 => "The requested URL's length exceeds the capacity limit for this server.",
            415 => "The supplied request data is not in a format acceptable for processing by this resource.",
            416 => 'Requested Range Not Satisfiable',
            417 => "The expectation given in the Expect request-header field could not be met by this server. The client sent <code>Expect:</code>",
            422 => "The server understands the media type of the request entity, but was unable to process the contained instructions.",
            423 => "The requested resource is currently locked. The lock must be released or proper identification given before the method can be applied.",
            424 => "The method could not be performed on the resource because the requested action depended on another action and that other action failed.",
            425 => 'The server encountered an internal error or misconfiguration and was unable to complete your request.',
            426 => "The requested resource can only be retrieved using SSL. Either upgrade your client, or try requesting the page using https://",
            500 => 'The server encountered an internal error or misconfiguration and was unable to complete your request.',
            501 => "This type of request method to %U% is not supported.",
            502 => "The proxy server received an invalid response from an upstream server.",
            503 => "The server is temporarily unable to service your request due to maintenance downtime or capacity problems. Please try again later.",
            504 => "The proxy server did not receive a timely response from the upstream server.",
            505 => 'The server encountered an internal error or misconfiguration and was unable to complete your request.',
            506 => "A variant for the requested resource <code>%U%</code> is itself a negotiable resource. This indicates a configuration error.",
            507 => "The method could not be performed.  There is insufficient free space left in your storage allocation.",
            510 => "A mandatory extension policy in the request is not accepted by the server for this resource.",
            999 => "The URL is returning a 200 but should be considered a 500"
       );

        if(!empty($nRecord)) {
            return '';
        } else {
            if ($lExpanded) {
                return $status_msg[$nRecord];
            } else {
                return $status_reason[$nRecord];
            }
        }
    }
    /**
     * create hyperlinks for any links that appear that they should be 
     * clickable.  Excluding those tags that may cause issues.  This was built 
     * for a forum text conversion.
     * 
     * @param  string $text [the string to be parsed]
     * 
     * @return string
     */
    public function linkText($text)
    {
        $text = preg_replace('#(script|about|applet|activex|chrome):#is', "\\1:", $text);
        // pad it with a space so we can match things at the start of the 1st line.
        $ret = ' ' . $text;
        // matches an "xxxx://yyyy" URL at the start of a line, or after a space or closing bracket.
        // xxxx can only be alpha characters.
        // yyyy is anything up to the first space, newline, comma, double quote or <
        $ret = preg_replace("#(^|[\n | |>])([\w]+?://[\w\#$%&~/.\-;:=,?@\[\]+]*)#is", "\\1<a href=\"\\2\" target=\"_blank\">\\2</a>", $ret);
        // matches a "www|ftp.xxxx.yyyy[/zzzz]" kinda lazy URL thing
        // Must contain at least 2 dots. xxxx contains either alphanum, or "-"
        // zzzz is optional.. will contain everything up to the first space, newline,
        // comma, double quote or <.
        $ret = preg_replace("#(^|[\n | |>])((www|ftp)\.[\w\#$%&~/.\-;:=,?@\[\]+]*)#is", "\\1<a href=\"http://\\2\" target=\"_blank\">\\2</a>", $ret);
        // matches an email@domain type address at the start of a line, or after a space.
        // Note: Only the followed chars are valid; alphanums, "-", "_" and or ".".
        $ret = preg_replace("#(^|[\n | |>])([a-z0-9&\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)#i", "\\1<a href=\"mailto:\\2@\\3\">\\2@\\3</a>", $ret);
        // Remove our padding..
        $ret = substr($ret, 1);
        return $ret;
    }
    /**
     * this function will look at the object and then reorder 
     * the attributes array to match the optimal order required.
     * 
     */
    private function optimizeAttributeOrder() 
    {
        $attributes = array();
        $default = array(
            'class',
            'id', 
            'name',
            'data-',
            'src', 
            'for', 
            'type', 
            'href', 
            'value',
            'title', 
            'alt',
            'aria-', 
            'role'
       );
        $tags = array(
            'a' => $default,
            'img' => $default
           );
        if (array_key_exists($this->_tag, $tags)) {
            foreach ($tags[$this->_tag] as $n => $key) { 
                if (substr($key, -1) === '-') { 
                    // check for new stuff
                    foreach ($this->_attributes as $field => $value) {                     
                        if ($key == substr($field, 0, strlen($key))) { 
                            $attributes[$field] = $value;
                            unset($this->_attributes[$field]);
                        }                        
                    }
                } else { 
                    foreach ($this->_attributes as $field => $value) { 
                        if ($key == $field) { 
                            //echo 'Added: ' . $field;
                            $attributes[$field] = $value;
                            unset($this->_attributes[$field]);
                        }
                    }
                }
            }            
        }
        $this->_attributes = array_merge($this->_attributes, $attributes);
    }

    /**
     * create the opening of the requested tag.
     * 
     * @return string
     * 
     */    
    public function openTag()
    {
        $text = '<' . $this->_tag . ' ';
        foreach ($this->_attributes as $k => $v) {
            $text .= ' ' . $k . '="' . str_replace('"', '\"', $v) . '"';
        }
        $this->clearVariables();

        return $text . '>';
    }

    /**
     * remove anything from a string that appears to be a link.
     * 
     * @param  string $text [the string to be parsed]
     *      
     * @return string
     */
    public function removeLinks($text)
    {
        // match protocol://address/path/file.extension?some=variable&another=asf%
        $text = preg_replace("/\s([a-zA-Z]+:\/\/[a-z][a-z0-9\_\.\-]*[a-z]{2,6}[a-zA-Z0-9\/\*\-\?\&\%]*)([\s|\.|\,])/i", '', $text);
        // match www.something.domain/path/file.extension?some=variable&another=asf%
        $text = preg_replace("/\s(www\.[a-z][a-z0-9\_\.\-]*[a-z]{2,6}[a-zA-Z0-9\/\*\-\?\&\%]*)([\s|\.|\,])/i", '', $text);
        // match name@address
        // $text = preg_replace("/\s([a-zA-Z][a-zA-Z0-9\_\.\-]*[a-zA-Z]*\@[a-zA-Z][a-zA-Z0-9\_\.\-]*[a-zA-Z]{2,6})([\s|\.|\,])/i",'', $text);
        //
        return $text;
    }

    /**
     * render the html element based on current class values
     *             
     * @return string
     */
    public function render()
    {
        if (!empty($this->_tag)) {
            $this->optimizeAttributeOrder();
            if ($this->_tag == 'select') {
                $text = '<select ';
                foreach ($this->_attributes as $k => $v) {
                    // special case for required attribute
                    if ($k == 'required') { 
                        if ($v == 1 || $v == 'required' || $v == 'true' || $v == true) { 
                            $text .= ' ' . $k . '="' . str_replace('"', '\"', $v) . '"';
                        }
                    } else { 
                        $text .= ' ' . $k . '="' . str_replace('"', '\"', $v) . '"';                        
                    }
                }
                $text .= '>';
                foreach ($this->_options as $k => $v) {
                    ($k == '_empty_' || empty($k)) ? $text .= '<option value=""' : $text .= '<option value="' . $k . '"';
                    ($k == $this->_value) ? $text .= ' selected="selected"' : false;
                    $text .= '>' . $v . '</option>';
                }
                $text .= '</select>';
            } else {
                $text = '<' . $this->_tag;
                foreach ($this->_attributes as $k => $v) {
                    // special case for required attribute
                    if ($k == 'required') { 
                        if ($v == 1 || $v == 'required' || $v == 'true' || $v == true) { 
                            $text .= ' ' . $k . '="' . str_replace('"', '\"', $v) . '"';
                        }
                    } else { 
                        $text .= ' ' . $k . '="' . str_replace('"', '\"', $v) . '"';                        
                    }
                }
                if (in_array($this->_tag, $this->_singletonTags)) {
                    $text .= ' value="' . $this->_value . '" />';
                } else {
                    $text .= '>' . $this->_value . '</' . $this->_tag . '>';
                }
            }
        }
        $this->clearVariables();
        return $text;
    }   
    /**
     * [id description]
     * @param  [type] $string [description]
     * @return [type]         [description]
     */
    public function id($string) 
    { 
        $this->_id = $string;
        return $this;
    }
    /**
     * [name description]
     * @param  [type] $string [description]
     * @return [type]         [description]
     */
    public function name($string) 
    { 
        $this->_name = $string;
        return $this;
    }
    /**
     * 
     * set options array
     * 
     * @param string $name  [the name associated with the option]
     * @param string $value [the value associated with the option]
     * 
     * @return  Html
     */
    public function option($name = null, $value = null)
    {
        !empty($name) ? $this->_options[$name] = $value : false;
        return $this;
    }
    /**
     * set the options variable to the provided array
     * 
     * @param array $array [array of name/value pairs]
     * 
     *  @return Html
     */
    public function options($array)
    {
        !is_array($array) ? $array = (array) $array : false;
        $this->_options = $array;        
        return $this;
    }
    /**
     * set value of element 
     * 
     * @param string $value [string for value]
     * 
     * @return  Html
     *
     */
    public function value($value) 
    {
        if($value != strip_tags($value)) {
            // contains HTML and should not be encoded.
            $this->_value = trim($value);        
        } else {
            $this->_value = htmlentities(trim($value));        
        }
        return $this;
    }
}
