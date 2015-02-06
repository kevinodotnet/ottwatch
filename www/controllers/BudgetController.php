<?php

class BudgetController {

	// getRoute()->get('/budget/(\d+)/(capital)/(draft)/(\d+)', array('BudgetController', 'showEntry'));
  static public function search($year, $type, $draft) {

		$table = 'budget_draft_2015';
		$sql = " select * from $table where 1=1 ";
		foreach (array('ward','committee','dept','service_area','category') as $a) {
			if (isset($_GET[$a])) {
				if ($a == 'ward') {
					$sql .= " and (
						$a = '".mysql_escape_string($_GET[$a])."'
						or $a like '".mysql_escape_string($_GET[$a]).",%'
						or $a like '%,".mysql_escape_string($_GET[$a]).",%'
						or $a like '%,".mysql_escape_string($_GET[$a])."'
						)
					";
				} else {
					$sql .= " and $a = '".mysql_escape_string($_GET[$a])."' ";
				}
			}
		}
		$sql .= "
			order by
				committee, dept, service_area, category, completion, ward
		";

		$rows = getDatabase()->all($sql);

		if (count($rows) == 0) {
			top3();
			print "No data returned from query... Huh.";
			bottom3();
			return;
		}

		top3("$year $type budget : $draft : search results");

		?>
	  <table class="table table-bordered table-hover table-condensed">
		<tr>
		<th>details</th>
		<?php
		foreach ($rows[0] as $k=>$v) {
			if ($k == 'id') { continue; }
			print "<th>$k</th>\n";
		}
		?>
		</tr>
		<?php
		foreach ($rows as $r) {
			print "<tr>";
			print "<th class=\"text-center\"><a href=\"{$r['id']}\"><i class=\"fa fa-external-link\"></i></a></th>\n";
			foreach ($r as $k=>$v) {
				if ($k == 'id') { continue; }
				print "<td>$v</td>\n";
			}
			print "</tr>";
		}
		?>
		</table>
		<?php
		bottom3();
	}

  static public function showEntry($year, $type, $draft, $id) {

		if ($year != 2015) {
			print "Bad year: $year";
			bottom3();
			return;
		}
		if ($type != 'capital' && $type != 'operating') {
			print "'$type' is invalid\n";
		}
		if ($draft != 'draft' && $draft != 'adopted') {
			print "'$draft' is invalid\n";
		}

		$table = 'budget_draft_2015';

		$row = getDatabase()->one(" select * from $table where id = :id ",array('id'=>$id));
		unset($row['id']);

    top3($row['name']." ($type budget : year $year : $draft)");

		?>
		<div class="row">
		<div class="col-sm-6">
		<h3><?php print $row['name']; ?></h3>
	  <table class="table table-bordered table-hover table-condensed">
		<?php

		$linkit = array('committee','dept','service_area','category','ward');
		foreach ($row as $k=>$v) {
			if ($k == 'name') { continue; }
			$url = '';
			if (in_array($k,$linkit,true)) {
				$url = "/budget/$year/$type/$draft/search?$k=".urlencode($v);
			}
			?>
			<tr>
				<th><?php print $k; ?></th>
				<td>
					<?php if ($url != '') { print "<a href=\"$url\">"; } ?>
					<?php if ($k == 'amount') { print "$"; } ?>
					<?php print $v; ?>
					<?php if ($url != '') { print "</a>"; } ?>
				</td>
			</tr>
			<?php
		}
		?>
		</table>
		<a class="btn btn-default" href="search">See All (<?php print "$year $type $draft"; ?>)</a>
		</div>
		<div class="col-sm-6">
		<?php disqus(); ?>
		</div>
		</div><!-- // row -->
		<?php
		bottom3();
	}

}

