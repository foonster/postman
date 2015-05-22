<?php
// ===========================================================================
// ===========================================================================
//                          CONFIGURATION FILE                              //
// ===========================================================================
// ===========================================================================

// version 6.53.08 - see README file for more information.

// turn OFF all error reporting	

error_reporting(0);

// Postman variables

$postman = array(
	'return_type' => 'redirect', // self, redirect, or json
	'redirect' => false,
	'captcha'  => true,
	'attach'   => false, // allow file uploads to be used by this form
	
	// reCaptcha configuration
	'google' => array(
		'site_key' => '',
		'secret'   => ''
	)
);

// Configure the required fields, 

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
	'to'  		=> '',
	'cc'  		=> '',
	'bcc' 		=> '',
	'subject' => '',

	'msg-html' => array(
		'path' => __DIR__ . '/email-html.php',
		'character-set' => 'utf-8',
		'content-type' => '8bit'
	),

	'msg-text' => array(
		'path' => __DIR__ . '/email-text.php',
		'character-set' => 'utf-8',
		'content-type' => '8bit'
	),

	'attachments' => array(
		array(
			'path' => $_FILES['fupd_1']['tmp_name'],
			'name' => $_FILES['fupd_1']['name']
		)
	)
);

// Acknowledgement email to person submitting 

$aAcknowledgment = array(
	'from' 		 => '', 
	'fromname' => '', 
	'cc' 			 => '', 
	'bcc'      => '', 
	'subject'  => 'Thank You,',

	'msg-html' => array(
		'path' => __DIR__ . '/acknowledgment-html.php',
		'character-set' => 'utf-8',
		'content-type' => '8bit'
	),

	'msg-text' => array(
		'path' => __DIR__ . '/acknowledgment-text.php',
		'character-set' => 'utf-8',
		'content-type' => '8bit'
	),

	'attachments' => array(
		array(
			'path' => $_FILES['fupd_1']['tmp_name'],
			'name' => $_FILES['fupd_1']['name']
		)
	)
);

$postman['stop_words'] = 'stop-file.txt';

// =============================================================================
// =============================================================================
// 'self' => array('type' => 'url', 'what' => 'http://www.foonster.com'), // 
// 'self' => array('type' => 'message', 'what' => 'Thank you for your feedback. We will be in contact with you.'),

$postman['return_method'] = array(
	'json' => array(
		'type' => 'na'
	),

	'self' => array(
		'message' => 'Message sent'
	),

	'redirect' => array(
		'url' => $postman['redirect']
	)
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