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

if ( isset($_REQUEST['iid']) ) {
	$vIID = $_REQUEST['iid'];
} else {
	$vIID = $_SESSION['mm_Investor'];
}
 
mysql_select_db($database_adminConn, $adminConn);
$query_rsReports = "SELECT ip_investors.iName, ip_reportgroup.rgName, ip_reports.rID, ip_reports.rName, ip_reports.rDateTimeStamp FROM ip_investors, ip_reportgroup, ip_reports WHERE ip_investors.iID=ip_reports.Investor and ip_reportgroup.rgID=ip_reports.ReportGroup and ip_reports.ReportStatus IS NOT NULL and ip_investors.iID=".$vIID." ORDER BY ip_reportgroup.rgName, ip_reports.rName";
$rsReports = mysql_query($query_rsReports, $adminConn) or die(mysql_error());
$row_rsReports = mysql_fetch_assoc($rsReports);
$totalRows_rsReports = mysql_num_rows($rsReports);
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
<h2 class="rpt_title">Investor Reports </h2>
<?php 
if ($_SESSION['MM_UserGroup']=='adm' || $_SESSION['MM_UserGroup']=='amadm' || $_SESSION['MM_UserGroup']=='astmgr' || $_SESSION['MM_UserGroup']=='acct') {
	require('adminNav.php'); 
} elseif ($_SESSION['MM_UserGroup']=='exec') {
	echo('<div id="adminNav"><p><a href="allreports.php">All Investors</a></p></div>');
} else {
    echo("<br />");
}
?>
<div class="generic-content">
  <div class="iReport">
<?php 
if ($totalRows_rsReports==0) { //no reports returned
	mysql_select_db($database_adminConn, $adminConn);
	$query_rsInvestor = "SELECT iName FROM ip_investors WHERE iID=".$vIID;
	$rsInvestor = mysql_query($query_rsInvestor, $adminConn) or die(mysql_error());
	$row_rsInvestor = mysql_fetch_assoc($rsInvestor);
	$totalRows_rsInvestor = mysql_num_rows($rsInvestor);
?>
<h2><?php echo $row_rsInvestor['iName']; ?></h2>
<br />
<p>No investor reports are currently available.</p>
<div style="height:75px" />
<?php
mysql_free_result($rsInvestor);
?>
<?php } else { ?>
<h2><?php echo $row_rsReports['iName']; ?></h2>
<br />
<div class="subrpt">
<table border="0" cellpadding="1" cellspacing="1" summary="report list">
  <tr>
  <th>Report Type</th>
  <th>Report Name</th>
  </tr>
  <?php do { ?>
    <tr>
      <td class="t1"><?php echo $row_rsReports['rgName']; ?></td>
      <td class="t2"><a href="file.php?ref=<?php echo $row_rsReports['rName']; ?>&id=<?php echo $row_rsReports['rID']; ?>"><?php echo $row_rsReports['rName']; ?></a></td>
    </tr>
    <?php } while ($row_rsReports = mysql_fetch_assoc($rsReports)); ?>
</table>
<?php } //end check for reports ?>
  </div>
</div>
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
mysql_free_result($rsReports);
?>
