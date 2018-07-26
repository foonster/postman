<?php
/**
 * @author Nicolas Colbert <nicolas@foonster.com>
 * @copyright 2005 Foonster Technology
 */
/**
 * A set of methods developed through the various years that are commonly needed 
 * on numerous projects 
 */
class Anvil
{
    public $error;
    private $_vars = array();
    private $_benchmarks = array();    

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
    public function __set($index, $value)
    {
        $this->_vars[$index] = $value;
    }
    /**
     * @ignore
     */
    public function __get($index)
    {
        return $this->_vars[$index];
    }
    /**
     * convert an array to various arrays so the values are in columns
     * 
     * @param  array   $aArray     [oringal array to sort]
     * @param  integer $nCols      [number of columns to return]
     * @param  string  $cDirection [vertical sorting vs horizontal sorting]
     * 
     * @return array
     */
    public function arrayToColumns($aArray, $nCols = 2, $cDirection = 'vertical')
    {
        $aReturn = array();   
        $nRows = @ sizeof($aArray);
        for($i=1; $i<= $nCols; $i++) { $aReturn[$i] = array(); }
        if ($nRows > 0) {
            $nTemp = ceil($nRows / $nCols);
            if ($cDirection == 'vertical') {        
                $nCols = 1;
                $nLoop = 0;      
                foreach ($aArray AS $nId => $aValue) 
                {   
                    $nLoop++;               
                    if ($nLoop <= $nTemp) {
                        $aReturn[$nCols][$nId] = $aValue;

                    } else {    
                        $nLoop = 1;   
                        $nCols++;
                        $aReturn[$nCols][$nId] = $aValue;         
                    }                
                }            
            } else {        
                // horizontal    
                foreach ($aArray AS $nId => $aValue) {   
                    $nLoop++;               
                    if ($nLoop <= $nCols) {                        
                        $aReturn[$nLoop][$nId] = $aValue;            
                    } else {
                        $nLoop = 0;   
                        $nLoop++;
                        $aReturn[$nLoop][$nId] = $aValue;
                    }            
                }
            }       
        }              
        return $aReturn;
    } 

    /**
     * convert an object to an array
     * @param  object $obj [the object to be converted]
     * @return array
     */
    public static function arrayToObject($array)
    {
        $return;
        is_object($array) ? $array = (array) $array : false;
        foreach ($array as $k => $v) {
            if (is_object($v) || is_array($v)) { 
                $return->{$k} = self::arrayToObject($v);
            } else { 
                $return->{$k} = $v;
            } 
        }
        return $return;
    }

