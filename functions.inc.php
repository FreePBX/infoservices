<?php

function infoservices_get_config($engine) {
	$modulename = 'infoservices';
	
	// This generates the dialplan
	global $ext;  
	switch($engine) {
		case "asterisk":
			if (is_array($featurelist = featurecodes_getModuleFeatures($modulename))) {
				foreach($featurelist as $item) {
					$featurename = $item['featurename'];
					$fname = $modulename.'_'.$featurename;
					if (function_exists($fname)) {
						$fcc = new featurecode($modulename, $featurename);
						$fc = $fcc->getCodeActive();
						unset($fcc);
						
						if ($fc != '')
							$fname($fc);
					} else {
						$ext->add('from-internal-additional', 'debug', '', new ext_noop($modulename.": No func $fname"));
						var_dump($item);
					}	
				}
			}
		break;
	}
}

function infoservices_directory($c) {
	global $ext;
	global $db;

	$oxtn = $db->getOne("SELECT value from globals where variable='OPERATOR_XTN'");	//this needs to be here!

	$id = "app-directory"; // The context to be included. This must be unique.

	// Start creating the dialplan
	$ext->addInclude('from-internal-additional', $id); // Add the include from from-internal
	// Build the context
	$ext->add($id, $c, '', new ext_answer(''));
	$ext->add($id, $c, '', new ext_wait('1')); // $cmd,1,Wait(1)
	$ext->add($id, $c, '', new ext_agi('directory,${DIR-CONTEXT},from-did-direct,${DIRECTORY:0:1}${DIRECTORY_OPTS}'.($oxtn != '' ? 'o' : '') ));
	$ext->add($id, $c, '', new ext_playback('vm-goodbye')); // $cmd,n,Playback(vm-goodbye)
	$ext->add($id, $c, '', new ext_hangup('')); // hangup
	if ($oxtn != '') {
		$ext->add($id, 'o', '', new ext_goto('from-internal,${OPERATOR_XTN},1')); 
	} else {
		$ext->add($id, 'o', '', new ext_playback('privacy-incorrect'));
	}
}

function infoservices_calltrace($c) {
	global $ext;

	$id = "app-calltrace"; // The context to be included

	$ext->addInclude('from-internal-additional', $id); // Add the include from from-internal

	$ext->add($id, $c, '', new ext_goto('1', 's', 'app-calltrace-perform')); 

	// Create the calltrace application, which we are doing a 'Goto' to above.
	// I just reset these for ease of copying and pasting. 
	$id = 'app-calltrace-perform';
	$c = 's';
	$ext->add($id, $c, '', new ext_answer(''));
	$ext->add($id, $c, '', new ext_wait('1'));
	$ext->add($id, $c, '', new ext_macro('user-callerid')); 
	$ext->add($id, $c, '', new ext_playback('info-about-last-call&telephone-number'));
	$ext->add($id, $c, '', new ext_setvar('lastcaller', '${DB(CALLTRACE/${AMPUSER})}'));
	$ext->add($id, $c, '', new ext_gotoif('$[ $[ "${lastcaller}" = "" ] | $[ "${lastcaller}" = "unknown" ] ]', 'noinfo'));
	$ext->add($id, $c, '', new ext_saydigits('${lastcaller}'));
	$ext->add($id, $c, '', new ext_setvar('TIMEOUT(digit)', '3'));
	$ext->add($id, $c, '', new ext_setvar('TIMEOUT(response)', '7'));
	$ext->add($id, $c, '', new ext_background('to-call-this-number&press-1'));
	$ext->add($id, $c, '', new ext_goto('fin'));
	$ext->add($id, $c, 'noinfo', new ext_playback('from-unknown-caller'));
	$ext->add($id, $c, '', new ext_macro('hangupcall')); 
	$ext->add($id, $c, 'fin', new ext_noop('Waiting for input'));
	$ext->add($id, $c, '', new ext_waitexten(60));
	$ext->add($id, $c, '', new ext_Playback('sorry-youre-having-problems&goodbye'));
	$ext->add($id, '1', '', new ext_goto('1', '${lastcaller}', 'from-internal'));
	$ext->add($id, 'i', '', new ext_playback('vm-goodbye')); 
	$ext->add($id, 'i', '', new ext_macro('hangupcall')); 
	$ext->add($id, 't', '', new ext_playback('vm-goodbye')); 
	$ext->add($id, 't', '', new ext_macro('hangupcall')); 

}

