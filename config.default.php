<?php

/**
 * Configuration file
 */

  class Config{
   
   function __construct(){
   	 
   	  $docroot=$_SERVER['DOCUMENT_ROOT'];
   	  $serverURL='http://www.bildersee.eu';
   	  $staticServerURL='http://static.bildersee.eu';
   	  
   	  $this->hash=md5(time()); // security
  
	  $this->dbServer='127.0.0.1';
      $this->dbUser='seesite2';
      $this->dbPassword='-----';
      $this->dbBase='seesite';   	  
   	 
   	  $this->enableBrowserCaching=true;
   	  $this->backDoor='-----'; //md5 hash of backdoor password
   	  
   	  $this->local=false;
   	  $this->alwaysLoggedIn=false;
   	  $this->perPage=1000;
   	  $this->localReplacement='http://www.bildersee.eu';
   	  
   	  $this->serverURL=$serverURL;
   	  $this->viewPath=$docroot.'/view';
   	  $this->viewURL=$serverURL.'/view';
   	  $this->logsPath=$docroot.'/logs';
   	  $this->contentPath=$docroot.'/pics';
   	  $this->contentURL=$serverURL.'/pics';
   	  $this->tempPath=$docroot.'/temp';
      $this->uploadPath=$docroot.'/upload_pics';
      $this->cachePath=$docroot.'/cache';
      $this->updateURL=$serverURL.'/upload';
      
      $this->imageGetterURL=$staticServerURL.'/view/getimage.php';
      $this->cacheURL=$staticServerURL.'/cache';
      $this->designURL=$staticServerURL.'/view/design';
      
      $this->pageTitle='bildersee.eu';
      $this->pageDescription='Der Bildersee ist eine nicht-kommerzielle Bildersammlung von Felix Winkelnkemper. Hier werden seine persönlichen Fotos sowie Fotos befreundeter Gruppen und Vereine veröffentlicht.';  
      $this->copyright='&copy;  2015, Felix Winkelnkemper - winkelnkemper@googlemail.com - 0151/40432121';
      
      $this->privacyBig='<h1>Datenschutzerklärung</h1>
      
<h2>Datenschutz</h2>
<p>Nachfolgend möchten wir Sie über unsere Datenschutzerklärung informieren. Sie finden hier Informationen über die Erhebung und Verwendung persönlicher Daten bei der Nutzung unserer Webseite. Wir beachten dabei das für Deutschland geltende Datenschutzrecht. </p>

<p>Wir weisen ausdrücklich darauf hin, dass die Datenübertragung im Internet (z.B. bei der Kommunikation per E-Mail) Sicherheitslücken aufweisen und nicht lückenlos vor dem Zugriff durch Dritte geschützt werden kann.</p>

<p>Die Verwendung der Kontaktdaten unseres Impressums zur gewerblichen Werbung ist ausdrücklich nicht erwünscht, es sei denn wir hatten zuvor unsere schriftliche Einwilligung erteilt oder es besteht bereits eine Geschäftsbeziehung. Der Anbieter und alle auf dieser Website genannten Personen widersprechen hiermit jeder kommerziellen Verwendung und Weitergabe ihrer Daten.</p> 

<h2>Personenbezogene Daten</h2>
<p>Sie können unsere Webseite ohne Angabe personenbezogener Daten besuchen. Für die Betrachtung von Bildern auf bildersee.eu werden keine persönlichen Daten erhoben. Registrierungen für bildersee.eu, bei der sich Nutzer mit Nutzername und Passwort anmelden, werden nur auf persönlicher Basis erteilt und sind dem Betreiber persönlich bekannt. In diesem Fall werden für den Benutzer Name, Nutzername und Passwort gespeichert. Bei einer Deregistrierung als Nutzer dieser Website werden diese Daten gelöscht.</p>

<h2>Aufzeichnung von Daten zur Administraion</h2>
<p>Im Rahmen der normalen Nutzung dieser Seite fallen technische Daten an, die in Log-Dateien zum Zweck der technischen Wartung gespeichert werden. Eine weitergehende Auswertung dieser Daten findet nicht statt. Zugriff auf diese Log-Dateien erfolgt nur im Falle einer Störung des Angebotes.</p>

<p>Diese Datenschutzerklärung wurde unter anderem erstellt mit Hilfe des <a href="http://www.juraforum.de/impressum-generator/">Impressum-Generators von juraforum.de</a></p>
      
      ';
	  $this->legal='<h1>Impressum</h1><p>Der Bildersee ist eine nicht-kommerzielle Bildersammlung von Felix Winkelnkemper. Hier werden seine persönlichen Fotos sowie Fotos befreundeter Gruppen und Vereine veröffentlicht.</p>
	  <p>Verantwortlich für diese Internetseite ist:</p>
	  <p>Felix Winkelnkemper<br>Horner Straße 22<br>33102 Paderborn<br><br>E-Mail: winkelnkemper@googlemail.com<br>Telefon: 0151/40432121</p>
	  
<h2>Haftungsbeschränkung</h2>
<p>Die Inhalte dieser Website werden mit größtmöglicher Sorgfalt erstellt. Der Anbieter übernimmt jedoch keine Gewähr für die Richtigkeit, Vollständigkeit und Aktualität der bereitgestellten Inhalte. Die Nutzung der Inhalte der Website erfolgt auf eigene Gefahr des Nutzers. Mit der reinen Nutzung der Website des Anbieters kommt keinerlei Vertragsverhältnis zwischen dem Nutzer und dem Anbieter zustande.</p> 
	  
<h2>Urheber- und Leistungsschutzrechte</h2>
<p>Die auf dieser Website veröffentlichten Inhalte unterliegen dem deutschen Urheber- und Leistungsschutzrecht. Jede vom deutschen Urheber- und Leistungsschutzrecht nicht zugelassene Verwertung bedarf der vorherigen schriftlichen Zustimmung des Anbieters oder jeweiligen Rechteinhabers. Dies gilt insbesondere für Vervielfältigung, Bearbeitung, Übersetzung, Einspeicherung, Verarbeitung bzw. Wiedergabe von Inhalten in Datenbanken oder anderen elektronischen Medien und Systemen. Inhalte und Rechte Dritter sind dabei als solche gekennzeichnet. Die unerlaubte Vervielfältigung oder Weitergabe einzelner Inhalte oder kompletter Seiten ist nicht gestattet und strafbar. Lediglich die Herstellung von Kopien und Downloads für den persönlichen, privaten und nicht kommerziellen Gebrauch ist erlaubt.</p>

<h2>Datenschutz</h2>
<p>Um die öffentlichen Galerien auf bildersee.eu zu betrachten ist es nicht nötig, persönliche Daten einzugeben. Registrierte Nutzer haben Zugriff auf einen umfangreicheren Bildumfang und auf erweiterte Suchfunktionen. Zugangsberechtigungen hierzu werden nur auf persönlicher Basis gewährt. Weitere Informationen finden sie in der Datenschutzerklärung. <a href="?mode=privacy">Datenschutzerklärung</a></p>

<h2>Recht am eigenen Bild</h2>
<p>Die Bilder auf dieser Website werden nicht von Google oder anderen Suchmaschinen indiziert. Die Veröffentlichung von Bildern, auf denen Personen abgebildet sind, setzt in den meisten Fällen ihre persönliche Zustimmung voraus. Die Fotografen haben diese Zustimmung so weit wie möglich eingeholt. Sollten Sie dennoch ein Bild finden, das nicht öffentlich sichtbar sein soll, senden Sie eine E-Mail an winkelnkemper@googlemail.com. Die Bilder werden so schnell wie möglich gelöscht.</p>


<p>Dieses Impressum wurde unter anderem erstellt mit Hilfe des <a href="http://www.juraforum.de/impressum-generator/">Impressum-Generators von juraforum.de</a></p>';
      
   }
 
} 

 $config=new Config(); 
 $securityHash=$config->hash;

?>
