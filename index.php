<?php require_once('Connections/adminConn.php'); ?>
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
?>
<?php
// *** Validate request to login to this site.
if (!isset($_SESSION)) {
	session_start();
	// set timeout period (in seconds)
	$inactive = 1200; 
	// check to see if $_SESSION['timeout'] is set
	if ( isset($_SESSION['timeout']) ) {
		$session_life = time() - $_SESSION['timeout'];
		if ( $session_life > $inactive ) {
			session_destroy();
			header("Location: /");
		}
	}
	$_SESSION['timeout'] = time();
}

$loginFormAction = $_SERVER['REQUEST_URI'];
if (isset($_GET['accesscheck'])) {
  $_SESSION['PrevUrl'] = $_GET['accesscheck'];
}

if (isset($_REQUEST['tbUName'])) {
  $loginUsername=$_REQUEST['tbUName'];
  $password=$_REQUEST['pswd'];
  $MM_fldUserAuthorization = "ugID";
  //$MM_redirectLoginSuccess = "main.php" . "?".$_SERVER['QUERY_STRING'];
  $MM_redirectLoginSuccess = "main.php";
  $MM_redirectLoginFailed = "denied.php";
  $MM_redirecttoReferrer = false;
  mysql_select_db($database_adminConn, $adminConn);
  	
/*$LoginRS__query=sprintf("SELECT uName, uPassword, ugID, iID FROM ip_users WHERE uName=%s AND uPassword=%s",
  	  GetSQLValueString($loginUsername, "text"), GetSQLValueString($password, "text")); */
  $LoginRS__query=sprintf("SELECT uID, uName, ePassword, ugID, iID FROM ip_users WHERE uName=%s", 
	  GetSQLValueString($loginUsername, "text")); 
   
  $LoginRS = mysql_query($LoginRS__query, $adminConn) or die(mysql_error());
  $loginFoundUser = mysql_num_rows($LoginRS);
  if ($loginFoundUser) {
	//check password
	$password_hash = mysql_result($LoginRS,0,'ePassword');
	if(crypt($password, $password_hash) == $password_hash) {
		// password is correct
	} else {
    	header("Location: ". $MM_redirectLoginFailed );
		exit;
	}
    
	$loginUserID  = mysql_result($LoginRS,0,'uID');
	$loginStrGroup  = mysql_result($LoginRS,0,'ugID');
    $loginInvGroup  = mysql_result($LoginRS,0,'iID');
    
	if (PHP_VERSION >= 5.1) {session_regenerate_id(true);} else {session_regenerate_id();}
    //declare session variables and assign them
    $_SESSION['MM_UserID'] = $loginUserID;
	$_SESSION['MM_Username'] = $loginUsername;
    $_SESSION['MM_UserGroup'] = $loginStrGroup;	      
    $_SESSION['mm_Investor'] = $loginInvGroup;	      

    if (isset($_SESSION['PrevUrl']) && false) {
      $MM_redirectLoginSuccess = $_SESSION['PrevUrl'];	
    }
    header("Location: " . $MM_redirectLoginSuccess );
	} else {
    header("Location: ". $MM_redirectLoginFailed );
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
<title>Log In : Report Portal</title>
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
-->
</style>
<script src="SpryAssets/SpryValidationTextField.js" type="text/javascript"></script>
<script src="SpryAssets/SpryValidationPassword.js" type="text/javascript"></script>
<link href="SpryAssets/SpryValidationTextField.css" rel="stylesheet" type="text/css" />
<link href="SpryAssets/SpryValidationPassword.css" rel="stylesheet" type="text/css" />
</head>

<body class="access_layout">
<?php require('rpt_header.php'); ?>
<div id="divPage" class="container" role="main">
<div class="row" id="rpt_layout">
<h2 class="rpt_title">Access Reports </h2>
      <div class="generic-content"><p> Clients with login privileges may access their reports here. Simply enter 
        your username and password below to begin.</p>
        <br>

        <form ACTION="<?php echo $loginFormAction; ?>" method="POST" id="frmLogin" name="frmLogin">
          <table id="tblForm" border="0" cellspacing="1" cellpadding="4" summary="login form">
            <tr>
              <th><label for="tbUName">Username</label> </th>
              <td><span id="spryUName">
                <input type="text" name="tbUName" id="tbUName" />
                <span class="textfieldRequiredMsg"><strong>Username</strong> is required.</span></span></td>
            </tr>
            <tr>
              <th><label for="pswd">Password</label> </th>
              <td><span id="spryPswd">
                <input type="password" name="pswd" id="pswd" />
                <span class="passwordRequiredMsg"><strong>Password</strong> is required.</span></span></td>
            </tr>
            <tr>
            	<th></th>
                <td>
                	<p><input name="btnSubmit" type="submit" value="Login" class="btn" /></p>
                    <br>
                    <p><a href="password_recovery.php">Forgotten username or password?</a></p>
                </td>
            </tr>
          </table>
        </form>
      </div>
    </div>
  </div>
  <?php require('rpt_footer.php'); ?>

<script type="text/javascript">
<!--
var sprytextfield1 = new Spry.Widget.ValidationTextField("spryUName");
var sprypassword1 = new Spry.Widget.ValidationPassword("spryPswd");
//-->
</script> 
</div>

<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>

<!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="js/bootstrap.min.js"></script>

</body>

</html>