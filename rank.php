<?php
$loggedOutOk = true;
include_once 'classes/init.php';

$PAGE->addHead('<script src="inc/jquery/jquery-3.3.1.js"></script>');



$ahead = array();
$data = Sql::query("
SELECT team_number, count(*) as num_matches, ranking_heading, taken, type,
       min(earned) as min_earned,
       avg(earned) as avg_earned,
       max(earned) as max_earned
  FROM (SELECT mta.team_number, max(t.taken) as taken, max(a.type) as type,
			   a.ranking_heading, min(a.order_by) as order_by, sum(mta.earned) as earned
		  FROM match_team_actions mta
			   JOIN actions a ON mta.action_id = a.id
			   LEFT OUTER JOIN taken t ON mta.team_number = t.team_number
		 WHERE ifnull(a.ranking_heading,'') != ''
		 GROUP BY mta.team_number, mta.match_number, a.ranking_heading
		)  as x
 GROUP BY team_number, ranking_heading
 ORDER BY team_number, order_by
");

$rank = 1; // Init counter

// Calculate scale of each value based on a curve
$headings = []; // Key is heading text, value is max ever seen

foreach ($data as $i => $record) {
	if (empty($headings[ $record['ranking_heading'] ])) { // init the heading entry
		$headings[ $record['ranking_heading'] ] = 0;
	}
	if ($headings[ $record['ranking_heading'] ] < $record['max_earned']) {
		$headings[ $record['ranking_heading'] ] = $record['max_earned'];
	}
}


function isFirstHeading($heading) {
	GLOBAL $headings;
	return array_keys($headings)[0] == $heading;
}
function isLastHeading($heading) {
	GLOBAL $headings;
	return array_pop(array_keys($headings)) == $heading;
}
function valueToLeft($heading, $value) {
	GLOBAL $headings;
	$max = $headings[$heading];
	return round(100 * $value / $max, 1) . '%';
}
function valueToRight($heading, $value) {
	$wrong = str_replace('%','',valueToLeft($heading, $value));
	return (100 - $wrong) . '%';
}
//prd('headings', $headings, array_keys($headings), isLastHeading('Cargo Ship'), isLastHeading('Rocket 3'));


?>
<style>
	body { margin:6px; }
    .isreloading .reloadbutton { display:none; }
    table {
        border-collapse: collapse;
    }
    table, th, td {
        border: 1px solid #ddd;
        padding: 3px 6px;
    }

	.min, .avg, .max, .pct { position: absolute; top:0; bottom:0; z-index:-1; }
	.min { background-color: #ff3333; }
	.avg { background-color: #ff6600; }
	.max { background-color: #00cc00; }
	.pct { background-color: #00cc00; }
</style>
<table style="width:100%">
	<tr>
		<th>Taken</th>
		<th>Rank</th>
		<th>N</th>
		<th>Team</th>
		<?php foreach ($headings as $heading => $max) { ?>
			<th><?= $heading ?></th>
		<?php } ?>
	</tr>
<?php foreach ($data as $i => $record) { ?>
	<?php if (isFirstHeading($record['ranking_heading'])) { ?>
		<tr>
            <td style="text-align:center;">
				<input type="checkbox" value=<?= $record['team_number'] ?> class="taken"
                       <?= $record['taken'] == "1" ? "checked" : "" ?>>
			</td>
			<td>#<?= $rank++ ?></td>
			<td><?= $record['num_matches'] ?></td>
			<td><?= $record['team_number'] ?></td>
            <?php
            if ($record['taken']) {
                for ($i = 0; $i < count($headings); $i++) {
					echo '<td></td>';
				}
            }
			?>
	<?php } ?>
	
	<?php if (!$record['taken']) { // draw data item, and see about closing the <tr> ?>

		<td style="position:relative;">
			<?php if ($record['type']=='INT') { ?>
				<div class="min" style="left:0; right:<?=valueToRight($record['ranking_heading'], $record['min_earned'])?>"></div>
				<div class="avg" style="left:<?=valueToLeft($record['ranking_heading'], $record['min_earned'])?>; right:<?=valueToRight($record['ranking_heading'], $record['avg_earned'])?>"></div>
				<div class="max" style="left:<?=valueToLeft($record['ranking_heading'], $record['avg_earned'])?>; right:<?=valueToRight($record['ranking_heading'], $record['max_earned'])?>"></div>
				<?= round($record['avg_earned'],1) ?>
			<?php } ?>
			<?php if ($record['type']=='BOOLEAN') { ?>
				<div class="pct" style="left:0; $record['avg_earned'])?>; right:<?=100*(1-$record['avg_earned'])?>%"></div>
				<?= round($record['avg_earned'] * 100) ?>%
			<?php } ?>
		</td>

		<?php if (isLastHeading($record['ranking_heading'])) { ?>
			</tr>
		<?php } ?>
	<?php } ?>
<?php } ?>
</table>
<script>
$(document).ready(function () {
	$("input.taken").change(function () {
		$.post("taken.php", {team_number: this.value, taken: this.checked ? 1 : 0});
	});
	$.getScript("reload.php?since=<?= @filemtime("_lastsave.txt") ?>");
});
</script>