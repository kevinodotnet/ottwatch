<?php

class OpenDataController {

	/*
	Import JSON obtained from maps.ottawa.ca service, discarding any existing data in the destination table.
	*/
	public static function geoOttawaImport($table,$files) {

		if (count($files) == 0) {
			print "ERROR: no files specified\n";
			return;
		}
		foreach ($files as $f) {
			if (!file_exists($f)) {
				print "ERROR: file not found: $f\n";
				return;
			}
		}

		# use field metadata to construct a 'create table' stament.
		$data = json_decode(file_get_contents($files[0]));

		$sql = "  create table $table (\n ";
		$sql .= "   ottwatchid mediumint not null auto_increment, ";

		foreach ($data->fields as &$f) {
			# '.' in name screws up SQL later.
			$f->name = preg_replace('/\./','_',$f->name);
			$f->name = preg_replace('/SAM_teranet_parcels_addresses_/','',$f->name);
			$my_type = "";
			switch ($f->type) {
				case "esriFieldTypeOID":
					$my_type = "mediumint unsigned";
					break;
				case "esriFieldTypeSmallInteger":
				case "esriFieldTypeInteger":
					$my_type = "int";
					break;
				case "esriFieldTypeDouble":
					$my_type = "float";
					break;
				case "esriFieldTypeString":
					$my_type = "varchar({$f->length})";
					break;
				default:
					print "ERROR: unknown gis type {$f->type}\n";
					exit;
			}
			$sql .= "  `{$f->name}` $my_type, \n";
		}
		switch ($data->geometryType) {
			case "esriGeometryPoint":
			case "esriGeometryPolygon":
				# ok
				break;
			default:
				print "ERROR: unknown esriGeometryPoint: {$data->geometryType}\n";
				exit;
		}

		$sql .= "  `shape` geometry, \n";
  	$sql .= "  primary key (ottwatchid) \n";
		$sql .= " ) engine = innodb \n";

		# drop current data
		try {
			getDatabase()->execute(" drop table $table ");
		} catch (Exception $e) {
			if (!preg_match('/Unknown table/',$e)) {
				throw($e);
			}
		}

		#print "\n\n$sql\n\n";
		getDatabase()->execute($sql);

		$fileIndex = 1;

		foreach ($files as $f) {
			$index = 1;
			print "Importing $f (".($fileIndex++)."/".count($files).") \n";
			$data = json_decode(file_get_contents($f));
			foreach ($data->features as $f) {
				if (++$index % 80 == 0) { print " $index/".count($data->features)."\n"; }
				switch ($data->geometryType) {
					case "esriGeometryPoint":
						$shapeValue = " PointFromText(' POINT( {$f->geometry->x} {$f->geometry->y} ) ') ";
						break;
					case "esriGeometryPolygon":
						$shapeValue = "  PolygonFromText(' POLYGON(\n ";
						$rings = $f->geometry->rings;
						foreach ($rings as $ring) {
							$shapeValue .= self::pointListToString($ring);
							$shapeValue .= "\n ,\n";
						}
						$shapeValue = chop($shapeValue,",\n");
						$shapeValue .= " ) ') ";
						break;
					default:
						pr($f);
						print "ERROR: unknown esriGeometryPoint: {$data->geometryType}\n";
						exit;
				}
				# '.' to '_'
				$values = get_object_vars($f->attributes);
				foreach ($values as $k => $v) {
					unset($values[$k]);
					$k = preg_replace('/\./','_',$k);
					$k = preg_replace('/SAM_teranet_parcels_addresses_/','',$k); # applies only to properties
					$values[$k] = $v;
				}
				print ".";
				$id = db_insert($table,$values);
				$sql = " update $table set shape = $shapeValue where ottwatchid = $id ";
				getDatabase()->execute($sql);
			}
			print "\n";
		}

	}

