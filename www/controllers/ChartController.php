<?php

class ChartController {

  static public function lobbyingWeightedActivity($days) {
    top();

    $rows = getDatabase()->all("
      select
        f.client, count(1) hits,
        count(distinct(f.id)) files,
        count(distinct(f.lobbyist)) lobbyists,
        sum(case 
          when activity = 'Meeting' then 5
          when activity = 'Telephone' then 3
          when activity = 'Email' then 1
          when activity = 'Mail' then 1
          when activity = 'Other' then 1
          else 1 end) weighted
      from lobbying l
        join lobbyfile f on f.id = l.lobbyfileid
      where 
        datediff(NOW(),lobbydate) <= :days
      group by
        f.client
      order by
        weighted desc,
				f.client
    ",array('days'=>$days));

    ?>

    <div class="row-fluid">
    <div class="span6">
    <h1>Lobbying intensity report</h1>
    <b>Period:</b> last <?php print $days; ?> days.
    <b>Switch to: </b>
    <a href="7">1 week</a>,
    <a href="14">2 weeks</a>,
    <a href="30">1 month</a>,
    <a href="60">2 months</a>,
    <a href="180">6 months</a>,
    <a href="9999">all time</a>
    </div>
    <div class="span6">
    <b>Intensity</b> is calculated by allocating a number of points for each occurance of a lobbying activity:<br/>
    <b>In person:</b>  5 points,
    <b>Telephone:</b> 3 points,
    <b>Email:</b> 1 points,
    <b>Postal Mail:</b> 1 points,
    <b>Other:</b> 1 points
    </div>
    </div>

    <div>&nbsp;</div>

    <?php
    $total = 0;
    foreach ($rows as $r) {
      $total += $r['weighted'];
    }
    ?>

    <table class="table table-bordered table-hover table-condensed" style="">
      <tr>
      <th>Intensity</th>
      <th>% of total (<?php print $total; ?>)</th>
      <th>Client</th>
      <th>Active Files</th>
      <th>Active Lobbyists</th>
      <th>Unweighted Activities</th>
      </tr>
    <?php
    foreach ($rows as $r) {
      ?>
      <tr>
      <td><?php print $r['weighted']; ?></td>
      <td><?php print number_format($r['weighted']/$total*100,1); ?></td>
      <td><a href="<?php print OttWatchConfig::WWW."/lobbying/clients/{$r['client']}"; ?>"><?php print $r['client']; ?></a></td>
      <td><?php print $r['files']; ?></td>
      <td><?php print $r['lobbyists']; ?></td>
      <td><?php print $r['hits']; ?></td>
      </tr>
      <?php
    }
    ?>
    </table>
    <?php

    bottom();
    return;

    self::highJS();
    ?>
    <script src="http://code.highcharts.com/highcharts.js"></script>
    <script src="http://code.highcharts.com/modules/exporting.js"></script>
    <div id="container" style="min-width: 310px; margin: 0 auto;"></div>
    <script>
$(function () {
        $('#container').highcharts({
            chart: {
                type: 'bar'
            },
            title: {
                text: 'Stacked bar chart'
            },
            xAxis: {
                categories: [ 'Lobbying' 
                <?php
	              foreach ($rows as $r) {
//                   print "'".preg_replace("/'/",'',$c['client'])."',\n";
                }
                ?>
                ]
            },
            yAxis: {
                min: 0,
                title: {
                    text: 'Weighted lobbying activity'
                },
            },
            legend: {
                borderColor: '#CCC',
            },
            plotOptions: {
                series: {
                  
                }
            },
            series: [
            { name: '', data: [5, 3, ] }, 
            ],

        });
    });
    </script>
    <?php

    bottom();
  }

  static public function lobbyingDaily($groupBy,$days,$client) {
    top('',true);
    self::lobbyingDailyInner($groupBy,$days,$client);
    bottom(true);
  }

  static public function lobbyingDailyInner($groupBy,$days,$client) {
    error_reporting(E_ALL);
    self::highJS();

    if (!isset($groupBy) || $groupBy == '') {
      $groupBy = 'daily';
    }

    if (!isset($client)) {
      $client = '';
    }
    if (isset($_GET['client'])) {
      $client = $_GET['client'];
    }
    $whereArr = array();
    $whereClient = '';
    if ($client != '') {
      $whereArr['client'] = $client;
      $whereClient = ' and lower(f.client) = lower(:client) ';
    }

    if (!isset($days) || $days == '') {
      $days = 9999;
    }
    $whereArr['days'] = $days;

    if ($groupBy == 'monthly') {
      $sqlField = " concat(date_format(lobbydate,'%Y-%m'),'-01') ";
    } else {
      $sqlField = ' date(lobbydate) ';
    }

    #$rows = getDatabase()->all(" select date(lobbydate) date, count(1) actions from lobbying where datediff(NOW(),lobbydate) <= 60 group by date(lobbydate) order by date(lobbydate) ");
    $sql = "
      select 
        $sqlField date, 
        count(1) actions 
      from lobbying l
        join lobbyfile f on f.id = l.lobbyfileid
      where 
        datediff(CURRENT_TIMESTAMP,lobbydate) < :days
        $whereClient
      group by 
        $sqlField
      order by 
        $sqlField ";

    $rows = getDatabase()->all($sql,$whereArr);

    if (count($rows) > 0) {
      $minDate = $rows[0]['date'];
      $maxDate = $rows[count($rows)-1]['date'];
    } else {
      print "No lobbying found in the last $days day(s).";
      return;
    }
    $startDate = $rows[0]['date'];


    $stats = array();
    $data = array();
    $xlabels = array();
    foreach ($rows as $r) {
      $stats[$r['date']] = $r['actions']+0;
      $endDate = $r['date'];
    }

    $checkDate = $startDate;
    $done = FALSE;
    do {
      if ($checkDate >= $endDate) { $done = TRUE; }
      if ($groupBy == 'daily' || isset($stats[$checkDate])) {
        $data[] = array($checkDate, isset($stats[$checkDate]) ? $stats[$checkDate] : 0 );
      }
      $checkDate = date ("Y-m-d", strtotime ("+1 day", strtotime($checkDate)));
    } while (!$done);

    $series1 = new HighRollerSeriesData();
    $series1->addName('Activities')->addData($data);

    $linechart = new HighRollerSplineChart();
    $linechart->chart->renderTo = 'linechart';
    $linechart->title->text = "$client Lobbying Activities from $minDate to $maxDate ($groupBy)";
    $linechart->addSeries($series1);

    $linechart->xAxis->type = 'datetime';
    $linechart->xAxis->tickInterval = 1 * 24 * 3600 * 1000; // One day
    $linechart->yAxis->min = 0;
    $linechart->yAxis->title->text = 'Number of Lobbying Contacts';

    print HighRoller::setHighChartsLocation(OttWatchConfig::WWW."/highcharts/js/highcharts.js");
    ?>
    <div id="linechart"></div><script><?php echo $linechart->renderChart();?></script>
    <?php
  }

  static public function test() {
  error_reporting(E_ALL);
    top();
    self::highJS();


 $chartData = array(5324, 7534, 6234, 7234, 8251, 10324);
# 
 $linechart = new HighRollerLineChart();
 $linechart->chart->renderTo = 'linechart';
 $linechart->title->text = 'Line Chart';
# 
 $series1 = new HighRollerSeriesData();
 $series1->addName('myData')->addData($chartData);
# 
 $linechart->addSeries($series1);
# 
# print "HI<hr/>\n";
print HighRoller::setHighChartsLocation(OttWatchConfig::WWW."/highcharts/js/highcharts.js");
# print "HI<hr/>\n";
# 
?>
<div id="linechart"></div>
<script>
<?php echo $linechart->renderChart();?>
</script>
<?php
# print "HI<hr/>\n";


    bottom();
  }

  static public function highJS() {
    require_once(__DIR__.'/../HighRoller/HighRoller.php');
    require_once(__DIR__.'/../HighRoller/HighRollerSeriesData.php');
    require_once(__DIR__.'/../HighRoller/HighRollerLineChart.php');
    require_once(__DIR__.'/../HighRoller/HighRollerSplineChart.php');
  }

  
}

?>
