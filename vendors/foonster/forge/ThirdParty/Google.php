<?php
/**
 * @author Nicolas Colbert <nicolas@foonster.com>
 * @copyright 2005 Foonster Technology
 */
/**
 * A set of classes/methods making it easier to use the Google API, please note 
 * that you are responsible for any charges incurred by using the Google API.  We always ensure
 * our clients have proper licensing
 */  
class Google
{
    private $error;
    private $key;
    private $sensor = 'true';

    /**
     * @ignore
     */
    public function __construct($key = '')
    {
        $this->key = $key;
    }

    /**
     * @ignore
     */
    public function __destruct()
    {
    }
    /**
     * Use GoogleMaps API to determine Lon/Lat - this may require a license depending on how you are using the application.
     *                
     * @param  string $address [represents address line 1]
     * @param  string $city    [represents city ]
     * @param  string $state   [represents state]
     * @param  string $zip     [represents zip code]
     * @param  string $country [represents country]
     * 
     * @return object
     */
    public function getAddressLonLat($address = '', $city = '', $state = '', $zip = '', $country = 'US')
    {

        $return = array('lon' => '0.00000', 'lat' => '0.00000');
        $input = array();
        !empty($address) ? $input[] = urlencode($address) : false;
        !empty($city) ? $input[] = urlencode($city) : false;
        !empty($state) ? $input[] = urlencode($state) : false;
        !empty($zip) ? $input[] = urlencode($zip) : false;
        !empty($country) ? $input[] = urlencode($country) : false;

        $url = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . implode(',+', $input) . '&sensor=' . $this->sensor;

        !empty($this->key) ? $url .= '&key=' . $this->key : false;

        $map = file_get_contents($url);
        $array = json_decode($map, true);

        if ($array['status'] == 'OK') {
            $return['lat'] = $array['results'][0]['geometry']['location']['lat'];
            $return['lon'] = $array['results'][0]['geometry']['location']['lng'];
        } else { 
            $return['message'] = $array['error_message'];
        }
        return (object) $return;
    }
    /**
     * [getError description]
     * @return [type] [description]
     */
    public function getError()
    {
        return $this->error;
    }
    /**
     * Use GoogleMaps API to determine Lon/Lat - this may require a license depending on how you are using the application.
     *                
     * @param  string $address [the string address we are looking for - it can accept parital]
     * @return object
     */
    public function getMapInformation($address = '')
    {
        $return = array('lat' => 0, 'lon' => 0, 'county' => '', 'state' => '', 'success' => '0', 'message' => '');

        $url = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode($address) . '&sensor=' . $this->sensor;

        !empty($this->key) ? $url .= '&key=' . $this->key : false;

        $map = file_get_contents($url);
        
        $array = json_decode($map, true);
        
        if (strtoupper($array['status']) == 'OK') { 
            $return = array();

            $return['success'] = 1;
            $return['formatted_address'] = $array['results'][0]['formatted_address'];            
            $return['lat'] = $array['results'][0]['geometry']['location']['lat'];
            $return['lon'] = $array['results'][0]['geometry']['location']['lng'];
            $return['location_type'] = $array['results'][0]['geometry']['location_type'];
            $return['viewport'] = $array['results'][0]['geometry']['viewport'];
            if (is_array($array['results'][0]['address_components'])) {
                foreach ($array['results'][0]['address_components'] as $k => $arr) {                    
                    if ($arr['types'][0] == 'administrative_area_level_1') { 
                        $return['abbv'] = $arr['short_name'];
                    }
                    $return[$arr['types'][0]] = $arr['long_name'];                
                }      
            }
            $return['county'] = trim(str_replace('County', '', $return['administrative_area_level_2']));
            $return['state'] = $return['administrative_area_level_1'];
        } else { 
            $return['message'] = $array['error_message'];
        }
        return (object) $return;
    } 
    /**
     * [setApiKey description]
     * @param string $key [description]
     */
    public function setApiKey($key = '') 
    { 
        $this->key = $key;
    }
    /**
     * 
     * 
     */ 
    public function setSensor($option = '') 
    {

        if (empty($option) || $option === false || strtolower(trim($option)) == 'false' || $option == 0) { 
            $this->sensor = 'false';
        } else { 
            $this->sensor = 'true';
        }

    }
    /**
     * return an HTML image call to generate the 
     * @param string $url [
     * - url: http://www.foonster.com
     * - email address: mailto:wherever@example.com
     * - MECARD:N:Owen,Sean;ADR:76 9th Avenue, 4th Floor, New York, NY 10011;TEL:+12125551212;EMAIL:srowen@example.com;
     * - sms:+15105550101?body=hello%20there
     * - geo:40.71872,-73.98905,100]
     * 
     * @param integer $size [the image size, note this is a square]
     * @param integer $ec_level [the error correction level to use when rendering the image.]
     * @param integer $margin [the margin to assign to the image.]
     * 
     * 
     * @return string [valid html img string]
     */ 
    public static function qrCode($url, $altText = 'QR Code', $size = 250, $ec_level = 1, $margin = 0)
    {
        $url = urlencode($url);
        return  '<img src="https://chart.apis.google.com/chart?chs=' . $size . 'x' . $size . '&cht=qr&chld=' . $ec_level.'|'.$margin . '&chl=' . $url . '" alt="' . $altText . '" widhtHeight="' . $size . '" widhtHeight="' . $size . '"/>';
    }
}
