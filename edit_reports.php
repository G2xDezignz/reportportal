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


if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "frmReport")) {
  $updateSQL = sprintf("UPDATE ip_reports SET rName=%s, ReportGroup=%s, Investor=%s, ReportStatus=%s WHERE rID=%s",
                       GetSQLValueString($_POST['fname'], "text"),
                       GetSQLValueString($_POST['rgroup'], "int"),
                       GetSQLValueString($_POST['igroup'], "int"),
                       GetSQLValueString($_POST['rStatus'], "text"),
                       GetSQLValueString($_POST['hfID'], "int"));

  mysql_select_db($database_adminConn, $adminConn);
  $Result1 = mysql_query($updateSQL, $adminConn) or die(mysql_error());
  
  $updateGoTo = "reports.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo));
}

mysql_select_db($database_adminConn, $adminConn);
$query_rsIGroup = "SELECT * FROM ip_investors";
$rsIGroup = mysql_query($query_rsIGroup, $adminConn) or die(mysql_error());
$row_rsIGroup = mysql_fetch_assoc($rsIGroup);
$totalRows_rsIGroup = mysql_num_rows($rsIGroup);

$colname_rsReport = "-1";
if (isset($_GET['rid'])) {
  $colname_rsReport = $_GET['rid'];
}
mysql_select_db($database_adminConn, $adminConn);
$query_rsReport = sprintf("SELECT ip_reports.rID, ip_reports.rName, ip_reports.rFile, ip_reports.ReportGroup, ip_reports.Investor FROM ip_reports, ip_reportgroup WHERE rID = %s and (ip_reportgroup.rgID=ip_reports.ReportGroup and ip_reportgroup.rgHide!=1)", GetSQLValueString($colname_rsReport, "int"));
$rsReport = mysql_query($query_rsReport, $adminConn) or die(mysql_error());
$row_rsReport = mysql_fetch_assoc($rsReport);
$totalRows_rsReport = mysql_num_rows($rsReport);
if ($totalRows_rsReport == 0) { //recordset is empty
	header("Location: reports.php" );
}


mysql_select_db($database_adminConn, $adminConn);
$query_rsRGroup = "SELECT * FROM ip_reportgroup where rgHide<>1 ORDER BY ip_reportgroup.rgName";
$rsRGroup = mysql_query($query_rsRGroup, $adminConn) or die(mysql_error());
$row_rsRGroup = mysql_fetch_assoc($rsRGroup);
$totalRows_rsRGroup = mysql_num_rows($rsRGroup);
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
<link href="SpryAssets/SpryValidationTextField.css" rel="stylesheet" type="text/css" />
</head>

<body>
<?php require('rpt_header.php'); ?>
<div id="divPage" class="container" role="main">
<div class="row" id="rpt_layout">
<h2 class="rpt_title">Reports Admin</h3>
<?php require('adminNav.php'); ?>
<div class="generic-content">
<h3>Edit Report</h3>
<form action="<?php echo $editFormAction; ?>" name="frmReport" id="frmReport" method="POST">
<table id="tblForm" border="0" cellpadding="4" cellspacing="1" summary="add new report form">
<tr>
<th><label for="fname">Report Title</label></th>
<td><span id="sprytextfieldReportName">
  <input name="fname" type="text" id="fname" value="<?php echo $row_rsReport['rName']; ?>" size="40" maxlength="45" />
<span class="textfieldRequiredMsg">A value is required.</span></span></td>
</tr>
<tr>
<th>Report File</th>
<td><a href="reports/<?php echo $row_rsReport['rFile']; ?>" target="_blank"> - view document - </a></td>
</tr>
<tr>
  <th> <label for="igroup">Investor/Company</label></th>
  <td><select name="igroup" id="igroup">
    <?php
do {  
?>
    <option value="<?php echo $row_rsIGroup['iID']?>"<?php if (!(strcmp($row_rsIGroup['iID'], $row_rsReport['Investor']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsIGroup['iName']?></option>
    <?php
} while ($row_rsIGroup = mysql_fetch_assoc($rsIGroup));
  $rows = mysql_num_rows($rsIGroup);
  if($rows > 0) {
      mysql_data_seek($rsIGroup, 0);
	  $row_rsIGroup = mysql_fetch_assoc($rsIGroup);
  }
?>
  </select> 
  </td>
</tr>
<tr>
<th><label for="rgroup">Report Group</label></th>
<td><select name="rgroup" id="rgroup">
  <?php
do {  
?>
  <option value="<?php echo $row_rsRGroup['rgID']?>"<?php if (!(strcmp($row_rsRGroup['rgID'], $row_rsReport['ReportGroup']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsRGroup['rgName']?></option>
  <?php
} while ($row_rsRGroup = mysql_fetch_assoc($rsRGroup));
  $rows = mysql_num_rows($rsRGroup);
  if($rows > 0) {
      mysql_data_seek($rsRGroup, 0);
	  $row_rsRGroup = mysql_fetch_assoc($rsRGroup);
  }
?>
</select></td>
</tr>
</table>
<br />
<input name="hfID" type="hidden" id="hfID" value="<?php echo $row_rsReport['rID']; ?>" />
<input name="rStatus" type="hidden" id="rStatus" value="<?php echo $_SESSION['MM_Username']; ?>" />
<input name="btnSubmit" type="submit" id="btnSubmit" class="btn" value="Update Report" />
<input type="hidden" name="MM_update" value="frmReport" />
</form>
</div>
<script type="text/javascript">
<!--
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfieldReportName");
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

mysql_free_result($rsReport);

mysql_free_result($rsRGroup);
?>
