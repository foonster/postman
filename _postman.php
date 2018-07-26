<?php
/******************************************************************************\
+------------------------------------------------------------------------------+
| Foonster Publishing Software                                                 |
| Copyright (c) 2004 Foonster Technology                                       |
| All rights reserved.                                                         |
+------------------------------------------------------------------------------+
|                                                                              |
| Permission is hereby granted, free of charge, to any person obtaining a copy |
| of this software and associated documentation files (the "Software"), to deal| 
| in the Software without restriction, including without limitation the rights |
| to use, copy, modify, merge, publish, distribute, sublicense, and/or sell    |
| copies of the Software, and to permit persons to whom the Software is        |
| furnished to do so, subject to the following conditions:                     |
|                                                                              |
| The above copyright notice and this permission notice shall be included in   |
| all copies or substantial portions of the Software.                          |
|                                                                              |
| THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR   |
| IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,     |
| FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE  |
| AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER       |
| LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,| 
| OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE| 
| SOFTWARE.                                                                    |
|                                                                              |
+------------------------------------------------------------------------------+
/******************************************************************************/
// load up FOONSTER EMAIL CLASS
error_reporting(0);
date_default_timezone_set($postman['timezone']);
require __DIR__ . '/vendors/autoload.php';
$cError = '';
$cSuccess = '';
$email = new \Email();
$email->setconstants($postman);
$email->setMTA($postman['mta']['service']);
$result = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	// fire google recaptcha if needed.
	if ($postman['captcha']) {
		$query_params = [
			'secret' => $postman['google']['secret'],
			'response' => $_POST['g-recaptcha-response'],
			'remoteip' => $_SERVER['REMOTE_ADDR']
		];
    	$email->curlPOST('https://www.google.com/recaptcha/api/siteverify', $query_params, $result);
    	$response = json_decode($result);
    }
    // - run the code     
	if (!$postman['captcha'] || $response->success == 'true') {	
		try {
			// VERIFY ALL FIELDS THAT ARE REQUIRED HAVE VALUES.
			foreach ($aRequiredFields AS $cKey => $aValue) {
				if (substr($aValue['id'], 0, 4) != 'fupd') {
					// scrub the variable before performing any test.
					$_POST[ $aValue['id'] ] = $email->scrubVar( $_POST[ $aValue['id'] ] , $aValue['scrub'] );
					// test the length of the values all except for HYPERLINKS and EMAIL 
					// because those do not make sense.
					if ( 
						strlen( $_POST[ $aValue['id'] ] ) < $aValue['min-length'] &&
						$aValue['scrub'] != 'EMAIL' &&
						$aValue['scrub'] != 'HYPERLINK') {
						throw new Exception ( "The $cKey field is missing or contains invalid data." );					
					}					
					// if an email address perform a second validation to ensure the 
					// email address is valid.					
					if (strtoupper($aValue['scrub']) == 'EMAIL') {
						if (!$email->isAddressValid($_POST[ $aValue['id'] ])) {		
							throw new Exception ( 'The email address provided is not valid. (' . $cKey . ')' );      
						} 						
					}  
				}
			}	

			if (!empty($_SERVER['HTTP_USER_AGENT'])) {
				foreach ($_POST AS $cKey => $cValue) {
					// LOAD STOP WORD FILE	
					if (file_exists($postman['stop_words'])) {
						$aStopWords = preg_split( "/\n/", $email->slurp( $postman['stop_words'] ));
						foreach ($aStopWords AS $xKey => $xValue) {
							$aStopWords[$xKey] = '/\b' . trim( $xValue ) . '\b/i';
						}
					}
					
					if ($cKey != 'email' && $cKey != 'msg' && substr( $cKey , 0 , 4 ) != 'fupd') {   
						// EACH FIELD IS PROCESSED AND ANY STOP WORDS ARE REMOVED
						if (sizeof( $aStopWords ) > 0) {
							$cStop = $email->stopText( $_POST[$cKey] );
							$_POST[$cKey] = preg_replace( $aStopWords, $cStop , $_POST[$cKey] );
						}
						$email->checkVar( $_POST[$cKey] );
					}					
				}		
				// attach any files in the file upload
				if ($postman['attach']) {
					foreach ($_FILES AS $xKey => $xValue) {
						$email->addFileAttachment($xValue);						
					}					// add attachments from configuration file.			
					if (is_array($aEmail['attachments'])) {
						foreach ($aEmail['attachments'] AS $xKey => $xValue) {
							$email->addFileAttachment($xValue);
						}				
					}

				}			
				$email->to($aEmail['to']);
				!empty($aEmail['fromname']) ? $fromName = $aEmail['fromname'] : $fromName = $_POST['name'];
				!empty($aEmail['cc']) ? $email->cc($aEmail['cc']) : false;
				!empty($aEmail['bcc']) ? $email->bcc($aEmail['bcc']) : false;
				!empty($aEmail['from']) ? $email->from($aEmail['from'], $fromName) : $email->from($_POST['email'], $fromName);
				$email->Subject($aEmail['subject']);
				$email->message($email->slurp($aEmail['msg-html']['path'] , $_POST), 'HTML');
				$email->message($email->slurp($aEmail['msg-text']['path'], $_POST), 'TEXT');
				if ($email->send()) {     
					if ($email->isAddressValid($aAcknowledgment['from']) 
						&& !empty( $aAcknowledgment['subject'])) {				
						$ack = new \Email();
						$ack->setconstants($postman);
						$ack->setMTA($postman['mta']['service']);
						$ack->to($_POST['email']);					
						$ack->from($aAcknowledgment['from']);					
						// add CC's
						!empty($aAcknowledgment['cc']) ? $ack->cc($aAcknowledgment['cc']) : false;
						!empty($aAcknowledgment['bcc']) ? $ack->bcc($aAcknowledgment['bcc']) : false;
						$ack->Subject($aAcknowledgment['subject']);					
						$ack->message($email->slurp($aAcknowledgment['msg-html']['path'] , $_POST) , 'HTML');
						$ack->message($email->slurp($aAcknowledgment['msg-text']['path'], $_POST), 'TEXT');
						if (is_array($aAcknowledgment['attachments'])) {
							foreach ($aAcknowledgment['attachments'] AS $xKey => $xValue) {
								$ack->addAttachment($xValue['path'], $xValue['name']);
							}
						}					
						$ack->send();            
					}
					// finishing up 
					if ($postman['return_type'] == 'json') {
						echo json_encode(['success' => 'true', 'message' => '']);
						exit;
					} else {
						if ($postman['return_type'] == 'redirect') {
							header("Location:" . trim($postman['return_method']['redirect']['url']));
							exit;
						} else {
							$cSuccess = $postman['return_method']['self']['message'];
							$_POST = array();
						}							
					}
				} else {
					!empty($email->getError()) ? $cError = $email->getError() : $cError = '';
					if ($postman['return_type'] == 'json') {
						echo json_encode(['success' => 'false', 'message' => $cError]);
						exit;
					}					
				}
			} else {
				throw new Exception ( 'HTTP User agent not provided' );		
			}
		}
		catch ( Exception $e ) {		
			if ($postman['return_type'] == 'json') {
				echo json_encode(array('success' => 'false', 'message' => $e->getMessage()));
				exit;
			} else {
				$cError = $e->getMessage();	
			}					
		}
	} else {
		if ($postman['return_type'] == 'json') {
			echo json_encode(array('success' => 'false', 'message' => 'You have failed the reCaptcha process'));
			exit;
		} else {
			$cError = 'You have failed the reCaptcha process';	
		}		
	}
}
