<?php
/******************************************************************************\
+------------------------------------------------------------------------------+
| Foonster Publishing Software                                                 |
| Copyright (c) 2002 Foonster Technology                                       |
| All rights reserved.                                                         |
+------------------------------------------------------------------------------+
|                                                                              |
| OWNERSHIP. The Software and all modifications or enhancements to, or         |
| derivative works based on the Software, whether created by Foonster          |
| Technology or you, and all copyrights, patents, trade secrets, trademarks    |
| and other intellectual property rights protecting or pertaining to any       |
| aspect of the Software or any such modification, enhancement or derivative   |
| work are and shall remain the sole and exclusive property of Foonster        |
| Technology.                                                                  |
|                                                                              |
| LIMITED RIGHTS. Pursuant to this Agreement, you may: (a) use the Software    |
| on one website only, for purposes of running one website only. You must      |
| provide Foonster Technology with exact URL (Unique Resource Locator) of the  |
| website you install the Software to; (b) modify the Software and/or merge    |
| it into another program; c) transfer the Software and license to another     |
| party if the other party agrees to accept the terms and conditions of this   | 
| Agreement.                                                                   |
|                                                                              |
| Except as expressly set forth in this Agreement, you have no right to use,   |
| make, sublicense, modify, transfer or copy either the original or any copies |
| of the Software or to permit anyone else to do so. You may not allow any     |
| third party to use or have access to the Software. It is illegal to copy the |
| Software and install that single program for simultaneous use on multiple    | 
| machines.                                                                    |
|                                                                              |
| PROPRIETARY NOTICES. You may not remove, disable, modify, or tamper with     |
| any copyright, trademark or other proprietary notices and legends contained  |
| within the code of the Software.                                             |
|                                                                              |
| COPIES.  "CUSTOMER" will be entitled to make a reasonable number of          |
| machine-readable copies of the Software for backup or archival purposes.     |
|                                                                              |
| LICENSE RESTRICTIONS. "CUSTOMER" agrees that you will not itself, or through |
| any parent, subsidiary, affiliate, agent or other third party:               | 
|(a) sell, lease, license or sub-license the Software or the Documentation;    |
|(b) decompile, disassemble, or reverse engineer the Software, the Database,   |
| in whole or in part; (c) write or develop any derivative software or any     |
| other software program based upon the Software or any Confidential           |
| Information, | except pursuant to authorized Use of Software, if any; (d) use|
| the Software to provide services on a 'service bureau' basis; or (e) provide,|
| disclose, | divulge or make available to, or permit use of the Software by   |
| any unauthorized third party without Foonster Technology's prior written     |
| consent.                                                                     |
|                                                                              |
+------------------------------------------------------------------------------+
\******************************************************************************/

class EMAIL
{	

	private $_mailer = 'FOONSTER TECHNOLOGY EMAIL MODULE 6.5.3';
	
	private $vars = array();

	private $_to; // to field
	
	private $_from; // from field
	
	private $_fromname; // comman name associated with email address

	private $_sender; // sender field if set - also could be added in _headers
	
	private $_cc = array(); // array of cc email addresses
	
	private $_bcc = array(); // blind cc field.

	private $_attachments = array(); // number of attachments

	private $_priority = 3; // priority level
		
	private $_headers; // array of headers associated with email
	
	private $_lCheckDns = false; // DNS widget

	private $_html_msg; // html version of email
	
	private $_text_msg; // plain-text version of email
	
	private $_html_content_transfer_encoding = '7bit';
		
	private $_html_charset = 'utf-8'; // UTF-8 - iso-8859-1

	private $_text_content_transfer_encoding = '7bit';
		
	private $_text_charset = 'utf-8'; // UTF-8 - iso-8859-1
	
	// private variables
	
	public $error;	

	function __construct( $lDNSCheck = 'false' )
	{
	
		$this->checkDns( $lDNSCheck );
	
	}
	
	function __destruct()
	{
	
	
	}
	
	/**
	*
	* @set undefined vars
	*
	* @param string $index
	*
	* @param mixed $value
	*
	* @return void
	*
	*/
	public function __set( $index , $value )
	{
	
		$this->vars[ $index ] = $value;
 	}

	/**
	*
	* @get variables
	*
	* @param mixed $index
	*
	* @return mixed
	*
	*/
	public function __get( $index )
	{
	
		return $this->vars[ $index ];

	}			
		
	// =============================================================================
	// =============================================================================
	//           NAME: addFileAttachment
	//   DATE CREATED: 03/13/2004
	//  DATE MODIFIED: 07/22/2006
	//         USAGE : $this->addAttachment( Full Path to File );
	//       PURPOSE : To attach various files for inclusion into the mail message.
	//        RETURNS: nothing
	//      COMMENTS : 
	//
	//
	// =============================================================================
	// =============================================================================	
	
	function addFileAttachment( $aArray = null )  
	{

		if ( is_array( $aArray ) )
		{
		
			$this->_attachments[] = $aArray;
         
		}
			
	}		
	
