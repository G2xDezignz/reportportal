<?php
// *** Logout the current user.
$logoutGoTo = ".";
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
$_SESSION['MM_UserID'] = NULL;
$_SESSION['MM_Username'] = NULL;
$_SESSION['MM_UserGroup'] = NULL;
$_SESSION['MM_Investor'] = NULL;
unset($_SESSION['MM_UserID']);
unset($_SESSION['MM_Username']);
unset($_SESSION['MM_UserGroup']);
unset($_SESSION['MM_Investor']);
session_destroy();

if ($logoutGoTo != "") {header("Location: $logoutGoTo");
exit;
}
?>
