<?php require_once('Connections/adminConn.php'); ?>
<?php
// check to prevent eMail injections
function checkmail($email)
{
	//filter_var() santitizes the eMail address using FILTER_SANITIZE_EMAIL
	$email = filter_var($email, FILTER_SANITIZE_EMAIL);
	
	//filter_var() validates the eMail address using FILTER_VALIDATE_EMAIL
	if ( filter_var($email, FILTER_VALIDATE_EMAIL) ) {
		return TRUE;
	} else {
		return FALSE;
	}
}
?>
<?php
if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  if (PHP_VERSION < 6) {
    $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
  }

  $theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);

  switch ($theType) {
    case "text":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;    
    case "long":
    case "int":
      $theValue = ($theValue != "") ? intval($theValue) : "NULL";
      break;
    case "double":
      $theValue = ($theValue != "") ? doubleval($theValue) : "NULL";
      break;
    case "date":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;
    case "defined":
      $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
      break;
  }
  return $theValue;
}
}

//initialize vars
$msg = '';

if ( isset($_POST['hid']) && $_POST['hid']=='pswdrecv' && $_POST['cEmail']!='' ) { // begin password recovery
	//create a random 8-char password
	$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!#$%&ABCDEFGHIJKLmnopqrstu";
    $password = substr( str_shuffle( $chars ), 0, 8);
	$password_hash = better_crypt($password);
	
	mysql_select_db($database_adminConn, $adminConn);
	$query_rsPassword = sprintf("UPDATE ip_users SET ePassword=%s WHERE ip_users.eMail=%s",
		GetSQLValueString($password_hash, "text"),
		GetSQLValueString($_POST['cEmail'], "text"));
	$rsPassword = mysql_query($query_rsPassword, $adminConn) or die(mysql_error());
	$query_rsPassword = sprintf("SELECT ip_users.uName FROM ip_users WHERE ip_users.eMail=%s",
		GetSQLValueString($_POST['cEmail'], "text"));
	$rsPassword = mysql_query($query_rsPassword, $adminConn) or die(mysql_error());
	$row_rsPassword = mysql_fetch_assoc($rsPassword);
	$totalRows_rsPassword = mysql_num_rows($rsPassword);	
	
	if ($totalRows_rsPassword > 0) { //show if recordset not empty 
		  //email credentials
		  $to = $_POST['cEmail'];
		  $subject = 'Report Portal Log In - Password Recovery';
		  $message = 'You are registered on the Report Portal web site with the following credentials:' . "\r\n";
		  $message .= "\r\n";
		  $message .= '[Username] : '. $row_rsPassword['uName'] ."\r\n";
		  $message .= '[New Password] :  '. $password ."\r\n";
		  $message .= "\r\n";
		  $message .= 'Visit the Report Portal website (http://reportportal.com) and login to view your investment reports.';
		  $mailcheck = checkmail($to);
		  if ( $mailcheck==TRUE ) { //send email
			  if (mail($to, $subject, $message, 'From: nobody@domain.com')):
			  	$msg = '<p>Your account information has been forwarded. Please check your eMail for more information.</p>';
			  else:
				$msg = '<p>eMail has *not* been sent</p>';
			  endif;
		  }
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Password Recovery : Report Portal</title>
<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />

<!-- Bootstrap -->
<link href="css/bootstrap.min.css" rel="stylesheet">

<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->

<style type="text/css">
<!--
@import url("css/default.css");
legend { margin-left:-25px; }
-->
</style>
</head>

<body class="access_layout">
<?php require('rpt_header.php'); ?>
<div id="divPage" class="container" role="main">
<div class="row" id="rpt_layout">
<h2 class="rpt_title">Password Recovery</h2>
<div id="login" class="generic-content">
<?php 
if ($totalRows_rsPassword > 0) { //show if recordset not empty 
	echo $msg;
} else { //show if recordset is empty or default page
	if (isset($_POST['hid']) && $_POST['cEmail']<>'') {
		$msg = 'No account was found registered with provided address';
	}
?>
<form action="password_recovery.php" method="POST" id="precovery">
<p>Forgotten your username or password? Simply provide your email and we'll forward login credentials to you.</p>
<br>
<table border="0" cellspacing="1" cellpadding="4" summary="password recovery form" id="tblForm">
<tr>
<th class="lbl"><label for="cEmail">eMail Address</label></th>
<td><input name="cEmail" type="text" id="cEmail" size="35" maxlength="150" value="<?php if (isset($_POST['cEmail']) && $_POST['cEmail']!='') { echo($_POST['cEmail']); } ?>" /></td>
</tr>
<?php 
if ( $msg <> '' ) {
	echo('<tr><td></td><td><span style="font-size:11px;color:red">'.$msg.'</span></td></tr>');
}
?>
<tr>
<th></th>
<td>
<input type="hidden" id="hid" name="hid" value="pswdrecv" />
<p><input name="submit" type="submit" class="btn" id="submit" value="Submit" /></p>
</td>
</tr>
</table>
</form>
<?php } //end password recovery check ?>
<br>
<p>&larr; return to <a href="login.php">login</a></p>
</div>
      </div>
  </div>
  <?php require('rpt_footer.php'); ?>

<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>

<!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="js/bootstrap.min.js"></script>

</body>
</html>
<?php
mysql_free_result($rsPassword);
?>