	// =============================================================================
	// =============================================================================
	//           NAME: addBlindCopyRecipient
	//   DATE CREATED: 03/13/2004
	//  DATE MODIFIED: 07/22/2006
	//         USAGE : $this->bcc( email@address.com )
	//       PURPOSE : Add a single address or multiple addresses to the bcc field.
	//        RETURNS: none
	//      COMMENTS : strings with comma's "," or semi-colons ";" are split and
	//                 each address verified and then added or not included in the 
	//                 final message.
	//
	//
	// =============================================================================
	// =============================================================================
	
	function addBlindCopyRecipient( $cEmail = null )  
	{
	
		if ( strpos ( $cEmail , ',' ) > 0 || strpos ( $cEmail , ';' ) > 0 )
		{
		
			$aSplit = preg_split( '/[,|;]/' , $cEmail );
			
			foreach ( $aSplit AS $cValue )
			{
			
				$cValue = strtolower( trim( $cValue ) );
				
				$this->isAddressValid ( $cValue ) ?	$this->_bcc[] = $cValue : false;		
			
			}
		
		
		}
		else {
	
			$this->isAddressValid ( $cEmail ) ?	$this->_bcc[] = $cEmail : false;		
			
		}
			
	}		

	// =============================================================================
	// =============================================================================
	//           NAME: addCarbonCopyRecipient
	//   DATE CREATED: 03/13/2004
	//  DATE MODIFIED: 07/22/2006
	//         USAGE : 
	//       PURPOSE : 
	//        RETURNS: none
	//      COMMENTS : strings with comma's "," or semi-colons ";" are split and
	//                 each address verified and then added or not included in the 
	//                 final message.
	//
	// =============================================================================
	// =============================================================================
	
	function addCarbonCopyRecipient( $cEmail = null )  
	{
	
		if ( strpos ( $cEmail , ',' ) > 0 || strpos ( $cEmail , ';' ) > 0 )
		{
				
			$aSplit = preg_split( '/[,|;]/' , $cEmail );
			
			foreach ( $aSplit AS $cValue )
			{
							
				$this->isAddressValid ( $cValue ) ?	$this->_cc[] = $cValue : false;		
			
			}
		
		
		}
		else {
			
			$this->isAddressValid ( $cEmail ) ?	$this->_cc[] = $cEmail : false;		
			
		}
					
	}	
	
	// =============================================================================
	// =============================================================================
	//           NAME: checkDns
	//   DATE CREATED: 03/13/2004
	//  DATE MODIFIED: 07/22/2006
	//         USAGE : 
	//       PURPOSE : 
	//        RETURNS: NONE
	//      COMMENTS : 
	//
	//
	// =============================================================================
	// =============================================================================

	function checkDns ( $lCheck = false ) 
	{

		( $lCheck == true || $lCheck == 1 || strtoupper( trim( $lCheck ) ) == 'YES' ) ? $this->_lCheckDns = true : $this->_lCheckDns = false;
		     
	}			
	
	// =============================================================================
	// =============================================================================
	//           NAME: checkVar
	//   DATE CREATED: 03/13/2004
	//  DATE MODIFIED: 07/22/2006
	//         USAGE : 
	//       PURPOSE : To replace any restricted characters from various header fields.
	//        RETURNS: none - value modified by reference
	//      COMMENTS : 
	//
	//
	// =============================================================================
	// =============================================================================

	function checkVar ( &$cVariable ) 
	{

		$cVariable = preg_replace( '/(;|\||`|>|<|&|^|"|'."\t|\n|\r|'".'|{|}|[|]|\)|\()/i' , '' , $cVariable ); 
		
		$spam = strtolower( $cVariable );
		
		( preg_match( "/bcc: /i" , $spam ) || preg_match( "/cc: /i" , $spam ) || preg_match( "/subject: /i", $spam ) ) ? $cVariable = '' : false;
     
	}	
	
	// =============================================================================
	// =============================================================================
	//           NAME: from
	//   DATE CREATED: 03/13/2004
	//  DATE MODIFIED: 07/22/2006
	//         USAGE : 
	//       PURPOSE : 
	//        RETURNS: true || false
	//      COMMENTS : 
	//
	//
	// =============================================================================
	// =============================================================================
	
	function from( $cEmail = null , $cName = null )  
	{
	
		$this->isAddressValid ( $cEmail ) ?	$this->_from = $cEmail : false;		
		
		!empty( $cName ) ? $this->_fromname = $cName : false;
			
	}

	// =============================================================================
	// =============================================================================
	//           NAME: isAddressValid
	//   DATE CREATED: 03/13/2004
	//  DATE MODIFIED: 07/22/2006
	//         USAGE : ( self:: || $obj-> ) isValidEmailAddress( 'me@example.com' )
	//       PURPOSE : to check if an email address follows the proper conventions
	//        RETURNS: true || false
	//      COMMENTS : 
	//
	//
	// =============================================================================
	// =============================================================================
	
