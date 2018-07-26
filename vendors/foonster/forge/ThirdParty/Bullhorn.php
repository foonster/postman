<?php
/**
 * @author Nicolas Colbert <ncolbert@zeekee.com>
 * @copyright 2002 Zeekee Interactive
 */
use foonster\forge\Html as Html;
/**
 * A set of classes/methods making it easier to use the Google API, please note 
 * that you are responsible for any charges incurred by using the Google API.  We always ensure
 * our clients have proper licensing
 https://github.com/bullhorn/fast-rest
 */ 
class Bullhorn extends \Anvil
{
    private $username;
    private $password;
    private $client_id;
    private $client_secret;
    private $state = 1;
    private $auth_uri = 'https://auth.bullhornstaffing.com';
    private $rest_uri = 'https://rest.bullhornstaffing.com';
    private $auth_code;
    private $access_token;
    private $token_type;
    private $expires_in;
    private $refresh_token;
    private $rest_token;
    private $rest_url;
    private $response;

    /**
     * @ignore
     */
    public function __construct($username = '', $password = '', $client_id = '', $client_secret = '')
    {
        $this->username = $username;
        $this->password = $password;
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->state = md5(uniqid());
        $this->auth_code = $auth_code;
    }
    /**
     * @ignore
     */
    public function __destruct()
    {
    }
    /**
     * 
     * 
     */ 
    public function access_token()
    {
        return $this->access_token;
    }

     public function associateFileToCandidate($candidateId, $filedata)
    {
        $params = array(
            'externalID' => (! empty($filedata['externalID']) ? $filedata['externalID'] : ''),
            'fileType' => 'SAMPLE',
            'fileContent' => (! empty($filedata['fileContent']) ? $filedata['fileContent'] : ''),
            'name' => ! empty($filedata['name']) ? $filedata['name'] : '',
            'contentType' => (! empty($filedata['contentType']) ? $filedata['contentType'] : '')
        );
        
        (!empty($filedata['type'])) ? $params['type'] = $filedata['type'] : false;
        (!empty($filedata['description'])) ? $params['description'] = $filedata['description'] : false;

        return $this->restRequest('file/Candidate/' . $candidateId, json_encode($params, JSON_PARTIAL_OUTPUT_ON_ERROR), 'PUT');

    }

    public function associateCandidateToJob($candidateId, $jobId)
    {
        // Creating job submission
        $params = array(
            'candidate' => array(
                'id' => $candidateId
            ),
            'jobOrder' => array(
                'id' => $jobId
            ),
            'status' => "New Lead"

        );
        if ($this->restRequest('entity/JobSubmission', json_encode($params, JSON_PARTIAL_OUTPUT_ON_ERROR), 'PUT')) {
            return $this->response;
        } else { 
            $this->error = $this->response->error . json_encode($this->response);
            return false;
        }
    }

    public function createCandidate($data)
    {
        $params = array(
            'firstName' => $data['firstname'],
            'nickName' => $data['firstname_prefer'],
            'lastName' => $data['lastname'],
            'email' => $data['email'],
            'phone' => $data['phone']
        );
        if ($this->restRequest('entity/Candidate', json_encode($params, JSON_PARTIAL_OUTPUT_ON_ERROR), 'PUT')) {
            return $this->response;
        } else { 
            $this->error = $this->response->error;
            return false;
        }
    }


