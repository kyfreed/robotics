<?php
$loggedOutOk = true;
include_once 'classes/init.php';

$PAGE->addHead('<script src="inc/jquery/jquery-3.3.1.js"></script>');


$data = Sql::query("
	SELECT botstats.team_number as team, ifnull(a.ranking_heading,'') as heading,
	       min(botstats.num_matches) as num_matches,
		   max(a.type) as type, min(a.order_by) as order_by,
	       sum(min_earned) as min_earned, sum(min_points) as min_points,
	       sum(avg_earned) as avg_earned, sum(avg_points) as avg_points,
	       sum(max_earned) as max_earned, sum(max_points) as max_points
	  FROM (SELECT concat(mta.team_number, '-', mta.match_number) as team_number, mta.team_number as team_numberi, mta.match_number as match_number, mta.action_id, count(*) as num_matches,
				   min(mta.earned) as min_earned, min(ifnull(soverride.score, sbasic.score*mta.earned)) as min_points,
				   avg(mta.earned) as avg_earned, avg(ifnull(soverride.score, sbasic.score*mta.earned)) as avg_points,
				   max(mta.earned) as max_earned, max(ifnull(soverride.score, sbasic.score*mta.earned)) as max_points
			  FROM match_team_actions mta
			       LEFT OUTER JOIN action_scores sbasic ON sbasic.action_id = mta.action_id
			                                           AND sbasic.earned IS NULL
			       LEFT OUTER JOIN action_scores soverride ON soverride.action_id = mta.action_id
			                                              AND soverride.earned = mta.earned
                         WHERE mta.team_number = " . Sql::val($_REQUEST["team"]) .  "                                          
			 GROUP BY mta.team_number, mta.match_number, mta.action_id) as botstats
			JOIN actions a ON a.id = botstats.action_id
			LEFT OUTER JOIN invalidated i ON botstats.team_number = i.team_number AND botstats.match_number = i.match_number
	 GROUP BY botstats.team_number, heading
	 ORDER BY botstats.team_number, order_by
");

$rank = 1; // Init counter

// Calculate scale of each value based on a curve
$headings = []; // Key is heading text, value is max ever seen
$teams = [];

foreach ($data as $i => $team) {
	// Build $headings data
	if (empty($headings[ $team['heading'] ])) { // init the heading entry
		$headings[ $team['heading'] ] = 0;
	}
	if ($headings[ $team['heading'] ] < $team['max_earned']) {
		$headings[ $team['heading'] ] = $team['max_earned'];
	}
	// Build $teams data
	if (empty($teams[ $team['team'] ])) { // init the team entry
		$teams[ $team['team'] ] = [
			'team' => $team['team'],
			'num_matches' => $team['num_matches'],
			'min_points' => 0,
			'avg_points' => 0,
			'max_points' => 0,
			'headings' => [],
		];
	}
	$teams[ $team['team'] ]['min_points'] += $team['min_points'];
	$teams[ $team['team'] ]['avg_points'] += $team['avg_points'];
	$teams[ $team['team'] ]['max_points'] += $team['max_points'];
	$teams[ $team['team'] ]['headings'][ $team['heading'] ] = [
		'type' => $team['type'],
		'min_earned' => $team['min_earned'],
		'avg_earned' => $team['avg_earned'],
		'max_earned' => $team['max_earned'],
	];
}

usort($teams, function($a, $b) {
	if ($a['avg_points'] == $b['avg_points']) return 0;
	return ($a['avg_points'] > $b['avg_points']) ? -1 : 1;
});

//prd('raw teams data (report being worked on)', $teams);

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
		<th>Invalidated</th>
		<th>Team</th>
		<?php foreach ($headings as $heading => $max) { ?>
			<?php if ($heading) { ?>
				<th><?= $heading ?></th>
			<?php } ?>
		<?php } ?>
	</tr>
<?php 
foreach ($teams as $i => $team) { 
    ?>
	
        <tr>
		<td style="text-align:center;">
			<input type="checkbox" value=<?= $team['team'] ?> class="invalidated"
				   <?= !(empty(Sql::query("SELECT * FROM invalidated WHERE team_number = " . Sql::val($_REQUEST['team']) . " AND match_number = " . str_replace("'", "", explode("-", Sql::val($team['team']))[1])))) ? "checked" : "" ?>>
		</td>
                <td><?= $team['team'] ?></td>
		<?php
		if ($team['invalidated']) {
			foreach ($headings as $heading => $max) {
				if ($heading) {
					echo '<td></td>';
				}
			}
			echo '</tr>';
			continue;
		}
		?>
		<?php foreach($team['headings'] as $label => $heading) { ?>
			<?php if ($label) { 
                                if($heading['type'] == 'INT'){?>
                                    <td style="position:relative;">
                                            <?= round($heading['avg_earned'],1)?>
                                    </td>
			<?php } if($heading['type'] == 'BOOLEAN'){?>
                                    <td style="position:relative;">
                                            <?= $heading['avg_earned'] == 1 ? "Yes" : "No"?>
                                    </td>
		<?php }
                }
                ?>
	
    <?php } ?>
       </tr>
       
<?php } ?>
</table>
<script>
$(document).ready(function () {
	$("input.invalidated").change(function () {
            $.post("invalidate.php", {team_number: this.value.split("-")[0], match_number: this.value.split("-")[1], invalidated: this.checked ? 1 : 0});
	});
	$.getScript("reloadi.php?since=<?= @filemtime("_lastsavei.txt") ?>");
});
</script>