<?php

if (!isset($config) || !isset($config->hash) || !isset($securityHash) || $securityHash!=$config->hash) die ('<h1>Forbidden!</h1>');

if (!$userIsAdmin) die();

$pageTitle='Administration';

echo '<h1>Administration - Overview</h1>';

echo '
<div style="width:700px;text-align:left;margin:auto">
<ul>
<li><a href="?mode=logs">View logs</a></li>
<li><a href="?mode=taginfo">Edit tags</a></li>
<li>Clean up cache</li>
</ul>
</div>
';

?>