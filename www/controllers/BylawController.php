<?php

class BylawController {

	static public function listAll() {
	  $rows = getDatabase()->all(' select * from bylaw order by bylawnum desc ,meetingid  desc ');

		top3("Bylaws");

		?>
    <table class="table table-bordered table-hover">
		<tr>
		<th style="white-space: nowrap;">Bylaw #</th>
		<th>PDF</th>
		<th>Enacted</th>
		<th>Summary</th>
		</tr>
		<?
		foreach ($rows as $r) {
			?>
			<tr>
			<td style="white-space: nowrap;"><?php print "<a href=\"/bylaws/{$r['bylawnum']}\">".$r['bylawnum']."</a>"; ?></td>
			<td><a target="_blank" href="<?php print $r['url']; ?>">view</a></td>
			<td style="white-space: nowrap;"><?php print $r['enacted']; ?></td>
			<td>
			<?php 
			$summary = $r['summary'];
			preg_match_all('/20\d\d-\d\d+/',$summary,$matches);
			foreach ($matches as $m) {
				foreach ($m as $b) {
					$link = "<a target=\"_blank\" href=\"/bylaws/$b\">$b</a>";
					$summary = preg_replace("/$b/",$link,$summary);
					#print "<b>---$b---</b><br/>";
				}
			}
			print $summary; 
			?>
			</td>
			</tr>
			<?
		}
		?>
		</table>
		<?

		bottom3();

	}

	static public function show($num) {
		top3("By-Law NO. $num");

		?><h1>By-Law No. <?php print $num; ?></h1><?php

		$rows = getDatabase()->all(" 
			select 
				summary,
				left(b.created,10) retrieved,
				url,
				enacted
			from bylaw b
			where 
				bylawnum = :num 
			order by 
				enacted desc
			",array('num'=>$num));

		if (count($rows) == 0) {
			?>
			<p>
			Sorry. OttWatch does not (yet) have an archived copy of this bylaw.
			<p>

			<div class="row">
			<div class="col-sm-4">
			<p>
			Maybe tomorrow!<br/>
			<img src="http://s3.ottwatch.ca/easter/littlesthobo.jpg" class="img-responsive responsive"/>
			<p>
			</div>
			</div>


			<?php
			bottom3();
			return;
		}
		?>

    <table class="table table-bordered table-hover">
		<tr>
		<th>Enacted</th>
		<th>PDF</th>
		<th>Summary</th>
		</tr>
		<?php
		foreach ($rows as $r) {
			?>
			<tr>
			<td style="white-space: nowrap;"><?php print $r['enacted']; ?></td>
			<td><a href="<?php print $r['url']; ?>" target="_blank">view</a>
			<!--<td><a href="/meetings/meeting/<?php print $r['meetid']; ?>" target="_blank"><?php print $r['meetid']; ?></a>-->
			<td><?php print $r['summary']; ?></td>
			</tr>
			<?php
		}
		?>
		</table>
		<a href="/bylaws/" class="btn btn-default">List of all by-laws</a>
		<?php
		bottom3();
	}

}