    /**
     * 
     * 
     * 
     */ 
    public function getCountry($id = 0, $fields = '*')
    {
        $params = array(
            'where' => 'id=' . $id,
            'count' => 1,
            'fields' => $fields
        );
        
        if ($this->restRequest('query/Country', $params)) {
            return $this->response;
        }
    }
    /**
     * 
     * 
     * 
     */ 
    public function getCountries($fields = '*')
    {
        $params = array(
            'where' => 'id>0',
            'count' => 500,
            'fields' => $fields
        );
        
        $this->restRequest('query/Country', $params);
        return $this->response;
    }
    /**
     * 
     * 
     */ 
    public function get_access_token()
    {
        if (empty($this->auth_code)) { 
            if (!$this->oauth_login()) { 
                return false;
            }
        }
        $vars = [
            'grant_type' => 'authorization_code',
            'code' => $this->auth_code,
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret
        ];
        $r = $this->curl($this->auth_uri . '/oauth/token', $vars, [], 'POST');
        if (self::isJSON($r->response)) { 
            $d = json_decode($r->response);
            if (!empty($d->access_token)) { 
                $this->access_token = $d->access_token;
                $this->token_type = $d->access_token;
                $this->expires_in = $d->access_token;
                $this->refresh_token = $d->access_token;
                return true;
            } else { 
                $this->error = 'BHAPI:' . $d->error_description;
                return false;
            }
        } else { 
            $this->error = 'BHCLSS:Invalid response from Bullhorn server.';
            return false;
        }
    }
    /**
     * 
     * 
     */ 
    public function getCandidates($vars = [])
    {
        //empty($vars['query']) ? $vars['query'] = 'isOpen:1' : false;
        empty($vars['sort']) ? $vars['sort'] = 'lastName' : false;
        (empty($vars['start']) || !is_numeric($vars['start'])) ? $vars['start'] = 0 : false;
        (empty($vars['count']) || !is_numeric($vars['count'])) ? $vars['count'] = 200 : false;
        empty($vars['fields']) ? $vars['fields'] = '*' : false;
        $params = [
            'query' => $vars['query'],
            'count' => (int) $vars['count'],
            'start' => (int) $vars['start'],
            'sort' => $vars['sort'],
            'fields' => $vars['fields']
        ];
        $this->restRequest('search/Candidate', $params);
        return $this->response;
    }
    /**
     * 
     * 
     */ 
    public function getError()
    {
        return $this->error;
    }
    /**
     * 
     * 
     */ 
    public function getJobPostings($vars = [])
    {
        empty($vars['query']) ? $vars['query'] = 'isOpen:1' : false;
        empty($vars['sort']) ? $vars['sort'] = 'title' : false;
        (empty($vars['start']) || !is_numeric($vars['start'])) ? $vars['start'] = 0 : false;
        (empty($vars['count']) || !is_numeric($vars['count'])) ? $vars['count'] = 200 : false;
        empty($vars['fields']) ? $vars['fields'] = '*' : false;
        $params = [
            'query' => $vars['query'],
            'count' => (int) $vars['count'],
            'start' => (int) $vars['start'],
            'sort' => $vars['sort'],
            'fields' => $vars['fields']
        ];
        $this->restRequest('search/JobOrder', $params);
        return $this->response;
    }
    /**
     * 
     * 
     */ 
    public function getJobPostingsCount($query = 'isOpen:1', $fields = 'isOpen')
    {
        $params = array(
            'query' => $query,
            'count' => 1,
            'fields' => $fields
        );
        
        $this->restRequest('search/JobOrder', $params);
        return $this->response->total;
    }
    /**
     * 
     * 
     * 
     */ 
    public function getJobsCategories($fields = 'id,name,enabled,occupation,type')
    {
        $params = array(
            'where' => 'id>0',
            'fields' => $fields
        );
        
        if ($this->restRequest('query/Category', $params)) {
            return $this->response;
        }
    }
    /**
     * 
     * 
     */ 
    public function getJobBoardPosts($vars = [])
    {

        empty($vars['query']) ? $vars['query'] = 'isOpen=true' : false;
        empty($vars['sortby']) ? $vars['sortby'] = 'id' : false;
        (empty($vars['start']) || !is_numeric($vars['start'])) ? $vars['start'] = 0 : false;
        (empty($vars['count']) || !is_numeric($vars['count'])) ? $vars['count'] = 500 : false;
        empty($vars['fields']) ? $vars['fields'] = 'id, title, address, isOpen, isPublic, categories, publicDescription, employmentType, dateAdded, externalCategoryID, externalID, startDate, payRate, dateEnd, dateLastPublished, branchCode, customText2, customText5' : false;
        $params = [
            'where' => $vars['query'],
            'count' => (int) $vars['count'],
            'start' => (int) $vars['start'],
            'fields' => $vars['fields']
        ];

        $this->restRequest('query/JobBoardPost', $params);
        return $this->response;
    }
    /**
     * 
     * 
     * 
     */ 
    public function getStates($id = '0', $fields = '*')
    {
        $params = array(
            'where' => 'id>' . $id,
            'count' => 500,
            'fields' => $fields
        );
        if ($this->restRequest('query/State', $params)) {
            return $this->response;
        }
    }
    /**
     * 
     * 
     * 
     */ 
    public function getState($id = '0', $fields = '*')
    {
        $params = array(
            'where' => 'id=' . $id,
            'count' => 500,
            'fields' => $fields
        );
        if ($this->restRequest('query/State', $params)) {
            return $this->response;
        }
    }
    /**
     * 
     * 
     */ 
    public function getTearSheets($vars = [])
    {
        //$id = $this->getUserId();
        $params = [
            'fields' => '*',
            'where' => '(isPrivate=false AND isDeleted=false) OR (isPrivate=true AND isDeleted=false)',
        ];
        if ($this->restRequest('query/Tearsheet', $params)) {
            return $this->response;
        }
    }
    /**
     * 
     * 
     * 
     */ 
    public function oauth_login()
    {
        $vars = [
            'response_type' => 'code',
            'client_id' => $this->client_id,
            'username' => $this->username,
            'password' => $this->password,
            'state' => $this->state,
            "action" => "Login"
        ];
        $r = $this->curl($this->auth_uri . '/oauth/authorize', $vars, [], 'POST', true);
        if (!empty($r->redirect_url)) { 
            parse_str(parse_url($r->redirect_url)['query'], $params);
            $this->auth_code = $params['code'];
            return true;
        } else { 
            $this->error = 'BHAPI:' . $r->response;
            return false;
        }
    }
    /**
     *
     *  // Perform REST login
     *   
     * 
     */ 
    public function rest_login()
    {
        if (empty($this->access_token)) { 
            if (!$this->get_access_token()) { 
                return false;
            }
        }
        $params = array(
            'version' => '2.0',
            'access_token' => $this->access_token
        );
        
        $r = $this->curl($this->rest_uri . '/rest-services/login', $params, [], 'POST');

        if (self::isJSON($r->response)) { 
            $d = json_decode($r->response);
            if (!empty($d->BhRestToken)) { 
                $this->rest_token = $d->BhRestToken;
                $this->rest_url = $d->restUrl;
                return true;
            } else { 
                $this->error = 'BHAPI:' . $d->error_description;
                return false;
            }
        } else { 
            $this->error = 'BHCLSS:Invalid response from Bullhorn server.';
            return false;
        }
    }
// -------------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------------
    public function jobOrder()
    { 
        $params = array(
            'query' => 'isOpen:1 AND isDeleted:0 AND NOT status:archive',
            'fields' => 'id,clientCorporation,clientContact,description',
            'count' => 20,
            'start' => 0
        );
        if ($this->restRequest('search/JobOrder', $params)) { 
           // $this->dumpvar($this->response);
        } else { 
            return false;
        }
        //$categories = json_decode($responseQuery->getBody()->__toString());
        //return $categories;

    }
   
