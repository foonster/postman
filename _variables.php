<?php
// ===========================================================================
// ===========================================================================
//                          CONFIGURATION FILE                              //
// ===========================================================================
// ===========================================================================
// Postman variables
$postman = [	
	'attach'   => false, // allow file uploads to be used by this form
	'captcha'  => false, // you must include the Google Recaptca JS script to use this.
	'error' => '', // place holder
	'mta' => [
		'service' => '', // enter service name - SENDGRID - PHPMAILER .
		'api_key' => '', // required based on service
		'id' => '', // required based on service
		'pw' => '', // required based on service
	],
	'return_type' => 'self', // self, redirect, or json - return method will make the final routing
	'return_method' => [
		'json' => ['type' => 'na'],
		'self' => ['message' => 'Message sent'],
		'redirect' => ['url' => 'http://www.example.com'],
	],
	'stop_words' => 'stop-file.txt', // to remove restricted words
	'timezone' => 'America/New_York', // if a tz requirement is required
	// Google reCaptcha configuration
	'google' => [
		'site_key' => '',
		'secret'   => ''
	]
];
// Configure the required fields that will be checked before the email will be sent. 
$aRequiredFields = array(
	'Name' => [
		'id' => 'name',
		'min-length' => 3,
		'scrub' => 'ALPHA'
	],
	'Email' => [
		'id' => 'email',
		'length' => 3,
		'scrub' => 'EMAIL'
	]
);
// Email sent to form reciepent
$aEmail = [
	'from'  	=> '', // if blank, postman will use the $_POST['email'] variable to send the email.
	'fromname'  => '', // if blank, postman will use the $_POST['name'] variable to send the email.
	'to'  		=> '',
	'cc'  		=> '',
	'bcc' 		=> '',
	'subject' => $_SERVER['HTTP_HOST'] . ' Form Submission',
	'msg-html' => [
		'path' => __DIR__ . '/email-html.php',
		'character-set' => 'utf-8',
		'content-type' => '8bit'
	],
	'msg-text' => [
		'path' => __DIR__ . '/email-text.php',
		'character-set' => 'utf-8',
		'content-type' => '8bit'
	],
	'attachments' => []
];
/*
'attachments' => array(
	array(
		'path' => $_FILES['fupd_1']['tmp_name'],
		'name' => $_FILES['fupd_1']['name']
	)
)
*/
// Acknowledgement email to person submitting - this function will only fire after the original form submission
// completes without an error. 
$aAcknowledgment = array(
	'from' 		 => '', // required
	'fromname' => '', 
	'cc' 			 => '', 
	'bcc'      => '', 
	'subject'  => '', // required
	'msg-html' => [
		'path' => __DIR__ . '/acknowledgment-html.php',
		'character-set' => 'utf-8',
		'content-type' => '8bit'
	],
	'msg-text' => [
		'path' => __DIR__ . '/acknowledgment-text.php',
		'character-set' => 'utf-8',
		'content-type' => '8bit'
	],
	'attachments' => []
);
// =============================================================================
// =============================================================================
// - do not adjust below this line, unless you know what you are doing.
// =============================================================================
// =============================================================================
include __DIR__ . '/_postman.php';
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
/ that's all folks
/******************************************************************************/