	function isAddressValid( $email = null )  
	{

		$this->checkVar( $email );

		$email = strtolower( trim( $email ) );
		
		if (preg_match( '/[\x00-\x1F\x7F-\xFF]/', $email)) 
		{
			return false;
		}   

		if (!preg_match( '/^[^@]{1,64}@[^@]{1,255}$/', $email)) 
		{
			return false;
		}   
   
		// Split it into sections to make life easier
		$email_array = explode("@", $email);
  
		$local_array = explode(".", $email_array[0]);
  
		// CHECK LOCAL ARRAY
  
		foreach ( $local_array as $local_part ) 
		{
			if (!preg_match( '/^(([A-Za-z0-9!#$%&\'*+\/=?^_`{|}~-]+)|("[^"]+"))$/', $local_part))
			{
				return false;   
			}
		}   
 
 		if ( !preg_match( '/^\[?[0-9\.]+\]?$/',  $email_array[1]) ) 
		{ // Check if domain is IP. If not, it should be valid domain name
    
			$domain_array = explode( '.', $email_array[1]);
    
			if (sizeof( $domain_array ) < 2 ) 
			{
				return false; // Not enough parts to domain
			}
    
			foreach ( $domain_array AS $domain_part ) 
			{
		
			 	if (!preg_match( '/^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$/',  $domain_part)) 
				{
					return false;
				}
    		}
    		    		    		    	
    		if ( $this->_lCheckDns ) 
    		{
    		    
				if ( checkdnsrr( $email_array[1] ) ) 
				{	

					return true;

				}
				else {

					return false;
    
		    	}
	    	
		    }
	    	else {
	    		    	    
	    		return true;
	    
		    }
  
		}

	}		
	
	// =============================================================================
	// =============================================================================
	//           NAME: message
	//   DATE CREATED: 03/13/2004
	//  DATE MODIFIED: 07/22/2006
	//         USAGE : 
	//       PURPOSE : 
	//        RETURNS: true || false
	//      COMMENTS : 
	//
	//
	// =============================================================================
	// =============================================================================
	
	function message( $cValue = NULL , $cType = 'TEXT' , $cChar = NULL , $cContent = NULL )  
	{
	
		if ( $cContent != NULL )
		{
		
			if ( strtoupper( trim( $cType ) ) == 'HTML' )
			{

				$this->_html_content_transfer_encoding = $cContent;
		
			}
			else {
		
				$this->_text_content_transfer_encoding = $cContent;
		
			}		
		
		}
		
		if ( $cChar != NULL )
		{
		
			if ( strtoupper( trim( $cType ) ) == 'HTML' )
			{

				$this->_html_charset = $cChar;		
		
			}
			else {
		
				$this->_text_charset = $cChar;		
		
			}		
		
		}
	
	
		if ( strtoupper( trim( $cType ) ) == 'HTML' )
		{
		
			if ( $this->_html_content_transfer_encoding == 'quoted-printable' )
			{
			
				$this->_html_msg = quoted_printable_encode( $cValue );
		
		
			}
			else {
			
				$this->_html_msg = $cValue;
			
			}
		
		}
		else {
		
			if ( $this->_text_content_transfer_encoding == 'quoted-printable' )
			{
			
				$this->_text_msg = quoted_printable_encode( $cValue );
		
		
			}
			else {
			
				$this->_text_msg = $cValue;
			
			}
		
		}
								
	}			
	
	// =============================================================================
	// =============================================================================
	//           NAME: priority
	//   DATE CREATED: 03/13/2004
	//  DATE MODIFIED: 07/22/2006
	//         USAGE : 1 (for highest priority), 3 (normal) and 5 (lowest).
	//       PURPOSE : To set the priority of the message
	//        RETURNS: none
	//      COMMENTS : 
	//
	//
	// =============================================================================
	// =============================================================================
	
	function priority( $nValue = 3 )  
	{
	
		if ( is_numeric( $nValue ) && $nValue >= 1 && $nValue <= 5 )
		{
		
			$this->_priority = $nValue;
			
		}
	
	}	
	
	// =============================================================================
	// =============================================================================
	//           NAME: quoted_printable_encode
	//   DATE CREATED: tzangerl [dot] pdc {dot} kth dot se 09-Apr-2010 02:05
	//  DATE MODIFIED: 
	//         USAGE : 
	//       PURPOSE : 
	//        RETURNS: 
	//      COMMENTS : 
	//
	//
	// =============================================================================
	// =============================================================================
	
