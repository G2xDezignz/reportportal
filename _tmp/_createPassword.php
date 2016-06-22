<?php
// Original PHP code by Chirp Internet: www.chirp.com.au
// Please acknowledge use of this code by including this header.
function better_crypt($input, $rounds = 7)
{
$salt = "";
$salt_chars = array_merge(range('A','Z'), range('a','z'), range(0,9));
for($i=0; $i < 22; $i++) {
  $salt .= $salt_chars[array_rand($salt_chars)];
}
return crypt($input, sprintf('$2y$%02d$', $rounds) . $salt);
}

// create password?
if ( isset($_POST['uPassword']) ) { // check if form submited
	$ePassword = better_crypt($_POST['uPassword']);
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Create Password</title>
<link href="/css/bootstrap.css" rel="stylesheet" type="text/css">
<link href="/css/default.css" rel="stylesheet" type="text/css">
<style type="text/css">
form p { padding-left:10px; }
</style>
</head>

<body>
<div class="container-fluid">
<h1>Create an Encrypted Password</h1>
<form method="post" id="pswdInput">
<fieldset><legend>Enter Password</legend>
<p><input name="uPassword" type="text" id="uPassword" placeholder="Password" required> <input name="submit" type="submit" id="submit" title="Create an encrypted password" value="Create"></p>
<p><?php echo($ePassword) ?></p>
</fieldset>
</form>
</div>
</body>
</html>