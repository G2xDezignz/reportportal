<?php require_once('Connections/adminConn.php'); ?>
<?php
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
//$MM_authorizedUsers = "adm,amadm,astmgr";
$MM_authorizedUsers = "adm,amadm,astmgr,acct,inv,exec";
$MM_donotCheckaccess = "false";

// *** Restrict Access To Page: Grant or deny access to this page
function isAuthorized($strUsers, $strGroups, $UserName, $UserGroup) { 
  // For security, start by assuming the visitor is NOT authorized. 
  $isValid = False; 

  // When a visitor has logged into this site, the Session variable MM_Username set equal to their username. 
  // Therefore, we know that a user is NOT logged in if that Session variable is blank. 
  if (!empty($UserName)) { 
    // Besides being logged in, you may restrict access to only certain users based on an ID established when they login. 
    // Parse the strings into arrays. 
    $arrUsers = Explode(",", $strUsers); 
    $arrGroups = Explode(",", $strGroups); 
    if (in_array($UserName, $arrUsers)) { 
      $isValid = true; 
    } 
    // Or, you may restrict access to only certain users based on their username. 
    if (in_array($UserGroup, $arrGroups)) { 
      $isValid = true; 
    } 
    if (($strUsers == "") && false) { 
      $isValid = true; 
    } 
  } 
  return $isValid; 
}

$MM_restrictGoTo = "denied.php";
if (!((isset($_SESSION['MM_Username'])) && (isAuthorized("",$MM_authorizedUsers, $_SESSION['MM_Username'], $_SESSION['MM_UserGroup'])))) {   
  $MM_qsChar = "?";
  $MM_referrer = $_SERVER['PHP_SELF'];
  if (strpos($MM_restrictGoTo, "?")) $MM_qsChar = "&";
  if (isset($QUERY_STRING) && strlen($QUERY_STRING) > 0) 
  $MM_referrer .= "?" . $QUERY_STRING;
  $MM_restrictGoTo = $MM_restrictGoTo. $MM_qsChar . "accesscheck=" . urlencode($MM_referrer);
  header("Location: ". $MM_restrictGoTo); 
  exit;
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


if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "frmUser")) {
  $updateSQL = sprintf("UPDATE ip_users SET ePassword=%s WHERE `uID`=%s",
                       GetSQLValueString(better_crypt($_POST['pswd']), "text"),
                       GetSQLValueString($_POST['hfID'], "int"));


  mysql_select_db($database_adminConn, $adminConn);
  $Result1 = mysql_query($updateSQL, $adminConn) or die(mysql_error());
	
  if ($_SESSION['MM_UserGroup']=='adm' || $_SESSION['MM_UserGroup']=='amadm' || $_SESSION['MM_UserGroup']=='astmgr' ) {
	   $updateGoTo = "users.php";
		  /*if (isset($_SERVER['QUERY_STRING'])) {
			$updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
			$updateGoTo .= $_SERVER['QUERY_STRING'];
		  }*/
	  $_SESSION['uinfo']="'".$_POST['fname']." ".$_POST['lname']."'";
	  $_SESSION['umsg']='<p><strong>' . $_SESSION['uinfo']."</strong> has been edited.</p>";
 } else {
	   $updateGoTo = "main.php";
	   $_SESSION['uinfo']="";
	   $_SESSION['umsg']="";

 }

  header(sprintf("Location: %s", $updateGoTo));
}

mysql_select_db($database_adminConn, $adminConn);
$query_rsIGroup = "SELECT * FROM ip_investors";
$rsIGroup = mysql_query($query_rsIGroup, $adminConn) or die(mysql_error());
$row_rsIGroup = mysql_fetch_assoc($rsIGroup);
$totalRows_rsIGroup = mysql_num_rows($rsIGroup);

mysql_select_db($database_adminConn, $adminConn);
$query_rsUGroup = "SELECT * FROM ip_usergroup";
$rsUGroup = mysql_query($query_rsUGroup, $adminConn) or die(mysql_error());
$row_rsUGroup = mysql_fetch_assoc($rsUGroup);
$totalRows_rsUGroup = mysql_num_rows($rsUGroup);

$colname_rsUser = "-1";
if (isset($_GET['uid'])) {
  $colname_rsUser = $_GET['uid'];
} else {
  $colname_rsUser = $_SESSION['MM_UserID'];
}
mysql_select_db($database_adminConn, $adminConn);
$query_rsUser = sprintf("SELECT `uID`, uName, FirstName, LastName, eMail, ugID, iID FROM ip_users WHERE `uID` = %s", GetSQLValueString($colname_rsUser, "int"));
$rsUser = mysql_query($query_rsUser, $adminConn) or die(mysql_error());
$row_rsUser = mysql_fetch_assoc($rsUser);
$totalRows_rsUser = mysql_num_rows($rsUser);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Report Portal</title>
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
<script src="SpryAssets/SpryValidationConfirm.js" type="text/javascript"></script>
<link href="SpryAssets/SpryValidationTextField.css" rel="stylesheet" type="text/css" />
<link href="SpryAssets/SpryValidationConfirm.css" rel="stylesheet" type="text/css" />
</head>

<body>
<?php require('rpt_header.php'); ?>
<div id="divPage" class="container" role="main">
<div class="row" id="rpt_layout">
<h2 class="rpt_title">User Admin</h3>
<?php require('adminNav.php'); ?>
<div class="generic-content">
<h3>Edit User Password</h3>
<form action="<?php echo $editFormAction; ?>" name="frmUser" id="frmUser" method="POST">
<p><?php echo ($row_rsUser['FirstName'].' '.$row_rsUser['LastName'].' ['.$row_rsUser['uName'].']');?></p>
<table id="tblForm" border="0" cellpadding="4" cellspacing="1" summary="edit user password form">
<tr>
<th><label for="pswd">Password</label></th>
<td><span id="spryPswd">
  <input id="pswd" name="pswd" type="password" size="35" maxlength="45" />
  <span class="textfieldRequiredMsg"><strong>Password</strong> is required.</span></span></td>
</tr>
<tr>
<th><label for="pswd2">Confirm Password</label></th>
<td><span id="spryconfirmPswd">
  <input id="pswd2" name="pswd2" type="password" size="35" maxlength="45" />
  <span class="confirmRequiredMsg">A value is required.</span><span class="confirmInvalidMsg"><br />The password values don't match.</span></span></td>
</tr>
</table>
<br />
<input name="hfID" type="hidden" id="hfID" value="<?php echo $row_rsUser['uID']; ?>" />
<input name="fname" type="hidden" id="fname" value="<?php echo $row_rsUser['FirstName']; ?>" />
<input name="lname" type="hidden" id="lname" value="<?php echo $row_rsUser['LastName']; ?>" />
<input name="btnSubmit" type="submit" id="btnSubmit" class="btn" value="Update Password" />
<input type="hidden" name="MM_update" value="frmUser" />
</form>
</div>
<script type="text/javascript">
<!--
var sprytextfield1 = new Spry.Widget.ValidationTextField("spryPswd");
var spryconfirm1 = new Spry.Widget.ValidationConfirm("spryconfirmPswd", "pswd");
//-->
</script>
</div>
<div class="row" id="user_logout"><?php require('logout.php'); ?></div>
</div>
<?php require('rpt_footer.php'); ?>


<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>

<!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="js/bootstrap.min.js"></script>

</body>
</html>
<?php
mysql_free_result($rsIGroup);

mysql_free_result($rsUGroup);

mysql_free_result($rsUser);
?>