	function quoted_printable_encode( $input = null , $line_max = 75 ) 
	{
	
		if ( version_compare( PHP_VERSION , '5.3.0' , '<' ) ) 
		{
		
			trim ( $input ); 
   
			$hex = array( '0','1','2','3','4','5','6','7', '8','9','A','B','C','D','E','F'); 
		
			$lines = preg_split("/(?:\r\n|\r|\n)/", $input); 

			$linebreak = "=0D=0A=\r\n"; 

			/* the linebreak also counts as characters in the mime_qp_long_line 
			* rule of spam-assassin */ 
			$line_max = $line_max - strlen($linebreak); 
			$escape = "="; 
			$output = ""; 
			$cur_conv_line = ""; 
			$length = 0; 
			$whitespace_pos = 0; 
			$addtl_chars = 0; 
	
			// iterate lines 
			for ($j=0; $j<count($lines); $j++) 
			{ 

				$line = $lines[$j]; 

				$linlen = strlen($line); 

				// iterate chars 
				for ($i = 0; $i < $linlen; $i++) 
				{ 

					$c = substr($line, $i, 1); 
					$dec = ord($c); 
					$length++; 

					if ( $dec == 32 ) 
					{ 
						// space occurring at end of line, need to encode 
						if (($i == ($linlen - 1))) 
						{ 
							$c = "=20"; 
							$length += 2; 
						} 

						$addtl_chars = 0; 
						$whitespace_pos = $i; 
					}
					elseif ( ( $dec == 61) || ( $dec < 32 ) || ( $dec > 126) ) 
					{ 
						$h2 = floor($dec/16); $h1 = floor($dec%16); 
						$c = $escape . $hex["$h2"] . $hex["$h1"]; 
						$length += 2; 
						$addtl_chars += 2; 
					}
					else {
				
						// dead
					
					} 

					// length for wordwrap exceeded, get a newline into the text 
					if ( $length >= $line_max ) 
					{ 
	
						$cur_conv_line .= $c; 
	
						// read only up to the whitespace for the current line 
						$whitesp_diff = $i - $whitespace_pos + $addtl_chars; 

						/* the text after the whitespace will have to be read 
						* again ( + any additional characters that came into 
						* existence as a result of the encoding process after the whitespace) 
						* 
						* Also, do not start at 0, if there was *no* whitespace in 
						* the whole line */ 
						if (( $i + $addtl_chars ) > $whitesp_diff ) 
						{ 
					
							$output .= substr($cur_conv_line, 0, (strlen($cur_conv_line) - $whitesp_diff)) . $linebreak; 
						
							$i =  $i - $whitesp_diff + $addtl_chars; 
						
						} 
						else { 
           			
           					$output .= $cur_conv_line . $linebreak; 

						} 

						$cur_conv_line = ""; 
						$length = 0; 
						$whitespace_pos = 0; 
      
					} 
					else { 
       	
       					// length for wordwrap not reached, continue reading 
						$cur_conv_line .= $c; 
					} 

				} // end of for 

				$length = 0; 
				$whitespace_pos = 0; 
				$output .= $cur_conv_line; 
				$cur_conv_line = ""; 

				if ($j<=count($lines)-1) 
				{ 

					$output .= $linebreak; 

				} 

			} 
			
		}
		else {
		
			$output = quoted_printable_encode( $input );
		
		}

 		return trim( $output ); 
	
	} 

	// =============================================================================
	// =============================================================================
	//           NAME: reset
	//   DATE CREATED: 03/13/2004
	//  DATE MODIFIED: 07/22/2006
	//         USAGE : 
	//       PURPOSE : 
	//        RETURNS: true || false
	//      COMMENTS : 
	//
	//
	// =============================================================================
	// =============================================================================
	
	function reset() 
	{

		$this->_to = '';
					
		$this->_from = '';
		
		$this->_fromname = '';		
					
		$this->_cc = array();
				
		$this->_bcc = array();
					
		$this->_subject = '';
            
		$this->_text_msg = '';
					
		$this->_html_msg = '';

		$this->_priority = 3;
		
		$this->_content_transfer_encoding = '7bit';
		
		$this->_charset = 'iso-8859-1'; // UTF-8		
										
		$this->attachments = array();

		$this->_headers = array();

	}
	
	/**
 	*	remove characters from string that veriifies a string and removes
 	* 	characters that to match the requested scrubing.
 	* 
 	*	@param string $value - string that is being scrubbed.
 	*	 
 	* 	@param string $cType - The type of scrubbing that needs to be completed.
 	* 	ALPHA - Only characters from A-Z and spaces.
 	* 	ALPHA_NUM - Only characters from A-Z & 0-9 and spaces.
 	* 	SIMPLE - Only characters found on the keyboard no special characters..
 	* 	EMAIL - Only characters that are part of a well-formed email address.
 	* 	HYPERLINK - A string that has been properly formatted as a URL.
 	* 	WHOLE_NUM - A whole number example 1000000 valid 1,000,000 invalid
 	* 	FLOAT_NUM - A float point number
 	* 	FORMAT_NUM - A properly formatted number
 	* 	SQL_INJECT - Only allow characters that are valid in value SQL calls.
 	* 	REMOVE_SPACES - Remove all spaces from the string.
 	* 	REMOVE_DOUBLESPACE - Remove double space and replace with single spaces.
 	* 	BASIC - Only characters found on the keyboard no special characters.
 	*
	* 	@return string - the string after all invalid characters have been
	*	removed.
	*
	* 	@access		public
	* 	@author 	N.Colbert of Foonster Technology
	*	@copyright 	Foonster Technology 2004
	*	@version	1.0
	*	@created 	09/28/2004	
	*	@modified	02/17/2013
	*/	
		
