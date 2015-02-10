<?php

class BudgetController {

	// getRoute()->get('/budget/(\d+)/(capital)/(draft)/(\d+)', array('BudgetController', 'showEntry'));
  static public function searchAll() {

		$searchArray = array('q','adopted','year','name','code','ward','committee','dept','service_area','category');

		$table = 'budget_capital';
		$sql = " select * from $table where 1 = 1 ";
		$searchTerms = '';
		$doSearch = 0;
		foreach ($searchArray as $a) {
			if (isset($_GET[$a]) && $_GET[$a] != '') {
				$doSearch = 1;
				$searchTerms .= " $a=".htmlspecialchars($_GET[$a]);
				if ($a == 'adopted' && $_GET[$a] == 'a') {
					continue; // do not filter on the column
				}
				if ($a == 'ward') {
					$sql .= " and (
						$a = '".mysql_escape_string($_GET[$a])."'
						or $a like '".mysql_escape_string($_GET[$a]).",%'
						or $a like '%,".mysql_escape_string($_GET[$a]).",%'
						or $a like '%,".mysql_escape_string($_GET[$a])."'
						)
					";
				} else if ($a == 'q') {
					$sql .= " and ( ";
					$sql .= "   program like '%".mysql_escape_string($_GET[$a])."%'";
					$sql .= "   or description like '%".mysql_escape_string($_GET[$a])."%'";
					$sql .= "   or name like '%".mysql_escape_string($_GET[$a])."%'";
					$sql .= "   or listing like '%".mysql_escape_string($_GET[$a])."%'";
					$sql .= " ) ";
				} else {
					$sql .= " and $a = '".mysql_escape_string($_GET[$a])."' ";
				}
			}
		}
		$sql .= "
			order by
				year desc, committee, dept, service_area, category, completion, ward
		";

		if ($doSearch == 0) {
			$rows = array();
		} else {
			$rows = getDatabase()->all($sql);
		}

		top3("Budget Search");
		?>
		<div class="row">
		<div class="col-sm-6">
		<h1>Budget Search</h1>
		</div>
		<div class="col-sm-6">
			<p><i><a href="http://ottawa.ca/budget2015">For full budget details and to participate officially visit <b>ottawa.ca/budget2015</b></a></i></p>
		<?php
		if ($doSearch == 1) {
			?>
			<h4>Search:<?php print $searchTerms;?></h4>
			<h5>Matches: <?php print count($rows); ?></h5>
			<p>
			<a href="/budget/search" class="btn btn-default">Back to Search Form</a>
			</p>
			<?php 
		}
		?>
		</div>
		</div>