    /**
    * convert the bb_code sting into a valid html string
    * 
    * @param  string $cString [string using bbcode for encoding]
    * @return string
    */
    public function bbcode_convert_to_html($cString) {
    $bbcode = array(
        "/\[b\](.*?)\[\/b\]/is" => "<strong>$1</strong>",
        "/\[u\](.*?)\[\/u\]/is" => "<u>$1</u>",
        "/\[i\](.*?)\[\/i\]/is" => "<i>$1</i>",
        "/\[s\](.*?)\[\/i\]/is" => "<s>$1</s>",
        "/\[img\](.*?)\[\/img\]/is" => "<img src=\"$1\" alt=\"\" />",
        "/\[code\](.*?)\[\/code\]/is" => "<pre>$1</pre>",
        "/\[quote\](.*?)\[\/quote\]/is" => "<blockquote><p>$1</p></blockquote>",
        "/\[quote\=(.*?)\](.*?)\[\/quote\]/is" => "<blockquote><i>$1</i><p>$2</p></blockquote>",
        "/\[url\](.*?)\[\/url\]/is" => "<a href=\"$1\">$1</a>",
        "/\[url\=(.*?)\](.*?)\[\/url\]/is" => "<a href=\"$1\">$2</a>"
   );
    $cString = preg_replace(array_keys($bbcode), array_values($bbcode), $cString);    
    //$cString = clickable_link($cString);
    return $cString;
    }    
    /**
     * determine if attribute is currently assigned to this object.
     * 
     * @param  string $cAttribute [name of attribute to be located.]
     * 
     * @return boolean
     * 
     */
    public static function attributeExists($attribute, $object = null)
    {
        if (!is_null($object)) { 
            $obj = get_object_vars($object);
        } else {
            $obj = get_object_vars($this);
        }
        return array_key_exists($attribute, $object_vars);
    }
    /**
     * add benchmark point - this function can be used to 
     * measure execution time.
     * @param  string $cType [notation to tell time to this part of the execution]     
     */
    public function benchMark($marker = null)
    {
        if ($marker == null) {
            $marker = 'Mark: ' . (sizeof($this->_benchmarks)+1);
        }

        $this->_benchmarks[] = 
        array(
            'marker' => $marker,
            'time' => microtime());
    }
    /**
     * array of benchmark points and the time to this part of the execution
     * 
     * @return array
     */
    public function benchMarkElapsedTime()
    {
        $return = array(); 
        array_unshift($this->_benchmarks, array('marker' => 'start', 'time' => $_SERVER["REQUEST_TIME_FLOAT"]));
        if (sizeof($this->_benchmarks) > 0) {
            if (sizeof($this->_benchmarks) == 1) {
                // there is only one value
                list($su, $sw) = preg_split('/ /', $this->_benchmarks[0]['time']);
                list($eu, $ew) = preg_split('/ /', microtime());
                $return[$this->_benchmarks[0]['marker']] = round((($ew + $eu) - ($sw + $su)), 4);
            } else {
                foreach ($this->_benchmarks as $n => $mark) {
                    if ($n > 0) {
                        list($su, $sw) = preg_split('/ /', $this->_benchmarks[($n-1)]['time']);
                        list($eu, $ew) = preg_split('/ /', $mark['time']);
                        $return[$this->_benchmarks[($n)]['marker']] = round((($ew + $eu) - ($sw + $su)), 4);
                    } else {
                        $return[$this->_benchmarks[($n)]['marker']] = 0;
                    }
                }
            }
        } else {
            $return['null'] = 0;
        }    
        return $return;
    }
    /**
     * reset the benchmarks
     */
    public function benchMarkReset()
    {
        $_benchmarks = array();
    }
    public function benchmarkRender()
    {
        $string = '';
        $total = 0;
        foreach ($this->benchMarkElapsedTime() as $key => $value) {
            $string .= "$value : $key<br />";
            $total += $value;
        }
        $string .= "total time; $total<br />";
        return $string;
    }
    public function reduceNumber($start, $floor, $factor = 2)
    {
        if ($start > $floor) {
            $start = ($start/$factor);
            if ($start > $floor) {
                $start = self::reduceNumber($start, $floor, $factor);
            }
        }
        return round($start);
    }
    /**
     * bind one variable to another variable overwriting values
     * and returnin a like variable.
     * @param $variables array/object - variable being bound
     * @param $obj array/obj - variable new values are bound to
     *
     * @return array/obj - depending on the casting of the original variable
     *                     the variable returned will match.
     */
    public static function bindtovar($variables, $obj)
    {

        (is_object($obj)) ? $return = 'object' : $return = 'array';
        is_object($variables) ? $variables = (array) $variables : false;
        is_object($obj) ? $obj = (array) $obj : false;
        if ($return == 'object') {
            return (object) array_merge($obj, $variables);
        } else {
            return array_merge($obj, $variables);
        }
    } 
    /**
     * return filesize in human readable format]
     * @param  integer $size [number of filesize]
     * @return string
     */
    public static function bytes($size)
    {
        $i=0;
        $iec = array("B", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB");
        while (($size / 1024) > 1) {
            $size= $size / 1024;
            $i++;
        }

        return substr($size, 0, strpos($size, '.') + 4) . ' ' . $iec[$i];
    }

    /**
     * build category directory based on Foonster Technology assumptions on parnt_id
     * 
     * @param  array $aCategories [array to build category list from]
     * @param  string $cCat        [name of current category]
     *      
     * @return string json encoded string 
     */
    public function categoryDirectory($aCategories, $cCat)
    {

        $aReturn = array();     

        if ($cCat != null) {            
            $nParent = $aCategories[$cCat]['parnt_id'];
            ($nParent == null) ? $nParent = 0 : false;
            $aReturn[] = $aCategories[$cCat]['name'];
            while ($nParent > 0) {
                foreach ($aCategories AS $cCategory => $aValue) {
                    if (isset($aValue['id'])) {
                        if ($aValue['id'] == $nParent) {
                            $aReturn[] = $aValue['name'];                        
                            $nParent = $aValue['parnt_id'];
                        }
                    }
                }
            }
        }
        return json_encode(array_reverse($aReturn));
    }

    /**
     * build category directory based on Foonster Technology assumptions on parnt_id
     * 
     * @param  array $aCategories [array to build category list from]
     * @param  string $cCat        [name of current category]
     *      
     * @return string json encoded string 
     */
    public function categoryTitle($aCategories , $cCat)
    {
        $aReturn = array();
        if ($cCat != null) {
            $nParent = $aCategories[$cCat]['parnt_id'];
            empty($nParent) ? $nParent = 0 : false;
            $aReturn[] = $aCategories[$cCat]['name'];
            while ($nParent > 0) {
                foreach ($aCategories AS $cCategory => $aValue) {
                    if ($aValue['id'] == $nParent) {
                        $aReturn[] = $aValue['name'];
                        $nParent = $aValue['parnt_id'];
                    }
                }
            }
        }
        return array_reverse($aReturn);
    }
    /**
     * [checkRecordNumber description]
     * @param  [type] $string [description]
     * @return [type]         [description]
     */
    public static function checkRecordNumber(&$string)
    {        
        ($string == NULL || !is_numeric($string)) ? $string = 0 : false;        
    }
    /**
     * [stripSlashesFromArrayValues description]
     * @param  [type] $array [description]
     * @return [type]        [description]
     */
    public static function stripSlashesFromArrayValues($array)
    {
        !is_array($array) ? $array = (array) $array : false;

        foreach ($array as $key => $value) { 
            if (!is_string($value)) {

            } else { 
                $array[$key] = stripslashes($value);
            }            
        }

        return $array;
    }

    /**
     * @param  [array] - an array of values/keys
     * @param  string - the keys to be excluded
     * @return [array] - clean array
     */
    public static function cleanArray($array, $restrict = '') 
    { 
        $return = array();
        $restricted = preg_split("/,/", strtolower($restrict));
        foreach ($restricted as $n => $v) { 
            $restricted[$n] = trim($v);
        }
        foreach ($array as $key => $value) {
            if (!in_array($key, $restricted)) { 
                $return[$key] = $value;
            }
        }
        return $return;
    }
    /**
     * 
     * 
     */ 
    public static function getDifferences($original, $new) 
    {
        $changes = [];
        !is_array($original) ? $original = (array) $original : false; 
        !is_array($new) ? $new = (array) $new : false; 
        unset($new['id']);
        foreach ($new as $key => $value) {
            if (substr($key, 0, 5) != 'addl_' 
                && !preg_match("/._/is", $key) 
                && substr($key, 0, 5) != 'meta_' 
                && substr($key, 0, 8) != 'modified') {
                if (array_key_exists($key, $original)) { 
                    if ($new[$key] != $original[$key]) {
                        if (strlen($original[$key]) <= 255) { 
                            $changes[] = 'changed ' . str_replace('_', ' ', $key) . ' from ' . $original[$key] . ' to ' . $new[$key];
                        } else { 
                            $changes[] = 'updated field' . str_replace('_', ' ', $key);
                        }                     
                    } 
                } else { 
                    $changes[] = 'added ' . str_replace('_', ' ', $key) . ' : ' . $value;  
                }
            }

        }
        return $changes;
    }
    /**
     * convert lbs to kilograms
     * 
     * @param  integer $pounds [number of pounds]
     * 
     * @return integer
     */
    public static function convertLbtoKg($pounds)
    {
        return $pounds * 0.4535923;
    }
    /**
     * convert number to two decimal places without any commas
     * @param  int $number [number to be converted]
     * @return decimal         [number in two decimal places]
     */
    public static function convertToMoney($number, $deci = 2)
    {
        return number_format(self::scrubVar($number, 'MONEY'), $deci, '.', '');
    }
    /**
     * The file upload for multiple file uploads changes the returned array to 
     * an unusable associative array.
     * 
     */ 
    public function correctFileUpload($files)
    {

        $array = array();

        // use the tmp_name to correct.
        foreach ($files['tmp_name'] as $n => $value) { 
            $file = array();
            $file['name'] = $files['name'][$n];
            $file['type'] = $files['type'][$n];
            $file['tmp_name'] = $files['tmp_name'][$n];
            $file['error'] = $files['error'][$n];
            $file['size'] = $files['size'][$n];

            $array[] = (object) $file;
        }

        return $array;  
    }

    /**
     * [createDateRange description]
     * @param  [type] $startDate [description]
     * @param  [type] $endDate   [description]
     * @param  string $format    [description]
     * @return [type]            [description]
     */
    public function createDateRange($startDate, $endDate, $format = "Y-m-d")
    {
        $begin = new DateTime($startDate);
        $end = new DateTime($endDate);
        $interval = new DateInterval('P1D'); // 1 Day
        $dateRange = new DatePeriod($begin, $interval, $end);
        $range = array();
        foreach ($dateRange as $date) {
            $range[] = $date->format($format);
        }
        return $range;
    }

    /**
     * create a random text string
     * @see  pi()
     * @return string
     */
    public function csrf($string)
    {
        return self::encryptString($string);
    }
    /**
     * post data in fields to url
     * 
     * @param  string $cUrl   [URL]
     * @return boolean 
     */
    public static function curl($url, $data, $headers = array(), $method = 'GET', $debug = 0)
    {        
        $output = array();
        $httpAuth = false;
        $httpAuthValue = false;
        $curlHeaders = array();
        if (sizeof($headers) > 0) {
            foreach($headers as $key => $value) { 
                if (array_key_exists('HTTPAUTH', $headers)) {
                    $httpAuth = true;
                    $httpAuthValue = $headers['HTTPAUTH'];
                } else { 
                    $curlHeaders[] = "$key: $value";
                }
            }                    
        }

        if (is_array($headers)) { 
            if (array_key_exists('Content-Type', $headers) && strpos($headers['Content-Type'], 'xml') > 0) {
                $query = $data;
            } else {     
                if (is_object($data) || is_array($data)) {            
                    $query = trim(http_build_query($data, '', '&'));    
                } else { 
                    $query = $data;
                }
            }
        } else { 
            if (is_object($data) || is_array($data)) {            
                $query = trim(http_build_query($data, '', '&'));    
            } else { 
                $query = $data;
            }
        }
        $ch = curl_init();                  // URL of gateway for cURL to post to

        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);                
            curl_setopt($ch, CURLOPT_POSTFIELDS, $query);            
            curl_setopt($ch, CURLOPT_URL, $url);
        } else { 
            if (!empty($query)) { 
                curl_setopt($ch, CURLOPT_URL, $url . '?' . $query);
            } else { 
                curl_setopt($ch, CURLOPT_URL, $url);
            }
        } 
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_REFERER, 'http://' . $_SERVER['HTTP_HOST'] . ' /'); // to prevent error code 500
        curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
        (strtoupper(substr(PHP_OS, 0, 3) == 'WIN')) ? curl_setopt($ch, CURLOPT_CAINFO, 'C:\WINNT\curl-ca-bundle.crt') : false;

        if ($httpAuth) { 
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, $httpAuthValue);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $curlHeaders); 
        
        if ($debug) { 
            curl_setopt($ch, CURLOPT_HEADER, 1); // set to 0 to eliminate header info from response       
            curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        } else { 
            curl_setopt($ch, CURLOPT_HEADER, 0); // set to 0 to eliminate header info from response       
            curl_setopt($ch, CURLINFO_HEADER_OUT, false);
        }
        $response = curl_exec($ch);    
        $output = new StdClass();
        if ($debug) { 
            $output = (object) curl_getinfo($ch);
            //$output->query = trim($query);
            $output->headerSent = curl_getinfo($ch, CURLINFO_HEADER_OUT);
        }
        if (!$response) {
            $output->response = 'CURL ERROR: ' . curl_error($ch) . ' - ' . curl_errno($ch); 
        } else { 
            $output->response = $response;    
        }    

        return $output;
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
     * return the difference between two dates and provide the count.
     * 
     * @param  string  $nStart [starting date]
     * @param  string  $nEnd   [ending date]
     * @param  string  $sel    [what kind of calculation to perform]
     * 
     *     Y - Years
     *     W - Weeks
     *     D - Days
     *     H - Hours
     *     M - Minutes
     *     S - Seconds
     * 
     * @return integer         
     */
    public static function dateDiff($nStart, $nEnd, $sel = 'Y')
    {

        $sY = 31536000;
        $sW = 604800;
        $sD = 86400;
        $sH = 3600;
        $sM = 60;
        $r = 0;

        $sel = strtolower(trim($sel));
        $nEnd = strtotime($nEnd);
        $nStart = strtotime($nStart);

        if ($nEnd < $nStart) {
            $nEnd = $nStart;
        }

        $t = ($nEnd - $nStart);

        if ($sel == 'y') { // years

            return ($t / $sY);
        } elseif ($sel == 'w') { // weeks

            return ($t / $sW);
        } elseif ($sel == 'd') { // days

            return ($t / $sD);
        } elseif ($sel == 'h') { // hours

            return ($t / $sH);
        } elseif ($sel == 'm') { // minutes

            return ($t / $sM);
        } else { // seconds

            return $t;
        }
    }
    /**
     * decode string based on simple ordinal encryption
     * 
     * @param  string $string [string to encode]
     * @param  string $key    [string to use as key/salt]
     * @return string
     */
    public function decodeValue($string, $key = '')
    {
        return base64_decode(strrev($string));
    }
    /**
     * return an array of contents of a directory.
     * 
     * @param  string  $directory [path to directory]
     * @param  string  $extension [limit look up to this file extension]
     * @param  boolean $full_path [return full path information]
     * @param  boolean $recursive [perform a lookup for the full list]
     * @return array
     */
    public function directoryToArray($directory, $extension = '', $full_path = true, $recursive = true)
    {

        $array_items = array();
        if ($handle = opendir($directory)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != '.' && $file != '..') {
                    if (is_dir($directory. '/' . $file)) {
                        if ($recursive) { 
                            $array_items = array_merge($array_items, self::directoryToArray($directory. '/' . $file, $extension, $full_path));
                        }                        
                    } else {
                        if (!$extension || (preg_match("/." . $extension . '/', $file))) {
                            if ($full_path) {
                                $array_items[] = $directory . '/' . $file;
                            } else {
                                $array_items[] = $file;
                            }
                        }
                    }
                }
            }
            closedir($handle);
            return $array_items;
        }
    }
    /**
     * 
     */ 
    public static function directoryTree($directory, $recursive = false, $full_path = true)
    {
        $array_items = array();
        if ($handle = opendir($directory)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != '.' && $file != '..') {
                    if (is_dir($directory . '/' . $file)) {
                        /*
                        if ($recursive) { 
                            $array_items = array_merge($array_items, $this->directoryToArray($directory. '/' . $file, $extension, $full_path));
                        }
                        */
                        ($recursive) ? $array_items[] = $directory . '/' . $file : $array_items[] = $file;                        
                    }
                }
            }
            closedir($handle);
            return $array_items;
        }
    }
    /**
     * [escapeCsvVariable description]
     * @param  string 
     * @param  string $optionalEscape 
     * @return string 
     */
    public static function escapeCsvVariable($string, $optionalEscape = "\\")
    {
        if (preg_match("/[\,\"\']/", $string)) { 
            $string = str_replace('"', $optionalEscape . '"', $string);
            $string = '"' . $string . '"';
            return $string;
        } else { 
            return $string;
        }
    }    
    /**
     * dump variable to HTML
     * @param  [various] $var [the variable to be dumped to the screen in HTML]
     * @return string
     */
    public static function dumpVar($var)
    {
        return self::dumpVariable($var);
    }

    /**
     * dump variable to HTML
     * @param  [various] $var [the variable to be dumped to the screen in HTML]
     * @return string
     */
    public static function dumpVariable($var)
    {        
        if(isset($var->tyfoon)) { 
            unset($var->tyfoon);
        }
        echo '<pre>';
        print_r($var);
        echo '</pre>';
    }
    /**
     * simple encoding method
     * @param  string $string [string to encode]
     * @param  string $key    [key for encoding]
     * @return string
     */
    public static function encodeValue($string, $key = '')
    {
        return strrev(base64_encode($string));
    }

    /**
     * encode XML string with proper html elments
     * 
     * @param  string $string [valid XML string to be encoded]
     * 
     * @return string
     */
    public static function encodeXMLString(&$string)
    {
        str_replace(array('&', '"', "'", '<', '>'), array ('&amp;', '&quot;', '&apos;', '&lt;', '&gt;'), $string);
    }

    /**
     * one-way encryption of a string
     * @param  string $cString [the string to be encoded]
     * @param  string $cMethod [the method to use when encoding]
     * @param  string $cSalt   [the salt to use, if applicable.]
     * @return string
     */
    public static function encryptString($cString, $cSalt = 'th3ra1ninsp@1ns@ysMainly1nth3pl#3n',  $cMethod = 'SHA512')
    {
        $date = new DateTime($cSalt, new DateTimeZone('America/New_York'));
        $cMethod = 'CRYPT_' . strtoupper($cMethod);
        $nLoop = intval($date->format('Hms'));
        $cString = trim($cString);
        if ($cMethod == 'CRYPT_MD5') {
            return md5($cString);
        } elseif ($cMethod == 'CRYPT_STND_DES') {
            return str_replace(substr($cSalt, 0, 2), '', crypt($cString, substr($cSalt, 0, 2)));
        } elseif ($cMethod == 'CRYPT_EXT_DES') {
            $cSalt = $this->scrubVar($cSalt);
            return str_replace('_F6..' . substr($cSalt, 0, 4), '', crypt($cString, '_F6..' . substr($cSalt, 0, 4)));
        } elseif ($cMethod == 'CRYPT_BLOWFISH') {
            $cSalt = $this->scrubVar($cSalt);
            return str_replace('$2a$06$' . $cSalt .'$', '', crypt($cString, '$2a$06$' . $cSalt .'$'));

        } elseif ($cMethod == 'CRYPT_SHA256') {
            return str_replace('$5$rounds=' . $nLoop . '$' . substr($cSalt, 0, 16) . '$', '', crypt($cString, '$5$rounds=' . $nLoop . '$' . substr($cSalt, 0, 16) . '$'));

        } else {
            return str_replace('$6$rounds=' . $nLoop . '$' . substr($cSalt, 0, 16) . '$', '', crypt($cString, '$6$rounds=' . $nLoop . '$' . substr($cSalt, 0, 16) . '$'));
        }
    }
    /**
     * extract the domain name from a string
     * 
     * @param  string $cString [a string containing a domain name]
     * @return string
     */
    public static function extractDomainName($cString)
    {        
        $aUrl = parse_url($cString);
        return preg_replace('/^(?:.+?\.)+(.+?\.(?:co\.uk|com|net|edu|gov|org))(\:[0-9]{2,5})?\/*.*$/is', '$1', $aUrl['host']);
    }

    /**
     * format date in predefined methods of display
     * 
     * @param  string $date [date string to be converted]
     * @param  string $type what type of string to be returned.
     * 
     *     expanded
     *     fancy
     *     fancywithhours
     *     europe: d/m/Y
     *     standard(default): m/d/Y
     * 
     * 
     * @return string
     */
    public function formatDate($date, $type = 'mdY')
    {
        if ($type == 'expanded') {
            return date('l F j, Y', strtotime($date));
        } elseif ($type == 'mysql') {
            return date('Y-m-d H:i:s', strtotime($date));
        } elseif ($type == 'fancy') {
            return date('l F j', strtotime($date)) . '<sup>' . date('S', strtotime($date)) . '</sup> ' . date('Y', strtotime($date));
        } elseif ($type == 'fancy_with_hours') {
            return date('l F j', strtotime($date)) . '<sup>' . date('S', strtotime($date)) . '</sup>' . date('G:i Y', strtotime($date));
        } elseif ($type == 'europe') {
            return date('d/m/Y', strtotime($date));
        } else {
            return date('m/d/Y', strtotime($date));
        }
    }    

    /**
     * take raw string and format according to appropriate country code
     * @param  string $cString [string containing number]
     * @param  string $cType   [country to use as template]
     * @return string
     */
    public static function formatPhoneNumber($cString, $cType = 'US')
    {
        $cString = preg_replace("/[^0-9a-zA-Z]/", '', $cString);
        if ($cType == 'US') {
            $strArea = substr($cString, 0, 3);
            $strPrefix = substr($cString, 3, 3);
            $strNumber = substr($cString, 6, 4);
            $strElse = substr($cString, 10);
            return "(".$strArea.") ".$strPrefix."-".$strNumber." ".$strElse;
        }
    }
    /**
     * generate a string to use as key for various purposes
     * @return string 
     */
    public function generateSessionKey()
    {
        return md5(uniqid(md5(rand()), true)) . '.' . uniqid();               
    }
    /**
     * compute the number of years between two dates.
     *
     *  This function does not suffer the rounding issue found in other solutions when 
     *  you start hitting the 70+ range.
     * 
     * @param  string $cDOB string representing birthday]
     * @return integer
     */
    public static function getAge($cDOB)
    {

        $cDOB = date('Y-m-d', strtotime($cDOB));
        if ($cDOB != null && ($cDOB != '0000-00-00' || $cDOB != '0000-00-00 00:00:00')) {
            $cDOB = strtolower(preg_replace("/[^0-9\/\-\.]/", '', trim(date('m/d/Y', strtotime($cDOB)))));
            if (strlen($cDOB) == 10 || strlen($cDOB) == 9 || strlen($cDOB) == 8) {
                $ddiff = date("d") - date("d", strtotime($cDOB));
                $mdiff = date("m") - date("m", strtotime($cDOB));
                $ydiff = date("Y") - date("Y", strtotime($cDOB));
                if ($mdiff < 0) {
                    $ydiff--;
                } elseif ($mdiff==0) {
                    if ($ddiff < 0) {
                        $ydiff--;
                    }
                } else {
                }
                return $ydiff;
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }
    /**
     * 
     */ 
    function getClassName($obj) {
        $classname = get_class($obj);
        if (preg_match('@\\\\([\w]+)$@', $classname, $matches)) {
            $classname = $matches[1];
        }
        return $classname;
    }  
    /**
     * 
     * 
     * @return array
     */ 
    public function getDirectoryContents($directory, $recursive = false) 
    { 

        $return = array();

        if (!is_dir($directory)) {
            return false;
        } elseif (is_readable($directory)) {
            $handle = opendir($directory);
            while (false !== ($item = readdir($handle))) {
                if ($item != '.' && $item != '..') {
                    if (is_dir($directory)) {
                        if ($recursive) { 
                            $return[] = self::getDirectoryContents($directory . '/'. $item , $recursive);
                        } else { 
                            $return[] = $item;
                        }                        
                    } else {
                        $return[] = $item;
                    }
                }
            }
            closedir($handle);
        }

        return $return;
    }
    /**
     * extract the file extension from a path name.
     * @param  string $name [The path name to be evaluated]
     * @return string
     */
    public function getFileExtension($name)
    {
        $ext = strrchr($name, '.');
        if ($ext !== false) {
            $name = substr($name, 0, -strlen($ext));
        }
        return $ext;
    }
    /**
     * return the IP address of incoming traffic
     * 
     * @return string [The IP Address]
     */
    public static function getIpAddress()
    {
        if (getenv('HTTP_CLIENT_IP')) {
            return getenv('HTTP_CLIENT_IP');
        } elseif (getenv('HTTP_X_FORWARDED_FOR')) {
            return getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('HTTP_X_FORWARDED')) {
            return getenv('HTTP_X_FORWARDED');
        } elseif (getenv('HTTP_FORWARDED_FOR')) {
            return getenv('HTTP_FORWARDED_FOR');
        } elseif (getenv('HTTP_FORWARDED')) {
            return getenv('HTTP_FORWARDED');
        } else {
            return $_SERVER['REMOTE_ADDR'];
        }
    }
    /**
    * 
    * 
    * 
    */ 
    function getRecordByTag($array, $tag = '') 
    { 
        !is_array($array) ? $array = (array) $array : false;
        foreach ($array as $n => $value) {
            if (preg_match("/$tag/is", $value->tags)) { 
                return (object) $value;                
                break;
            }            
        }
    }
    /**
    * This searches an array to locate all the records that
    * have the provided tag in the record.
    * 
    * @param $array The array to be searched
    * @param $tag The tag to look for
    * @param $limit the limit of matches that should be returned.
    * 
    * @return array : the array of records from the original array
    */ 
    function getRecordsByTag($array, $tag = '', $limit = 999) 
    { 
        $return = array();
        !is_array($array) ? $array = (array) $array : false;        
            foreach ($array as $n => $value) {
                !is_object($value) ? $value = (object) $value : false;
                if (preg_match("/$tag/is", $value->tags)) { 
                    $return[] = $value;
                }            
            }        
        return $return;
    }
    /**
     * return a formatted timestamp
     * 
     * @param  string $cFormat [what format to use]
     * 
     * @return string
     */
    public static function getTimeStamp($cFormat = 'MYSQL', $timestamp = '')
    {

        $cFormat = strtoupper(trim($cFormat));

        empty($timestamp) ? $nTime = (time() - date('Z')) : $nTime = (strtotime($timestamp) - date('Z'));

        if ($cFormat == 'ISO8601' || $cFormat == 'ATOM' || $cFormat == 'W3C') {
            return date("Y-m-d", $nTime).'T'.date("H:i:sO", $nTime);

        } elseif ($cFormat == 'COOKIE' || $cFormat == 'RFC822' || $cFormat == 'RFC1123') {
            return date("D, d M Y H:i:s", $nTime).' UTC';

        } elseif ($cFormat == 'RFC850' || $cFormat == 'RFC1036') {
            return date("l, d-M-y H:i:s", $nTime).' UTC';

        } elseif ($cFormat == 'RFC2822') {
            return date("D, d M Y H:i:s O", $nTime);

        } elseif ($cFormat == 'RSS') {
            return date("D, d M Y H:i:s", $nTime).' UTC';

        } elseif ($cFormat == 'EPOCH') {
            return time();

        } else {
            return date("Y-m-d H:i:s", $nTime);

        }

    }
    /**
     * determine if string is a JSON string
     * 
     * @param  string  $string [string to be tested]
     * @return boolean
     */
    public function isJSON($string)
    {
        if (!empty($string)) {
            json_decode($string);
            return (json_last_error() == JSON_ERROR_NONE);
        } else {
            return 0;
        }
    }
    /**
     * ensure variable is a numeric integer
     * 
     * @param  string  $nRecord [string to be verified]
     * @return boolean
     */
    public static function isRecord($nRecord = null)
    {
        if (!empty($nRecord) && $nRecord > 0) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * determine if string is hardend to requested level.]
     * @param  string  $cString       [string to be tested]
     * @param  integer $nLen          [the minimum length required for the string]
     * @param  boolean $lUpperCase    [require at least one uppercase character.]
     * @param  boolean $lSpecial      [require at least one special character.]
     * @param  boolean $nSpecialCnt   [the number greater than one, special characters required.]
     * 
     * @return boolean
     */
    public function isStringHard ($cString, $nLen = 8, $lUpperCase = false, $lSpecial = true, $nSpecialCnt = 1)
    {
        if (strlen($cString) >= $nLen) {
            if (preg_match('/[A-Z]/', $cString)) {
                if (preg_match('/[a-z]/', $cString)) {
                    if (preg_match('/[0-9]/', $cString)) {
                        if ($lSpecial) {
                            if (preg_match("/\~\@\#\%\^\&\*\(\)\-\_\=\+\[\]\{\}\\\|\:\"\,\.\<\>\/\?/", $cString)) {
                                return true;
                            } else {
                                return false;
                            }
                        } else {
                            return true;
                        }
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

  /**
   * a simple test to ensure that the provided string is a valid email address.
   * 
   * @param  string  $email     [string to be validated]
   * @param  boolean $lDNSCheck [if true, perform DNS check to ensure domain is valid.]
   * 
   * @return boolean
   */
    public function isValidEmailAddress($email, $lDNSCheck = false)
    {

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

        if (!preg_match('/^\[?[0-9\.]+\]?$/', $email_array[1])) { // Check if domain is IP. If not, it should be valid domain name

            $domain_array = explode('.', $email_array[1]);

            if (sizeof($domain_array) < 2) {
                return false; // Not enough parts to domain
            }

            foreach ($domain_array as $domain_part) {

                if (!preg_match('/^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$/', $domain_part)) {
                    return false;
                }
            }

            if ($lDNSCheck) {
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
     * test if string matches Foonster Technology identified as a robot
     * 
     * @param  string  $cAgent  [represents agent string to compare]
     * 
     * @return boolean
     */
    public static function isRobot ($cAgent = null)
    {
        $cRobots = array(
            'ABCdatos\sBotLink',            
            'YodaoBot'
          );

        empty($cAgent) ? $cAgent = $_SERVER['HTTP_USER_AGENT'] : false ;

        $cImplode = implode('|', $aCrawlers);

        if (preg_match("/$cImplode/i", $cAgent)) {
            return true;
        } else {
            return false;
        }

    }

    /**
     * pad the left side of a string with a character.
     * @param  integer $length  [the total length of the string]
     * @param  string $string   [the string to pad]
     * @param  string $character  [the character to pad with]
     * @return string
     */
    public static function lpad($length, $string, $character = ' ')
    {
        $length = ($length - strlen($string));
        for ($counter = 1; $counter <= $length; $counter++) {
            $string = $character . $string;
        }
        return $string;
    }
    /**
     * convert a string to lower-case letters
     * @param  string $cString [the string to convert]
     * @return string
     */
    public static function lwcase($cString)
    {
        return strtolower(trim($cString));
    }
    /**
     * create the directory path if not present.
     * @param  string  $dir       [directory path]
     * @param  integer $mode      [numeric representation of permissions]
     * @param  boolean $recursive [create directories recursively]
     * @return boolean
     */
    public static function mkdirs($dir, $mode = 0777, $recursive = true)
    {
        if (is_null($dir) || $dir === '') {
            return false;
        }
        if (is_dir($dir) || $dir === "/") {
            return true;
        }
        if (self::mkdirs(dirname($dir), $mode, $recursive)) {
            $old_umask = @ umask(0);
            return @ mkdir($dir, $mode);
            @ umask($old_umask);
        }
        return false;
    }

    /**
     * return the mime-type for common file extensions.
     * 
     * @return string
     */ 
    public function mimeType($extension) 
    {
        $mimeTypes = array(
        'acx' => 'application/internet-property-stream',
        'ai' => 'application/postscript',
        'aif' => 'audio/x-aiff',
        'aifc' => 'audio/x-aiff',
        'aiff' => 'audio/x-aiff',
        'asf' => 'video/x-ms-asf',
        'asr' => 'video/x-ms-asf',
        'asx' => 'video/x-ms-asf',
        'au' => 'audio/basic',
        'avi' => 'video/x-msvideo',
        'axs' => 'application/olescript',
        'bas' => 'text/plain',
        'bcpio' => 'application/x-bcpio',
        'bin' => 'application/octet-stream',
        'bmp' => 'image/bmp',
        'c' => 'text/plain',
        'cat' => 'application/vnd.ms-pkiseccat',
        'cdf' => 'application/x-cdf',
        'cdf' => 'application/x-netcdf',
        'cer' => 'application/x-x509-ca-cert',
        'class' => 'application/octet-stream',
        'clp' => 'application/x-msclip',
        'cmx' => 'image/x-cmx',
        'cod' => 'image/cis-cod',
        'cpio' => 'application/x-cpio',
        'crd' => 'application/x-mscardfile',
        'crl' => 'application/pkix-crl',
        'crt' => 'application/x-x509-ca-cert',
        'csh' => 'application/x-csh',
        'css' => 'text/css',
        'dcr' => 'application/x-director',
        'der' => 'application/x-x509-ca-cert',
        'dir' => 'application/x-director',
        'dll' => 'application/x-msdownload',
        'dms' => 'application/octet-stream',
        'doc' => 'application/msword',
        'dot' => 'application/msword',
        'dvi' => 'application/x-dvi',
        'dxr' => 'application/x-director',
        'eps' => 'application/postscript',
        'etx' => 'text/x-setext',
        'evy' => 'application/envoy',
        'exe' => 'application/octet-stream',
        'fif' => 'application/fractals',
        'flr' => 'x-world/x-vrml',
        'gif' => 'image/gif',
        'gtar' => 'application/x-gtar',
        'gz' => 'application/x-gzip',
        'h' => 'text/plain',
        'hdf' => 'application/x-hdf',
        'hlp' => 'application/winhlp',
        'hqx' => 'application/mac-binhex40',
        'hta' => 'application/hta',
        'htc' => 'text/x-component',
        'htm' => 'text/html',
        'html' => 'text/html',
        'htt' => 'text/webviewhtml',
        'ico' => 'image/x-icon',
        'ief' => 'image/ief',
        'iii' => 'application/x-iphone',
        'ins' => 'application/x-internet-signup',
        'isp' => 'application/x-internet-signup',
        'jfif' => 'image/pipeg',
        'jpe' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'js' => 'application/x-javascript',
        'latex' => 'application/x-latex',
        'lha' => 'application/octet-stream',
        'lsf' => 'video/x-la-asf',
        'lsx' => 'video/x-la-asf',
        'lzh' => 'application/octet-stream',
        'm13' => 'application/x-msmediaview',
        'm14' => 'application/x-msmediaview',
        'm3u' => 'audio/x-mpegurl',
        'man' => 'application/x-troff-man',
        'mdb' => 'application/x-msaccess',
        'me' => 'application/x-troff-me',
        'mht' => 'message/rfc822',
        'mhtml' => 'message/rfc822',
        'mid' => 'audio/mid',
        'mny' => 'application/x-msmoney',
        'mov' => 'video/quicktime',
        'movie' => 'video/x-sgi-movie',
        'mp2' => 'video/mpeg',
        'mp3' => 'audio/mpeg',
        'mpa' => 'video/mpeg',
        'mpe' => 'video/mpeg',
        'mpeg' => 'video/mpeg',
        'mpg' => 'video/mpeg',
        'mpp' => 'application/vnd.ms-project',
        'mpv2' => 'video/mpeg',
        'ms' => 'application/x-troff-ms',
        'msg' => 'application/vnd.ms-outlook',
        'mvb' => 'application/x-msmediaview',
        'nc' => 'application/x-netcdf',
        'nws' => 'message/rfc822',
        'oda' => 'application/oda',
        'p10' => 'application/pkcs10',
        'p12' => 'application/x-pkcs12',
        'p7b' => 'application/x-pkcs7-certificates',
        'p7c' => 'application/x-pkcs7-mime',
        'p7m' => 'application/x-pkcs7-mime',
        'p7r' => 'application/x-pkcs7-certreqresp',
        'p7s' => 'application/x-pkcs7-signature',
        'pbm' => 'image/x-portable-bitmap',
        'pdf' => 'application/pdf',
        'pfx' => 'application/x-pkcs12',
        'pgm' => 'image/x-portable-graymap',
        'pko' => 'application/ynd.ms-pkipko',
        'pma' => 'application/x-perfmon',
        'pmc' => 'application/x-perfmon',
        'pml' => 'application/x-perfmon',
        'pmr' => 'application/x-perfmon',
        'pmw' => 'application/x-perfmon',
        'pnm' => 'image/x-portable-anymap',
        'pot' => 'application/vnd.ms-powerpoint',
        'ppm' => 'image/x-portable-pixmap',
        'pps' => 'application/vnd.ms-powerpoint',
        'ppt' => 'application/vnd.ms-powerpoint',
        'prf' => 'application/pics-rules',
        'ps' => 'application/postscript',
        'pub' => 'application/x-mspublisher',
        'qt' => 'video/quicktime',
        'ra' => 'audio/x-pn-realaudio',
        'ram' => 'audio/x-pn-realaudio',
        'ras' => 'image/x-cmu-raster',
        'rgb' => 'image/x-rgb',
        'rmi' => 'audio/mid',
        'roff' => 'application/x-troff',
        'rtf' => 'application/rtf',
        'rtx' => 'text/richtext',
        'scd' => 'application/x-msschedule',
        'sct' => 'text/scriptlet',
        'setpay' => 'application/set-payment-initiation',
        'setreg' => 'application/set-registration-initiation',
        'sh' => 'application/x-sh',
        'shar' => 'application/x-shar',
        'sit' => 'application/x-stuffit',
        'snd' => 'audio/basic',
        'spc' => 'application/x-pkcs7-certificates',
        'spl' => 'application/futuresplash',
        'src' => 'application/x-wais-source',
        'sst' => 'application/vnd.ms-pkicertstore',
        'stl' => 'application/vnd.ms-pkistl',
        'stm' => 'text/html',
        'sv4cpio' => 'application/x-sv4cpio',
        'sv4crc' => 'application/x-sv4crc',
        'svg' => 'image/svg+xml',
        'swf' => 'application/x-shockwave-flash',
        't' => 'application/x-troff',
        'tar' => 'application/x-tar',
        'tcl' => 'application/x-tcl',
        'tex' => 'application/x-tex',
        'texi' => 'application/x-texinfo',
        'texinfo' => 'application/x-texinfo',
        'tgz' => 'application/x-compressed',
        'tif' => 'image/tiff',
        'tiff' => 'image/tiff',
        'tr' => 'application/x-troff',
        'trm' => 'application/x-msterminal',
        'tsv' => 'text/tab-separated-values',
        'txt' => 'text/plain',
        'uls' => 'text/iuls',
        'ustar' => 'application/x-ustar',
        'vcf' => 'text/x-vcard',
        'vrml' => 'x-world/x-vrml',
        'wav' => 'audio/x-wav',
        'wcm' => 'application/vnd.ms-works',
        'wdb' => 'application/vnd.ms-works',
        'wks' => 'application/vnd.ms-works',
        'wmf' => 'application/x-msmetafile',
        'wps' => 'application/vnd.ms-works',
        'wri' => 'application/x-mswrite',
        'wrl' => 'x-world/x-vrml',
        'wrz' => 'x-world/x-vrml',
        'xaf' => 'x-world/x-vrml',
        'xbm' => 'image/x-xbitmap',
        'xla' => 'application/vnd.ms-excel',
        'xlc' => 'application/vnd.ms-excel',
        'xlm' => 'application/vnd.ms-excel',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.ms-excel',
        'xlt' => 'application/vnd.ms-excel',
        'xlw' => 'application/vnd.ms-excel',
        'xof' => 'x-world/x-vrml',
        'xpm' => 'image/x-xpixmap',
        'xwd' => 'image/x-xwindowdump',
        'z' => 'application/x-compress',
        'zip' => 'application/zip');
        $extension = trim($extension,'.');
        if (array_key_exists($extension, $mimeTypes)) {
            return $mimeTypes[$extension];
        } else {
            return '';
        }

    }

    /**
     * convert an object to an array
     * @param  object $obj [the object to be converted]
     * @return array
     */
    public function objectToArray($obj)
    {
        return json_decode(json_encode($obj), true);
    }

    /**
     * build an array that represents where pagination points would occur.
     * 
     * @param  integer $record  [the starting record number]
     * @param  integer $records [the total number of records]
     * @param  integer $perpage [number of records to be on each page.]
     * @param  integer $range   [number of pages on left and right of selected page.]
     * 
     * @return array
     * 
     */
    public function pagination($record = 0, $records = 0, $perpage = 24, $range = 2)
    {

        !is_numeric($record) ? $record = 0 : false;

        ($record < 0) ? $record = 0 : false;
        ($record > $records) ? $record = $records : false;
        $aPaginate = array();
        $aPaginate['total'] = ceil($records / $perpage);
        $aPaginate['page'] = ceil(($record + 1) / $perpage);
        ($aPaginate['page'] > $aPaginate['total']) ? $aPaginate['page'] = $aPaginate['total'] : false;
        $aPaginate['current'] = (($aPaginate['page'] * $perpage) - $perpage);
        ($aPaginate['current'] < 0) ? $aPaginate['current'] = 0 : false;
        $aPaginate['first'] = 0;
        $aPaginate['last'] = (($aPaginate['total'] * $perpage) - $perpage);
    // next
        $aPaginate['next'] = ($aPaginate['current'] + $perpage);
        ($aPaginate['next'] > $aPaginate['last']) ? $aPaginate['next'] = $aPaginate['last'] : false;
    // prev
        $aPaginate['prev'] = ($aPaginate['current'] - $perpage);
        ($aPaginate['prev'] < $aPaginate['first']) ? $aPaginate['prev'] = $aPaginate['first'] : false;
    // range
        $First = ($aPaginate['current'] + 1);
        ($First > $records) ? $First = $records : false;
        $Top = ($aPaginate['current'] + $perpage);
        ($Top > $records) ? $Top = $records : false;
        $aPaginate['range'] = number_format($First, 0)  . ' to ' . number_format($Top, 0) . ' of ' . number_format($records, 0);
        $aPaginate['pages'] = array();
    // the items before
        for ($i = $range; $i >= 1; $i--) {
            if (($aPaginate['current'] - ($i * $perpage)) >= 0) {
                $aPaginate['pages'][] = array(
                'cnt' => ($aPaginate['current'] - ($i * $perpage)),
                'pg' => ceil((($aPaginate['current'] - ($i * $perpage)) + 1) / $perpage)
               );
            }
        }
        $aPaginate['pages'][] = array(
            'cnt' => $aPaginate['current'],
            'pg' => $aPaginate['page']
       );

        for ($i = 1; $i <= $range; $i++) {
            if (($aPaginate['current'] + ($i * $perpage)) <= $aPaginate['last']) {
                $aPaginate['pages'][] = array(
                'cnt' => ($aPaginate['current'] + ($i * $perpage)),
                'pg' => ceil((($aPaginate['current'] + ($i * $perpage)) + 1) / $perpage)
               );
            }
        }

        return (object) $aPaginate;

    }

    /**
     * the value of pi to the requested length.
     * 
     * @return integer
     * 
     */
    public static function pi($length = 24)
    {
        $pi = '3.141592653589793238462643383279502884197169399375105820974944592307816406286208998628034825342117067982148086513282306647093844609550582231725359408128481117450284102701938521105559644622948954930381964428810975665933446128475648233786783165271201909145648566923460348610454326648213393607260249141273724587006606315588174881520920962829254091715364367892590360011330530548820466521384146951941511609433057270365759591953092186117381932611793105118548074462379962749567351885752724891227938183011949129833673362440656643086021394946395224737190702179860943702770539217176293176752384674818467669405132000568127145263560827785771342757789609173637178721468440901224953430146549585371050792279689258923542019956112129021960864034418159813629774771309960518707211349999998372978049951059731732816096318595024459455346908302642522308253344685035261931188171010003137838752886587533208381420617177669147303598253490428755468731159562863882353787593751957781857780532171226806613001927876611195909216420198938095257201065485863278865936153381827968230301952035301852968995773622599413891249721775283479131515574857242454150695950829533116861727855889075098381754637464939319255060400927701671139009848824012858361603563707660104710181942955596198946767837449448255379774726847104047534646208046684259069491293313677028989152104752162056966024058038150193511253382430035587640247496473263914199272604269922796782354781636009341721641219924586315030286182974555706749838505494588586926995690927210797509302955321165344987202755960236480665499119881834797753566369807426542527862551818417574672890977772793800081647060016145249192173217214772350141441973568548161361157352552133475741849468438523323907394143334547762416862518983569485562099219222184272550254256887671790494601653466804988627232791786085784383827967976681454100953883786360950680064225125205117392984896084128488626945604241965285022210661186306744278622039194945047123713786960956364371917287467764657573962413890865832645995813390478027590099465764078951269468398352595709825822620522489407726719478268482601476990902640136394437455305068203496252451749399651431429809190659250937221696461515709858387410597885959772975498930161753928468138268683868942774155991855925245953959431049972524680845987273644695848653836736222626099124608051243884390451244136549762780797715691435997700129616089441694868555848406353422072225828488648158456028506016842739452267467678895252138522549954666727823986456596116354886230577456498035593634568174324112515076069479451096596094025228879710893145669136867228748940560101503308617928680920874760917824938589009714909675985261365549781893129784821682998948722658804857564014270477555132379641451523746234364542858444795265867821051141354735739523113427166102135969536231442952484937187110145765403590279934403742007310578539062198387447808478489683321445713868751943506430218453191048481005370614680674919278191197939952061419663428754440643745123718192179998391015919561814675142691239748940907186494231961567945208095146550225231603881930142093762137855956638937787083039069792077346722182562599661501421503068038447734549202605414665925201497442850732518666002132434088190710486331734649651453905796268561005508106658796998163574736384052571459102897064140110971206280439039759515677157700420337869936007230558763176359421873125147120532928191826186125867321579198414848829164470609575270695722091756711672291098169091528017350671274858322287183520935396572512108357915136988209144421006751033467110314126711136990865851639831501970165151168517143765761835155650884909989859982387345528331635507647918535893226185489632132933089857064204675259070915481416549859461637180270981994309924488957571282890592323326097299712084433573265489382391193259746366730583604142813883032038249037589852437441702913276561809377344403070746921120191302033038019762110110044929321516084244485963766983895228684783123552658213144957685726243344189303968642624341077322697802807318915441101044682325271620105265227211166039666557309254711055785376346682065310989652691862056476931257058635662018558100729360659876486117910453348850346113657686753249441668039626579787718556084552965412665408530614344431858676975145661406800700237877659134401712749470420562230538994561314071127000407854733269939081454664645880797270826683063432858785698305235808933065757406795457163775254202114955761581400250126228594130216471550979259230990796547376125517656751357517829666454779174501129961489030463994713296210734043751895735961458901938971311179042978285647503203198691514028708085990480109412147221317947647772622414254854540332157185306142288137585043063321751829798662237172159160771669254748738986654949450114654062843366393790039769265672146385306736096571209180763832716641627488880078692560290228472104031721186082041900042296617119637792133757511495950156604963186294726547364252308177036751590673502350728354056704038674351362222477158915049530984448933309634087807693259939780541934144737744184263129860809988868741326047215695162396586457302163159819319516735381297416772947867242292465436680098067692823828068996400482435403701416314965897940924323789690706977942236250822168895738379862300159377647165122893578601588161755782973523344604281512627203734314653197777416031990665541876397929334419521541341899485444734567383162499341913181480927777103863877343177207545654532207770921201905166096280490926360197598828161332316663652861932668633606273567630354477628035045077723554710585954870279081435624014517180624643626794561275318134078330336254232783944975382437205835311477119926063813346776879695970309833913077109870408591337464144282277263465947047458784778720192771528073176790770715721344473060570073349243693113835049316312840425121925651798069411352801314701304781643788518529092854520116583934196562134914341595625865865570552690496520985803385072242648293972858478316305777756068887644624824685792603953527734803048029005876075825104747091643961362676044925627420420832085661190625454337213153595845068772460290161876679524061634252257719542916299193064553779914037340432875262888963995879475729174642635745525407909145135711136941091193932519107602082520261879853188770584297259167781314969900901921169717372784768472686084900337702424291651300500516832336435038951702989392233451722013812806965011784408745196012122859937162313017114448464090389064495444006198690754851602632750529834918740786680881833851022833450850486082503930213321971551843063545500766828294930413776552793975175461395398468339363830474611996653858153842056853386218672523340283087112328278921250771262946322956398989893582116745627010218356462201349671518819097303811980049734072396103685406643193950979019069963955245300545058068550195673022921913933918568034490398205955100226353536192041994745538593810234395544959778377902374216172711172364343543947822181852862408514006660443325888569867054315470696574745855033232334210730154594051655379068662733379958511562578432298827372319898757141595781119635833005940873068121602876496286744604774649159950549737425626901049037781986835938146574126804925648798556145372347867330390468838343634655379498641927056387293174872332083760112302991136793862708943879936201629515413371424892830722012690147546684765357616477379467520049075715552781965362132392640616013635815590742202020318727760527721900556148425551879253034351398442532234157623361064250639049750086562710953591946589751413103482276930624743536325691607815478181152843667957061108615331504452127473924544945423682886061340841486377670096120715124914043027253860764823634143346235189757664521641376796903149501910857598442391986291642193994907236234646844117394032659184044378051333894525742399508296591228508555821572503107125701266830240292952522011872676756220415420516184163484756516999811614101002996078386909291603028840026910414079288621507842451670908700069928212066041837180653556725253256753286129104248776182582976515795984703562226293486003415872298053498965022629174878820273420922224533985626476691490556284250391275771028402799806636582548892648802545661017296702664076559042909945681506526530537182941270336931378517860904070866711496558343434769338578171138645587367812301458768712660348913909562009939361031029161615288138437909904231747336394804575931493140529763475748119356709110137751721008031559024853090669203767192203322909433467685142214477379393751703443661991040337511173547191855046449026365512816228824462575916333039107225383742182140883508657391771509682887478265699599574490661758344137522397096834080053559849175417381883999446974867626551658276584835884531427756879002909517028352971634456212964043523117600665101241200659755851276178583829204197484423608007193045761893234922927965019875187212726750798125547095890455635792122103334669749923563025494780249011419521238281530911407907386025152274299581807247162591668545133312394804947079119153267343028244186041426363954800044800267049624820179289647669758318327131425170296923488962766844032326092752496035799646925650493681836090032380929345958897069536534940603402166544375589004563288225054525564056448246515187547119621844396582533754388569094113031509526179378002974120766514793942590298969594699556576121865619673378623625612521632086286922210327488921865436480229678070576561514463204692790682120738837781423356282360896320806822246801224826117718589638140918390367367222088832151375560037279839400415297002878307667094447456013455641725437090697939612257142989467154357846878861444581231459357198492252847160504922124247014121478057345510500801908699603302763478708108175450119307141223390866393833952942578690507643100638351983438934159613185434754649556978103829309716465143840700707360411237359984345225161050702705623526601276484830840761183013052793205427462865403603674532865105706587488225698157936789766974220575059683440869735020141020672358502007245225632651341055924019027421624843914035998953539459094407046912091409387001264560016237428802109276457931065792295524988727584610126483699989225695968815920560010165525637567';
        return substr($pi, 0, $length);   
    }

    /**
     * convert a string to proper case.
     * 
     * @param  string $cString [the string to be modified]
     * @return string
     */
    public static function properCase($cString)
    {
        $aWords = explode(' ', trim($cString));
        foreach ($aWords as $cKey => $cValue) {
            $aWords[$cKey] = ucwords(strtolower(trim($cValue)));
        }
        return implode(' ', $aWords);

    }

    /**
     * generate a random string
     * 
     * @param  integer $length [length of random string]
     * @return string
     */
    public static function randomString($length = 32)
    {
        return substr(md5(rand(0, 9999) . uniqid('', 1)), 0, $length);
    }
    /**
     * redirect the internet browser to URL.
     * 
     * @param  string $value [url to redirect to.]
     */
    public static function redirect($value)
    {
        // because MS-Windows Sucks.
        if (strtoupper(substr(PHP_OS, 0, 3) == 'WIN')) {
            header("Refresh: 0; URL=$value");
        } else {
            header("Location: $value");
        }
        exit;

    }

    /**
     * remove a directory and all contents
     * @param  [type]  $directory [description]
     * @param  boolean $empty     [description]
     * @return boolean
     */
    public function recursiveRemoveDirectory($directory, $empty = false)
    {

        if (substr($directory, -1) == '/') {
            $directory = substr($directory, 0, -1);
        }

        if (!file_exists($directory) || !is_dir($directory)) {
            return false;
        } elseif (is_readable($directory)) {
            $handle = opendir($directory);
            while (false !== ($item = readdir($handle))) {
                if ($item != '.' && $item != '..') {
                    $path = $directory.'/'.$item;
                    if (is_dir($path)) {
                        $this->recursiveRemoveDirectory($path);
                    } else {
                        unlink($path);
                    }
                }
            }
            closedir($handle);
            if ($empty == false) {
                if (@!rmdir($directory)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * determine if variable is located in object
     * 
     * @param  mixed  $var [variable to be tested]
     * @return boolean
     */
    public static function isHash($var)
    {
        return is_array($var) && array_diff_key($var, array_keys(array_keys($var)));
    }
    /**
     * [salutations description]
     * @return [type] [description]
     */
    public static function basicSalutations() { 
        return array('Mr.' , 'Mrs.' , 'Ms.' , 'Dr.' , 'Prof.');
    }
    /**
     * [salutations description]
     * @return [type] [description]
     */
    public static function basicMonthList() { 
        return  array(
    '01' => 'January', 
    '02' => 'February', 
    '03' => 'March', 
    '04' => 'April', 
    '05' => 'May', 
    '06' => 'June', 
    '07' => 'July', 
    '08' => 'August', 
    '09' => 'September', 
    '10' => 'October', 
    '11' => 'November',
    '12' => 'December');  
    }
    /**
     * ping the url and return the http response code.
     * 
     * @param  string the URL to be tested
     * @return string 
     */
    public static function pingUrl($url = null)
    {
        if (empty($url)) {
            return false;
        }
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, false);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSLVERSION, 3);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $data = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $httpcode;
    }    

    /**
     *  generate a random letter
     * 
     * @return string 
     */ 
    public function randomLetter() 
    {
        return chr(97 + mt_rand(0, 25));
    }

    /**
     * replace UTF-8 characters for a safe string is ISO environments.
     * 
     * @param  string $string [the string to be converted.]
     * @return string
     */
    public function replaceUTF8Chars($string)
    {

        $search = array(chr(0xe2) . chr(0x80) . chr(0x98),
        chr(0xe2) . chr(0x80) . chr(0x99),
        chr(0xe2) . chr(0x80) . chr(0x9c),
        chr(0xe2) . chr(0x80) . chr(0x9d),
        chr(0xe2) . chr(0x80) . chr(0x93),
        chr(0xe2) . chr(0x80) . chr(0x94));

        $replace = array('&lsquo;', '&rsquo;', '&ldquo;','&rdquo;', '&ndash;','&mdash;');

        $string = str_replace($search, $replace, $string);
        $string = str_replace(chr(130), ',', $string);    // baseline single quote
        $string = str_replace(chr(131), 'NLG', $string);  // florin
        $string = str_replace(chr(132), '"', $string);    // baseline double quote
        $string = str_replace(chr(133), '...', $string);  // ellipsis
        $string = str_replace(chr(134), '**', $string);   // dagger (a second footnote)
        $string = str_replace(chr(135), '***', $string);  // double dagger (a third footnote)
        $string = str_replace(chr(136), '^', $string);    // circumflex accent
        $string = str_replace(chr(137), 'o/oo', $string); // permile
        $string = str_replace(chr(138), 'Sh', $string);   // S Hacek
        $string = str_replace(chr(139), '<', $string);    // left single guillemet
        $string = str_replace(chr(140), 'OE', $string);   // OE ligature
        $string = str_replace(chr(145), "'", $string);    // left single quote
        $string = str_replace(chr(146), "'", $string);    // right single quote
        $string = str_replace(chr(147), '"', $string);    // left double quote
        $string = str_replace(chr(148), '"', $string);    // right double quote
        $string = str_replace(chr(149), '-', $string);    // bullet
        $string = str_replace(chr(150), '-', $string);    // endash
        $string = str_replace(chr(151), '--', $string);   // emdash
        $string = str_replace(chr(152), '~', $string);    // tilde accent
        $string = str_replace(chr(153), '(TM)', $string); // trademark ligature
        $string = str_replace(chr(154), 'sh', $string);   // s Hacek
        $string = str_replace(chr(155), '>', $string);    // right single guillemet
        $string = str_replace(chr(156), 'oe', $string);   // oe ligature
        $string = str_replace(chr(159), 'Y', $string);    // Y Dieresis

        return $string;

    }

    /**
     * this function uses the gdimage library to reduce an image and save it to a path 
     * or replace the existing file. 
     * 
     * @param  string  $cInput   [file path for the source image]
     * @param  string  $cOutput  [the output path]
     * @param  integer $nH       [the image height]
     * @param  integer $nW       [the image width]
     * @param  string  $xType    [alternate ways to crop image]
     * @param  integer $nQuality [image quality]
     * 
     */
    public function reduceImage($cInput, $cOutput, $nH = 1600, $nW = 2560, $xType = 'normal', $nQuality = 100)
    {
        if (function_exists('imagecreatefromgif')) {
            $src_img = '';
            $nH == $nW ? $xType = 'square' : false;
            $cOutput == null ? $cOutput = $cInput : false;
            $cType = strtolower(substr(stripslashes($cInput), strrpos(stripslashes($cInput), '.')));

            if ($cType == '.gif' || $cType == 'image/gif') {
                $src_img = imagecreatefromgif($cInput); /* Attempt to open */
                $cType = 'image/gif';
            } elseif ($cType == '.png' || $cType == 'image/png' || $cType == 'image/x-png') {
                $src_img = imagecreatefrompng($cInput); /* Attempt to open */
                $cType = 'image/x-png';
            } elseif ($cType == '.bmp' || $cType == 'image/bmp') {
                $src_img = imagecreatefrombmp($cInput); /* Attempt to open */
                $cType = 'image/bmp';
            } elseif ($cType == '.jpg' || $cType == '.jpeg' || $cType == 'image/jpg' || $cType == 'image/jpeg' || $cType == 'image/pjpeg') {
                $src_img = imagecreatefromjpeg($cInput); /* Attempt to open */
                $cType = 'image/jpeg';
            } else {
            }

            if (!$src_img) {
                $src_img = imagecreatefromgif(_TYFOON . '/images/widget.gif'); /* Attempt to open */
                $cType = 'image/gif';
            } else {

                $tmp_img;
                list($width, $height) = getimagesize($cInput);
                if ($xType == 'square' && $width != $height) {
                    $biggestSide = '';
                    $cropPercent = .5;
                    $cropWidth   = 0;
                    $cropHeight  = 0;
                    $c1 = array();
                    if ($width > $height) {
                        $biggestSide = $width;
                        $cropWidth   = round($biggestSide*$cropPercent);
                        $cropHeight  = round($biggestSide*$cropPercent);
                        $c1 = array("x"=>($width-$cropWidth)/2, "y"=>($height-$cropHeight)/2);
                    } else {
                        $biggestSide = $height;
                        $cropWidth   = round($biggestSide*$cropPercent);
                        $cropHeight  = round($biggestSide*$cropPercent);
                        $c1 = array("x"=>($width-$cropWidth)/2, "y"=>($height-$cropHeight)/7);
                    }
                    $thumbSize = $nH;

                    if ($cType == 'image/gif') {
                        $tmp_img = imagecreate($thumbSize, $thumbSize);
                        imagecolortransparent($tmp_img, imagecolorallocate($tmp_img, 0, 0, 0));
                        imagecopyresized($tmp_img, $src_img, 0, 0, $c1['x'], $c1['y'], $thumbSize, $thumbSize, $cropWidth, $cropHeight);
                    } elseif ($cType == 'image/x-png') {
                        $tmp_img = imagecreatetruecolor($thumbSize, $thumbSize);
                        imagealphablending($tmp_img, false);
                        imagesavealpha($tmp_img, true);    
                        imagecopyresampled($tmp_img, $src_img, 0, 0, $c1['x'], $c1['y'], $thumbSize, $thumbSize, $cropWidth, $cropHeight);
                    } elseif ($cType == 'image/bmp') {
                        $tmp_img = imagecreatetruecolor($thumbSize, $thumbSize);
                        imagecopyresampled($tmp_img, $src_img, 0, 0, $c1['x'], $c1['y'], $thumbSize, $thumbSize, $cropWidth, $cropHeight);

                    } elseif ($cType == 'image/jpeg') {
                        $tmp_img = imagecreatetruecolor($thumbSize, $thumbSize);
                        imagecopyresampled($tmp_img, $src_img, 0, 0, $c1['x'], $c1['y'], $thumbSize, $thumbSize, $cropWidth, $cropHeight);
                    } else {
                        $tmp_img = imagecreatetruecolor($thumbSize, $thumbSize);
                        imagecopyresampled($tmp_img, $src_img, 0, 0, $c1['x'], $c1['y'], $thumbSize, $thumbSize, $cropWidth, $cropHeight);
                    }

                    imagedestroy($src_img);
                    $src_img = $tmp_img;
                } else {
                    $ow = imagesx($src_img);
                    $oh = imagesy($src_img);
                    if ($nH == 0 && $nW == 0) {
                        $nH = $oh;
                        $nW = $ow;
                    }
                    if ($nH == 0) {
                        $nH = $nW;
                    }
                    if ($nW == 0) {
                        $nW = $nH;
                    }
                    if ($nH > $oh && $nW > $ow) {
                        $width  = $ow;
                        $height = $oh;
                    } else {

                        if ($nW && ($ow < $oh)) {
                            $nW = ($nH / $oh) * $ow;
                        } else {
                            $nH = ($nW / $ow) * $oh;
                        }
                        $width  = $nW;
                        $height = $nH;
                    }
                    if ($cType == 'image/gif') {
                        $tmp_img = imagecreate($width, $height);
                        imagecolortransparent($tmp_img, imagecolorallocate($tmp_img, 0, 0, 0));
                        imagecopyresized($tmp_img, $src_img, 0, 0, $off_w, $off_h, $width, $height, $ow, $oh);
                    } elseif ($cType == 'image/x-png') {
                        $tmp_img = imagecreatetruecolor($width, $height);
                        imagealphablending($tmp_img, false);
                        imagesavealpha($tmp_img, true);
                        imagecopyresampled($tmp_img, $src_img, 0, 0, $off_w, $off_h, $width, $height, $ow, $oh);
                    } elseif ($cType == 'image/bmp') {
                        $tmp_img = imagecreatetruecolor($width, $height);
                        imagecopyresampled($tmp_img, $src_img, 0, 0, $off_w, $off_h, $width, $height, $ow, $oh);
                    } elseif ($cType == 'image/jpeg') {
                        $tmp_img = imagecreatetruecolor($width, $height);
                        imagecopyresampled($tmp_img, $src_img, 0, 0, $off_w, $off_h, $width, $height, $ow, $oh);
                    } else {
                        $tmp_img = imagecreatetruecolor($width, $height);
                        imagecopyresampled($tmp_img, $src_img, 0, 0, $off_w, $off_h, $width, $height, $ow, $oh);
                    }
                    imagedestroy($src_img);
                    $src_img = $tmp_img;
                }
            }
            // set the output
            if ($cType == 'image/gif') {
                imageGIF($src_img, $cOutput);
            } elseif ($cType == 'image/x-png') {
                imagePNG($src_img, $cOutput);
            } elseif ($cType == 'image/bmp') {
                imageJPEG($src_img, $cOutput, $nQuality);
            } elseif ($cType == 'image/jpeg') {
                imageJPEG($src_img, $cOutput, $nQuality);
            } else {
                imageJPEG($src_img, $cOutput, $nQuality);
            }
        }
    }
    /**
     * this function uses the gdimage library to resize an image and save it to a path 
     * or replace the existing file.
     * 
     * @param  string  $cInput   [file path for the source image]
     * @param  string  $cOutput  [the output path]
     * @param  integer $nH       [the image height]
     * @param  integer $nW       [the image width]
     * @param  string  $xType    [alternate ways to crop image]
     * @param  integer $nQuality [image quality]
     * 
     */
    public function resizeImage($cInput, $cOutput, $nH = 1600, $nW = 2560, $xType = 'normal', $nQuality = 100)
    {
        if (function_exists('imagecreatefromgif')) {
            $src_img = '';
            $nH == $nW ? $xType = 'square' : false;
            $cOutput == null ? $cOutput = $cInput : false;
            $cType = strtolower(substr(stripslashes($cInput), strrpos(stripslashes($cInput), '.')));

            if ($cType == '.gif' || $cType == 'image/gif') {
                $src_img = imagecreatefromgif($cInput); /* Attempt to open */
                $cType = 'image/gif';
            } elseif ($cType == '.png' || $cType == 'image/png' || $cType == 'image/x-png') {
                $src_img = imagecreatefrompng($cInput); /* Attempt to open */
                $cType = 'image/x-png';
            } elseif ($cType == '.bmp' || $cType == 'image/bmp') {
                $src_img = imagecreatefrombmp($cInput); /* Attempt to open */
                $cType = 'image/bmp';
            } elseif ($cType == '.jpg' || $cType == '.jpeg' || $cType == 'image/jpg' || $cType == 'image/jpeg' || $cType == 'image/pjpeg') {
                $src_img = imagecreatefromjpeg($cInput); /* Attempt to open */
                $cType = 'image/jpeg';
            } else {
            }

            if (!$src_img) {
                $src_img = imagecreatefromgif(_TYFOON . '/images/widget.gif'); /* Attempt to open */
                $cType = 'image/gif';
            } else {

                $tmp_img;
                list($width, $height) = getimagesize($cInput);
                if ($xType == 'square' && $width != $height) {
                    $biggestSide = '';
                    $cropPercent = .5;
                    $cropWidth   = 0;
                    $cropHeight  = 0;
                    $c1 = array();
                    if ($width > $height) {
                        $biggestSide = $width;
                        $cropWidth   = round($biggestSide*$cropPercent);
                        $cropHeight  = round($biggestSide*$cropPercent);
                        $c1 = array("x"=>($width-$cropWidth)/2, "y"=>($height-$cropHeight)/2);
                    } else {
                        $biggestSide = $height;
                        $cropWidth   = round($biggestSide*$cropPercent);
                        $cropHeight  = round($biggestSide*$cropPercent);
                        $c1 = array("x"=>($width-$cropWidth)/2, "y"=>($height-$cropHeight)/7);
                    }
                    $thumbSize = $nH;

                    if ($cType == 'image/gif') {
                        $tmp_img = imagecreate($thumbSize, $thumbSize);
                        imagecolortransparent($tmp_img, imagecolorallocate($tmp_img, 0, 0, 0));
                        imagecopyresized($tmp_img, $src_img, 0, 0, $c1['x'], $c1['y'], $thumbSize, $thumbSize, $cropWidth, $cropHeight);
                    } elseif ($cType == 'image/x-png') {
                        $tmp_img = imagecreatetruecolor($thumbSize, $thumbSize);
                        imagealphablending($tmp_img, false);
                        imagesavealpha($tmp_img, true);
                        imagecopyresampled($tmp_img, $src_img, 0, 0, $c1['x'], $c1['y'], $thumbSize, $thumbSize, $cropWidth, $cropHeight);
                    } elseif ($cType == 'image/bmp') {
                        $tmp_img = imagecreatetruecolor($thumbSize, $thumbSize);
                        imagecopyresampled($tmp_img, $src_img, 0, 0, $c1['x'], $c1['y'], $thumbSize, $thumbSize, $cropWidth, $cropHeight);

                    } elseif ($cType == 'image/jpeg') {
                        $tmp_img = imagecreatetruecolor($thumbSize, $thumbSize);
                        imagecopyresampled($tmp_img, $src_img, 0, 0, $c1['x'], $c1['y'], $thumbSize, $thumbSize, $cropWidth, $cropHeight);
                    } else {
                        $tmp_img = imagecreatetruecolor($thumbSize, $thumbSize);
                        imagecopyresampled($tmp_img, $src_img, 0, 0, $c1['x'], $c1['y'], $thumbSize, $thumbSize, $cropWidth, $cropHeight);
                    }

                    imagedestroy($src_img);
                    $src_img = $tmp_img;
                } else {
                    $ow = imagesx($src_img);
                    $oh = imagesy($src_img);
                    if ($nH == 0 && $nW == 0) {
                        $nH = $oh;
                        $nW = $ow;
                    }
                    if ($nH == 0) {
                        $nH = $nW;
                    }
                    if ($nW == 0) {
                        $nW = $nH;
                    }
                    if ($nH > $oh && $nW > $ow) {
                        $width  = $ow;
                        $height = $oh;
                    } else {

                        if ($nW && ($ow < $oh)) {
                            $nW = ($nH / $oh) * $ow;
                        } else {
                            $nH = ($nW / $ow) * $oh;
                        }
                        $width  = $nW;
                        $height = $nH;
                    }
                    if ($cType == 'image/gif') {
                        $tmp_img = imagecreate($width, $height);
                        imagecolortransparent($tmp_img, imagecolorallocate($tmp_img, 0, 0, 0));
                        imagecopyresized($tmp_img, $src_img, 0, 0, $off_w, $off_h, $width, $height, $ow, $oh);
                    } elseif ($cType == 'image/x-png') {
                        $tmp_img = imagecreatetruecolor($width, $height);
                        imagealphablending($tmp_img, false);
                        imagesavealpha($tmp_img, true);
                        imagecopyresampled($tmp_img, $src_img, 0, 0, $off_w, $off_h, $width, $height, $ow, $oh);
                    } elseif ($cType == 'image/bmp') {
                        $tmp_img = imagecreatetruecolor($width, $height);
                        imagecopyresampled($tmp_img, $src_img, 0, 0, $off_w, $off_h, $width, $height, $ow, $oh);
                    } elseif ($cType == 'image/jpeg') {
                        $tmp_img = imagecreatetruecolor($width, $height);
                        imagecopyresampled($tmp_img, $src_img, 0, 0, $off_w, $off_h, $width, $height, $ow, $oh);
                    } else {
                        $tmp_img = imagecreatetruecolor($width, $height);
                        imagecopyresampled($tmp_img, $src_img, 0, 0, $off_w, $off_h, $width, $height, $ow, $oh);
                    }
                    imagedestroy($src_img);
                    $src_img = $tmp_img;
                }
            }
            // set the output
            if ($cType == 'image/gif') {
                imageGIF($src_img, $cOutput);
            } elseif ($cType == 'image/x-png') {
                imagePNG($src_img, $cOutput);
            } elseif ($cType == 'image/bmp') {
                imageJPEG($src_img, $cOutput, $nQuality);
            } elseif ($cType == 'image/jpeg') {
                imageJPEG($src_img, $cOutput, $nQuality);
            } else {
                imageJPEG($src_img, $cOutput, $nQuality);
            }
        }
    }
    /**
     * [removeBinaryCharacters description]
     * @param  string $string [the string to clean]
     * @return string         [description]
     */
    public function removeBinaryCharacters($string) 
    {
        if (empty($string)) { 
            $string = trim($string);
            // This will remove unwanted characters.
            // Check http://www.php.net/chr for details
            for ($i = 0; $i <= 31; ++$i) { 
                $string = str_replace(chr($i), "", $string); 
            }
            $string = str_replace(chr(127), "", $string);
            // This is the most common part
            // Some file begins with 'efbbbf' to mark the beginning of the file. (binary level)
            // here we detect it and we remove it, basically it's the first 3 characters 
            if (0 === strpos(bin2hex($string), 'efbbbf')) {
                $string = substr($string, 3);
            }
        }
        return $string;        
    }
    /**
     * remove directory
     * 
     * @param  string  $directory [directory path]
     * @param  boolean $empty     [description]
     * @return boolean
     */
    public function rmDirectory($directory, $empty = false)
    {

        if (substr($directory, -1) == '/') {
            $directory = substr($directory, 0, -1);
        }

        if (!file_exists($directory) || !is_dir($directory)) {
            return false;
        } elseif (is_readable($directory)) {

            $handle = opendir($directory);

            while (false !== ($item = readdir($handle))) {

                if ($item != '.' && $item != '..') {

                    $path = $directory.'/'.$item;

                    if (is_dir($path)) {
                        self::rmDirectory($path);
                    } else {

                        unlink($path);
                    }

                }

            }
            closedir($handle);
            if ($empty == false) {
                if (@ !rmdir($directory)) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * clean a variable to follow the contstraints to ensure it only contains
     * valid characters
     * 
     * @param  string $value     [the string to be scrubed]
     * @param  string $cType     [what kind of scrubbing is required]
     * 
     *     ALPHA: only alpha characters
     *     TOKEN: only the characters A-Za-z0-9\-_/
     *     ALPHA_NUM: only Alpha Numeric values
     *     ALPHA_NUM_WS: only Alpha Numeric values and spaces
     *     SIMPLE: only standard input from the keyboard
     *     EMAIL: only a properly formatted email address.
     *     HYPERLINK: only a properly formatted hyperlink
     *     WHOLE_NUM: only a whole number - no floats
     *     FLOAT: characters that allow for floating numbers.
     *     FORMAT_NUM: characters that allow for a formatted number.
     *     SQL_INJECT: remove common characters taht are using for SQL injection.
     *     REMOVE_SPACES: no spaces allowed.
     *     REMOVE_DOUBLESPACE: no doublespaces allowed.
     *     BASIC: allow only very generic keyboard values.
     * 
     * @param  string $stopWords [a file or array of words that are not allowed]
     * @return string the variable passed with only the allowed characters
     */
    public static function scrubVar($value, $cType = 'BASIC', $stopWords = '')
    {

        $cType = strtoupper(trim($cType));

        if ($cType == 'ALPHA') {
            return preg_replace('/[^A-Za-z\s]/', '', $value);
        } elseif ($cType == 'TOKEN') {
            return preg_replace('%[^A-Za-z0-9\\\-\_\/]%', '', $value);
        } elseif ($cType == 'PHONE_NUM') {
            return preg_replace('%[^A-Za-z0-9\-\.\)\(\)\ ]%', '', $value);
        } elseif ($cType == 'ALPHA_NUM') {
            return preg_replace('/[^A-Za-z0-9]/', '', $value);            
        } elseif ($cType == 'ALPHA_NUM_WS') {
            return preg_replace('/[^A-Za-z0-9\s]/', '', $value);            
        } elseif ($cType == 'SIMPLE') {
            $cPattern = '[^A-Za-z0-9\s\-\_\.\ \,\@\!\#\$\%\^\&\*\(\)\[\]\{\}\?]';
            return preg_replace("/$cPattern/", '', $value);
        } elseif ($cType == 'HTML') {
            $cPattern = '[^A-Za-z0-9]';
            return preg_replace($cPattern, '', $value);
        } elseif ($cType == 'EMAIL') {
            $cPattern = '/(;|\||`|>|<|&|^|"|'."\t|\n|\r|'".'|{|}|[|]|\)|\()/i';
            return preg_replace($cPattern, '', $value);
        } elseif ($cType == 'HYPERLINK') {
            // match protocol://address/path/file.extension?some=variable&another=asf%
            $value = preg_replace("/\s([a-zA-Z]+:\/\/[a-z][a-z0-9\_\.\-]*[a-z]{2,6}[a-zA-Z0-9\/\*\-\?\&\%]*)([\s|\.|\,])/i", '', $value);
            // match www.something.domain/path/file.extension?some=variable&another=asf%
            return preg_replace("/\s(www\.[a-z][a-z0-9\_\.\-]*[a-z]{2,6}[a-zA-Z0-9\/\*\-\?\&\%]*)([\s|\.|\,])/i", '', $value);
        } elseif ($cType == 'MONEY') {
            return preg_replace('/[^0-9\.]/', '', $value);
        } elseif ($cType == 'MONEY_EXTENDED') {
            return preg_replace('/[^0-9\.\,]/', '', $value);
        } elseif ($cType == 'WHOLE_NUM') {
            return preg_replace('/[^0-9]/', '', $value);
        } elseif ($cType == 'FLOAT') {
            return preg_replace('/[^0-9\-\+\.]/', '', $value);
        } elseif ($cType == 'FORMAT_NUM') {
            return preg_replace('/[^0-9\.\,\-]/', '', $value);
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
            return preg_replace("/$cPattern/", '', preg_replace($aRestrictedWords, '', $value));

        } elseif ($cType == 'REMOVE_SPACES') {
            return preg_replace("/\s/", '', trim($value));
        } elseif ($cType == 'REMOVE_DOUBLESPACE') {
            return preg_replace("/\s+/", ' ', trim($value));
        } else {
            $cPattern = '[^A-Za-z0-9\s\-\_\.\ \,\@\!\#\$\%\^\&\*\(\)\[\]\{\}\?]';
            return preg_replace("/$cPattern/", '', strip_tags(trim($value)));
        }

    }
    /**
     * echo out all the global variables.     
     */
    public static function showGlobals()
    {
        echo '<hr /><strong>SESSION</strong><hr />';
        self::dumpVariable($_SESSION);        
        echo '<hr /><strong>GET</strong><hr />';
        self::dumpVariable($_GET);
        echo '<hr /><strong>POST</strong><hr />';
        self::dumpVariable($_POST);
        echo '<hr /><strong>REQUEST</strong><hr />';
        self::dumpVariable($_REQUEST);
        echo '<hr /><strong>SERVER</strong><hr />';
        self::dumpVariable($_SERVER);
        echo '<hr /><strong>COOKIE</strong><hr />';
        self::dumpVariable($_COOKIE);        
        echo '<hr /><strong>CONNECTION</strong><hr />';
        self::dumpVariable($this);
        self::bytes(memory_get_peak_usage(true));
    }    
    /**
     * [use the array provided to createa simple list of array items]
     * 
     * @param  [various] $variables [the values or objects used to seed the array]
     * @param  string $key [the name of the variable to be used as the key]
     * @param  string $value [the name of the variable to be used as the value]
     * @return array [the array of values]
     */
    public static function simpleArray($variables, $key, $value)
    {

        $return = array();
        !is_array($variables) ? $variables = (array) $variables : false;
        foreach ($variables as $n => $variable) { 
            !is_array($variable) ? $variable = (array) $variable : false;
            array_key_exists($key, $variable) ? $return[$variable[$key]] = $variable[$value] : false;
        }
        return $return;
    }
    /**
     * read file into string
     * 
     * @param  string  $f        [path to file]
     * @param  array   $output   [array of values to replace]
     * @param  integer $lDynamic [True - PHP executed on string]
     * @return string
     */
    public static function slurp($f, $output = array(), $lDynamic = 1)
    {
        !is_object($output) ? $output = (object) $output : false;        
        $cReturn = '';
        if (file_exists($f)) {
            ob_start(null, 0, PHP_OUTPUT_HANDLER_CLEANABLE ^ PHP_OUTPUT_HANDLER_REMOVABLE);
            if (strtolower(substr(stripslashes($f), strrpos(stripslashes($f), '.'))) == '.php' && $lDynamic) {
                include $f;
                $cReturn = @ ob_get_contents();
            } else {
                $retval = readfile($f);
                if (false !== $retval) { // no readfile error
                    $cReturn = @ ob_get_contents();
                }
            }
            ob_end_clean();
        } else {
            if (substr(trim(strtolower($f)), 0, 4) == 'http' 
                || substr(trim(strtolower($f)), 0, 5) == 'https') {                
                $cReturn = @ file_get_contents($f);            
            }
        }
        return $cReturn;
    }
    /**
     * [split a string to capture the first name]
     * 
     * @param  string $string [the string to split up]
     * @return array         [description]
     */
    public static function splitName($string = '') 
    {
        $values = preg_split("/\ /is", self::scrubVar(trim($string),'REMOVE_DOUBLESPACE'));
        $return = [];
        $return['first_name'] = array_shift($values);
        $return['last_name'] = implode(' ', $values);
        return $return;
    }
    /**
     * split a string into the an array with each row as a substring to a 
     * specific length.
     * 
     * @param  string $cString [string to split]
     * @param  integer $nLen [length of individual string, default = 72]
     * @return array
     */
    public static function splitString($cString, $nLen = 72)
    {
        $cString = preg_replace("/([\r\n])([\r\n])[\s]+/si", '', $cString); // remove line breaks replace with paragraph
        $aArray = preg_split("/\s+/", $cString);
        $aFinal = array();
        $cTemp = '';
        foreach ($aArray as $cLabel => $cContent) {
            if ((strlen($cContent) + strlen($cTemp)) > $nLen) {
                $aFinal[] = $cTemp;
                $cTemp = $cContent.' ';
            } else {
                $cTemp .= $cContent.' ';
            }
        }
        strlen($cTemp) > 0 ? $aFinal[] = $cTemp : false;
        return($aFinal);
    }

    /**
     * remove file extension from string
     * 
     * @param  string $value [string to be evaluated]
     * 
     * @return string the string without the file extension
     */
    public static function stripFileExtension($value)
    {
        $ext = strrchr($value, '.');
        if ($ext !== false) {
            $value = substr($value, 0, -strlen($ext));
        }
        return $value;

    }

    /**
     * emove extra white-space from string
     * @param  string $cStr [string to be modified]
     * @return string
     */
    public static function stripWhiteSpace($cStr)
    {
        return trim(preg_replace("/\s+/", ' ', $cStr));
    }

    /**
     * return a specified number of words from a text string
     * 
     * @param  string  $cString [string to extract substring]
     * @param  integer $nLen    [number of words to be extracted]
     * @return string 
     */
    public static function substrWord($cString, $nLen = 250)
    {
        $aArray = str_word_count(strip_tags(self::stripWhiteSpace($cString)), 1);
        $aSlice = array_slice($aArray, 0, $nLen);
        if (sizeof($aArray) > $nLen) {
            return implode(' ', $aSlice) . ' ...';
        } else {
            return implode(' ', $aSlice);
        }
    }

    /**
     * swaptext in string
     * 
     * @param  string $cText    [string to evaluate]
     * @param  string $cReplace [string to find]
     * @param  string $cWith    [string to replace with]
     * @return string           [evaluated string]
     */
    public function swapText ($cText, $cReplace, $cWith)
    {
        $cText = str_replace('&lt;@' . strtoupper($cReplace) . '@&gt;', $cWith, $cText);
        $cText = str_replace('<@' . strtoupper($cReplace) . '@>', $cWith, $cText);
        return $cText;
    }
    /**
     * [create a string token]
     * 
     * @param  string  $string [the string to use to create the token]
     * @param  integer $min    [the minimum number of characters in the token]
     * @param  integer $max    [the maximum number of characters in the token]
     * @return string          [the token]
     */
    public static function tokenize($string = '', $min = 7, $max = 10)
    {                
        empty($string) ? $string = self::randomString($max) : false;
        $string = preg_replace("/[^A-Za-z0-9\-\ ]/", '', trim($string));
        return strtolower(str_replace(' ', '-', preg_replace('/[\s+|\-+]/', ' ',$string)));
    }

    /**
     * convert string to uppercase.
     * @param  string $cString
     * @return string
     */
    public static function ucase($cString)
    {
        return strtoupper(trim($cString));
    }

    /**
     * upload file to server usin
     * 
     * @param  string $file   [path to file to store on server]
     * @param  string $target [target where to save file]
     * @return boolean 
     */
    public function uploadFile($file, $target)
    {
        if (is_uploaded_file($file['tmp_name'])) {
            if (move_uploaded_file($file['tmp_name'], $target)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    /**
     * @ignore     
     */
    public static function userTime($cDate)
    {
        if (!empty($cDate) && $cDate != '0000-00-00 00:00:00') {
            return date('Y-m-d H:i:s', (strtotime($cDate) + ((substr(date('c'), -6, 3) * 60) * 60))) . ' ' . date('T');
        } else {
            return '';
        }
    }

    /**
     * validate credit card number against valid card methods.
     * 
     * @param  string $cardnumber [string to be tested]
     * 
     *     Test Card Number: 4007000000027
     * 
     * @param  string $cardname   [string identifying card type to be tested]
     * @param  string $error      [error code if error detected and passed by reference]
     * @return  boolean
     */
    public function validateCreditCard ($cardnumber, $cardname, &$error)
    {

        $cardnumber = preg_replace('/[^0-9]/', '', $cardnumber);

        if ($cardnumber == '4007000000027') {
            return true;
        }

        $cards = array (
            array ('name' => 'Foonster',
                   'length' => '16',
                   'prefixes' => '71,73,78',
                   'checkdigit' => true
         ),
            array ('name' => 'American Express',
                   'length' => '15',
                   'prefixes' => '34,37',
                   'checkdigit' => true
         ),
            array ('name' => 'AMEX',
                   'length' => '15',
                    'prefixes' => '34,37',
                    'checkdigit' => true
         ),
            array ('name' => 'Carte Blanche',
                    'length' => '14',
                    'prefixes' => '300,301,302,303,304,305,36,38',
                    'checkdigit' => true
         ),
            array ('name' => 'Diners',
                    'length' => '14',
                    'prefixes' => '300,301,302,303,304,305,36,38',
                    'checkdigit' => true
         ),
            array ('name' => 'Diners Club',
                    'length' => '14',
                    'prefixes' => '300,301,302,303,304,305,36,38',
                    'checkdigit' => true
         ),
            array ('name' => 'Discover',
                    'length' => '16',
                    'prefixes' => '6011',
                    'checkdigit' => true
         ),
            array ('name' => 'Disc',
                'length' => '16',
                'prefixes' => '6011',
                'checkdigit' => true
         ),
            array ('name' => 'Enroute',
                'length' => '15',
                'prefixes' => '2014,2149',
                'checkdigit' => true
         ),
            array ('name' => 'JCB',
                'length' => '15,16',
                'prefixes' => '3,1800,2131',
                'checkdigit' => true
         ),
            array ('name' => 'Maestro',
                'length' => '16',
                'prefixes' => '5020,6',
                'checkdigit' => true
         ),
            array ('name' => 'MasterCard',
                'length' => '16',
                'prefixes' => '51,52,53,54,55',
                'checkdigit' => true
         ),
            array ('name' => 'MC',
                'length' => '16',
                'prefixes' => '51,52,53,54,55',
                'checkdigit' => true
         ),
            array ('name' => 'Solo',
                'length' => '16,18,19',
                'prefixes' => '6334,6767',
                'checkdigit' => true
         ),
            array ('name' => 'Switch',
                'length' => '16,18,19',
                'prefixes' => '4903,4905,4911,4936,564182,633110,6333,6759',
                'checkdigit' => true
         ),
            array ('name' => 'Visa',
                'length' => '13,16',
                'prefixes' => '4',
                'checkdigit' => true
         ),
            array ('name' => 'Visa Electron',
                'length' => '16',
                'prefixes' => '417500,4917,4913',
                'checkdigit' => true
         )
          );

        $ccErrorNo = 0;
        $ccErrors [0] = "Unknown card type";
        $ccErrors [1] = "No card number provided";
        $ccErrors [2] = "Credit card number has invalid format";
        $ccErrors [3] = "Credit card number is invalid";
        $ccErrors [4] = "Credit card number is wrong length";
        $ccErrors [5] = "Credit card number prefix invalid";

        // Establish card type
        $cardType = -1;
        for ($i=0; $i<sizeof($cards); $i++) {

            // See if it is this card (ignoring the case of the string)
            if (strtolower($cardname) == strtolower($cards[$i]['name'])) {
                $cardType = $i;
                break;
            }

        }

        // If card type not found, report an error
        if ($cardType == -1) {

            $errornumber = 0;
            $error = $ccErrors [$errornumber];

            return false;

        }

        // Ensure that the user has provided a credit card number
        if (strlen($cardnumber) == 0) {

            $errornumber = 1;
            $error = $ccErrors [$errornumber];

            return false;

        }

        // Remove any spaces from the credit card number

        $cardNo = str_replace(' ', '', $cardnumber);

        // Check that the number is numeric and of the right sort of length.

        if (!preg_match('/^[0-9]{13,19}$/i', $cardNo)) {

            $errornumber = 2;
            $error = $ccErrors [$errornumber];

            return false;

        }

        // Now check the modulus 10 check digit - if required
        if ($cards[$cardType]['checkdigit']) {

            $checksum = 0;  // running checksum total
            $mychar = "";   // next char to process
            $j = 1;         // takes value of 1 or 2

            // Process each digit one by one starting at the right
            for ($i = strlen($cardNo) - 1; $i >= 0; $i--) {

                // Extract the next digit and multiply by 1 or 2 on alternative digits.
                $calc = $cardNo{$i} * $j;

                // If the result is in two digits add 1 to the checksum total
                if ($calc > 9) {

                    $checksum = $checksum + 1;

                    $calc = $calc - 10;

                }

                // Add the units element to the checksum total

                $checksum = $checksum + $calc;

                // Switch the value of j

                ($j == 1) ? $j = 2 : $j = 1;
            }

            // All done - if checksum is divisible by 10, it is a valid modulus 10.
            // If not, report an error.

            if ($checksum % 10 != 0) {

                $errornumber = 3;

                $error = $ccErrors [$errornumber]." $checksum";

                return false;

            }
        }

        // The following are the card-specific checks we undertake.

        // Load an array with the valid prefixes for this card
        $prefix = explode(',', $cards[$cardType]['prefixes']);

        // Now see if any of them match what we have in the card number
        $PrefixValid = false;
        for ($i=0; $i<sizeof($prefix); $i++) {
            $exp = '/^' . $prefix[$i].'/';

            if (preg_match($exp, $cardNo)) {
                $PrefixValid = true;
                break;

            }
        }

         // If it isn't a valid prefix there's no point at looking at the length
        if (!$PrefixValid) {

            $errornumber = 5;
            $error = $ccErrors [$errornumber];

            return false;

        }

        // See if the length is valid for this card
        $LengthValid = false;
        $lengths = explode(',', $cards[$cardType]['length']);
        for ($j=0; $j<sizeof($lengths); $j++) {

            if (strlen($cardNo) == $lengths[$j]) {
                $LengthValid = true;
                break;
            }
        }

        // See if all is OK by seeing if the length was valid.
        if (!$LengthValid) {
            $errornumber = 4;
            $error = $ccErrors [$errornumber];
            return false;
        }
        // The credit card is in the required format.
        return true;

    }

    /**
    * write file to server
    *
    * @param  string  $cContent    [the data to be written]
    * @param  string  $cPath       [the file path to be written]
    * @param  string  $cMode       [the file mode to be used]
    *     
    * 'r'  Open for reading only; place the file pointer at the beginning of the file.
    *
    * 'r+' Open for reading and writing; place the file
    * pointer at the beginning of the file.
    *
    * 'w'  Open for writing only; place the file pointer at
    * the beginning of the file and truncate the file to zero length. If the file does not
    * exist, attempt to create it.
    *
    * 'w+' Open for reading and writing; place the file pointer
    * at the beginning of the file and truncate the file to zero length. If the file does not
    * exist, attempt to create it.
    *
    * 'a'  Open for writing only; place the file pointer at the
    * end of the file. If the file does not exist, attempt to create it.
    *
    * 'a+' Open for reading and writing; place the file pointer
    * at the end of the file. If the file does not exist, attempt to create it.
    *
    * 'x'  Create and open for writing only; place the file
    * pointer at the beginning of the file. If the file already exists, the fopen() call will
    * fail by returning FALSE and generating an error of level E_WARNING. If the file does not
    * exist, attempt to create it. This is equivalent to specifying O_EXCL|O_CREAT flags for the
    * underlying open(2) system call.
    *
    * 'x+' Create and open for reading and writing; otherwise it
    * has the same behavior as 'x'.
    *
    * 'c'  Open the file for writing only. If the file does
    * not exist, it is created. If it exists, it is neither truncated (as opposed to 'w'),
    * nor the call to this function fails (as is the case with 'x'). The file pointer is
    * positioned on the beginning of the file. This may be useful if it's desired to get an
    * advisory lock (see flock()) before attempting to modify the file, as using 'w' could
    * truncate the file before the lock was obtained (if truncation is desired,
    * ftruncate() can be used after the lock is requested).
    *
    * 'c+' Open the file for reading and writing; otherwise it has the same behavior as 'c'. 
    * 
     * @param  integer $nPermission [numerical representation of file permissions.]
     * @return boolean TRUE/FALSE if file was stored 
     */
    public static function writeToFile($cContent = '', $cPath = '', $cMode = 'w+', $nPermission = 0777)
    {    
        self::mkdirs(dirname($cPath));
        if ($fp = @ fopen(trim($cPath), $cMode)) {
            @ fwrite($fp, $cContent);
            @ fclose($fp);
            @ chmod(dirname($cPath), $nPermission);
            return true;
        } else {
            return false;
        }
    }

    /**
     * @ignore     
     */
    public function xmlDataDump($aArray, &$cOutput)
    {
        if (is_array($aArray)) {
            foreach ($aArray as $c => $v) {
                if (is_array($v)) {
                    if ($lChildren) {
                        converttoXML($v);
                    } else {
                        $cOutput .= $this->ucase("<$c>") . $v . $this->ucase("</$c>\n");
                    }
                } else {
                    $cOutput .= $this->ucase("<$c>") . $v . $this->ucase("</$c>\n");
                }
            }
        }
    }

    /**
     * [createXml description]
     * @param  string $startTag [description]
     * @param  array $array     [description]
     * @return string           [description]
     */
    protected function XMLCreate($startTag, $array)
    {
        $xml = new \XmlWriter();
        $xml->openMemory();
        $xml->startDocument('1.0', 'utf-8');
        $xml->startElement($startTag);
        $this->XMLWrite($xml, $array);
        $xml->endElement();
        return $xml->outputMemory(true);
    }

    /**
     * [writeXml]
     * @param  XMLWriter $xml  : Standard XMLWriter Class
     * @param  array
     * @return string
     */
    protected function XMLWrite(\XMLWriter $xml, $data)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $xml->startElement($key);
                $this->XMLWrite($xml, $value);
                $xml->endElement();
                continue;
            }
            $xml->writeElement($key, $value);
        }
    }
}
