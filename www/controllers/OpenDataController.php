<?php

class OpenDataController {

  /*
  Scan the data.ottawa.ca website and injest all datasets and the files within the sets.
  */
  public static function scanOpenData() {
	  $datasets = self::getDatasets();
    $index = 0;

    foreach ($datasets as $d) {

      $index++;

      $set = self::getDataset($d);

      $count = getDatabase()->execute(" delete from opendata where guid = :id ",array('id'=>$set->id));

      $values = array();
      $values['guid'] = $set->id;
      $values['name'] = $set->name;
      $values['title'] = $set->title;
      $values['url'] = $set->ckan_url;
      $values['updated'] = $set->metadata_modified;

      $dataid = db_insert('opendata',$values);

      foreach ($set->resources as $r) {
        $values = array();
        $values['dataid'] = $dataid;
        $values['guid'] = $r->id;
        $values['size'] = $r->size;
        $values['description'] = trim($r->description);
        $values['format'] = $r->format;
        $values['name'] = $r->name;
        $values['url'] = $r->url;
        $values['updated'] = $r->last_modified;
        db_insert('opendatafile',$values);
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
    <table class="table table-bordered table-hover table-condensed" style="width: 100%;">
    <tr>
    <th>Updated</th>
    <th>File</th>
    <th>Dataset</th>
    <th>Size (kB)</th>
    <th>Format</th>
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
      <td><nobr><a href="<?php print $r['url']; ?>"><?php print $r['title']; ?></a></nobr></td>
      <td><?php print $size ?></td>
      <td><?php print $r['format']; ?></td>
      <td><?php print $r['description']; ?></td>
      </tr>
      <?php
    }
    ?>
    </table>
    <?php
    bottom();
  }
	
}


