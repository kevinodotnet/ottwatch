<?php

class ScrapeApiController {

	public static function itemVote($itemid) {

		$url = 'http://app05.ottawa.ca/sirepub/item.aspx?itemid=' . $itemid;

		# get HTML and fix it so we can XML it
		$html = file_get_contents($url);
		$html = strip_tags($html,'<td><span>');
		$html = preg_replace('/\r/','',$html);
		$html = preg_replace('/\&nbsp;/',' ',$html);
		$html = preg_replace('/\n/','',$html);
		$html = preg_replace('/  /','',$html);
		$html = preg_replace('/  /','',$html);
		$html = preg_replace('/  /','',$html);
		$html = preg_replace('/  /','',$html);
		$html = preg_replace('/  /','',$html);
		$html = preg_replace('/.*Vote Records<\/td>/','',$html);
		$html = preg_replace('/HideText.*/','',$html);
		$html = preg_replace('/style="[^"]*"/','',$html);
		$html = preg_replace('/<SPAN/','<span',$html);
		$html = preg_replace('/<\/SPAN/','</span',$html);

		$xml = simplexml_load_string($html);
    $tds = $xml->xpath("/td/td");

		$votes = array();
		while (count($tds) > 0) {
			# pull out "TD Pairs" that contain motion text and votes respectively.
			$td = array_shift($tds);
			$td = simplexml_load_string($td->asXML()); 
			$vote['rawmotion'] = $td;
			$td = array_shift($tds);
			$td = simplexml_load_string($td->asXML()); 
			$vote['rawvotes'] = $td;

			# fix up 'rawmotion'
			$text = $vote['rawmotion']->xpath('/td/span/span/text()');
			$text = $text[0].'';
			$vote['motion'] = $text;
			unset($vote['rawmotion']);

			# fix up 'rawvotes'
			$votetds = $vote['rawvotes']->xpath('/td/td');;
			$casts = array();
			#pr($votetds);
			while (count($votetds) > 0) {
				$who = array_shift($votetds);
				$how = array_shift($votetds);
				$who = simplexml_load_string($who->asXML()); 
				$how = simplexml_load_string($how->asXML()); 
				$who = $who[0].'';
				$who = preg_replace('/\([^\)]*\) */','',$who);
				$how = $how[0].'';
				$how = preg_replace('/Yes/','y',$how);
				$how = preg_replace('/No/','n',$how);
				$how = preg_replace('/Absent/','a',$how);
				$casts[$who] = $how;
			}
			unset($vote['rawvotes']);
			$vote['votes'] = $casts;

			$votes[] = $vote;

		}

		#foreach ($votes as $vote) { pr($vote); }

		$result = array();
		$result['itemid'] = $itemid;
		$result['votes'] = $votes;
		return $result;
	}

}
