<?php
/**
 * @author Nicolas Colbert <nicolas@foonster.com>
 * @copyright 2005 Foonster Technology
 */
/**
 * A set of methods to provide basic calendar functions beyond
 * the normal datetime.
 */ 
class Calendar extends \DateTime
{

    /**
     * @ignore
     */
    private $vars = array();    
    /**
     * @ignore
     */
    private $year;
    /**
     * @ignore
     */
    private $time;
    /**
     * @ignore
     */
    private $standard = 'US';
    /**
     * @ignore
     */
    private $_hoidays = array();
    /**
     * @ignore
     */
    public $error;

    public $days = array();

    /**
     * class constructor and will load the default year
     * @param integer $year numeric representing the year to load, if null then the current year as determined by the server running the code returns.
     */
    public function __construct($year = null)
    {       
        empty($year) ? $this->year = date('Y') : $this->year = $year;        
        !is_numeric($this->year) ? $this->year = date('Y') : false;        
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
     * build an asosociative array containing the days and weeks. 
     * additionally, this does NOT use ISO 8601 to make the weeks.
     * 
     * @param integer $month [number representing the month]
     * @param integer $year  [number representing the year]
     * 
     * @return array
     * 
     */
    public function fillByWeek($month, $year) 
    {
        $days = array();
        $weeks = array();
        $date = new \stdClass();

        $date->month = strtotime($month . '/1/' . $year);
        $date->previous = strtotime("-1 month", $date->month);
        $date->next = strtotime("+1 month", $date->month);
        $dayOfWeek = date('w', $date->month);        
        if ($dayOfWeek > 0) {
            $n = date('t', $date->previous);
            for ($i = $dayOfWeek; $i > 0; $i--) {   
                $days[] = date('m', $date->previous) . '/' . $n . '/' . date('Y', $date->previous);
                $n--;    
            }
            $days = array_reverse($days);
        }
        for ($i = 1; $i <= date('t', $date->month); $i++) {         
            $days[] = date('m', $date->month) . '/' . $i . '/' . date('Y', $date->month);
        }

        $dayOfWeek = date('w', strtotime("-1 day", $date->next));
        if (date('w', strtotime("-1 day", $date->next)) < 6) {
            $n = 1;
            for ($i = date('w', strtotime("-1 day", $date->next)); $i < 6; $i++) {  
                $days[] = date('m', $date->next) . '/' . $n . '/' . date('Y', $date->next);
                $n++;
            }
        }

        // the calendar is built on a non-ISO 8601 - because Sunday is part of the last
        // week.
        $n = 0;
        $count = 0;
        foreach ($days as $day) {
            if ($day == 1 && self::getWeekNumber($day) == 53) { 
                $weeks[1][] = $day;    
            } else { 
                $weeks[self::getWeekNumber($day)][] = $day;    
            }
        }

        return $weeks;
    }

    /**
     * fill array with what would appear on a calendar page.
     * 
     * @param  integer $month [integer representing the requested month]
     * @param  integer $year  [integer representing the requested year]
     * @return array
     */
    public function fillPage($month, $year) 
    {
        $days = array();
        $date = new \stdClass();

        $date->month = strtotime($month . '/1/' . $year);
        for ($i = 1; $i <= date('t', $date->month); $i++) {         
            $d = date('m', $date->month) . '/' . $i . '/' . date('Y', $date->month);
            $days[] = $d;
        }
        return $days;
    }
    /**
     * 
     * 
     * 
     */ 
    public function getBusinessDays($time, $end) { 

        $time = strtotime($time);
        $end = strtotime($end);

        $loop = 0;
        $count =0;
        while ($time < $end)
        {
            $loop++;
            $day_of_week = date("N", $time) - 1; // 0 = Monday, 6 = Sunday

            if ($day_of_week >= 0 && $day_of_week <= 5) { 

                if ($this->isHoliday(date("m/d", $time))) { 
                    $celebrate = $this->getHoliday(date("m/d", $time));                            
                    if ($celebrate->type != 'National Holiday') { 
                        $count++;
                    }
                } else { 
                    $count++;
                }
            }
            $time = strtotime("+1 day", $time); // add one day to time

            if($loop == 1000) { break; }
        }
        return $count;
    }

    /**
     * return the week number the day appears on.
     * 
     * @param string $date [string in any format representing the date to be checkeed] 
     * 
     * @return integer 
     * 
     */ 
    public function getWeekNumber($date)
    {
        if ($this->standard == 'US') {
            $week = date('W', strtotime($date));            
            $dayOfWeek = date('w', strtotime($date));            
            ($dayOfWeek == 0) ? $week++ : false;     
            ($week == 54) ? $week = 1 : false;
        } else {
            $week = date('W', strtotime($date));
        }

        return intval($week);
    }
    /**
     * @ignore
     */
    public function loadCalendar()
    {
        for ($i = 1; $i <= 12; $i++) {   
            $month = self::month($i, $this->year);            
            $this->{strtolower($month->long_name)} = $month;        
        }
    }

    /**
     * generate general information about requested month.
     * 
     * @param  integer $month [integer representing the ]
     * @param  integer $year  [year]
     * @param  boolean $parent [true, will build information about next/previous months as well]
     * @return object an object containing all informaiton about this month.
     * 
     * Example
     * 
     * - long_name => December
     * - long_number => 12
     * - short_name => December
     * - short_number => 12
     * - number_of_days => 31
     * - year => 1971
     * - days => Array (list of julian calendar formatted days.)        
     * - weeks => Array (integer weeknumber => Array (list of julian calendar formatted days.)        
     * - previous => self:month stdClass - same as month - but does not carry forward with "next/previous"
     * - next => => self:month  - same as month - but does not carry forward with "next/previous"
     * */
    public function month($month = null, $year = null, $parent = true) 
    {
        is_numeric($month) ? $time = strtotime($month . '/1/' . $year) : $time = strtotime($month . ' 1st, ' . $year);
        $month = array();
        $month['long_name'] = date('F', $time);
        $month['long_number'] = date('m', $time);
        $month['short_name'] = date('M', $time);
        $month['short_number'] = date('n', $time);
        $month['number_of_days'] = date('t', $time);
        $month['year'] = date('Y', $time);
        $month['days'] = self::fillPage($month['short_number'], $year);
        $month['weeks'] = self::fillByWeek($month['short_number'], $year);
        if ($parent) {            
            $previous = strtotime("-1 month", $time);
            $next = strtotime("+1 month", $time);
            $month['previous'] = self::month(date('m', $previous), date('Y', $previous), false);
            $month['next'] = self::month(date('m', $next), date('Y', $next), false);
        }
        return (object) $month;
    }

    /**
     * 
     * return a text string representing the season based on the provided month.
     * 
     * @param string $string [the date to evaluate]
     * 
     * @return string
     * 
     */ 
    public function season($string)
    {
        $string = date('n', strtotime($string));

        $month = intval($string);

        $seasons = array(
            1 => 'Winter',
            2 => 'Winter',
            3 => 'Spring',
            4 => 'Spring',
            5 => 'Spring',
            6 => 'Summer',
            7 => 'Summer',
            8 => 'Summer',
            9 => 'Fall',
            10 => 'Fall',
            11 => 'Fall',
            12 => 'Winter');

        return $seasons[$month];
    }

    /**
     * uses the default for PHP, but this will change over to other applicable 
     * standards. ISO 8601 is the only standard that changes items at this time.
     * 
     * @param string $standard [the standard to use for date functions. - default is US - the only other option is EU or ISO 8601]
     * 
     */ 
    public function standard($standard) { 
        if ($standard == 'EU') { 
            $this->standard = 'EU';
        } else {
            $this->standard = 'US';
        }
    }

    /**
     * sets the default timezone used by all date/time functions.
     * 
     * @param string $timezone [The timezone identifier, like UTC or Europe/Lisbon or America/Anchorage.]
     * 
     * @return boolean 
     */ 
    public function timezone($timezone = 'UTC')
    {
        return date_default_timezone_set($timezone);
    }

    /**
     * change the year assgined to the class, there is no return value but the method
     * will run the loadCalendar function.
     *          
     * @param integer $year 
     */
    public function year($year)
    {
        empty($year) ? $this->year(date('Y')) : $this->year = $year;
        !is_numeric($this->year) ? $this->year = date('Y') : $this->year = $year;    
        self::loadCalendar();

    }

    public function addHoliday($date, $name, $reason = '', $moreinfo = '', $type = '')
    {

    }

    public function isHoliday($date, $type = 'all')
    {
        $date = date('m/d', strtotime($date));
        $days = $this->listHolidays($type);

        if (array_key_exists($date, $days)) { 
            return true;
        } else { 
            return false;
        }
    }

    public function getHoliday($date, $type = 'all')
    {
        $date = date('m/d', strtotime($date));
        $days = $this->listHolidays($type);
        if (array_key_exists($date, $days)) { 
            return (object) $days[$date];
        } else { 
            return (object) array('name' => '', 'reason' => '', 'moreinfo' => '', 'type' => '');
        }        
    }

    /**
     * [getdays description]
     * @return [type] [description]
     */
    public function getdays()
    {    
        for($m = 1; $m <= 12; $m++) { 
            $days = date("t", strtotime($m . '/01/' . $this->year));
            for($d = 1; $d <= $days; $d++) { 
                $this->days[] = $m . '/' . $d . '/' . $this->year;
            }
        }
    }


    /**
     * @ignore
     * 
     */
    public function listHolidays($type = 'all')
    {

        $type = strtolower(trim($type));

        $holidays = array();

        $global = array(
            '01/01' => array('name' => 'New Years Day', 'reason' => '', 'moreinfo' => '', 'type' => 'National Holiday'),
            '12/31' => array('name' => 'Last Day of the Year', 'reason' => '', 'moreinfo' => '', 'type' => 'National Holiday'),
       );
        $christian = array(
            '12/24' => array('name' => 'New Years Day', 'reason' => '', 'moreinfo' => '', 'type' => 'National Holiday'),
            '12/25' => array('name' => 'Last Day of the Year', 'reason' => '', 'moreinfo' => '', 'type' => 'National Holiday'),
       );

        $usa = array(
            '07/14' => array('name' => 'Independence Day', 'reason' => '', 'moreinfo' => '', 'type' => 'National Holiday'),
            date('m/d', strtotime('last thursday of november ' . $this->year)) => array('name' => 'Thanksgiving Day', 'reason' => '', 'moreinfo' => '', 'type' => 'National Holiday'),
            '12/07' => array('name' => 'Pearl Harbor Day', 'reason' => '', 'moreinfo' => '', 'type' => 'Government Holiday'),
       );

        $islam = array();

        $chinese = array();
        $hebrew = array();

        if ($type == 'global' || $type == 'all') { 
            $holidays = array_merge($global, $holidays);
        }

        if ($type == 'christian' || $type == 'all') { 
            $holidays = array_merge($christian, $holidays);
        }

        if ($type == 'usa' || $type == 'all') { 
            $holidays = array_merge($usa, $holidays);
        }

        return $holidays;
    }

}