		<?php
		if ($doSearch == 1) {
			?>
		  <table class="table table-bordered table-hover table-condensed">
			<tr>
			<th>details</th>
			<?php
			foreach ($rows[0] as $k=>$v) {
				if ($k == 'id') { 
					$id = $v;
					continue; 
				}
				print "<th>$k</th>\n";
			}
			?>
			<?php
			foreach ($rows as $r) {
				$url = "/budget/{$r['year']}/capital/".($r['adopted'] == 1 ? 'adopted' : 'draft')."/{$r['id']}";
				print "<tr>";
				print "<td class=\"text-center\"><a href=\"{$url}\"><i class=\"fa fa-external-link\"></i></a></td>\n";
				foreach ($r as $k=>$v) {
					if ($k == 'id') { continue; }
					if ($k == 'program' || $k == 'description') {
						if (isset($_GET['q']) && $_GET['q'] != '') {
							$q = $_GET['q'];
							$m = array();
							if (preg_match("/(.*)$q(.*)/",$v,$m)) {
								$bound = 50;
								$s = $v;
								$v = substr($m[1],strlen($m[1])-$bound,strlen($m[1]));;
								$v .= "$q";
								$v .= substr($m[2],0,$bound);
							} else {
								$v = '';
							}
						} else {
							$v = '';
						}
					}
					if ($k == 'name') {
						print "<td class=\"text-center\"><a href=\"{$url}\">$v</a></td>\n";
					} else {
						print "<td>$v</td>\n";
					}
				}
				print "</tr>";
			}
			?>
			</table>
			<?php
		} else {
			?>
			<form method="GET">
				<div class="form-group">
					<label for="keyword">Name/Project/Description:</label>
					<input type="text" class="form-control" name="q" id="keyword" placeholder="Enter search keyword" value="<?php print htmlentities($_GET['q']); ?>"/>
				</div>
				<div class="form-group">
					<label for="code">Code</label>
					<input type="text" class="form-control" name="code" id="code" placeholder="Project Code (ie: 945345)" value="<?php print htmlentities($_GET['code']); ?>"/>
				</div>
				<div class="form-group">
					<label for="adopted">Adopted/Draft</label>
					<select class="form-control" name="adopted" id="adopted">
						<option value="1">Adopted</option>
						<option value="0">Draft</option>
						<option value="a">Both</option>
					</select>
				</div>

				<?php $rows = getDatabase()->all(" select r from ( select distinct(year) r from budget_capital ) t order by r "); ?>
				<div class="form-group">
					<label for="year">Year</label>
					<select class="form-control" name="year" id="year">
						<option value="">All</option>
						<?php foreach ($rows as $r) { print "<option>".htmlentities($r['r'])."</option>\n"; } ?>
					</select>
				</div>

				<?php $rows = getDatabase()->all(" select r from ( select distinct(committee) r from budget_capital ) t order by r "); ?>
				<div class="form-group">
					<label for="committee">Committee</label>
					<select class="form-control" name="committee" id="committee">
						<option value="">All</option>
						<?php foreach ($rows as $r) { print "<option>".htmlentities($r['r'])."</option>\n"; } ?>
					</select>
				</div>

				<?php $rows = getDatabase()->all(" select ward_num, ward_en from wards_2010 order by ward_en "); ?>
				<div class="form-group">
					<label for="ward">Ward (2014-present only)</label>
					<select class="form-control" name="ward" id="ward">
						<option value="">All</option>
						<option value="CW">City Wide</option>
						<?php foreach ($rows as $r) { print "<option value=\"{$r['ward_num']}\">".htmlentities($r['ward_en'])."</option>\n"; } ?>
					</select>
				</div>

				<button type="submit" class="btn btn-default">Submit</button>
			</form>
			<?php
		}
		bottom();

	}

  static public function search($year, $type, $draft) {

		top3();
		?>
		<h1>Oops</h1>
		<p>
		This page shouldn't be reachable anymore. Please use this search form instead. 
		</p>
		<p>
		<a href="/budget/search" class="btn btn-default">Back to Search Form</a>
		</p>
		<?php
		bottom3();

	}

  static public function showEntry($year, $type, $draft, $id) {

		if ($type != 'capital' && $type != 'operating') {
			print "'$type' is invalid\n";
		}
		if ($draft != 'draft' && $draft != 'adopted') {
			print "'$draft' is invalid\n";
		}

		$table = 'budget_capital';

		$row = getDatabase()->one(" select * from $table where id = :id ",array('id'=>$id));
#		$sql = " select * from $table ";
#		$sql .= " where year = $year ";
#		$sql .= " and adopted = ".($draft == 'draft' ? 0 : 1);
		unset($row['id']);

    top3($row['name']." ($type budget : year $year : $draft)");

		?>
		<div class="row">
		<div class="col-sm-6">
		<h3><?php print $row['name']; ?></h3>
		<p><i><a href="http://ottawa.ca/budget2015">For full budget details and to participate officially visit <b>ottawa.ca/budget2015</b></a></i></p>
	  <table class="table table-bordered table-hover table-condensed">
		<?php

		foreach ($row as $k=>$v) {
			if ($k == 'name') { continue; }
			?>
			<tr>
				<th><?php print $k; ?></th>
				<td>
					<?php if ($k == 'amount') { print "$"; } ?>
					<?php print $v; ?>
				</td>
			</tr>
			<?php
		}
		?>
		</table>
		</div>
		<div class="col-sm-6">
		<p>
		<a href="/budget/search" class="btn btn-default">Back to Search Form</a>
		</p>
		<?php disqus(); ?>
		</div>
		</div><!-- // row -->
		<?php
		bottom3();
	}

}

