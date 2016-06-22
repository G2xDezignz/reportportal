<div id="logout" class="container">
<div class="row">
[ <strong><?php echo $_SESSION['MM_Username']; ?></strong> 
<?php if ($_SESSION['MM_Username']<>'demo') { ?>
&nbsp;|&nbsp; <a href="edit_user_password.php">edit password</a> 
<?php } ?>
&nbsp;|&nbsp; <a href="login.php">logout</a> ]
</div>
</div>