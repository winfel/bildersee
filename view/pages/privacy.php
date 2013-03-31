<?php

if (!isset($config) || !isset($config->hash) || !isset($securityHash) || $securityHash!=$config->hash) die ('<h1>Forbidden!</h1>');

$pageTitle=translate('data privacy statement',true);
$legalShort='';
$activePart='info';

?>

<div style="width:800px;text-align:left;margin:auto">

<?php

echo $config->privacyBig;

?>

</div>