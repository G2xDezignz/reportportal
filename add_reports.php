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


if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "frmReport")) { 
	//upload file
	if ( $_FILES["file"]["size"] < 15728640 ) { /* max size 15M */
			if ($_FILES["file"]["error"] > 0)
			{
				echo "Return Code: " . $_FILES["file"]["error"] . "<br />";
				exit;
			} else 	{
				$file_name = time()."_".str_replace(" ","",$_FILES["file"]["name"]);
				move_uploaded_file($_FILES["file"]["tmp_name"],
				"reports/" . $file_name);
			}
		} else {
			echo "File cannot be uploaded due to file size; maximum file size is 15MB.";
			exit;
		}

	//save info to database
	if ($_SESSION['MM_UserGroup']=="adm" || $_SESSION['MM_UserGroup']=="amadm") {
	$insertSQL = sprintf("INSERT INTO ip_reports (rName, rFile, ReportGroup, Investor, ReportStatus) VALUES (%s, %s, %s, %s, %s)",
						   GetSQLValueString($_POST['rtitle'], "text"),
						   GetSQLValueString($file_name, "text"),
						   GetSQLValueString($_POST['rgroup'], "int"),
						   GetSQLValueString($_POST['igroup'], "int"),
						   GetSQLValueString($_SESSION['MM_Username'], "text"));
	} else {
	$insertSQL = sprintf("INSERT INTO ip_reports (rName, rFile, ReportGroup, Investor) VALUES (%s, %s, %s, %s)",
						   GetSQLValueString($_POST['rtitle'], "text"),
						   GetSQLValueString($file_name, "text"),
						   GetSQLValueString($_POST['rgroup'], "int"),
						   GetSQLValueString($_POST['igroup'], "int"));
	}

  mysql_select_db($database_adminConn, $adminConn);
  $Result1 = mysql_query($insertSQL, $adminConn) or die(mysql_error());

  $insertGoTo = "reports.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
		
	if ($_SESSION['MM_UserGroup']!="adm" && $_SESSION['MM_UserGroup']!="amadm") { 
		//send pending notifications to AM Admin
		mysql_select_db($database_adminConn, $adminConn);
		$query_rsAMadm = "SELECT eMail FROM ip_users WHERE ip_users.ugID = 'amadm'";
		$rsAMadm = mysql_query($query_rsAMadm, $adminConn) or die(mysql_error());
		$row_rsAMadm = mysql_fetch_assoc($rsAMadm);
		$totalRows_rsAMadm = mysql_num_rows($rsAMadm);
		
		if ($totalRows_rsAMadm > 0) { //show if recordset not empty
			$to = ''; $z=1; //initialize 
			do {
				$to .= $row_rsAMadm['eMail'];
				if ($z < $totalRows_rsAMadm) {
					$to .= ', ';
					++$z;
				}
			} while ($row_rsAMadm = mysql_fetch_assoc($rsAMadm)); 
			mysql_free_result($rsAMadm);

			$headers = 'From: Report Portal <admin@reportportal.com>';
			$subject = 'Report Portal - Pending Report';
			$message = 'An investor report has been uploaded and is pending approval.' . "\r\n";
			$message .= "\r\n";
			$message .= 'Login to the Report Portal website (http://reportportal.com) and go to the Reports Admin section. To approve a report marked \'Pending\', click [Edit] and then [Update Report].';
			mail($to, $subject, $message, $headers);
		}
		  //end email pending notification(s)
	}
  
  header(sprintf("Location: %s", $insertGoTo));
}

mysql_select_db($database_adminConn, $adminConn);
$query_rsIGroup = "SELECT * FROM ip_investors";
$rsIGroup = mysql_query($query_rsIGroup, $adminConn) or die(mysql_error());
$row_rsIGroup = mysql_fetch_assoc($rsIGroup);
$totalRows_rsIGroup = mysql_num_rows($rsIGroup);

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
</head>

<body>
<?php require('rpt_header.php'); ?>
<div id="divPage" class="container" role="main">
<div class="row" id="rpt_layout">
<h2 class="rpt_title">Reports Admin</h3>
<?php require('adminNav.php'); ?>
<div class="generic-content">
<h3>Add New Report</h3>
<form action="<?php echo $editFormAction; ?>" name="frmReport" id="frmReport" method="POST" enctype="multipart/form-data">
<?php 
if ($_SESSION['MM_UserGroup']=='adm' || $_SESSION['MM_UserGroup']=='amadm') {
?>
<p class="note">A <a href="add_investors.php">new Investment Company</a> and/or a <a href="add_category.php">new Report Group</a> may need to be added first, before adding a report.</p>
<?php
} else {
?>
<p class="note">A new <strong>Investment Company</strong> and/or a new <strong>Report Group</strong> may need to be added first, before adding a report. Contact an administrator if applicable.</p>
<?php
}
?>
<table id="tblForm" border="0" cellpadding="4" cellspacing="1" summary="add new user form">
<tr>
<th><label for="rtitle">Report Title</label></th>
<td><input id="rtitle" name="rtitle" type="text" size="40" maxlength="100" /></td>
<tr>
<th><label for="file">Filename</label></th>
<td><input type="file" name="file" id="file" size="27" /> <span class="note">(Max file size @ 15MB)</span></td>
</tr>
<tr>
<th> <label for="igroup">Investor/Company</label></th>
<td><select name="igroup" id="igroup">
  <?php
do {  
?>
  <option value="<?php echo $row_rsIGroup['iID']?>"><?php echo $row_rsIGroup['iName']?></option>
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
  <option value="<?php echo $row_rsRGroup['rgID']?>"><?php echo $row_rsRGroup['rgName']?></option>
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
<input name="btnSubmit" type="submit" id="btnSubmit" class="btn" value="Add New Report" />
<input type="hidden" name="MM_insert" value="frmReport" />
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
mysql_free_result($rsIGroup);

mysql_free_result($rsRGroup);
?>
