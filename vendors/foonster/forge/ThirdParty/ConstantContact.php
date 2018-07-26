<?php
/**
 * @author Nicolas Colbert <nicolas@foonster.com>
 * @copyright 2005 Foonster Technology
 */
namespace foonster;

use Ctct\ConstantContact AS CC;
use Ctct\Exceptions\CtctException AS CtctException;
use Ctct\Components\Contacts\Contact AS Contact;
use Ctct\Components\Contacts\ContactList AS ContactList;
use Ctct\Components\Contacts\EmailAddress AS CCEmailAddress;
use Ctct\Components\Contacts\CustomField as CustomField;
use Ctct\Components\Contacts\Address as Address;

class ConstantContact
{
    private $_vars;    
    private $_errorMessage;
    private $cc;
    private $apiKey;
    private $accessToken;
    /**
    * Injects dependencies into the class.
    *
    * @param $cc object An instance of CtCt\ConstantContact
    * @return void
    */
    function __construct($token = '', $apikey = 'qayryzexuhgb8xwvzqjzcx56')
    {
        $this->apiKey = $apikey;
        $this->accessToken = $token;
        $this->cc = new CC($this->apiKey);    
    }

    /**
     * 
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
     * 
     * 
     */ 
    public function addContact($email, $firstName, $lastName, $lists = array(), $additional = array(), $actualUser = true)
    {
        try {
            $contact = new Contact();
            $contact->addEmail($email);

            foreach ($lists as $v) { 
                $contact->addList($v);
            }
            $contact->first_name = $firstName;
            $contact->last_name = $lastName;

            !empty($additional['company']) ? $contact->company_name = $additional['company'] : false;

            !empty($additional['job_title']) ? $contact->job_title = $additional['job_title'] : false;

            !empty($additional['work_phone']) ? $contact->work_phone = $additional['work_phone'] : false;

            $this->cc->addContact($this->accessToken, $contact, $actualUser);
            return true;
        } catch (CtctException $ex) {
            $error = (array) $ex->getErrors();
            $this->_errorMessage = $error[0]['error_message'];
            return false;            
        }        

    }
    /**
     * [updateContact description]
     * @param  array  $vars  [description]
     * @param  array  $lists [description]
     * @return [type]        [description]
     */
    public function updateContact($vars = array(), $lists = array(), $actualUser = true)
    {

        $allowedVars = array();

        try {

            $contact = $this->getContactByEmail($vars['email']);

            if ($contact->id > 0) { 
                !empty($vars['first_name']) ? $contact->first_name = $vars['first_name'] : false;
                !empty($vars['last_name']) ? $contact->last_name = $vars['last_name'] : false;
                !empty($vars['company']) ? $contact->company_name = $vars['company'] : false;
                !empty($vars['job_title']) ? $contact->job_title = $vars['job_title'] : false;
                !empty($vars['work_phone']) ? $contact->work_phone = $vars['work_phone'] : false;        

                foreach ($lists as $v) { $contact->addList($v); }
                foreach ($vars as $key => $value) {
                    if (preg_match("/^custom/", $key)) { 
                        $field = new CustomField();
                        $field->name = $key;
                        $field->value = $value;
                        $contact->AddCustomField($field);
                    }
                }

                $contact->addAddress(Address::create(array(
                    "address_type"=>"PERSONAL",
                    "line1"=>$vars['street'],
                    "city"=>$vars['city'],
                    "state"=> $vars['state'],
                    "state_code"=> $vars['state_code'],
                    "postal_code"=>$vars['postal_code'])));               
                $this->cc->updateContact($this->accessToken, $contact, $actualUser);                

            } else { 
                $contact = new Contact();            
                $contact->addEmail(trim(strtolower($vars['email'])));
                foreach ($lists as $v) { $contact->addList($v); }

                foreach ($vars as $key => $value) {
                    if (preg_match("/^custom/", $key)) { 
                        $field = new CustomField();
                        $field->name = $key;
                        $field->value = $value;
                        $contact->AddCustomField($field);
                    }
                }

                $contact->addAddress(Address::create(array(
                    "address_type"=>"PERSONAL",
                    "line1"=>$vars['street'],
                    "city"=>$vars['city'],
                    "state"=> $vars['state'],
                    "state_code"=> $vars['state_code'],
                    "postal_code"=>$vars['postal_code'])));               

                $contact->first_name = $vars['first_name'];            
                $contact->last_name = $vars['last_name'];            
                !empty($vars['company']) ? $contact->company_name = $vars['company'] : false;
                !empty($vars['job_title']) ? $contact->job_title = $vars['job_title'] : false;
                !empty($vars['work_phone']) ? $contact->work_phone = $vars['work_phone'] : false;        
                $this->cc->addContact($this->accessToken, $contact, $actualUser);                
            }        
            return true;
        } catch (CtctException $ex) {
            $error = (array) $ex->getErrors();
            $this->_errorMessage = $error[0]['error_message'];
            return false;            
        }        
    }

    /**
     * generate a access token
     * 
     */ 
    public function generateToken()
    {

    }

    public function getContactByEmail($email = '')
    {
        $result = $this->cc->getContactByEmail($this->accessToken, $email)->results;

        if (sizeof($result) == 1) { 
            return $result[0];
        } else { 
            return (object) array('id' => 0);
        }
    }

    /**
     * Fetches an array of lists and returns them in a name to ID order, for quick array look up.   
     * 
     *  @return array
     */ 
    public function getList($id, $includeContacts = false)
    {
        $list = $this->cc->getList($this->accessToken, $id);

        if ($includeContacts) {
            $list->contacts = $this->cc->getContactsFromList($this->accessToken, $list->id)->results;
        }

        return $list;
    }

    /**
     * Fetches an array of lists and returns them in a name to ID order, for quick array look up.   
     * 
     *  @return array
     */ 
    public function getListContactsByEmail($id)
    {
        $array = array();
        $contacts = $this->cc->getContactsFromList($this->accessToken, $id);

        if (is_array($contacts->results)) { 
            foreach ($contacts->results as $n => $contact) { 
                foreach ($contact->email_addresses as $i => $person) { 
                    $array[$person->email_address] = $contact;
                }
            }
        }

        return $array;
    }
    /**
     * [getError description]
     * @return [type] [description]
     */
    public function getError()
    {
        return $this->lastError();
    }
    /**
     * Fetches an array of lists and returns them in a name to ID order, 
     * for quick array look up.   
     * 
     *  @return array
     */ 
    public function getLists()
    {
        $lists = $this->cc->getLists($this->accessToken);
        $organized_list = array();
        foreach($lists as $list) {
            $organized_list[$list->name] = $list;
        }
        return $organized_list;
    }
    /**
     * Get the contents of the _errorMessage variable.
     * 
     * @return string 
     */ 
    public function lastError()
    {
        return $this->_errorMessage;
    }

    /**
     * 
     * 
     * 
     */ 
    public function removeContact()
    {

    }
}
