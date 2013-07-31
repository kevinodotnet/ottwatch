<?php

class ChartController {

  static public function lobbyingDaily() {
  error_reporting(E_ALL);
    top();
    self::highJS();

    $rows = getDatabase()->all(" select date(lobbydate) date, count(1) actions from lobbying where datediff(NOW(),lobbydate) <= 60 group by date(lobbydate) order by date(lobbydate) ");
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
      $data[] = array(strtotime($checkDate), isset($stats[$checkDate]) ? $stats[$checkDate] : 0 );
      $checkDate = date ("Y-m-d", strtotime ("+1 day", strtotime($checkDate)));
    } while (!$done);


    $series1 = new HighRollerSeriesData();
    $series1->addName('Activities')->addData($data);

    $linechart = new HighRollerSplineChart();
    $linechart->chart->renderTo = 'linechart';
    $linechart->title->text = 'Lobbying Activities';
    $linechart->addSeries($series1);

    $linechart->xAxis->type = 'datetime';
    $linechart->xAxis->tickInterval = 1 * 24 * 3600 * 1000; // One day
    $linechart->yAxis->min = 0;
    $linechart->yAxis->title->text = 'Number of Lobbying Contacts';


    print HighRoller::setHighChartsLocation(OttWatchConfig::WWW."/highcharts/js/highcharts.js");
    ?>
    <div id="linechart"></div><script><?php echo $linechart->renderChart();?></script>
    <?php
    bottom();
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
