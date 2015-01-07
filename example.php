<?php

	include '_variables.php';
		
?>   
<style type="text/css">

fieldset {
	padding: 1em;
	font:80%/1 sans-serif;
	}

legend {
	font-size: 18px;
	font-weight: bold;
}
  
label {
  float:left;
  width:25%;
  margin-right:0.5em;
  padding-top:0.2em;
  text-align:right;
  font-weight:bold;
  }
  
input[type=text]{
	font: 14px/1.25 "Lucida Sans Unicode", Arial, Helvetica, sans-serif; 	
	border-radius:4px;
    -moz-border-radius:4px;
    -webkit-border-radius:4px;
    box-shadow:0 3px 4px #ddd;
    -moz-box-shadow:0 3px 4px #ddd;
    -webkit-box-shadow:0 3px 4px #ddd;    
    background: white;
    border:1px solid #aaa;
    color:#555555;
    padding:6px;
    margin-bottom: 4px;
}


input[type=password]{
	font: 14px/1.25 "Lucida Sans Unicode", Arial, Helvetica, sans-serif; 
	border-radius:4px;
    -moz-border-radius:4px;
    -webkit-border-radius:4px;
    box-shadow:0 3px 4px #ddd;
    -moz-box-shadow:0 3px 4px #ddd;
    -webkit-box-shadow:0 3px 4px #ddd;    
    background: white;
    border:1px solid #aaa;
    color:#555555;
    padding:6px;

}

select{
	font: 14px/1.25 "Lucida Sans Unicode", Arial, Helvetica, sans-serif; 	
	border-radius:4px;
    -moz-border-radius:4px;
    -webkit-border-radius:4px;
    box-shadow:0 3px 4px #ddd;
    -moz-box-shadow:0 3px 4px #ddd;
    -webkit-box-shadow:0 3px 4px #ddd;    
    background: white;
    border:1px solid #aaa;
    color:#555555;
    padding:6px;
}

select[multiple] {
    background: #fff;
}

textarea {
	font: 14px/1.25 "Lucida Sans Unicode", Arial, Helvetica, sans-serif; 
    overflow:           visible;
    height:             16em;
    padding:6px;
} 

.btn {
	text-align: left;
	font: 16px/16px "agb" , Arial , Helvetica , sans-serif;
	font-weight: normal;
	color: black;
	cursor: pointer;
	filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#fcfcfc', endColorstr='#f0f0f0'); /* for IE */
	background: -webkit-gradient(linear, left top, left bottom, from(#fcfcfc), to(#f0f0f0)); /* for webkit browsers */
	background: -moz-linear-gradient(top,  #fcfcfc,  #f0f0f0); /* for firefox 3.6+ */ 
	border: 2px solid #e6e6e6;
	border-radius: 4px;
    -moz-border-radius: 4px;
    -webkit-border-radius: 4px;
}

.btn:hover {
	background: #e6e6e6;
	border: 2px solid #E6722E;	
} 

.error {
	color: red;
}
  
</style>

<?php 

echo !empty( $cError ) ? '<div class="error">' . $cError . '</div>' : false;

if ( $_SERVER['REQUEST_METHOD'] == 'GET' || !empty( $_POST ) )
{

?>

<form action="<?=$_SERVER['PHP_SELF'] ?>" method="POST" id="foonster" name="foonster" enctype="multipart/form-data">
	<fieldset>
		<legend>Contact Form</legend>
	
		<label for="name">Name:</label>
		<input type="text" name="name" id="name" value="<?=$_POST['name'] ?>"/><br />

		<label for="email">Email:</label>
		<input type="text" name="email" id="email" value="<?=$_POST['email'] ?>"/><br />

		<label for="address">Address:</label>
		<input type="text" name="address" id="address" value="<?=$_POST['address'] ?>"/><br />

		<label for="city">City:</label>
		<input type="text" name="city" id="city" value="<?=$_POST['city'] ?>"/><br />

		<label for="state">State:</label>
		<input type="text" name="state" id="state" value="<?=$_POST['state'] ?>"/><br />

		<label for="postal_code">Postal Code:</label>
		<input type="text" name="postal_code" id="postal_code" value="<?=$_POST['postal_code'] ?>"/><br />

		<label for="phone">Phone:</label>
		<input type="text" name="phone" id="phone" value="<?=$_POST['phone'] ?>"/><br />

		<label for="msg">Message:</label>
		<textarea name="msg"><?=$_POST['msg'] ?></textarea><br />

		<label for="fupd_1">File:</label>
		<input type="file" name="fupd_1" id="fupd_1"/><p />

		<?php
		
		if ( $lCaptcha ) {
		
			echo recaptcha_get_html( $publickey , $reError); 
		
		}
		
		?>
		
		<input type="submit" name="sbmtbtn" id="sbmtbtn" value="Send Form" class="btn">

	</fieldset>
</form>

<?php
}