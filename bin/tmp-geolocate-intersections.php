<?php

/**
 Wrote this custom to geolocate intersections by name. Keeping it around for 
 reference later, because this will come up again.
 */

$dirname = `dirname $argv[0]`;
$dirname = preg_replace("/\n/","",$dirname);

set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../lib");
set_include_path(get_include_path() . PATH_SEPARATOR . "$dirname/../www");
require_once('include.php');

$stops[] = array('ref'=>57008,'stop'=>'Goulbourn Forced Road and Kanata Rockeries Private ');
$stops[] = array('ref'=>57049,'stop'=>'Kanata Avenue and Huntsville Drive ');
$stops[] = array('ref'=>57061,'stop'=>'Delamere Drive and Crantham Crescent ');
$stops[] = array('ref'=>57098,'stop'=>'Aquaview Drivve and Clermont Crescent East');
$stops[] = array('ref'=>57224,'stop'=>'Otterson Drive and Revelstoke Drive');
$stops[] = array('ref'=>57292,'stop'=>'Sleaford Gate and Claridge Drive');
$stops[] = array('ref'=>57328,'stop'=>'Stable Way South and Equestrian Drive South');
$stops[] = array('ref'=>57329,'stop'=>'Stable Way South and Equestrian Drive South');
$stops[] = array('ref'=>57330,'stop'=>'Doulton Gate and Queensbury Drive');
$stops[] = array('ref'=>57420,'stop'=>'Goldridge Drive and Blackdome Crescent West');
$stops[] = array('ref'=>57422,'stop'=>'Maravista Drive and Cedarview Road');
$stops[] = array('ref'=>57459,'stop'=>'Maravista Drive and Cobble Hill Drive');
$stops[] = array('ref'=>57469,'stop'=>'Navan Road and Milton Road');
$stops[] = array('ref'=>57528,'stop'=>'Clegg Street and Glenora Street');
$stops[] = array('ref'=>57530,'stop'=>'Ruskin Street and Gwynne Avenue');
$stops[] = array('ref'=>57547,'stop'=>'Trim Road and Montmere Avenue');
$stops[] = array('ref'=>57657,'stop'=>'Ogden Street and Cadboro Road');
$stops[] = array('ref'=>57713,'stop'=>'Daniel Avenue and Premier Avenue');
$stops[] = array('ref'=>57802,'stop'=>'Maravista Drive and Cedarview Road');
$stops[] = array('ref'=>57879,'stop'=>'Legget Drive and Terry Fox Drive');
$stops[] = array('ref'=>57894,'stop'=>'Vann Drive and Jay Avenue');
$stops[] = array('ref'=>57930,'stop'=>'Rothwell Drive and Wick Crescent North');
$stops[] = array('ref'=>58017,'stop'=>'Harthill Way and Fraser Fields Way');
$stops[] = array('ref'=>58084,'stop'=>'Fringewood Drive and Woodwind Crescent South');
$stops[] = array('ref'=>58137,'stop'=>'Wessex Road and Alberni Street');
$stops[] = array('ref'=>58187,'stop'=>'Lawrence Street and Bank Street');
$stops[] = array('ref'=>58266,'stop'=>'Queensway Terrace and North Community Association');
$stops[] = array('ref'=>58300,'stop'=>'York Mills Drive North and Princess Louise Drive North');
$stops[] = array('ref'=>58318,'stop'=>'Stonehaven Drive and Tamara Way');
$stops[] = array('ref'=>58368,'stop'=>'Martello Drive and Portobello Boulevard');
$stops[] = array('ref'=>58369,'stop'=>'Frank Kenny Road and Ted Kenny Lane');
$stops[] = array('ref'=>58411,'stop'=>'Irving Avenue and Laurel Street');
$stops[] = array('ref'=>58429,'stop'=>'Wellesley Avenue and Broadview Avenue');
$stops[] = array('ref'=>58433,'stop'=>'Coriolis Crescent and Huntmar Drive');
$stops[] = array('ref'=>58456,'stop'=>'Eight Line Road and Cooper Hill Road');
$stops[] = array('ref'=>58493,'stop'=>'Queensdale Avenue and Bannon Way');
$stops[] = array('ref'=>58546,'stop'=>'Melrose Avenue and Gladstone Avenue');
$stops[] = array('ref'=>58557,'stop'=>'Hincks Lane and Old Riverside Drive');
$stops[] = array('ref'=>58558,'stop'=>'Old Riverside Drive and Frobisher Lane');
$stops[] = array('ref'=>58816,'stop'=>'North Bluff Drive and Fireweed Trail');
$stops[] = array('ref'=>58820,'stop'=>'North Bluff Drive and Dusty Miller Crescent');
$stops[] = array('ref'=>58853,'stop'=>'Longhearth Way and Veranda Crescent South');
$stops[] = array('ref'=>58945,'stop'=>'Harvest Valley Avenue and Esprit Drive');
$stops[] = array('ref'=>58958,'stop'=>'Beaufort Drive and Chimo Drive');
$stops[] = array('ref'=>58964,'stop'=>'Halton Terrace and Flamborough Way');
$stops[] = array('ref'=>58966,'stop'=>'Cope Drive and Northgraves Crescent');
$stops[] = array('ref'=>56877,'stop'=>'Halton Terrace and Flamborough Way');
$stops[] = array('ref'=>56990,'stop'=>'Edith Avenue and Donald Street');
$stops[] = array('ref'=>56991,'stop'=>'Tanglewood Drive and Wareham Street');
$stops[] = array('ref'=>57058,'stop'=>'Maralisa Street and Oldfield Street');
$stops[] = array('ref'=>57141,'stop'=>'Notting Hill Avenue and Vancouver Avenue');
$stops[] = array('ref'=>57193,'stop'=>'Levis Avenue and Begin Street');
$stops[] = array('ref'=>57253,'stop'=>'Sweetnam Drive and Harry Douglas Drive');
$stops[] = array('ref'=>57289,'stop'=>'Felicity Crescent and Fountainhead Drive East');
$stops[] = array('ref'=>57324,'stop'=>'Golfinks Drive and Jockvale Road');
$stops[] = array('ref'=>57389,'stop'=>'Central Park Drive and Bloomingdale Street');
$stops[] = array('ref'=>57567,'stop'=>'Monk Street and Woodlawn Avenue');
$stops[] = array('ref'=>57595,'stop'=>'Acton Street and Avalon Place');
$stops[] = array('ref'=>57606,'stop'=>'Coyote Crescent North and West Ridge Drive');
$stops[] = array('ref'=>57627,'stop'=>'Cobble Hill Drive and Muskan Street');
$stops[] = array('ref'=>57705,'stop'=>'White Alder Avenue and Findlay Creek Drive');
$stops[] = array('ref'=>57715,'stop'=>'Grand Canal Street and Nutgrove Avenue');
$stops[] = array('ref'=>57767,'stop'=>'Data Centre Road and Billings Bridge Access');
$stops[] = array('ref'=>57977,'stop'=>'Keenan Avenue and Knightsbridge Road');
$stops[] = array('ref'=>58067,'stop'=>'Ste. Monique Street and Marquette Avenue');
$stops[] = array('ref'=>58112,'stop'=>'Millbrook Crescent and Hilliard Avenue');
$stops[] = array('ref'=>58181,'stop'=>'Fernbank Road and Shea Road');
$stops[] = array('ref'=>58194,'stop'=>'Varley Drive South and Carr Crescent South');
$stops[] = array('ref'=>58310,'stop'=>'Frederick Place and Cambridge Street');
$stops[] = array('ref'=>58313,'stop'=>'Hill Park High Street and Percifor Way ');
$stops[] = array('ref'=>58495,'stop'=>'Cedarview Road and Highway 416');
$stops[] = array('ref'=>58607,'stop'=>'McClellan Road and Midland Crescent North');
$stops[] = array('ref'=>58676,'stop'=>'Cope Drive and Templeford Avenue');
$stops[] = array('ref'=>58696,'stop'=>'Riviera Drive and Apple Tree Lane');
$stops[] = array('ref'=>58851,'stop'=>'Innes Road and Dunning Avenue');
$stops[] = array('ref'=>5041215,'stop'=>'Crownridge and Meadowbreeze');
$stops[] = array('ref'=>5041257,'stop'=>'Borealis and Den Haag');
$stops[] = array('ref'=>5041551,'stop'=>'Meandering Brook and Hidden Meadow');
$stops[] = array('ref'=>5041552,'stop'=>'Meandering Brook West and Stedman');
$stops[] = array('ref'=>5041757,'stop'=>'Fairpark and Sunvale');
$stops[] = array('ref'=>5041929,'stop'=>'Celtic Ridge and Marconi');
$stops[] = array('ref'=>5043269,'stop'=>'Johnston and Macoun');
$stops[] = array('ref'=>5043288,'stop'=>'Crownrdge and Grassy Plains');
$stops[] = array('ref'=>5043976,'stop'=>'Berrigan and Colindale');
$stops[] = array('ref'=>5045427,'stop'=>'Castlefrank North and Sheldrake North');
$stops[] = array('ref'=>5046634,'stop'=>'Birkett and Tartan');
$stops[] = array('ref'=>5046768,'stop'=>'Fernbank Road and Shea Road');
$stops[] = array('ref'=>5046793,'stop'=>'Paul Metivier and Beatrice');
$stops[] = array('ref'=>5047431,'stop'=>'Murphy Side and Second Line');
$stops[] = array('ref'=>5048137,'stop'=>'Paul Metivier and Glendore');
$stops[] = array('ref'=>5050512,'stop'=>'Hyannis and Natick');
$stops[] = array('ref'=>5051510,'stop'=>'McCarthy and Plante South');
$stops[] = array('ref'=>5052447,'stop'=>'Elmgrove and Winona');
$stops[] = array('ref'=>5052460,'stop'=>'Abott and Talltree West');
$stops[] = array('ref'=>5052528,'stop'=>'Strasbourg and Chinian');
$stops[] = array('ref'=>5052599,'stop'=>'Elizabeth and Osgoode Main');
$stops[] = array('ref'=>5053392,'stop'=>'Cedarview Road and Highway 416');
$stops[] = array('ref'=>5053611,'stop'=>'Uplands and Huntmaster Lane');
$stops[] = array('ref'=>5053613,'stop'=>'Riverside and Walkley');
$stops[] = array('ref'=>5053620,'stop'=>'Uplands and Country Club North');
$stops[] = array('ref'=>5053621,'stop'=>'Bronson and Colonel By Drive');
$stops[] = array('ref'=>5053649,'stop'=>'Woodthrush and Uplands');
$stops[] = array('ref'=>5054044,'stop'=>'Charlotte and Clarence');
$stops[] = array('ref'=>5054054,'stop'=>'Paul Metivier and Marjan');
$stops[] = array('ref'=>5054055,'stop'=>'Renfield and Willowdale');
$stops[] = array('ref'=>5054072,'stop'=>'Steeplechase and Kokanee');
$stops[] = array('ref'=>5055784,'stop'=>'Crownridge and Meadowbreeze');
$stops[] = array('ref'=>5056596,'stop'=>'Cambrian and Seeley\'s Bay');
$stops[] = array('ref'=>5056801,'stop'=>'Cartier and MacLaren');
$stops[] = array('ref'=>5057253,'stop'=>'Golfinks and Calabar');
$stops[] = array('ref'=>5058682,'stop'=>'Spartan Grove and Princiotta');
$stops[] = array('ref'=>5058872,'stop'=>'Bathgate and Leigh South');
$stops[] = array('ref'=>5060818,'stop'=>'Belcourt and Morningview');
$stops[] = array('ref'=>5060853,'stop'=>'Concord and Greenfield');
$stops[] = array('ref'=>5061335,'stop'=>'Avondale and Cole');
$stops[] = array('ref'=>5061336,'stop'=>'Princeton and Broadview');
$stops[] = array('ref'=>5061934,'stop'=>'Woodcliffe and Jeanne D\'Arc');
$stops[] = array('ref'=>5062026,'stop'=>'Goldridge and Insmill');
$stops[] = array('ref'=>5062164,'stop'=>'Princess Louise North and York Mills North');
$stops[] = array('ref'=>5063311,'stop'=>'South Keys and Clearwater');
$stops[] = array('ref'=>5064398,'stop'=>'Waters and Trim');
$stops[] = array('ref'=>5064561,'stop'=>'Legrand North and Wildflower North');
$stops[] = array('ref'=>5064958,'stop'=>'Domalatchy and Adirondack');
$stops[] = array('ref'=>5065700,'stop'=>'Steeplechase and Kokanee');
$stops[] = array('ref'=>5065739,'stop'=>'Kaymar and Quincy');
$stops[] = array('ref'=>5065786,'stop'=>'Strasbourg and Chinian');
$stops[] = array('ref'=>5066507,'stop'=>'Dundonald and Dundonald');
$stops[] = array('ref'=>5066570,'stop'=>'Macoun and Macoun');
$stops[] = array('ref'=>5067467,'stop'=>'Pinnacle and Fieldfair');
$stops[] = array('ref'=>5067940,'stop'=>'Juanita and Langstaff');
$stops[] = array('ref'=>5067958,'stop'=>'Hilliard and Malibu');
$stops[] = array('ref'=>5068463,'stop'=>'Golflinks and Calbar');
$stops[] = array('ref'=>5069731,'stop'=>'East Acres and Shefford');
$stops[] = array('ref'=>5070188,'stop'=>'Crownridge and Meadowbreeze');
$stops[] = array('ref'=>5070345,'stop'=>'Catterick and Shirley\'s Brook South');
$stops[] = array('ref'=>5071835,'stop'=>'Meadowlands and Sullivan');
$stops[] = array('ref'=>5071856,'stop'=>'Ramsayville and Ridge');
$stops[] = array('ref'=>5071857,'stop'=>'Southmore and Thorndale');
$stops[] = array('ref'=>5072324,'stop'=>'Burnbank and Grenfell');
$stops[] = array('ref'=>5072355,'stop'=>'May and McArthur');
$stops[] = array('ref'=>5072375,'stop'=>'Brambling and River Mist');
$stops[] = array('ref'=>5072495,'stop'=>'Gracewood and Creekview East');
$stops[] = array('ref'=>5073303,'stop'=>'Apple Hill and Crystal Ridge');
$stops[] = array('ref'=>5042862,'stop'=>'Maravista and Cobble Hill');
$stops[] = array('ref'=>5043497,'stop'=>'Cedarview and 416 Westhunt');
$stops[] = array('ref'=>5044152,'stop'=>'Joshua and Saddleridge');
$stops[] = array('ref'=>5045196,'stop'=>'Hidden Lake and Carp Highlands');
$stops[] = array('ref'=>5045303,'stop'=>'Esprit and Harvest Valley');
$stops[] = array('ref'=>5046330,'stop'=>'Strasbourg and Chinian');
$stops[] = array('ref'=>5046570,'stop'=>'Strasbourg and Chinian');
$stops[] = array('ref'=>5046922,'stop'=>'Reynolds and Warden');
$stops[] = array('ref'=>5047170,'stop'=>'Assaly and Regina');
$stops[] = array('ref'=>5047287,'stop'=>'Bridle Path and Hunter\'s Point West');
$stops[] = array('ref'=>5047430,'stop'=>'Diamondview and Donald B Munro');
$stops[] = array('ref'=>5047806,'stop'=>'Provost and Thorndale');
$stops[] = array('ref'=>5047845,'stop'=>'Benjamin and Fairlawn');
$stops[] = array('ref'=>5050215,'stop'=>'Harmer and Kenora');
$stops[] = array('ref'=>5050348,'stop'=>'Strasbourg and Chinian');
$stops[] = array('ref'=>5050351,'stop'=>'Grammond and Grammond');
$stops[] = array('ref'=>5050412,'stop'=>'Prestone and Mountainside South');
$stops[] = array('ref'=>5053289,'stop'=>'Wild Iris and Stedman');
$stops[] = array('ref'=>5053759,'stop'=>'Esprit and Harvest Valley');
$stops[] = array('ref'=>5054692,'stop'=>'Travis and Percifor West');
$stops[] = array('ref'=>5054908,'stop'=>'Cartier and Maclaren');
$stops[] = array('ref'=>5055180,'stop'=>'Crownridge and Meadowbreeze');
$stops[] = array('ref'=>5056306,'stop'=>'Kilmory and Tiverton');
$stops[] = array('ref'=>5056619,'stop'=>'Owl and Pigeon');
$stops[] = array('ref'=>5057015,'stop'=>'Castlefrank and Hungerford');
$stops[] = array('ref'=>5057622,'stop'=>'Goulburn and Wiggins');
$stops[] = array('ref'=>5057653,'stop'=>'Dorchester and Silver');
$stops[] = array('ref'=>5058438,'stop'=>'Craig and Newton');
$stops[] = array('ref'=>5058529,'stop'=>'Ross and Spencer');
$stops[] = array('ref'=>5060706,'stop'=>'Spartina and Golden Sedge');
$stops[] = array('ref'=>5060785,'stop'=>'Cambrian and Regatta');
$stops[] = array('ref'=>5062045,'stop'=>'Newbury and Tiverton');
$stops[] = array('ref'=>5063321,'stop'=>'Dunrobin and Kilmaurs Side');
$stops[] = array('ref'=>5063335,'stop'=>'Beausoleil and Murray');
$stops[] = array('ref'=>5064683,'stop'=>'Diamondview and Donald B Munro');
$stops[] = array('ref'=>5064719,'stop'=>'Beaudelaire and Harvest Valley');
$stops[] = array('ref'=>5065118,'stop'=>'Richard and Van Vliet');
$stops[] = array('ref'=>5065140,'stop'=>'Cummings and Wilson');
$stops[] = array('ref'=>5065337,'stop'=>'Hearst and Whitney');
$stops[] = array('ref'=>5065455,'stop'=>'Drumheller and Huntsville');
$stops[] = array('ref'=>5065470,'stop'=>'Second Line and Forestbrook');
$stops[] = array('ref'=>5067330,'stop'=>'Birchgrove and Colonial');
$stops[] = array('ref'=>5068396,'stop'=>'Malvern and Tripp');
$stops[] = array('ref'=>5068654,'stop'=>'Mountshannon and Woodford');
$stops[] = array('ref'=>5068732,'stop'=>'Cope');
$stops[] = array('ref'=>5068750,'stop'=>'Crestway and Oldfield');
$stops[] = array('ref'=>5069055,'stop'=>'Ambassador and City Park');
$stops[] = array('ref'=>5069714,'stop'=>'Logan Farm and Lombardy');
$stops[] = array('ref'=>5069859,'stop'=>'Flamborough and Halton North');
$stops[] = array('ref'=>5070050,'stop'=>'Harvest Valley and Glastonbury Walk');
$stops[] = array('ref'=>5070900,'stop'=>'Empire Grove and Courtland Grove West');
$stops[] = array('ref'=>5070958,'stop'=>'Frank Nighbor and Silver Seven');
$stops[] = array('ref'=>5071231,'stop'=>'Cope');
$stops[] = array('ref'=>5072380,'stop'=>'Iona and Piccadilly');
$stops[] = array('ref'=>5072381,'stop'=>'Geneva and Piccadilly');
$stops[] = array('ref'=>5072383,'stop'=>'Faraday and Mayfair');
$stops[] = array('ref'=>5072691,'stop'=>'Clarendon and Edina');
$stops[] = array('ref'=>5072692,'stop'=>'Clarendon and Geneva');
$stops[] = array('ref'=>5072693,'stop'=>'Clarendon and Helena ');
$stops[] = array('ref'=>5072694,'stop'=>'Clarendon and Kenora');
$stops[] = array('ref'=>5072700,'stop'=>'Clarendon and Java');
$stops[] = array('ref'=>5072701,'stop'=>'Kenora and Mayfair');
$stops[] = array('ref'=>5073323,'stop'=>'Solstice and Soleil');
$stops[] = array('ref'=>5070695,'stop'=>'Gwynne and Laurentian');
$stops[] = array('ref'=>5070697,'stop'=>'Hutchison and Reid');
$stops[] = array('ref'=>5072382,'stop'=>'Helena and Mayfair');
$stops[] = array('ref'=>5072482,'stop'=>'Mariposa and Sylvan');

