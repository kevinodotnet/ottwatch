<?php

error_reporting(E_ALL);

while ($l = fgets(STDIN)) {
	trim($l);

	# [Mon Oct 06 07:02:28 2014] [error] [client 209.5.124.194] traffic raw: {"ObjectType": "INCIDENT", "ObjectAction": "DELETE", "ObjectData": {"descriptionFrench":"Nicholas \xe0 Laurier","twitterMessage":"Nicholas N/B reduced to 3 lanes at Laurier - disabled vehicle. Duration unknown. Expect delays. #otttraffic","twitterMessageFrench":"Nicholas d/n r\xe9duit \xe0 3 voies \xe0 Laurier - v\xe9hicule en panne. Dur\xe9e inconnue. Pr\xe9voir d\xe9lais. #ottcircule","message":"Nicholas northbound reduced to 3 lanes at Laurier due to a disabled vehicle. Duration is unknown. Expect delays.","messageFrench":"Nicholas direction nord r\xe9duit \xe0 3 voies \xe0 Laurier en raison d\\u0027un v\xe9hicule en panne. Dur\xe9e est inconnue. Pr\xe9voir d\xe9lais.","id":3049,"latitude":45.423195,"longitude":-75.687346,"description":"Nicholas at Laurier"}}


	$l = preg_replace('/\\\\x[0-9a-e][0-9a-e]/','?',$l);
	#print "\n\n$l\n\n";
	$matches = array();
	if (!preg_match('/^.....(...) (\d\d) (\d\d:\d\d:\d\d) (201\d).*traffic raw: (.*NEW.*)/',$l,$matches)) {
		continue;
		#print "\n\n$l\n\n";
		#print "Failed to match\n";
		#exit;
	}

	#print "MATCHES\n\n";
	#print_r($matches);
	$json = $matches[5];
	$d = json_decode(utf8_decode($json));

	$date = $matches[4].'-'.$matches[1].'-'.$matches[2];

	print "$date";
	print "\t".$d->ObjectData->latitude;
	print "\t".$d->ObjectData->longitude;
	print "\t".$d->ObjectData->message;
	print "\n";

}
