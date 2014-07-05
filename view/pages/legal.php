<?php
if (!isset($config) || !isset($config->hash) || !isset($securityHash) || $securityHash!=$config->hash) die ('<h1>Forbidden!</h1>');


$pageTitle=translate('Legal Information | About us');
$legalShort='';
$activePart='info';

?>

<div style="width:800px;text-align:left;margin:auto">

	<?php
	
	echo translate('This section is demanded by German laws. To avoid misinterpretations by improper use of language, the following information is only available in German.');
	
	echo $config->legal;

	?>

</div>