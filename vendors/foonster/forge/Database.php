<?php
/**
 * @author Nicolas Colbert <nicolas@foonster.com>
 * @copyright 2005 Foonster Technology
 */
/**
 *                                                                   
 * A database abstraction class
 */
class Database
{
    private $dbh;
    private $database = '';
    private $host = 'localhost';
    private $type = 'mysql';
    private $dbUser = '';
    private $dbPass = '';
    private $dsn = '';
    private $lastCommandError = false;
    private $connectionError = false;
    private $errorMessage = false;
    private $databases = array();
    private $vars;

    /**
     * [__construct]
     * 
     * @param string $database   [the database name]
     * @param string $user       [the user associated with the database connection]
     * @param string $pass       [the password associated with the database connection]
     * @param string $dsn 
     *                           [The Data Source Name, or DSN, contains the information required to connect to
     *                           the database. ]
     */
    public function __construct($database = null, $user = null , $pass = null, $dsn = null, $host = null, $type = null)
    {
        if (!empty($database)) {
            $this->database = $database;
            !empty($user) ? $this->dbUser = $user : false;
            !empty($pass) ? $this->dbPass = $pass : false;
            !empty($dsn)  ? $this->dsn = $dsn : false;
            !empty($host) ? $this->host = $host : false;
            !empty($type) ? $this->type = $type : false;
            $this->connect();
        }
    }

    /**
     * @ignore
     */
    public function __destruct()
    {
    }

    /**
     * build a PDO query string
     * 
     * @param  string $table       [the table name to use as a template to build query]
     * @param  string $type        [what type of CRUD operation is occuring]     
     * @param  array  $variables   [list of variables to include in the record update]
     * @param  array  $constraints [list of constraining variables]
     * @param  string $limit       [limit on returned or impacted records]
     * @return string              [A PDO acceptable query string]
     */
    public function buildQuery(
        $table, 
        $type = 'INSERT', 
        $variables = array(), 
        $constraints = array(), 
        $limit = '1')
    {
        $sql = '';
        $fields = $values = $updates = $columns = array();
        $type = strtoupper(trim($type));
        $sth = $this->dbh->prepare('DESCRIBE ' . $table);
        $sth->execute();
        $limit > 0 ? $limit = " LIMIT $limit" : $limit = '';
        $tableinfo = $sth->fetchAll(\PDO::FETCH_ASSOC);
        // add field names by name
        foreach ($tableinfo as $key => $array) {
            strtoupper($array['Key']) == 'PRI' ? $primary = $array['Field'] : false;
            $columns[$array['Field']] = $array;
        }

        if ($type == 'SELECT') {
            foreach ($columns as $key => $array) {
                if (array_key_exists($array['Field'], $variables) && !empty($variables[ $array['Field'] ])) {
                    $updates[] = $array['Field'] . " = :$array[Field]";
                }
            }
            $sql = "SELECT FROM $table WHERE " . implode(' AND ', $updates) . "$limit";
        } elseif ($type == 'DELETE') {
            foreach ($columns as $key => $array) {
                if (array_key_exists($array['Field'], $variables) && !empty( $variables[ $array['Field'] ] )) {
                    $updates[] = $array['Field'] . " = :$array[Field]";
                }
            }
            $sql = "DELETE FROM $table WHERE " . implode(' AND ', $updates) . "$limit";
        } elseif ($type == 'UPDATE') {
            foreach ($columns as $key => $array) {
                if (array_key_exists($array['Field'], $variables) && $primary != $array['Field']) {
                    if (!is_object($variables[$array['Field']]) && !is_array($variables[$array['Field']])) { 
                        $updates[] = $array['Field'] . " = :$array[Field]";
                    }                    
                }
            }
            if (sizeof($constraints) > 0) {
                foreach ($constraints as $key => $array) {
                    $where[] = $key . " = :$key";
                }
                $sql = "UPDATE $table SET " . implode(', ', $updates) . " WHERE " . implode(' AND ', $where) . "$limit";
            } else {
                $sql = "UPDATE $table SET " . implode(', ', $updates) . " WHERE " . $primary . " = :" . $primary . "$limit";
            }
        } else {
            // check to see all null value no fields are accounted for.
            foreach ($columns as $key => $array) {
                if ($array['Field'] != $primary) {
                    if (!array_key_exists($array['Field'], $variables)) {
                        if ($array['Null'] == 'NO') {
                            if (!in_array($array['Field'], $fields)) {
                                $fields[] = $array['Field'];
                                $values[] = "'$array[Default]'";
                            }
                        }
                    } else {
                        $fields[] = $array['Field'];
                        $values[] = ":$array[Field]";
                    }
                }
            }   
            if (array_key_exists($primary, $variables)) { 
                if (sizeof($fields) > 0) { 
                    $sql = "INSERT INTO $table ( $primary, " . implode(' , ', $fields) . " ) VALUES ( '$variables[$primary]', " . implode(' , ', $values) . " );";
                } else { 
                    $sql = "INSERT INTO $table ($primary) VALUES ('$variables[$primary]');";
                }                
            } else { 
                $sql = "INSERT INTO $table ( " . implode(' , ', $fields) . ' ) VALUES ( ' . implode(' , ', $values) . " );";    
            }
        }
        return $sql;
    }