function infoservices_echotest($c) {
	global $ext;

	$id = "app-echo-test"; // The context to be included

	$ext->addInclude('from-internal-additional', $id); // Add the include from from-internal

	$ext->add($id, $c, '', new ext_answer('')); // $cmd,1,Answer
	$ext->add($id, $c, '', new ext_wait('1')); // $cmd,n,Wait(1)
	$ext->add($id, $c, '', new ext_playback('demo-echotest')); // $cmd,n,Macro(user-callerid)
	$ext->add($id, $c, '', new ext_echo('')); 
	$ext->add($id, $c, '', new ext_playback('demo-echodone')); // $cmd,n,Playback(...)
	$ext->add($id, $c, '', new ext_hangup('')); // $cmd,n,Macro(user-callerid)
}

function infoservices_speakingclock($c) {
	global $ext;

	$id = "app-speakingclock"; // The context to be included

	$ext->addInclude('from-internal-additional', $id); // Add the include from from-internal

	$ext->add($id, $c, '', new ext_answer('')); // $cmd,1,Answer
	$ext->add($id, $c, '', new ext_wait('1')); // $cmd,n,Wait(1)
	$ext->add($id, $c, '', new ext_setvar('NumLoops','0'));
	
	$ext->add($id, $c, 'start', new ext_setvar('FutureTime','$[${EPOCH} + 11]'));  // 10 seconds to try this out
	$ext->add($id, $c, '', new ext_playback('at-tone-time-exactly'));
	$ext->add($id, $c, '', new ext_gotoif('$["${TIMEFORMAT}" = "kM"]','hr24format'));
	$ext->add($id, $c, '', new ext_sayunixtime('${FutureTime},,IM \\\'and\\\' S \\\'seconds\\\' p'));
	$ext->add($id, $c, '', new ext_goto('waitloop'));
	$ext->add($id, $c, 'hr24format', new ext_sayunixtime('${FutureTime},,kM \\\'and\\\' S \\\'seconds\\\''));
	$ext->add($id, $c, 'waitloop', new ext_set('TimeLeft', '$[${FutureTime} - ${EPOCH}]'));
	$ext->add($id, $c, '', new ext_gotoif('$[${TimeLeft} < 1]','playbeep'));
	//$ext->add($id, $c, '', new ext_saynumber('${TimeLeft}'));
	$ext->add($id, $c, '', new ext_wait(1));
	$ext->add($id, $c, '', new ext_goto('waitloop'));
	$ext->add($id, $c, 'playbeep', new ext_playback('beep'));
	$ext->add($id, $c, '', new ext_wait(5));
	
	$ext->add($id, $c, '', new ext_setvar('NumLoops','$[${NumLoops} + 1]'));
	$ext->add($id, $c, '', new ext_gotoif('$[${NumLoops} < 5]','start')); // 5 is maximum number of times to repeat
	$ext->add($id, $c, '', new ext_playback('goodbye'));
	$ext->add($id, $c, '', new ext_hangup(''));
}

function infoservices_speakextennum($c) {
	global $ext;
	
	$id = "app-speakextennum";
	
	$ext->addInclude('from-internal-additional', $id); // Add the include from from-internal

	$ext->add($id, $c, '', new ext_answer('')); // $cmd,1,Answer
	$ext->add($id, $c, '', new ext_wait('1')); // $cmd,n,Wait(1)
	$ext->add($id, $c, '', new ext_macro('user-callerid')); // $cmd,n,Macro(user-callerid)
	$ext->add($id, $c, '', new ext_playback('your'));
	$ext->add($id, $c, '', new ext_playback('extension'));
	$ext->add($id, $c, '', new ext_playback('number'));
	$ext->add($id, $c, '', new ext_playback('is'));
	$ext->add($id, $c, '', new ext_saydigits('${AMPUSER}'));
	$ext->add($id, $c, '', new ext_wait('2')); // $cmd,n,Wait(1)
	$ext->add($id, $c, '', new ext_hangup(''));
}
?>