    /**
     * 
     * 
     */ 
    public function getUserId()
    {
        $this->restRequest('settings/userId');
        if ($this->response->userId > 0) { 
            return $this->response->userId;
        } else { 
            $this->error = 'Unable to locate the requested user Id.';
        }
    }

    /**
     * 
     * 
     * 
     */
    public function unauthenticated()
    {
        $this->error = 'Not authenticated';
    }
    /**
     * 
     * 
     * 
     */ 
    protected function restRequest($endpoint = '', $vars = [], $method = 'GET', $traceroute = false)
    {
        if (empty($this->rest_token)) { 
            if (!$this->rest_login()) { 
                return false;
            }
        }

        $r = $this->curl($this->rest_url . $endpoint, $vars, 
            ['BhRestToken' => $this->rest_token], 
            $method, $traceroute);

        /*
        $r = $this->curl($this->rest_url . $endpoint, 
            array_merge($vars,['BhRestToken' => $this->rest_token]), 
            ['BhRestToken' => $this->rest_token], 
            $method, $traceroute);
        */

        if (self::isJSON($r->response)) { 
            $this->response = json_decode($r->response);
            if (empty($this->response->errorCode)) { 
                return true;
            } else { 
                $this->error = $this->response->errorCode . ' : ' . $this->response->errorMessage . ' : ' . json_encode($vars);
                return false;
            }            
        } else { 
            $this->error = 'BHCLSS:Invalid response from Bullhorn server.';
            return false;
        }
     }




//POST https://rest.bullhornstaffing.com/rest-services/login?version=*&access_token={xxxxxxxx}


