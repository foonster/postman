<?php
// ===========================================================================
// ===========================================================================
//                          CONFIGURATION FILE                              //
// ===========================================================================
// ===========================================================================

// version 6.53.08 - see README file for more information.

// turn OFF all error reporting	

error_reporting(0); 

// enable reCaptcha

$return = 'redirect'; // self, redirect, or json

$redirectUrl = '';

$lCaptcha = true;

$googleSiteKey = '';

$googleSecretKey = '';

// allow file uploads to be used by this form.

$lAttachUploads = false;

// configure the required fields, 
//

$aRequiredFields = array( 
	'Name' => array( 
		'id' => 'name',
		'min-length' => 3,
		'scrub' => 'ALPHA'
	),
	'Email' => array( 
		'id' => 'email',
		'length' => 3,
		'scrub' => 'EMAIL'
	)
);

// Email sent to first form recipient

$aEmail = array(
'to' => '', // single email address
'cc' => '', // single email address
'bcc' => '', // single email address
'subject' => 'Contact Form', // subject of the email.
'msg-html' => 
	array( 
		'path' => __DIR__ . '/email-html.php' , // 'file path'
		'character-set' => 'utf-8' , //  'character set'
		'content-type' =>'8bit' // 'content type' 7bit, 8bit, base64 
	), // settings for HTML message
'msg-text' => 
	array( 
		'path' => __DIR__ . '/email-text.php' , // 'file path'
		'character-set' => 'utf-8' , //  'character set'
		'content-type' =>'8bit' // 'content type' 7bit, 8bit, base64 
	), // settings for Plain Text message
'attachments' => array( '0' => array( 'path' => $_FILES['fupd_1']['tmp_name'] , 'name' => $_FILES['fupd_1']['name'] ) )
);

// Acknowledgement email to person submitting 

$aAcknowledgment = array(
'from' => '', 
'fromname' => '', 
'cc' => '', 
'bcc' => '', 
'subject' => 'Thank You,',
'msg-html' => 
	array( 
		'path' => dirname( __FILE__ ) . '/acknowledgment-html.php' , // 'file path'
		'character-set' => 'utf-8' , //  'character set'
		'content-type' =>'8bit' // 'content type'
	), // settings for HTML message
'msg-text' => 
	array( 
		'path' => dirname( __FILE__ ) . '/acknowledgment-text.php' , // 'file path'
		'character-set' => 'utf-8' , //  'character set'
		'content-type' =>'8bit' // 'content type'
	), // settings for Plain Text message
'attachments' => array( '0' => array( 'path' => $_FILES['fupd_1']['tmp_name'] , 'name' => $_FILES['fupd_1']['name'] ) )
);

$cStopWords = 'stop-file.txt';

// =============================================================================
// =============================================================================
// 'self' => array('type' => 'url', 'what' => 'http://www.foonster.com'), // 
// 'self' => array('type' => 'message', 'what' => 'Thank you for your feedback. We will be in contact with you.'),

$returnMethod = 
	array(
		'json' => array('type' => 'na'),
		'self' => array('message'=> 'Message sent'), // 
		'redirect' => array('url' => $redirectUrl), // 
		);

// - do not adjust below this line, unless you know what you are doing.
// =============================================================================
// =============================================================================
// =============================================================================
// =============================================================================
// =============================================================================
// =============================================================================
// =============================================================================
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