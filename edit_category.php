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


/*if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}*/

$msg="";
if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "frmReport")) {
	if ($_POST["btnHide"] == "Remove from Report Group Selection") { //hide report group
		  $updateSQL = sprintf("UPDATE ip_reportgroup SET rgHide=1 WHERE rgID=%s",
							   GetSQLValueString($_POST['hID'], "int"));
		
		  mysql_select_db($database_adminConn, $adminConn);
		 // $Result1 = mysql_query($updateSQL, $adminConn) or die(mysql_error());
		  $Result1 = mysql_query($updateSQL, $adminConn);
		  $updateGoTo = "categories.php";
		  /*if (isset($_SERVER['QUERY_STRING'])) {
			$updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
			$updateGoTo .= $_SERVER['QUERY_STRING'];
		  }*/
		  header(sprintf("Location: %s", $updateGoTo));	
	} else {
	  $cName = $_POST["cName"];
	  if (empty($cName)) {
		  $msg = "A value is required.";
	  } else {
		  $updateSQL = sprintf("UPDATE ip_reportgroup SET rgName=%s WHERE rgID=%s",
							   GetSQLValueString($_POST['cName'], "text"),
							   GetSQLValueString($_POST['hID'], "int"));
		
		  mysql_select_db($database_adminConn, $adminConn);
		 // $Result1 = mysql_query($updateSQL, $adminConn) or die(mysql_error());
		  $Result1 = mysql_query($updateSQL, $adminConn);
		  if (!$Result1) {
			  $msg = "Value already exists; duplicates are not allowed.";
		  } else {
			  $updateGoTo = "categories.php";
			  /*if (isset($_SERVER['QUERY_STRING'])) {
				$updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
				$updateGoTo .= $_SERVER['QUERY_STRING'];
			  }*/
			  header(sprintf("Location: %s", $updateGoTo));
		  }
	  }
	}
}

$colname_rsReportGrp = "-1";
if (isset($_GET['id'])) {
  $colname_rsReportGrp = $_GET['id'];
}
mysql_select_db($database_adminConn, $adminConn);
$query_rsReportGrp = sprintf("SELECT * FROM ip_reportgroup WHERE rgID = %s", GetSQLValueString($colname_rsReportGrp, "int"));
$rsReportGrp = mysql_query($query_rsReportGrp, $adminConn) or die(mysql_error());
$row_rsReportGrp = mysql_fetch_assoc($rsReportGrp);
$totalRows_rsReportGrp = mysql_num_rows($rsReportGrp);
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
<h2 class="rpt_title">Report Group Admin</h3>
<?php require('adminNav.php'); ?>
<div class="generic-content">
<h3>Edit Report Group</h3>
<?php if ($row_rsReportGrp['rgHide'] == 1) { ?>
<p class="rgHide">This has been removed from the 'Report Group' dropdown selection on <strong>Reports Admin</strong></p>
<?php } ?>
<form action="<?php echo $editFormAction; ?>" id="frmReport" name="frmReport" method="POST">
<table border="0" cellspacing="1" cellpadding="4" id="tblForm" summary="report group form">
<tr>
<th><label for="cName">Report Group:</label></th>
<td>
<?php if ($row_rsReportGrp['rgHide'] != 1) { ?>
<input name="cName" type="text" id="cName" value="<?php echo $row_rsReportGrp['rgName']; ?>" size="50" maxlength="100" /><br />
<span class="err"><?php echo($msg); ?></span>
<?php } else { ?>
<?php echo $row_rsReportGrp['rgName']; ?>
<?php } ?>
</td>
</tr>
</table>
<br />
<input name="hID" type="hidden" value="<?php echo $row_rsReportGrp['rgID']; ?>" />
<?php if ($row_rsReportGrp['rgHide'] != 1) { ?>
<input name="btnSubmit" type="submit" id="btnSubmit" class="btn" value="Edit Report Group" /> &nbsp;
<input name="btnHide" type="submit" id="btnHide" class="btn" value="Remove from Report Group Selection" />
<input type="hidden" name="MM_update" value="frmReport" />
<?php } ?>
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
<?php
mysql_free_result($rsReportGrp);
?>