    /**
     * change the database 
     * 
     * @param  string $database [name of the database]
     * 
     * @return none
     */
    public function changeDatabase($database) 
    {
        $this->setDatabase($database);
        $this->connect();
    }
    /**
     * [cleanVariables description]
     * @param  [type] $table [description]
     * @param  [type] &$vars [description]
     * @return [type]        [description]
     */
    private function cleanVariables($table, &$vars)
    {
        $table = $this->describeTable($table);
        foreach ($vars as $key => $value) { 
            if (array_key_exists($key, $table)) { 
                if (!empty($value)) { 
                    if (preg_match("/decimal/is", $table[$key]['Type'])) { 
                        $deci = substr($table[$key]['Type'], -2, 1);
                        $vars[$key] = number_format($this->scrubVar($value, 'MONEY'), $deci, '.', '');
                    }
                }
            }
        }
    }
    /**
     * establish a connection to the requested database
     * 
     * @return none
     */
    public function connect()
    {
        $dsn;
        if ($this->type == 'mysql') {
            $dsn = $this->type . ':';            
            strlen($this->database) > 0 ? $dsn .= 'dbname=' . $this->database . ';' : false;             
            $dsn .= 'host=' . $this->host;            
        }
        try {
            $this->dbh = new \PDO($dsn, 
                $this->dbUser, 
                $this->dbPass);  
            $this->dbh->setAttribute( \PDO::MYSQL_ATTR_FOUND_ROWS, true);   
            $this->dbh->setAttribute (\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
            $this->dbh->setAttribute (\PDO::ATTR_ERRMODE, \PDO::ERRMODE_WARNING );
        } catch (\PDOException $e) {
            $this->connectionError = true;
            $this->errorMessage = "PDO Exception!: " . $e->getMessage() . "<br/>";
        } catch (\Exception $e) {
            $this->connectionError = true;
            $this->errorMessage = "Exception!: " . $e->getMessage() . "<br/>";
        }
    }

    /**
     * return the connection
     * 
     */ 
    public function connection()
    {
        return $this->dbh;
    }

    /**
     * return the value stored in the connectionError variable
     * 
     * @return boolean TRUE/FALSE - if the connect command had an issue.
     * 
     */
    public function connectionError() 
    {
        return $this->connectionError;
    }

    /**
     * get current connection settings 
     * 
     * @param string $return [what type or method to be returned]
     * 
     * @return mixed string|array [values for the connection]
     */
    public function connectionInfo()
    {        
        return array(
        'connection' => $this->dbConn,
        'database' => $this->dbName,
        'user' => $this->dbUser,
        'password' => $this->dbPass
        );
    }
    /**
     * [fill all the variables and santatize the variables where appropriate]
     * @param  string $table      [the table to be used to run the variable comparison]
     * @param  array &$variables  [the variables be to used to fill the variables]
     * @return none - returned by reference.
     */
    public function fillVariables($table, &$variables) 
    {
        $columns = array();
        $columns = $this->describeTable($table);
        foreach ($columns AS $key => $array) {            
            if (!array_key_exists($key, $variables)) {
                ($array['Null'] == 'NO' && $variables[$array['Field']]) ? $variables[$key] = $array['Default'] : false;
                if ($array['Null'] == 'NO' && strlen($variables[$array['Field']]) == 0) {
                    if (!preg_match("/auto_increment/is", $array['Extra'])) { 
                        empty($array['Default']) ? $variables[$key] = '' : $variables[$key] = $array['Default'];
                    } else { 
                        continue;
                    }
                } else {                     
                    if (strlen($variables[$array['Field']]) == 0) { 
                        !empty($array['Default']) ? $variables[$key] = "'" . $array['Default'] . "'" : $array['Default'] = '';
                    } else { 
                       $variables[$key] = "'" . trim($aVars[$array['Field']]) . "'";         
                    }                    
                }
                (strlen($variables[$array['Field']]) == 0 && preg_match("/int/is", $array['Type'])) ? $variables[$array['Field']] = 0 : false;                
                (strlen($variables[$array['Field']]) == 0 && $array['Type'] == 'date') ? $variables[$array['Field']] = '0000-00-00' : false;
                (strlen($variables[$array['Field']]) == 0 && $array['Type'] == 'datetime') ? $variables[$array['Field']] = '0000-00-00 00:00:00' : false;            
            } else {
                (strlen($variables[$array['Field']]) == 0 && substr( $array['Type'], 0, 3 ) == 'int') ? $variables[$array['Field']] = 0 : false;            
                if (substr($array['Type'], 0, 4) == 'deci') {                     
                    $deci = substr($array['Type'], -2, 1);
                    if (strlen($variables[$array['Field']]) == 0) {                         
                        $variables[$array['Field']] = number_format(0, $deci, '.', '');
                    } else { 
                        $variables[$array['Field']] = number_format($this->scrubVar($variables[$array['Field']], 'FLOAT'), $deci, '.', '');
                    }
                }
                (strlen($variables[$array['Field']]) == 0 && $array['Null'] == 'NO') ? $variables[$array['Field']] = $array['Default'] : false;
            }
        } 
    }
    /**
     * 
     * 
     */ 
    public function getId($table, $id = 0) 
    {
        if ($id > 0) { 
            $field = $this->getPrimaryKey($table);
            $query = "SELECT * FROM $table WHERE $field = :id;";
            $sth = $this->dbh->prepare($query);
            $sth->execute(array('id' => $id));
            if ($sth->rowCount() == 1) {
                return $sth->fetch(\PDO::FETCH_OBJ);
            } else {
                return new \StdClass();
            }

        }
    }
    /**
     * 
     * 
     */ 
    public function getRow($table, $id = 0) 
    {
        if ($id > 0) { 
            $field = $this->getPrimaryKey($table);
            $query = "SELECT * FROM $table WHERE $field = :id;";
            $sth = $this->dbh->prepare($query);
            $sth->execute(array('id' => $id));
            if ($sth->rowCount() == 1) {
                return $sth->fetch(\PDO::FETCH_OBJ);
            } else {
                return new \StdClass();
            }

        }
    }
    /**
     * 
     * 
     * 
     * 
     */
    public function generateFullTextQuery($searchTxt = '')
    {
        //$text = '';
        $this->sqlQuery("SHOW VARIABLES LIKE 'ft_min%';");
        $terms = explode(' ', preg_replace('/\s+/',' ', trim($searchTxt)));

        foreach ($terms AS $cKey => $cValue) {
            if (strlen($cValue) >= $var->Value) { 
                $terms[$cKey] = trim($cValue) . '*';
            } else { 
                unset($terms[$cKey]);
            }
        }
        if (sizeof($terms) > 0) {
            $terms[0] = '+' . $terms[0];
            return implode(' ', $terms);
        } else { 
            return '';
        }    
    }
    /**
     * split the string into the component sql calls.
     * 
     * @param  string $sql [string to be parsed into smaller sql statements.]
     * @return string the final sql to be executed
     * 
     */
    public function db_split_sql($sql) 
    {
       //delete comments
        $lines = explode("\n",$sql);
        $sql = '';
        foreach($lines as $line){
            $line = trim($line);
            ($line && !self::startsWith($line,'--')) ? $sql .= trim($line) . "\n" : false;
        }
        //convert to array
        return explode(";", $sql);
    }

    private function startsWith($haystack, $needle){
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }
    /**
     * [delete a single database record.
     * 
     * @param  string  $table [the name of the table to be modified]
     * @param  integer $id    [the id number of the record to be removed]
     * @return boolean 
     */
    public function delete($table, $id = 0) 
    {
        if ($id > 0) { 
            $field = $this->getPrimaryKey($table);
            $query = "DELETE FROM $table WHERE $field = :id;";
            $sth = $this->dbh->prepare($query);
            $sth->execute(array('id' => $id));
            if ($sth->rowCount() == 1) {
                return true;
            } else {
                $err = $sth->errorInfo();
                $this->errorMessage = 'Error: ' . $err[2];
                return false;
            }

        }
    }
    /**
     * 
     * query the currently selected database for properties DESCRIBE
     * 
     * @param  string $table [name of table]
     * 
     * @return array         [array attributes]
     * 
     */
    public function describeTable($table)
    {
        $columns = array();
        $sth = $this->dbh->prepare('DESCRIBE ' . $table);
        $sth->execute();
        $tableinfo = $sth->fetchAll(\PDO::FETCH_ASSOC);
        // add field names by name
        foreach ($tableinfo as $key => $array) {            
            $columns[$array['Field']] = $array;
        }
        return $columns;
    }
    /**
     * 
     * return message from errorInfo command
     * 
     * @return string
     * 
     */
    public function errorInfo()
    {
        return $this->dbh->errorInfo();
    }

    /**
     * [errorMessage description]
     * @return [type] [description]
     */
    public function errorMessage()
    {
        if (is_array($this->errorMessage)) { 
            return implode("\n", $this->errorMessage);
        } else { 
            return $this->errorMessage;
        }        
    }

    /**
     * [fetch description]
     * @param  string $type [description]
     * @return [type]       [description]
     */
    public function fetch($type = 'FETCH_OBJ')
    {
        $tableinfo = $sth->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * [getError description]
     * @return [type] [description]
     */
    public function getError()
    {
        $err = $this->dbh->errorInfo();
        return $err[2];
    }
    /**
     * 
     * query the currently selected database for properties DESCRIBE
     * 
     * @param  string $table [name of table]
     * 
     * @return array         [array attributes]
     * 
     */
    public function getFields($table)
    {
        $sth = $this->dbh->prepare('DESCRIBE ' . $table);
        $sth->execute();
        return $sth->fetchAll(\PDO::FETCH_COLUMN, 0);        
    }

    /**
     * [getPrimaryKey description]
     * @param  [type] $table [description]
     * @return [type]        [description]
     */
    public function getPrimaryKey($table)
    {
        $primaryKeyName = '';

        $table = $this->describeTable($table);

        foreach ($table as $key => $value) { 
            if (strtoupper($value['Key']) == 'PRI') { 
                return $value['Field'];
            }
        }
    }

    /**
     * pre-defined sql insert command
     * @param  string $table     [the table to update]
     * @param  array  $variables [variables to be used when updating the record]
     * 
     * @return integer    
     */ 
    public function insert($table, $variables)
    {    
        is_object($variables) ? $variables = (array) $variables : false;
        $field = $this->getPrimaryKey($table);
        if (empty($variables[$field])) { 
            unset($variables[$field]);
        } 
        $this->fillVariables($table, $variables);
        $sql = $this->buildQuery($table, 'insert', $variables);
        $fields = $this->setVariables($sql, $variables);
        $sth = $this->runQuery($sql, $fields->vars);
        $err = $sth->errorInfo();
        if ($err[1] > 0) {
            $this->errorMessage = json_encode($err);
            return 0;
        } else {
            return $this->insertId();
        }
    }

    /**
     *  @ignore
     */
    public function isError()
    {
        return $this->errorMessage;
    }    

    /**
     * 
     * id from the last insert command with auto-increment.
     * 
     * @return integer
     * 
     */
    public function insertId()
    {
        return $this->dbh->lastInsertId();
    }

    /**
     * [loadRecords]
     * @param  string $sql    [the query to run]
     * @param  array  $params [the parameters to bind to the query string.]
     * @return mixed
     */
    public function loadRecords($sql, $params = array(), $byPrimaryKey = false)
    {
        try {
            // $sql = preg_replace("/[\r\n|\n]/", ' ', $sql);
            // $sql = preg_replace("/\s+/", ' ', trim($sql));        
            $sth = $this->dbh->prepare($sql);
            if (!$sth) {                
                return($this->dbh->errorInfo());
            }
            $sth->execute($params);
            return $sth->fetchAll(\PDO::FETCH_OBJ);           
        } catch ( \PDOException $e) {            
            return $e->getCode() . ':' . $e->getMessage();
        } catch ( \Exception $e ) {
            return $e->getCode() . ':' . $e->getMessage();
        }
    }       
    /**
     * this commits the change to the database performs an insert 
     * or update based on id number.
     * 
     */ 
    public function persist($object)
    {
        if (preg_match('@\\\\([\w]+)$@', get_class($object), $matches)) {
            $classname = $matches[1];
        }  
        $table = $classname . 's';      
        if ($this->id > 0) {
            // this is an update
            $this->update($table, $object);
        } else {
            $this->insert($table, $object);
        }
    }    

    /**
     * run the provided sql query
     * 
     * @param  string $sql    [the query to run]
     * @param  array  $params [the parameters to bind to the query string.]
     * @return mixed object|array
     * 
     */
    public function runQuery($sql, $params = array())
    {
        $sql = preg_replace("/[\r\n|\n]/", ' ', $sql);
        $sql = preg_replace("/\s+/", ' ', trim($sql));        
        // ensure that only 
        $matches = array();
        foreach ($params as $key => $value) {
            if (preg_match("/\:$key/", $sql)) {
                $matches[$key] = $value;
            }
        }
        try {
            $sth = $this->dbh->prepare($sql);
            if (!$sth) {
                $this->errorMessage = "\nPDO::errorInfo():\n";            
            }
            $sth->execute($matches);    
            $this->errorMessage = $sth->errorInfo();            
            return $sth;
        } catch ( \PDOException $e) {
            return $e->getCode() . ':' . $e->getMessage();
        } catch ( \Exception $e ) {
            return $e->getCode() . ':' . $e->getMessage();
        }
    } 
    /**
     * [runSql]
     * @param  string $sql    [the query to run]
     * @param  array  $params [the parameters to bind to the query string.]
     * @return mixed
     */
    public function runSql($sql, $params = array())
    {
        try {
            $sql = preg_replace("/[\r\n|\n]/", ' ', $sql);
            $sql = preg_replace("/\s+/", ' ', trim($sql));        
            $sth = $this->dbh->prepare($sql);
            if (!$sth) {
                $this->errorMessage = "PDO::errorInfo(): " . $sql;
            }
            $sth->execute($params);
            if ($sth->rowCount() == 1) {
                return $sth->fetch(\PDO::FETCH_OBJ);
            } else {
                return $sth;
            }
        } catch ( \PDOException $e) {
            return $e->getCode() . ':' . $e->getMessage();
        } catch ( \Exception $e ) {
            return $e->getCode() . ':' . $e->getMessage();
        }
    }       

    /**
     * PDO command to run a SOURCE command File
     * 
     * @param  string $sqlfile [path to .sql file]
     * 
     * @return none
     */ 
    public function runSourceCommand($sqlfile) {
        $query = fread(fopen($sqlfile, 'r'), filesize($sqlfile));

        $pieces  = $this->db_split_sql($query);
        for ($i=0; $i<count($pieces); $i++) {
            $pieces[$i] = trim($pieces[$i]);
            if(!empty($pieces[$i]) && $pieces[$i] != "#") {
                try { 
                    $this->runSql($pieces[$i]);
                } catch ( \Exception $e ) {
                    return $e->getCode() . ':' . $e->getMessage();
                }       
            }
        }
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
    private static function scrubVar($value, $cType = 'BASIC', $stopWords = '')
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
        } elseif ($cType == 'SIMPLE') {
            $cPattern = '[^A-Za-z0-9\s\-\_\.\ \,\@\!\#\$\%\^\&\*\(\)\[\]\{\}\?]';
            return preg_replace("/$cPattern/", '', $value);
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
     * set the user/pw combonation to be used by the database connection.
     *                    
     * @param string $user [the user associated with the database connection]
     * @param string $pw   [the password associated with the database connection]
     * 
     * @return Database 
     */
    public function setCredentials($user = '', $pw = '')
    {
        $this->dbUser = $user;
        $this->dbPass = $pw;
        return $this;
    }

    /**
     * set the database variable.
     * 
     * @param string [the name of the database to be used]
     * 
     * @return Database 
     * 
     */
    public function setDatabase($database)
    {
        $this->database = $database;
        return $this;
    }

    /**
     *  set the Host variable.
     * 
     * @param string $dsn [The Data Source Name, or DSN, contains the information required 
     *                    to connect to the database. ]
     * 
     * @return Database 
     */
    public function setHost($host)
    {
        $this->host = $host;
        return $this;
    }    

    /**
     * set the variables used in PDO requests to match.
     * 
     * @param string $sql    [sql string to use as map]
     * @param array  $params [array used to populate variables for query]
     * 
     * @return Database
     * @todo  add checks to ensure data follows database structure requirements.
     * 
     */
    public function setVariables($sql, $params = array())
    {
        $vars = array();        
        $sql = str_replace(',', ', ', $sql);
        preg_match_all("/:(.*?)\s/", $sql, $matches);
        foreach ($matches[1] as $value) {
            $value = str_replace(',', '', $value);
            array_key_exists($value, $params) ? $vars[$value] = $params[$value] : false;
        }
        $this->vars = $vars;
        return $this;
    }        

    /**
     * return a list of databases the current user is able to view in the currently seelction connection.
     *                   
     * @return array
     * 
     */
    public function showDatabases()
    {
        $array = array();
        $sth = $this->dbh->prepare('SHOW databases');        
        $sth->execute();        
        $tableinfo = $sth->fetchAll(\PDO::FETCH_ASSOC);        
        foreach ($tableinfo as $key => $value) {            
            $array[] = $value['Database'];
        }        
        return $array;
    }

    /**
     * return the list of databases for the selected database.
     *                            
     * @return array
     * 
     */
    public function showTables()
    {
        $array = array();
        $sth = $this->dbh->prepare('SHOW tables');        
        $sth->execute();        
        $tableinfo = $sth->fetchAll(\PDO::FETCH_ASSOC);        
        foreach ($tableinfo as $key => $value) {            
            $array[] = $value['Tables_in_' . $this->database];
        }        
        return $array;
    }

    /**
     * [sqlQuery]
     * @param  string $sql    [the query to run]
     * @param  array  $params [the parameters to bind to the query string.]
     * @return mixed
     */
    public function sqlQuery($sql, $params = array())
    {
        try {
            $sth = $this->dbh->prepare($sql);
            if (!$sth) {
                return PDO::errorInfo();
            }
            $sth->execute($params);
            if ($sth->rowCount() == 1) {
                return $sth->fetch(\PDO::FETCH_OBJ);
            } else {            
                return $sth->fetchAll(\PDO::FETCH_OBJ);
            }
        } catch ( \PDOException $e) {
            // echo $e->getCode() . ':' . $e->getMessage();
            return $e->getCode() . ':' . $e->getMessage();

        } catch ( \Exception $e ) {
            // echo $e->getCode() . ':' . $e->getMessage();
            return $e->getCode() . ':' . $e->getMessage();
        }
    }   
    /**
     * 
     * 
     */ 
    public function showQuery($sql = '', $vars = [], $constraints = [])
    {
        foreach ($vars as $key => $value) { 
            $sql = preg_replace("/:" . $key. "/is", "'$value'", $sql, 1);
            //$sql = str_replace(":$key", "'$value'", $sql);
        }
        foreach ($constraints as $key => $value) { 
            $sql = preg_replace("/:" . $key. "/is", "'$value'", $sql, 1);
           // $sql = str_replace(":$key", "'$value'", $sql);
        }
        return $sql;
    }
    /**
    * pre-defined to run a sql update command
    * 
    * @param  string $table     [the table to update]
    * @param  array  $variables [variables to be used when updating the record]
    * @param  array $constraints 
    * 
    * @return integer    
    */
    public function update($table, $variables, $constraints = array())
    {
        is_object($variables) ? $variables = (array) $variables : false;    
        $this->cleanVariables($table, $variables);
        $sql = $this->buildQuery($table, 'update', $variables, $constraints);
        $fields = $this->setVariables($sql, array_merge($variables, $constraints));
        $sth = $this->runQuery($sql, $fields->vars);
        $err = $sth->errorInfo();
        if ($err[1] > 0 || $err[0] == 'HY093') {
            //$this->errorMessage = 'Error: ' . $err[2] . json_encode($err);
            $this->errorMessage = 'Error: ' . $this->showQuery($sql, $variables, $constraints);
            return 0;
        } else {
            if ($err[1] == 0) {
                return 1;
            } else {
                return $sth->rowCount();
            }
        }
    }
    /**
     * [updateAll all records supplied in prepared statement]
     * @param  string $table       [the table to be updated.]
     * @param  array  $variables   [variables to be updated]
     * @param  array  $constraints [variables used to restrict update]
     * @return boolean true/false
     */
    public function updateAll($table, $variables, $constraints = array())
    {
        if (sizeof($variables) > 0) {
            foreach ($variables as $v => $variable) { 
                is_object($variable) ? $variable = (array) $variable : false;    
                $this->cleanVariables($table, $variable);
                $variables[$v] = $variable;
            }
            $sql = $this->buildQuery($table, 'update', $variables[0], $constraints);            
            try {
                $sth = $this->dbh->prepare($sql);
                if (!$sth) {
                    $this->errorMessage = "\nPDO::errorInfo():\n";            
                }
                foreach ($variables as $v => $variable) { 
                    $sth->execute($variable);    
                }
                return $sth;
            } catch ( \PDOException $e) {
                $this->errorMessage = $e->getCode() . ':' . $e->getMessage();
                return 0;
            } catch ( \Exception $e ) {
                $this->errorMessage = $e->getCode() . ':' . $e->getMessage();
                return 0;
            }            
        } else { 
            $this->errorMessage = 'The variable was empty.';
            return 0;
        }
    }
}
