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

// version 6.53.09

// load up FOONSTER EMAIL CLASS

require __DIR__ . '/Email.php';

$email = new Email();
// $email->setHeader( 'X-CAN-SPAM-1' , 'This message is (or may be) a solicitation or advertisement within the specific meaning of the CAN-SPAM Act of 2003. (I am pretty sure it is not, but just to be safe ....' );
// $email->setHeader( 'X-CAN-SPAM-2' , 'You can decline to receive further email from this list (\'commercial\' and otherwise) by following the instructions in the body of the email or by using the resources in the List-Unsubscribe, X-Unsubscribe-Email and X-Unsubscribe-Web email headers.' );

$postman['error']  = '';
$result = '';

// run the script

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	// google reCaptcha	
	
	// if ( $reCAPTCHA->is_valid || !$lCaptcha ) 
	$query_params = array(
		'secret' => $postman['google']['secret'],
		'response' => $_POST['g-recaptcha-response'],
		'remoteip' => $_SERVER['REMOTE_ADDR']
		);

    $email->curlPOST('https://www.google.com/recaptcha/api/siteverify', $query_params, $result);

    $response = json_decode($result); 
    
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
						$email->addFileAttachment ($xValue);
					}
				}
			
				// add attachments from configuration file.
			
				if (is_array($aEmail['attachments'])) {
					foreach ($aEmail['attachments'] AS $xKey => $xValue) {
						$email->addFileAttachment ($xValue);
					}				
				}
   
				$email->to( $aEmail['to'] );
				$email->addCarbonCopyRecipient( $aEmail['cc'] );
				$email->addBlindCopyRecipient( $aEmail['bcc'] );
				$email->from( $_POST['email'] , $_POST['name'] );
				$email->subject( $aEmail['subject'] );			
				$email->message( 
					$email->slurp( $aEmail['msg-text']['path'] , $_POST ) , 
					'TEXT' , 
					$aEmail['msg-text']['character-set'] , 
					$aEmail['msg-text']['content-type'] 
				);

				$email->message( 
					$email->slurp( $aEmail['msg-html']['path'] , $_POST ) , 
					'HTML' , 
					$aEmail['msg-html']['character-set'] , 
					$aEmail['msg-html']['content-type'] 
				);
			            
				if ($email->send()) {     
					if ($email->isAddressValid($aAcknowledgment['from']) && !empty( $aAcknowledgment['subject'])) {				
						$email->reset(); // reset fields				
						$email->to( $_POST['email'] );					
						$email->from( $aAcknowledgment['from'] );					
						$email->addCarbonCopyRecipient( $aAcknowledgment['cc'] );
						$email->addBlindCopyRecipient( $aAcknowledgment['bcc'] );			
						$email->subject( $aAcknowledgment['subject'] );
					
						$email->message($email->slurp( 
							$aAcknowledgment['msg-text']['path'] , $_POST ) , 
							'TEXT' , 
							$aAcknowledgment['msg-text']['character-set'] , 
							$aAcknowledgment['msg-text']['content-type'] 
						);

						$email->message( 
							$email->slurp( $aAcknowledgment['msg-html']['path'] , $_POST ) , 
							'HTML' , 
							$aAcknowledgment['msg-html']['character-set'] , 
							$aAcknowledgment['msg-html']['content-type'] 
						);
					            					
						if (is_array($aAcknowledgment['attachments'])) {
							foreach ($aAcknowledgment['attachments'] AS $xKey => $xValue) {
								$email->addFileAttachment ($xValue);
							}
						}					
						$email->send();            
					}
					
					// finishing up 

					if ($postman['return_type'] == 'json') {
						echo json_encode(array('success' => 'true', 'message' => ''));
						exit;
					} else {
						if ($postman['return_type'] == 'redirect') {
							header( "Location: " . $postman['return_method']['redirect']['url']);
							exit;
						} else {
							$cError = $postman['return_method']['self']['message'];
							$_POST = array();
						}							
					}
				} else {
					!empty($email->error) ? $cError = 'Error: ' . $email->error : $cError = 'Error: there was an issue sending this message.';

					if ($postman['return_type'] == 'json') {
						echo json_encode(array('success' => 'false', 'message' => $cError));
						exit;
					} else {
						$cError = $e->getMessage();	
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
// - that's all folks.
?>