#foreach ($stops as $s) { print "$s\n"; } exit;

$skips = array('west','hill','lane','south','way','north','crescent','street','road','avenue','drive');

$matches = array();

$index = 0;

foreach ($stops as $stop) {
	$s = $stop['stop'];
	print "matches:".count($matches)." ".($index++)." of ".count($stops)." $s\n";
	$roads = explode(" and ",$s);
	if (count($roads) != 2) {
		print "SKIPPING: part count is bad: $s\n";
		continue;
	}
	$r1In = '';
	foreach (explode(' ',$roads[0]) as $r) {
		if ($r == '') { continue; }
		if (in_array(strtolower($r),$skips)) { continue; }
		$r = mysql_real_escape_string($r);
		$r1In .= " r1.rd_name like '%$r%' or \n";
	}
	$r2In = '';
	foreach (explode(' ',$roads[1]) as $r) {
		if ($r == '') { continue; }
		if (in_array(strtolower($r),$skips)) { continue; }
		$r = mysql_real_escape_string($r);
		$r2In .= " r2.rd_name like '%$r%' or \n";
	}
	$sql = "
		select 
			astext(r1.shape) l1,
			astext(r2.shape) l2,
			st_distance(r1.shape,r2.shape),
			concat(r1.left_from,' to ',r1.left_to,' ',r1.rd_name,' ',r1.rd_suffix) r1name,
			concat(r2.left_from,' to ',r2.left_to,' ',r2.rd_name,' ',r2.rd_suffix) r2name
		from roadways r1
			join roadways r2
		where 
			($r1In 1=2)
			and 
			($r2In 1=2)
		order by st_distance(r1.shape,r2.shape)
	";
	#print "$sql\n";
	$rows = getDatabase()->all($sql);
	$perfect = 0;
	foreach ($rows as $r) {
		if ($perfect == 1) {
			break;
		}
		$l1 = pointsFromLineString($r['l1']);
		$l2 = pointsFromLineString($r['l2']);
		foreach ($l1 as $p1) {
			foreach ($l2 as $p2) {
				if ($p1['lat'] == $p2['lat']) {
					if ($p1['lon'] == $p2['lon']) {
						print "MATCH|{$stop['ref']}|{$p1['lat']}|{$p1['lon']}|$s\n";
						$matches[] = $r;
						$perfect = 1;
					}
				}
			}
		}
	}
}

function pointsFromLineString($l) {
	$l = preg_replace('/^LINESTRING\(/','',$l);
	$l = preg_replace('/\)/','',$l);
	$parts = explode(',',$l);
	$points = array();
	foreach ($parts as $p) {
		$pp = explode(' ',$p);
		$point = array();
		$point['lat'] = $pp[1];
		$point['lon'] = $pp[0];
		$points[] = $point;
	}
	return $points;
}

?>