  /*
  Scan the data.ottawa.ca website and injest all datasets and the files within the sets.
  */
  public static function scanOpenData() {
	  $datasets = self::getDatasets();

    foreach ($datasets as $d) {

      $set = self::getDataset($d);

      $values = array();
      $values['guid'] = $set->id;
      $values['name'] = $set->name;
      $values['title'] = $set->title;
			if (preg_match('/^{/',$set->title)) {
				# title is embedded JSON; pull out English
				$title = json_decode($set->title);
				$values['title'] = $title->en;
			}
      $values['url'] = $set->ckan_url;
      $values['updated'] = $set->metadata_modified;
			$id = db_save('opendata',$values,'guid');
      $row = getDatabase()->one(" select * from opendata where guid = :id ",array('id'=>$set->id));

			$dataid = $row['id'];

      foreach ($set->resources as $r) {
				try {

        $values = array();
        $values['dataid'] = $dataid;
        $values['description'] = trim($r->description);
        $values['format'] = $r->format;
        $values['guid'] = $r->id;
        $values['hash'] = $r->hash;
        $values['name'] = $r->name;
        $values['size'] = $r->size;
        $values['url'] = $r->url;

	      $row = getDatabase()->one(" select * from opendatafile where guid = :id ",array('id'=>$r->id));
				if ($row['id']) {
					# exists
	        $values['id'] = $row['id'];
					if ($r->url == 'http://opl-bpo.ca/en/rss-feeds'
						|| $r->url == 'http://octranspo1.com/developers/register'
						|| $r->url == 'http://biblioottawalibrary.ca/branches.xml'
						|| $r->url == 'http://biblioottawalibrary.ca/en/events-feed'
						|| $r->url == 'http://app06.ottawa.ca/online_services/opendata/data/spotlight/events.xml'
						|| $r->url == 'http://app06.ottawa.ca/online_services/opendata/data/spotlight/venues.xml'

						|| $r->url == 'http://app01.ottawa.ca/inspections-opendata/yelp_ottawa_all_healthscores.zip'
						|| $r->url == 'http://app01.ottawa.ca/inspections-opendata/yelp_ottawa_healthscores.zip'
						|| $r->url == 'http://app06.ottawa.ca/online_services/opendata/classdata/available_courses.txt'

						|| preg_match('/maps.google.ca/',$r->url)
						|| preg_match('/google.com/',$r->url)

						|| preg_match('/ottawacityjobs/',$r->url)
						|| preg_match('/jobs.xml/',$r->url)

						|| preg_match('/biblioottawalibrary/',$r->url)
						) {
						# these pages are always updated on every scan, for some reason, and are spammy on the update channel.
						# save them, but without 'updated' timestamp changes, so they are in the catelog but dont make tweets.
						db_save('opendatafile',$values,'id');
						continue;
					}
					if ($r->hash == '') {
						# opendata portal does not have a hash, so do the actual download and calculate our own for 'change detection' purposes
						$data = '';
						# print "\n{$r->url}\n";
						if (preg_match('/stagingdata/',$r->url)) {
							# print "skipping possible huge file: {$r->url}\n";
							$row['hash'] = '';
							$r->hash = '';
							#pr($r);
						} else {
							$data = `wget -qO - "{$r->url}"`;
						}
#						if ($r->url == 'http://octranspo1.com/developers/register' || $r->url == 'http://biblioottawalibrary.ca/branches.xml') {
#							# these two URLs have random values inside the actual content. Supress so the random
#							# numbers don't mutate the hash calculation.
#							$lines = explode("\n",$data);
#							for ($x = 0; $x < count($lines); $x++) {
#								# back-to-back WGET calls show these lines have random nonce; kill them.
#								$lines[$x] = preg_replace('/jQuery.extend.*/','',$lines[$x]);
#								$lines[$x] = preg_replace('/styles_common_c.*/','',$lines[$x]);
#								$lines[$x] = preg_replace('/input.*hidden.*meta/','',$lines[$x]);
#							}
#							# put HumptyDumpty back together
#							$data = implode("\n",$lines);
#						}
						$hash = md5($data);
						$r->hash = $hash;
		        $values['hash'] = $r->hash;
					}
					if ($row['hash'] != $r->hash) {
						# changed hash means real update,
		        # $values['updated'] = $r->last_modified;
						# underlying data sometimes changed but data.ottawa last-modified doesn't? Override.
		        $values['updated'] = date('Y-m-d H:i:s');
					}
					# update meta data, if changed, though it probably hasnt
					db_save('opendatafile',$values,'id');
				} else {
	        db_insert('opendatafile',$values);
				}
			} catch (Exception $e) {
				print $e;
      }
			}

    }
  }

	public static function callData($path) {
	  $json = `wget -qO - "http://data.ottawa.ca/$path"`;
	  $result = json_decode($json);
	  return $result;
	}
	
	public static function getDatasets() {
	  $list = self::callData("/api/1/rest/dataset");
	  return $list;
	}
	
	public static function getDataset($name) {
	  $dataset = self::callData("/api/1/rest/dataset/$name");
	  return $dataset;
	}

  /*

  */
  public static function doList() {
    top();

    ?>
    <div class="row-fluid">
    <div class="span3">
    <h1>OpenData</h1>
    </div>
    <div class="span9">
    <p class="lead">
    <b>data.ottawa.ca</b> is Ottawa's open data portal. As data is added/updated
    on the portal it will pop to the top of the below list. For more information about
    each file use the <i>dataset</i> link to learn who maintains the data, and metadata
    about the file itself.
    </p>
    </div>
    </div>
    <?php

    $rows = getDatabase()->all("
      select
        d.title,d.url,
        f.size,f.description,f.format,f.name as fname,f.url as fileurl, left(f.updated,10) updated
      from opendatafile f
        join opendata d on d.id = f.dataid
      order by
        f.updated desc
    ");
    ?>
    <table class="table table-bordered table-hover table-condensed" style="width: 98%;">
    <tr>
    <th>Updated</th>
    <th>File</th>
    <th>Format</th>
    <th>Dataset</th>
    <th>Size (kB)</th>
    <th>Description</th>
    </tr>
    <?php
    foreach ($rows as $r) {
      $size = $r['size'];
      if ($size > 0) {
        $size = intval($size/1024);
      }
      ?>
      <tr>
      <td><nobr><?php print $r['updated']; ?></nobr></td>
      <td><nobr><a href="<?php print $r['fileurl']; ?>"><?php print $r['fname']; ?></a></nobr></td>
      <td><?php print $r['format']; ?></td>
      <td><nobr><a href="<?php print $r['url']; ?>"><?php print $r['title']; ?></a></nobr></td>
      <td><?php print $size ?></td>
      <td><?php print $r['description']; ?></td>
      </tr>
      <?php
    }
    ?>
    </table>
    <?php
    bottom();
  }

	public static function pointListToString ($list) {
		$str = "";
		foreach ($list as $point) {
			$str .= "{$point[0]} {$point[1]},";
		}
		$str = chop($str,',');
		return "({$str})";
	}
	
}
