# Postman
 
 Postman is a generic PHP processing script to the e-mail gateway that 
 parses the results of any form and sends them to the specified users. This 
 script has many formatting and operational options, most of which can be 
 specified within a variable file "_variables.php" each form.

## FILES

_postman.php - main processing file.

example.php - example PHP file using the script
 
Email.php - Foonster Technology Email Module - YAMM

PHPMailer - PHPMailer 6.0
  
stop-file.txt - file containing all words that you 
want to supress from the email.

_variables.php - file containing all the variables to handle
processing options and destinations.  You can have many 
variable files if you are running multiple forms on your site.

### VARIABLES IN CONFIGURATION FILE

$lAttachUploads - allow file uploads

$cStopWords = path to file containing all words that should be 
removed from any form field.  These are not partial and are considered
word boundry limitations

$lCaptcha = Do you want the captcha logic to be used when validating
the form.  You will need to include the HTML captcha information 
in your form from the example for this to work or you will just get
incorrect captcha errors all day.

$aEmail = associative array containing all the values required
to send an email to the reciepient of the form.

  to  | single email address
  from| single email address - $_POST['email'] default
  fromname    | comman name associated with from 
  cc  | comma delimited list of email addresses
  bcc | comma delimited list of email addresses
  subject     | character string
  msg-html    | associative array
      | 'path' - file path - HTML version of email
      | 'character-set' - character set to use for data
      | 'content-type' - content type set to use for data

  msg-text    
      | 'path' - file path - PLAINTEXT version of email
      | 'character-set' - character set to use for data
      | 'content-type' - content type set to use for data

  attachments | array( '0' => array( 'path' => '' , 'name' => '' ) )

$aAcknowledgment = associative array containing all the values required
to send an acknowledgement email to the end-user of the form.

  from| single email address - $_POST['email'] default
  fromname    | comman name associated with from 
  cc  | comma delimited list of email addresses
  bcc | comma delimited list of email addresses
  subject     | character string
  msg-html    | associative array
      | 'path' - file path - HTML version of email
      | 'character-set' - character set to use for data
      | 'content-type' - content type set to use for data

  msg-text    
      | 'path' - file path - PLAINTEXT version of email
      | 'character-set' - character set to use for data
      | 'content-type' - content type set to use for data
  attachments | array( '0' => array( 'path' => '' , 'name' => '' ) )

       $aRequiredFields = associative array containing all the fields
       that are verified, the length of each field and the type of variable
		 scrubbing required.

		 $aRequiredFields = array( 
       	'Name' => array( 
       		'id' => 'form id associated with field',
       		'min-length' => minimum length of value,
       		'scrub' => 'type of scrubbing for the variable.',
          'type' => 'type of field.' // must be marked as "file" to test.
       	),
       

Example: 
       $aRequiredFields = array( 
       	'Name' => array( 
       		'id' => 'name',
       		'min-length' => 3,
       		'scrub' => 'ALPHA',
          'type' => 'text'
       	),
       	'Email' => array( 
       		'id' => 'email',
       		'length' => 3,
       		'scrub' => 'EMAIL',
          'type' => 'text'
       	),
        'File' => array( 
          'id' => 'fupd_1',
          'length' => 3,
          'scrub' => 'N/A',
          'type' => 'file'
        )
       );


		 ALPHA - Only characters from A-Z and spaces.
		 ALPHA_NUM - Only characters from A-Z & 0-9 and spaces.
		 SIMPLE - Only characters found on the keyboard no special characters..
		 EMAIL - Only characters that are part of a well-formed email address.
		 HYPERLINK - A string that has been properly formatted as a URL.
		 WHOLE_NUM - A whole number example 1000000 valid 1,000,000 invalid
		 FLOAT_NUM - A float point number
		 FORMAT_NUM - A properly formatted number
		 SQL_INJECT - Only allow characters that are valid in value SQL calls.
		 REMOVE_SPACES - Remove all spaces from the string.
		 REMOVE_DOUBLESPACE - Remove double space and replace with single spaces.
		 BASIC - Only characters found on the keyboard no special characters.
     N/A - Not applicable and no scrubbing will be performed on the variable.


 ### RETURN

       This process will return one of three responses.

       1.) $cError : PHP Variable containing error message.

       2.) $cReturnTXT : PHP Variable containing return text.

       3.) $cReturnURL : If not null, the script will attempt to send
  			   the user to the URL provided with a GET.
