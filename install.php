<?php
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }
//for translation only
if (false) {
_("Info Services");
_("Directory");
_("Call Trace");
_("Echo Test");
_("Speaking Clock");
_("Speak Your Exten Number");
}

$fcc = new featurecode('infoservices', 'directory');
$fcc->setDescription('Directory');
$fcc->setDefault('#');
$fcc->setProvideDest();
$fcc->update();
unset($fcc);

$fcc = new featurecode('infoservices', 'calltrace');
$fcc->setDescription('Call Trace');
$fcc->setDefault('*69');
$fcc->update();
unset($fcc);	

$fcc = new featurecode('infoservices', 'echotest');
$fcc->setDescription('Echo Test');
$fcc->setDefault('*43');
$fcc->setProvideDest();
$fcc->update();
unset($fcc);	

$fcc = new featurecode('infoservices', 'speakingclock');
$fcc->setDescription('Speaking Clock');
$fcc->setDefault('*60');
$fcc->setProvideDest();
$fcc->update();
unset($fcc);	

$fcc = new featurecode('infoservices', 'speakextennum');
$fcc->setDescription('Speak Your Exten Number');
$fcc->setDefault('*65');
$fcc->update();
unset($fcc);

?>
