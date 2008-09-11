<?php
$directory = _("Directory");
$fcc = new featurecode('infoservices', 'directory');
$fcc->setDescription($directory);
$fcc->setDefault('#');
$fcc->update();
unset($fcc);

$calltrace = _("Call Trace");
$fcc = new featurecode('infoservices', 'calltrace');
$fcc->setDescription($calltrace);
$fcc->setDefault('*69');
$fcc->update();
unset($fcc);	

$echotest = _("Echo Test");
$fcc = new featurecode('infoservices', 'echotest');
$fcc->setDescription($echotest);
$fcc->setDefault('*43');
$fcc->update();
unset($fcc);	

$speakingclock = _("Speaking Clock");
$fcc = new featurecode('infoservices', 'speakingclock');
$fcc->setDescription($speakingclock);
$fcc->setDefault('*60');
$fcc->update();
unset($fcc);	

$speakextension = _("Speak Your Exten Number");
$fcc = new featurecode('infoservices', 'speakextennum');
$fcc->setDescription($speakextension);
$fcc->setDefault('*65');
$fcc->update();
unset($fcc);	

?>