    /*
    public function oauth_login()
    {
        $query_data = array(
            'client_id' => $this->client_id,
            'response_type' => 'code',
            'state' => '1', // TODO: add generation of state value as security measure
            'username' => $this->username,
            'password' => $this->password,
            'action' => 'Login'
        );
        
        $response = $this->Client->request('POST', 'https://auth.bullhornstaffing.com/oauth/authorize?' . http_build_query($query_data), array(
            'query' => $query_data,
            'allow_redirects' => false
        ));
        
        if ($response->getStatusCode() === 302) {
            $oauth_headers = $response->getHeaders();
            // parsing response URL
            
            $arr_qry = parse_url($oauth_headers["Location"][0], PHP_URL_QUERY);
            $arr_resp_values = array();
            parse_str(urldecode($arr_qry), $arr_resp_values);
            
            if (! empty($arr_resp_values['code'])) {
                $auth_code = $arr_resp_values['code'];
            }
        }
        
        assert(! empty($auth_code), 'Got Auth code');
        $this->auth_code = $auth_code;
    }
  

    public function oauth_authorize()
    {
        $params = array(
            'grant_type' => 'authorization_code',
            'code' => $this->auth_code,
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret
        );
        
        $access_response = $this->Client->request('POST', 'https://auth.bullhornstaffing.com/oauth/token', array(
            'query' => $params
        ));
        
        $access_data = json_decode($access_response->getBody()->__toString());
        
        $this->access_token = $access_data->access_token;
        $this->refresh_token = $access_data->refresh_token;
        $this->expires_in = $access_data->expires_in;
    }

    public function rest_login()
    {
        // Perform REST login
        // https://rest.bullhornstaffing.com/rest-services/login?version=2.0&access_token={xxxxxxxx}
        $params = array(
            'version' => '2.0',
            'access_token' => $this->access_token
        );
        
        $login_response = $this->Client->request('GET', 'https://rest.bullhornstaffing.com/rest-services/login', array(
            'query' => $params
        ));
        
        $login_data = json_decode($login_response->getBody()->__toString());
        
        $this->BhRestToken = $login_data->BhRestToken;
        $this->restUrl = $login_data->restUrl;
    }

    public function searchCandidates()
    {
        // Search for Candidates
        $params = array(
            'fields' => 'id,firstName,lastName',
            'query' => 'lastName:Smith',
            'count' => 10
        );
        
        $responseQuery = $this->Client->request('GET', $this->restUrl . 'search/Candidate', array(
            'query' => $params,
            'headers' => array(
                'BhRestToken' => $this->BhRestToken
            )
        ));
        
        $candidates = json_decode($responseQuery->getBody()->__toString());
        
        return $candidates;
    }

    public function getJobsCategories()
    {
        // https://rest1.bullhornstaffing.com/rest-services/k1ip0/query/Category?&where=id>0&fields=id,name
        $params = array(
            'where' => 'id>0',
            'fields' => 'id,name,enabled,occupation,type'
        );
        
        $headers = $this->getRequestHeaders();
        
        $responseQuery = $this->Client->request('GET', $this->restUrl . 'query/Category', array(
            'query' => $params,
            'headers' => $headers
        ));
        
        $categories = json_decode($responseQuery->getBody()->__toString());
        return $categories;
    }

    public function getJobPostingsCount()
    {
        $params = array(
            'query' => 'isOpen:1',
            'count' => 1,
            'fields' => 'id,isOpen'
        );
        
        $headers = $this->getRequestHeaders();
        
        $responseQuery = $this->Client->request('GET', $this->restUrl . 'search/JobOrder', array(
            'query' => $params,
            'headers' => $headers
        ));
        
        $jobPostingsCount = json_decode($responseQuery->getBody()->__toString());
        return (int) $jobPostingsCount->total;
    }

    public function searchJobPostings($query = '', $start = '', $sort = '', $count = 100)
    {
        if (empty($query)) {
            $query = 'isOpen:1';
        }
        
        $params = array(
            'query' => $query,
            // 'fields' => '*',
            'fields' => 'id, title, address, isOpen, isPublic, categories, publicDescription, employmentType, dateAdded, externalCategoryID, externalID, startDate, payRate, dateEnd, branchCode, customText2',
            'count' => (int) $count,
            'start' => (int) $start
        );
        
        $params['sort'] = 'id';
        
        if ($sort) {
            $params['sort'] = $sort;
        }
        
        $headers = $this->getRequestHeaders();
        
        $responseQuery = $this->Client->request('GET', $this->restUrl . 'search/JobOrder', array(
            'query' => $params,
            'headers' => $headers
        ));
        
        $jobPostings = json_decode($responseQuery->getBody()->__toString());
        
        return $jobPostings;
    }

    public function createCandidate($candidateData)
    {
        $params = array(
            'firstName' => $candidateData['firstname'],
            'nickName' => $candidateData['firstname_prefer'],
            'lastName' => $candidateData['lastname'],
            'email' => $candidateData['email'],
            'phone' => $candidateData['phone']
        );
        
        $headers = $this->getRequestHeaders();
        
        $responseQuery = $this->Client->request('PUT', $this->restUrl . 'entity/Candidate', array(
            'json' => $params,
            'headers' => $headers
        ));
        
        $responseData = false;
        if (200 === $responseQuery->getStatusCode()) {
            $responseData = json_decode($responseQuery->getBody()->__toString());
        }
        
        return $responseData;
    }

    public function associateCandidateToJob($candidateId, $jobId)
    {
        // Creating job submission
        $params = array(
            'candidate' => array(
                'id' => $candidateId
            ),
            'jobOrder' => array(
                'id' => $jobId
            )
        );
        
        $headers = $this->getRequestHeaders();
        
        $responseQuery = $this->Client->request('PUT', $this->restUrl . 'entity/JobSubmission', array(
            'json' => $params,
            'headers' => $headers
        ));
        
        $responseData = false;
        if (200 === $responseQuery->getStatusCode()) {
            $responseData = json_decode($responseQuery->getBody()->__toString());
        }
        
        return $responseData;
    }

    public function associateFileToCandidate($candidateId, $filedata)
    {
        $params = array(
            'externalID' => (! empty($filedata['externalID']) ? $filedata['externalID'] : ''),
            'fileType' => 'SAMPLE',
            'fileContent' => (! empty($filedata['fileContent']) ? $filedata['fileContent'] : ''),
            'name' => ! empty($filedata['name']) ? $filedata['name'] : '',
            'contentType' => (! empty($filedata['contentType']) ? $filedata['contentType'] : '')
        );
        
        if (! empty($filedata['type'])) {
            $params['type'] = $filedata['type'];
        }
        if (! empty($filedata['description'])) {
            $params['description'] = $filedata['description'];
        }
        
        $headers = $this->getRequestHeaders();
        
        $responseData = false;
        
        $responseQuery = $this->Client->request('PUT', $this->restUrl . '/file/Candidate/' . $candidateId, array(
            'json' => $params,
            'headers' => $headers
        ));
        
        if (201 === $responseQuery->getStatusCode()) {
            $responseData = json_decode($responseQuery->getBody()->__toString());
        }
        
        return $responseData;
    }

    public function fetchCountries()
    {
        $params = array(
            'where' => 'id>0',
            'count' => 500,
            'fields' => 'id, code, name'
        );
        
        $headers = $this->getRequestHeaders();
        $responseData = false;
        
        $responseQuery = $this->Client->request('GET', $this->restUrl . '/query/Country', array(
            'query' => $params,
            'headers' => $headers
        ));
        
        if (200 === $responseQuery->getStatusCode()) {
            $responseData = json_decode($responseQuery->getBody()->__toString());
        }
        
        return $responseData;
    }

    public function fetchStates($count = 600, $start = 0)
    {
        $params = array(
            'where' => 'id>0',
            'count' => $count,
            'start' => $start,
            'fields' => 'id, code, country, name'
        );
        $headers = $this->getRequestHeaders();
        
        $responseData = false;
        $responseQuery = $this->Client->request('GET', $this->restUrl . '/query/State', array(
            'query' => $params,
            'headers' => $headers
        ));
        
        if (200 === $responseQuery->getStatusCode()) {
            $responseData = json_decode($responseQuery->getBody()->__toString());
        }
        
        return $responseData;
    }

    protected function getRequestHeaders()
    {
        return array(
            'BhRestToken' => $this->BhRestToken
        );
    }

    public function getJobBoardPosts($query='',$sortby='',$count = 100, $start = 0)
    {
        if(!(bool)$query) {
            $query = 'isOpen=true';
        }
        if(!(bool)$sortby) {
            $sortby = 'id';
        }
        
        $params = array(
            'where' => $query,
            'count' => $count,
            'start' => $start,
            'fields' => 'id, title, address, isOpen, isPublic, categories, publicDescription, employmentType, dateAdded, externalCategoryID, externalID, startDate, payRate, dateEnd, dateLastPublished, branchCode, customText2, customText5'
        );
        
        $headers = $this->getRequestHeaders();
        
        $responseQuery = $this->Client->request('GET', $this->restUrl. '/query/JobBoardPost', array(
            'query' => $params,
            'headers' => $headers
        ));
        
        $responseData = false;
        if (200 === $responseQuery->getStatusCode()) {
            $responseData = json_decode($responseQuery->getBody()->__toString());
        }
        
        return $responseData;
    }

    public function getCandidate($id) {
        $params = array(
            'where' => 'id='.(int)$id
        );

        $headers = $this->getRequestHeaders();

        $responseQuery = $this->Client->request('GET', $this->restUrl. '/query/Candidate', array(
                'query' => $params,
                'headers' => $headers
            )
        );

        $responseData = false;
        if(200 === $responseQuery->getStatusCode()) {
            $responseData = json_decode($responseQuery->getBody()->__toString());
        }
        return $responseData;
    }
    */
}