	function scrubVar( $value , $cType = 'BASIC' , $cWordFile = '' )
	{
			
		$cType = strtoupper( trim( $cType ) );
		
		if ( $cType == 'ALPHA' ) 
		{
		
			return preg_replace( '/[^A-Za-z\s]/' , '' , $value );
	
		}
		elseif ( $cType == 'ALPHA_NUM' ) 
		{
					
			return preg_replace( '/[^A-Za-z0-9]/' , '' , $value );
					
	
		}
		elseif ( $cType == 'SIMPLE' ) 
		{
					
			$cPattern = '[^A-Za-z0-9\s\-\_\.\ \,\@\!\#\$\%\^\&\*\(\)\[\]\{\}\?]';
									
			return preg_replace( "/$cPattern/", '' , $value );						
	
		}
		elseif( $cType == 'EMAIL' ) 
		{
		
			$cPattern = '/(;|\||`|>|<|&|^|"|'."\t|\n|\r|'".'|{|}|[|]|\)|\()/i'; 

			return preg_replace( $cPattern , '' , $value );  
		}
		elseif( $cType == 'HYPERLINK' ) 
		{
		
	    	// match protocol://address/path/file.extension?some=variable&another=asf%
    
			$value = preg_replace("/\s([a-zA-Z]+:\/\/[a-z][a-z0-9\_\.\-]*[a-z]{2,6}[a-zA-Z0-9\/\*\-\?\&\%]*)([\s|\.|\,])/i",'', $value );

			// match www.something.domain/path/file.extension?some=variable&another=asf%
    	
			return preg_replace("/\s(www\.[a-z][a-z0-9\_\.\-]*[a-z]{2,6}[a-zA-Z0-9\/\*\-\?\&\%]*)([\s|\.|\,])/i",'', $value);
		}
		elseif( $cType == 'WHOLE_NUM' ) 
		{
		
			return preg_replace( '/[^0-9]/' , '' , $value );
		
		}
		elseif( $cType == 'FLOAT_NUM' ) 
		{
		
			return preg_replace( '/[^0-9\-\+]/' , '' , $value );

		}
		elseif( $cType == 'FORMAT_NUM' ) 
		{
		
			return preg_replace( '/[^0-9\.\,\-]/' , '' , $value );
				
		}
		elseif ( $cType == 'SQL_INJECT' ) 
		{
		
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
						
			return preg_replace( "/$cPattern/", '' , preg_replace( $aRestrictedWords , '' , $value ) );		
			
					
		}
		elseif( $cType == 'REMOVE_SPACES' ) 
		{
		
			return preg_replace( "/\s/" , '' , trim( $value ) );

		}
		elseif( $cType == 'REMOVE_DOUBLESPACE' ) 
		{
		
			return preg_replace( "/\s+/" , ' ' , trim( $value ) );
		
		}
		else {
				
			$cPattern = '[^A-Za-z0-9\s\-\_\.\ \,\@\!\#\$\%\^\&\*\(\)\[\]\{\}\?]';
									
			return preg_replace( "/$cPattern/", '' , strip_tags( trim( $value ) ) );		
				
		}

	}		
	
	// =============================================================================
	// =============================================================================
	//           NAME: send
	//   DATE CREATED: 03/13/2004
	//  DATE MODIFIED: 07/22/2006
	//         USAGE : 
	//       PURPOSE : 
	//        RETURNS: true || false
	//      COMMENTS : 
	//
	//
	// =============================================================================
	// =============================================================================
	
