<?php

class BylawController {

	static public function listAll() {
	  $rows = getDatabase()->all(' select * from bylaw order by bylawnum desc ,meetingid  desc ');

		top3("Bylaws");

		?>
    <table class="table table-bordered table-hover">
		<tr>
		<th style="white-space: nowrap;">Bylaw #</th>
		<th>Summary</th>
		</tr>
		<?
		foreach ($rows as $r) {
			?>
			<tr>
			<td style="white-space: nowrap;"><?php 
				print "<a href=\"/bylaws/{$r['bylawnum']}\">".$r['bylawnum']."</a>";
			?></td>
			<td><?php print $r['summary']; ?></td>
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
		$rows = getDatabase()->all(" 
			select 
				m.id,
				m.meetid,
				summary,
				left(b.created,10) retrieved,
				url,
				left(m.starttime,10) enacted
			from bylaw b
				join meeting m on m.id = b.meetingid
			where 
				bylawnum = :num 
			order by 
				meetingid desc 
			",array('num'=>$num));
		#pr($rows);
		?>
		<h1>By-Law No. <?php print $num; ?></h1>
    <table class="table table-bordered table-hover">
		<tr>
		<th>Enacted</th>
		<th>PDF</th>
		<th>Meeting</th>
		<th>Summary</th>
		</tr>
		<?php
		foreach ($rows as $r) {
			?>
			<tr>
			<td><?php print $r['enacted']; ?></td>
			<td><a href="<?php print $r['url']; ?>" target="_blank">pdf</a>
			<td><a href="/meetings/meeting/<?php print $r['meetid']; ?>" target="_blank"><?php print $r['meetid']; ?></a>
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

