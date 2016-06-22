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
$MM_authorizedUsers = "adm,amadm,astmgr,acct";
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

mysql_select_db($database_adminConn, $adminConn);
	$query_rsUsers = "SELECT `uID`, uName, FirstName, LastName, iName, eMail, ugID FROM ip_users LEFT JOIN ip_investors ON (ip_users.iID=ip_investors.iID) where uName!='dummy' ORDER BY FirstName ASC";

$rsUsers = mysql_query($query_rsUsers, $adminConn) or die(mysql_error());
$row_rsUsers = mysql_fetch_assoc($rsUsers);
$totalRows_rsUsers = mysql_num_rows($rsUsers);
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
table#tblList { width: 98%; }
table#tblList tr { /*border-top: 1px solid #999;*/ border-bottom: 1px solid #999; }
table#tblList td { padding: 5px 7px 5px 0; line-height: normal; }
-->
</style>
</head>

<body>
<?php require('rpt_header.php'); ?>
<div id="divPage" class="container" role="main">
<div class="row" id="rpt_layout">
<h2 class="rpt_title">User Admin</h3>
<?php require('adminNav.php'); ?>
<div class="generic-content">
<div style="color:#900;font-style:oblique">
<?php
echo($_SESSION['umsg']);

unset($_SESSION['umsg']);
unset($_SESSION['uinfo']);
?>
</div>
<?php if ($_SESSION['MM_UserGroup']!='acct') { ?>
<p><a href="add_user.php" class="btn">Add New User</a></p>
<?php } ?>
<table id="tblList" border="0" cellspacing="1" cellpadding="4" summary="report user list">
<tr<?php echo($_SESSION['MM_UserGroup']!='acct' ? ' class="caption"' : ''); ?>>
<th scope="col">Login</th>
<th scope="col">Name</th>
<th scope="col">Company</th>
<th>&nbsp;</th>
</tr>
<?php do { ?>
  <tr>
    <td><?php echo $row_rsUsers['uName']; ?></td>
    <td><?php echo $row_rsUsers['FirstName'] . ' ' . $row_rsUsers['LastName']; ?><br />
    <span><?php echo $row_rsUsers['eMail']; ?></span></td>
    <td><?php echo $row_rsUsers['iName']; ?></td>
    <td style="white-space:nowrap">
    <?php if ($_SESSION['MM_UserGroup']=='adm' || ($_SESSION['MM_UserGroup']=='amadm' && ($row_rsUsers['ugID']!='adm' && $row_Users['ugID']!='amadm')) || ($_SESSION['MM_UserGroup']=='astmgr' &&  $row_rsUsers['ugID']=="inv")) { ?>
       <a href="edit_user.php?uid=<?php echo $row_rsUsers['uID']; ?>">Edit</a> | <a href="delete_user.php?uid=<?php echo $row_rsUsers['uID']; ?>">Delete</a>
    <?php } ?>
    </td>
  </tr>
<?php } while ($row_rsUsers = mysql_fetch_assoc($rsUsers)); ?>
</table>
<br />
<?php if ($_SESSION['MM_UserGroup']!='acct') { ?>
<p><a href="add_user.php" class="btn">Add New User</a></p>
<?php } ?>
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
<?php
mysql_free_result($rsUsers);
?>