	function send()  
	{
	
		$eol;
		
		// set end of line marker
				
		if ( strtoupper( substr( PHP_OS , 0 , 3 ) == 'WIN' ) ) 
		{
	
			$eol = "\r\n";
		
		} 
		elseif ( strtoupper( substr( PHP_OS , 0 , 3 ) == 'MAC' ) ) 
		{
	
			$eol = "\r";
			
		} 
		else {
	
			$eol = "\n";
		} 
	
		$headers = 'X-Priority: ' . $this->_priority . $eol;
		$headers .= 'X-Mailer: ' . $this->_mailer . $eol;
		$headers .= 'From: ' . $this->_fromname . ' <' . $this->_from . '>' . $eol;
			
		if ( sizeof ( $this->_cc ) > 0 )
		{ 
		
			$headers .= 'Cc: '. implode( ' ,' , $this->_cc ) . $eol; 
			
		}
		
		if ( sizeof ( $this->_bcc ) > 0 )
		{ 
		
			$headers .= 'Bcc: '. implode( ' ,' , $this->_bcc ) . $eol; 
			
		}
		

		$headers .= 'Reply-To: ' . $this->_fromname . ' <' . $this->_from . '>' . $eol;
		$headers .= 'Return-Path: ' . $this->_from . $eol;
		!empty( $this->_sender ) ? $headers .= 'Sender: ' . $this->_sender . $eol : $headers .= 'Sender: ' . $this->_from . $eol;
		!empty( $this->_sender ) ? $headers .= 'X-Sender: ' . $this->_sender . $eol : $headers .= 'X-Sender: ' . $this->_from . $eol;
		$headers .= 'X-Originating-Email: ' . $this->_from . $eol;
		
		if ( is_array( $this->_headers ) )
		{
		
			foreach ( $this->_headers AS $c => $v )
			{

				$headers .= $c . ': ' . $v . $eol;

			}
			
		}
				
		$headers .= 'MIME-Version: 1.0' . $eol; // here is the line in question
		$msg = '';			
	
		$cSemiRand = strtoupper( substr( md5( rand( 0 , 500 ) . 'x' . rand( 100 , 200 ) ) , 0 , 15 ) );

		$cMiMeBoundry = "==Multipart_Boundary_x{$cSemiRand}x";
	
		if ( sizeof ( $this->_attachments ) > 0 ) 
		{ 
			
			$headers .= "Content-type: multipart/mixed; boundary=\"$cMiMeBoundry\"" . $eol;
			$msg .= "--$cMiMeBoundry" . $eol;		
			$msg .= "Content-type: multipart/alternative; boundary=\"alt--$cMiMeBoundry\"" . $eol . $eol;
		
			# -=-=-=- TEXT EMAIL PART 
			
			if ( !empty( $this->_text_msg ) )
			{

				$msg .= "--alt--$cMiMeBoundry" . $eol;  
				$msg .= 'Content-Type: text/plain; charset=' . $this->_text_charset . $eol;
				$msg .= 'Content-Transfer-Encoding: ' . $this->_text_content_transfer_encoding . $eol;
				$msg .= 'Content-Disposition: inline;' . $eol . $eol;				 				
				
				if ( strtolower( $this->_text_content_transfer_encoding ) == 'base64' )
				{
				
					$msg .= chunk_split( base64_encode( $this->_text_msg ) ) . $eol;
				
				
				}
				else {
				
					$msg .= $this->_text_msg . $eol; 
					
				}				

				$msg .= $eol .$eol;		
		
			}

			# -=-=-=- HTML EMAIL PART 

			if ( !empty( $this->_html_msg ) )
			{
  	
				$msg .= "--alt--$cMiMeBoundry" . $eol;
				$msg .= 'Content-Type: text/html; charset=' . $this->_html_charset . $eol; 
				$msg .= 'Content-Transfer-Encoding: ' . $this->_html_content_transfer_encoding . $eol; 				
				$msg .= 'Content-Disposition: inline;' . $eol . $eol;				 								

				if ( strtolower( $this->_html_content_transfer_encoding ) == 'base64' )
				{
				
					$msg .= chunk_split( base64_encode( $this->_html_msg ) ) . $eol;
				
				
				}
				else {
				
					$msg .= $this->_html_msg . $eol; 
					
				}				

				$msg .= "--alt--$cMiMeBoundry--" . $eol;         
			
			}
          
			foreach ( $this->_attachments AS $nKey => $aValue ) 
			{
			
				if ( is_uploaded_file( $aValue['tmp_name'] ) ) 
				{
         
					$bin = @ fopen( $aValue['tmp_name'] , 'rb' );
					$data = @ fread( $bin , $aValue['size'] );
					@ fclose( $bin );  
                 
					$msg .= "--$cMiMeBoundry" . $eol;
					$msg .= 'Content-Type: ' . $aValue['type'] . ';';
					$msg .= " name=\"$aValue[name]\"" . $eol;
					$msg .= 'Content-Transfer-Encoding: base64' . $eol;
					$msg .= 'Content-Disposition: attachment;' . $eol . $eol;
					$msg .= chunk_split( base64_encode( $data ) ) . $eol . $eol;              
              
				}
    	        else {
    	        
					if ( file_exists( $aValue['path'] ) ) 
					{
					
						$bin = @ fopen( $aValue['path'] , 'rb' );
						$data = @ fread( $bin , filesize( $aValue['path'] ) );
						@ fclose( $bin );  
                 
						$msg .= "--$cMiMeBoundry" . $eol;
						$msg .= 'Content-Type: application/octet-stream;';
						
						!empty( $aValue['name'] ) ? $msg .= ' name="' . $aValue['name'] . '"' . $eol : $msg .= ' name="' . basename( $aValue['path'] ) . '"' . $eol;
					
						$msg .= 'Content-Transfer-Encoding: base64' . $eol;
						$msg .= 'Content-Disposition: attachment;' . $eol . $eol;
						$msg .= chunk_split( base64_encode( $data ) ) . $eol . $eol;              
                           
					}
                                       
				}
								
			}
	
			$msg .= $eol . "--$cMiMeBoundry--" . $eol . $eol;
	
		}
		else {
        
			if ( !empty( $this->_html_msg ) && !empty( $this->_text_msg ) ) 
			{
			
				# -=-=-=- MAIL HEADERS 
			
				$headers .= "Content-Type: multipart/alternative; boundary=\"$cMiMeBoundry\"" . $eol;

				# -=-=-=- TEXT EMAIL PART 

				$msg .= "--$cMiMeBoundry" . $eol;
				$msg .= 'Content-Type: text/plain; charset=' . $this->_text_charset . $eol;
				$msg .= 'Content-Transfer-Encoding: ' . $this->_text_content_transfer_encoding . $eol;
				$msg .= 'Content-Disposition: inline;' . $eol . $eol;				 								 				
				
				if ( strtolower( $this->_text_content_transfer_encoding ) == 'base64' )
				{
				
					$msg .= chunk_split( base64_encode( $this->_text_msg ) ) . $eol;
				
				
				}
				else {
				
					$msg .= $this->_text_msg . $eol; 
					
				}				

				$msg .= $eol .$eol;

				# -=-=-=- HTML EMAIL PART 
  	
				$msg .= "--$cMiMeBoundry" . $eol;
				$msg .= 'Content-Type: text/html; charset=' . $this->_html_charset . $eol; 
				$msg .= 'Content-Transfer-Encoding: ' . $this->_html_content_transfer_encoding . $eol; 				
				$msg .= 'Content-Disposition: inline;' . $eol . $eol;				 								
				
				if ( strtolower( $this->_html_content_transfer_encoding ) == 'base64' )
				{
				
					$msg .= chunk_split( base64_encode( $this->_html_msg ) ) . $eol;
				
				
				}
				else {
				
					$msg .= $this->_html_msg . $eol; 
					
				}				

				$msg .= $eol .$eol;

				# -=-=-=- FINAL BOUNDARY 

				$msg .= $eol . "--$cMiMeBoundry--" . $eol . $eol; 
        
      
			}
			elseif ( !empty( $this->_html_msg ) ) 
			{
		
				$headers .= 'Content-Type: text/html; charset=' . $this->_html_charset . $eol; 
				$msg .= 'Content-Transfer-Encoding: ' . $this->_html_content_transfer_encoding . $eol;
				$msg .= 'Content-Disposition: inline;' . $eol . $eol;				 								 				
				
				if ( strtolower( $this->_html_content_transfer_encoding ) == 'base64' )
				{
				
					$msg .= chunk_split( base64_encode( $this->_html_msg ) ) . $eol;
				
				
				}
				else {
				
					$msg .= $this->_html_msg . $eol; 
					
				}				
								
				$msg .= $eol .$eol;
							
			}
			else {

				$headers .= 'Content-Type: text/plain; charset=' . $this->_text_charset . $eol;
				$msg .= 'Content-Transfer-Encoding: ' . $this->_text_content_transfer_encoding . $eol;
				$msg .= 'Content-Disposition: inline;' . $eol . $eol;				 								 				
				
				if ( strtolower( $this->_text_content_transfer_encoding ) == 'base64' )
				{
				
					$msg .= chunk_split( base64_encode( $this->_text_msg ) ) . $eol;
				
				
				}
				else {
				
					$msg .= $this->_text_msg . $eol; 
					
				}				
				
				$msg .= $eol .$eol;
			
			}	
	
		}
	
		ini_set( sendmail_from , $this->_from );
	
		if ( strtoupper( substr( PHP_OS , 0 , 3 ) != 'WIN' ) ) 
		{
	
			$headers .= $msg;
		
			$msg = '';
	
		}
		
		// need to add imap mail function
				
		if ( mail( $this->_to , $this->_subject , $msg , $headers , "-f " . $this->_from ) )
		{
        	           		
			return true;
        	
		}
		else {
		
			if ( !$this->isAddressValid( $this->_to ) )
			{
			
				$this->error = 'Invalid TO address : ' . $this->_to;
				
			}
			elseif ( !$this->isAddressValid( $this->_from ) )
			{

				$this->error = 'Invalid FROM address : ' . $this->_from;


			}
			else
			{

				$this->error = 'Unable to send email message';

			
			}
    	       		
			return false;  
        	
		}	
	
	}
	
