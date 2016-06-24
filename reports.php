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
$MM_authorizedUsers = "adm,amadm,astmgr,acct,exec";
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

$btn2 = 1;
mysql_select_db($database_adminConn, $adminConn);
$query_rsInvestors = "SELECT * FROM ip_investors WHERE ip_investors.iID!=6 ORDER BY ip_investors.iName";
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
#tblList td.t4 { text-align:left; }
-->
</style>
</head>

<body>
<?php require('rpt_header.php'); ?>
<div id="divPage" class="container" role="main">
<div class="row" id="rpt_layout">
<h2 class="rpt_title">Reports Admin</h2>
<?php 
if ($_SESSION['MM_UserGroup']=='adm' || $_SESSION['MM_UserGroup']=='amadm' || $_SESSION['MM_UserGroup']=="astmgr" || $_SESSION['MM_UserGroup']=="acct") {
	require('adminNav.php'); 
} else {
    echo("<br />");
}
?>
<div class="generic-content">
<div style="color:#900;font-style:oblique">
<?php
echo($_SESSION['umsg']);

unset($_SESSION['umsg']);
unset($_SESSION['uinfo']);
?>
</div>
<div class="iReport">
<p><a href="add_reports.php" class="btn">Add New Report</a></p>
<?php
do {
	mysql_select_db($database_adminConn, $adminConn);
$query_rsReports = "SELECT ip_investors.iName, ip_reportgroup.rgName, ip_reports.rName, ip_reports.rFile, ip_reports.rDateTimeStamp,  ip_reports.rID, ip_reports.ReportStatus, ip_reportgroup.rgHide FROM ip_reports, ip_reportgroup, ip_investors WHERE ip_reports.ReportGroup=ip_reportgroup.rgID and ip_reports.Investor=ip_investors.iID and ip_reports.Investor=".$row_rsInvestors['iID']." ORDER BY iName, rgName, rDateTimeStamp DESC";
$rsReports = mysql_query($query_rsReports, $adminConn) or die(mysql_error());
$row_rsReports = mysql_fetch_assoc($rsReports);
$totalRows_rsReports = mysql_num_rows($rsReports);
?>
<?php /*if ($totalRows_rsInvestors > 0) { // Show if recordset not empty*/ ?>
<?php if ($row_rsInvestors['iName']==$row_rsReports['iName']) { // Show if recordset not empty ?>
  <table id="tblList" border="0" cellspacing="1" cellpadding="4" summary="report list">
    <caption>
      <?php echo $row_rsReports['iName']; ?>
      </caption>
    <?php do { ?>
      <tr>
        <td class="t1"><?php echo $row_rsReports['rgName']; ?></td>
        <td class="t2"><a href="reports/<?php echo $row_rsReports['rFile']; ?>" target="_blank"><?php echo $row_rsReports['rName']; ?></a><br /><span>(<?php echo $row_rsReports['rDateTimeStamp']; ?>)</span></td>
        <td class="t3"><?php if ($row_rsReports['ReportStatus']==NULL) { echo('Pending'); } ?></td>
        <td class="t4">
        <?php if ($_SESSION['MM_UserGroup']=="adm" || $_SESSION['MM_UserGroup']=="amadm") { ?>
        <?php if ($row_rsReports['rgHide']!=1) { ?><a href="edit_reports.php?rid=<?php echo $row_rsReports['rID']; ?>">Edit</a> | <?php } ?><a href="delete_reports.php?rid=<?php echo $row_rsReports['rID']; ?>">Delete</a>
        <?php } else { echo("&nbsp;"); } ?>
        </td>
      </tr>
      <?php } while ($row_rsReports = mysql_fetch_assoc($rsReports)); ?>
  </table>
  <?php mysql_free_result($rsReports); ?>
  <?php $btn2 += $btn2; ?>
  <br />
  <?php } // Show if recordset not empty ?>
  <?php } while ($row_rsInvestors = mysql_fetch_assoc($rsInvestors)); ?>
</div>
<?php if ($btn2 > 1) { // Show if recordset not empty ?>
<p><a href="add_reports.php" class="btn">Add New Report</a></p>
<?php } //end recordset ?>
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
mysql_free_result($rsInvestors);
?>
