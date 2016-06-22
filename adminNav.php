<div id="adminNav">
<p><a href="main.php">Home</a> 
<?php 
	if ($_SESSION['MM_UserGroup']=='adm' || $_SESSION['MM_UserGroup']=='amadm' || $_SESSION['MM_UserGroup']=='astmgr' || $_SESSION['MM_UserGroup']=='acct') { 
?> 
 | <a href="users.php">User Admin</a>
<?php } ?> 
<?php 
	if ($_SESSION['MM_UserGroup']=='adm' || $_SESSION['MM_UserGroup']=='amadm') { 
?> 
 | <a href="investors.php">Investor Admin</a>
<?php } ?> 
<?php 
	if ($_SESSION['MM_UserGroup']=='adm' || $_SESSION['MM_UserGroup']=='amadm') { 
?> 
 | <a href="categories.php">Report Group Admin</a>
<?php } ?> 
<?php 
	if ($_SESSION['MM_UserGroup']=='adm' || $_SESSION['MM_UserGroup']=='amadm' || $_SESSION['MM_UserGroup']=='astmgr') { 
?> 
 | <a href="reports.php">Reports Admin</a>
 <?php } ?>
 </p>
</div>