	// =============================================================================
	// =============================================================================
	//           NAME: sender
	//   DATE CREATED: 03/13/2004
	//  DATE MODIFIED: 07/22/2006
	//         USAGE : 
	//       PURPOSE : set the sender address if needed.
	//        RETURNS: none
	//      COMMENTS : 
	//
	//
	// =============================================================================
	// =============================================================================
	
	function sender( $cEmail = null )  
	{
		
		$this->isAddressValid ( $cEmail ) ?	$this->_sender = $cEmail : $this->_sender = '';		
	
	}

	// =============================================================================
	// =============================================================================
	//           NAME: setCharacterSet
	//   DATE CREATED: 
	//  DATE MODIFIED: 
	//         USAGE : 
	//       PURPOSE : 
	//        RETURNS: 
	//      COMMENTS : 
	//
	//
	// =============================================================================
	// =============================================================================
	
	function setCharacterSet( $cValue , $cType = 'TEXT' )  
	{
	
		if ( strtoupper( trim( $cType ) ) == 'HTML' )
		{

			$this->_html_charset = strtolower( trim( $cValue ) );		
		
		}
		else {
		
			$this->_text_charset = strtolower( trim( $cValue ) );		
		
		}
	
	}	

	// =============================================================================
	// =============================================================================
	//           NAME: setContentType
	//   DATE CREATED: 
	//  DATE MODIFIED: 
	//         USAGE : 
	//       PURPOSE : 
	//        RETURNS: 
	//      COMMENTS : 
	//
	//
	// =============================================================================
	// =============================================================================
	
