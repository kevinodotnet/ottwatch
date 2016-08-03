<?php

class BylawController {

	static public function listAll() {
	  $rows = getDatabase()->all(' select * from bylaw order by bylawnum desc ');

		top3("Bylaws");

		?>
    <table class="table table-bordered table-hover">
		<tr>
		<th style="white-space: nowrap;">Bylaw #</th>
		<th>Summary</th>
		<th style="white-space: nowrap;">Retrieved On</th>
		</tr>
		<?
		foreach ($rows as $r) {
			?>
			<tr>
			<td style="white-space: nowrap;"><?php 
				if ($r['url'] == null) {
					print $r['bylawnum']; 
				} else {
					print "<a target=\"_blank\" href=\"".$r['url']."\">".$r['bylawnum']."</a>";
				}
			?></td>
			<td><?php print $r['summary']; ?></td>
			<td style="white-space: nowrap;"><?php print substr($r['created'],0,10); ?></td>
			</tr>
			<?
		}
		?>
		</table>
		<?

		bottom3();

	}

}

