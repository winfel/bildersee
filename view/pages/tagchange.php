<?php

if (!$userIsAdmin) die();

$pageTitle='Tag-Operationen';

$from=$_GET['from'];

echo '<h1>Tag bearbeiten</h1>';

echo '
<form method="get" action="">
<table style="margin:auto;text-align:left">
<input type="hidden" name="from" value="'.$from.'" />
<input type="hidden" name="mode" value="taginfo" />
<tr><th>Alter Tag:</th><td>'.$from.'</td></tr>
<tr><th>Neuer Tag:</th><td><input type="text" size="30" name="to" value="'.$from.'" /></td></tr>
<tr><th></th><td style="text-align:right"><input type="submit" value="Ändern" /></td></tr>
</table>
</form>
';

?>