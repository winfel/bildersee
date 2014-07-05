<?php

$pageTitle=translate('Login');
$activePart='login';

?>

<div style="width:800px;text-align:left;margin:auto">
<h1><?php echo translate('Login');?></h1>

<p><?php echo translate('Registred users have access to further albums and photos. Access is only granted on a personal basis.');?> (<a href="?mode=legal"><?php echo translate('see legal',true);?></a>)</p>
<form method="post" action="?mode=overview">
<table>
<tr><td><?php echo translate('user',true);?>: </td><td><input name="user" size="8" type="text" /></td></tr>
<tr><td><?php echo translate('password',true);?>: </td><td><input name="password" size="8" type="password" /></td></tr>
<tr><td><input value="<?php echo translate('cancel',true);?>" type="button" onclick="hide('login');document.getElementById('button_login').className='';" /></td><td style="text-align:right"><input value="<?php echo translate('login',true);?>" type="submit" /></td></tr>
</table>
</form>