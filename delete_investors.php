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
$MM_authorizedUsers = "adm,amadm";
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

if ((isset($_POST['hIID'])) && ($_POST['hIID'] != "")) {
   //delete all files associated with investor
	mysql_select_db($database_adminConn, $adminConn);
	$query_rsInvestorFiles = sprintf("SELECT rFile FROM ip_reports WHERE Investor = %s", 
		GetSQLValueString($_POST['hIID'], "int"));
	$rsInvestorFiles = mysql_query($query_rsInvestorFiles, $adminConn) or die(mysql_error());
	$row_rsInvestorFiles = mysql_fetch_assoc($rsInvestorFiles);
	$totalRows_rsInvestorFiles = mysql_num_rows($rsInvestorFiles);
	do {
		unlink("reports/".$row_rsInvestorFiles['rFile']);
	} while ($row_rsInvestorFiles = mysql_fetch_assoc($rsInvestorFiles));
	mysql_free_result($rsInvestorFiles);

  //delete investor from SQL	
  $deleteSQL = sprintf("DELETE FROM ip_investors WHERE iID=%s",
                       GetSQLValueString($_POST['hIID'], "int"));

  mysql_select_db($database_adminConn, $adminConn);
  $Result1 = mysql_query($deleteSQL, $adminConn) or die(mysql_error());

  $deleteGoTo = "investors.php";
  /*if (isset($_SERVER['QUERY_STRING'])) {
    $deleteGoTo .= (strpos($deleteGoTo, '?')) ? "&" : "?";
    $deleteGoTo .= $_SERVER['QUERY_STRING'];
  }*/
  $_SESSION['umsg']='<p><strong>' . $_SESSION['uinfo'] . '</strong> has been deleted.</p>';  
  header(sprintf("Location: %s", $deleteGoTo));
}

$colname_rsInvestors = "-1";
if (isset($_GET['iid'])) {
  $colname_rsInvestors = $_GET['iid'];
}
mysql_select_db($database_adminConn, $adminConn);
$query_rsInvestors = sprintf("SELECT * FROM ip_investors WHERE iID = %s", GetSQLValueString($colname_rsInvestors, "int"));
$rsInvestors = mysql_query($query_rsInvestors, $adminConn) or die(mysql_error());
$row_rsInvestors = mysql_fetch_assoc($rsInvestors);
$totalRows_rsInvestors = mysql_num_rows($rsInvestors);
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
</head>

<body>
<?php require('rpt_header.php'); ?>
<div id="divPage" class="container" role="main">
<div class="row" id="rpt_layout">
<h2 class="rpt_title">Investor Admin</h3>
<?php require('adminNav.php'); ?>
<div class="generic-content">
<h3>Delete Investor</h3>
<form name="frmInvestor" id="frmInvestor" method="POST">
<p class="note">Are you sure you want to delete the following investor?<br />All reports and users associated with this investor will be deleted as well.</p>
<table id="tblList" border="0" cellpadding="4" cellspacing="1" summary="edit investment company form">
<tr>
<th><label for="iname">Investment Company Name:</label>&nbsp;</th>
<td><?php echo $row_rsInvestors['iName']; ?></td>
</tr>
</table>
<br />
<input name="hIID" type="hidden" id="hIID" value="<?php echo $row_rsInvestors['iID']; ?>" />
<input name="btnSubmit" type="submit" id="btnSubmit" class="btn" value="Delete Investment Company" />
</form>
</div>
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
<?php $_SESSION['uinfo']="'" . $row_rsInvestors['iName'] . "'"; ?>
<?php
mysql_free_result($rsInvestors);
?>