	function setContentType( $cValue , $cType = 'TEXT' )  
	{
	
		if ( strtoupper( trim( $cType ) ) == 'HTML' )
		{

			$this->_html_content_transfer_encoding = strtolower( trim( $cValue ) );
		
		}
		else {
		
			$this->_text_content_transfer_encoding = strtolower( trim( $cValue ) );
		
		}
		
	}	
		
	// =============================================================================
	// =============================================================================
	//           NAME: setHeader
	//   DATE CREATED: 
	//  DATE MODIFIED: 
	//         USAGE : 
	//       PURPOSE : 
	//        RETURNS: 
	//      COMMENTS : 
	//
	//
	// =============================================================================
	// =============================================================================
	
	function setHeader( $cKey = null , $cValue )  
	{
	
		$this->_headers[ $cKey ] = $cValue;		
	
	}	
	
	// =============================================================================
	// =============================================================================
	//           NAME: slurp
	//   DATE CREATED: 02/13/2005
	//  DATE MODIFIED: 04/22/2007
	//         USAGE : $this->slurp( PathtoFile );
	//       PURPOSE : To read contents of file into single string or execute if
	//				   needed.
	//        RETURNS: string
	//      COMMENTS : 
	//
	//
	// =============================================================================
	// =============================================================================
	
	function slurp( $f = null , $aOutput = array() , $lDynamic = 1 ) 
	{
	
		$cReturn = '';
	   
		if ( file_exists( $f ) ) 
		{
   
			ob_start();
      
			if ( strtolower( substr ( stripslashes( $f ), strrpos ( stripslashes( $f ) ,'.'))) == '.php' && $lDynamic ) 
			{
     
				include( $f );
      
				$cReturn = ob_get_contents();
     
			}
			else {
     
				$retval = readfile($f);
        
				if (false !== $retval) { // no readfile error
        
					$cReturn = ob_get_contents(); 
        
				}
     
			}
         
			ob_end_clean();
   
		}
		else {

			if ( substr( trim( strtolower( $f ) ) , 0 , 4 ) == 'http' ) 
			{
		
				$cReturn = file_get_contents( $f );
		
			}
		
		}

		return $cReturn;	
	
	}				
	
	// =============================================================================
	// =============================================================================
	//           NAME: stripWhiteSpace
	//   DATE CREATED: 
	//  DATE MODIFIED: 
	//         USAGE : 
	//       PURPOSE : 
	//        RETURNS: 
	//      COMMENTS : 
	//
	//
	// =============================================================================
	// =============================================================================
	
	static function stripWhiteSpace( $cStr ) 
	{

		return preg_replace( "/\s\s+/" , ' ' , $cStr);

	}	
	
	// =============================================================================
	// =============================================================================
	//           NAME: stopText
	//   DATE CREATED: 
	//  DATE MODIFIED: 
	//         USAGE : 
	//       PURPOSE : 
	//        RETURNS: 
	//      COMMENTS : 
	//
	//
	// =============================================================================
	// =============================================================================
	
	function stopText( $cStr ) 
	{
	
		$cReturn = '';
		
		!empty( $cStr ) ? $nLen = strlen( $cStr ) : $nLen = 4;
		
		$nLen = rand( 2 , $nLen );
		
		for ( $i = 1; $i <= $nLen; $i++ ) 
		{
		
			$cReturn .= '*';
		}

		return $cReturn;

	}					
	
	// =============================================================================
	// =============================================================================
	//           NAME: subject
	//   DATE CREATED: 03/13/2004
	//  DATE MODIFIED: 07/22/2006
	//         USAGE : 
	//       PURPOSE : set the subject of the email message
	//        RETURNS: none
	//      COMMENTS : 
	//
	//
	// =============================================================================
	// =============================================================================
	
	function subject( $cValue = null )  
	{
		
		$this->_subject = $cValue;
	
	}			

	// =============================================================================
	// =============================================================================
	//           NAME: to
	//   DATE CREATED: 03/13/2004
	//  DATE MODIFIED: 07/22/2006
	//         USAGE : 
	//       PURPOSE : set the to field of the email message - only one email 
	//                 address allowed.
	//        RETURNS: none
	//      COMMENTS : 
	//
	//
	// =============================================================================
	// =============================================================================
	
	function to( $cEmail = null )  
	{
		
		$this->isAddressValid ( $cEmail ) ?	$this->_to = $cEmail : $this->_to = '';		
	
	}	

}

// -that's all folks