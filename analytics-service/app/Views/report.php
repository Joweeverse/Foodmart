<b>Report</b>
<p>Data Report</p>
<?php if (! empty($report) && is_array($report)): ?>
<div class="chart-container" style="position: relative; height:40vh; width:80vw">
<canvas id="dataReport"></canvas>
</div>
<script src="<?= base_url('chartjs/Chart.bundle.min.js') ?>"></script>
<script>
var dataReport = document.getElementById('dataReport');
var data_sales_report = [];
var data_label = [];
<?php foreach ($report as $report_item): ?>
var total_sales = <?= esc($report_item['total_sales']) ?>;
var total_store_cost = <?= esc($report_item['total_store_cost']) ?>;
var profit = total_sales - total_store_cost;
var time_id = <?= esc($report_item['time_id']) ?>;
data_sales_report.push(profit);
data_label.push(time_id);
<?php endforeach ?>
var data_chart_profit = {
labels : data_label,
datasets:[{
label : 'Profit/Loss',
data : data_sales_report,
fill : false
}]
};
var chart_profit = new Chart(dataReport,{
type : 'line',
data : data_chart_profit
});
</script>
<?php else: ?>
<h3>No Data</h3>
<p>Unable to find any data for you.</p>
<?php endif ?>