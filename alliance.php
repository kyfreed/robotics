<?php
$loggedOutOk = true;
include_once 'classes/init.php';

$PAGE->addHead('<script src="inc/jquery/jquery-3.3.1.js"></script>');

$us = '753';
if (!empty($_REQUEST['us'])) {
	$us = $_REQUEST['us'];
}

$secondTeam = '';
if (!empty($_REQUEST['team']) && $_REQUEST['team']!=$us) {
	$secondTeam = 'AND t2.team_number = ' . Sql::val($_REQUEST['team']);
}

// Add teams to teams table that are missing, and remove that no longer have data
Sql::exec("
INSERT INTO teams (team_number, team_name)
SELECT DISTINCT team_number, team_number
  FROM match_team_actions
 WHERE team_number NOT IN (SELECT team_number FROM teams)
");
Sql::exec("
DELETE FROM teams WHERE team_number NOT IN (SELECT team_number FROM match_team_actions)
");

$data = Sql::query("
	SELECT concat(t1.team_number,'-',t2.team_number,'-',t3.team_number) as team, ifnull(a.ranking_heading,'') as heading,
	       max(a.type) as type, min(a.order_by) as order_by,
	       sum(min_earned) as min_earned, sum(min_points) as min_points,
	       sum(avg_earned) as avg_earned, sum(avg_points) as avg_points,
	       sum(max_earned) as max_earned, sum(max_points) as max_points,
	       ifnull(a.alliance_max, a.max) as possible_earned, max(ifnull(maxoverride.score, maxbasic.score*a.max)) as possible_points
	  FROM (SELECT teams.team_number FROM teams LEFT OUTER JOIN taken ON teams.team_number = taken.team_number WHERE ifnull(taken.taken,0) = 0) t1
    	   JOIN (SELECT teams.team_number FROM teams LEFT OUTER JOIN taken ON teams.team_number = taken.team_number WHERE ifnull(taken.taken,0) = 0) t2
	         ON t2.team_number != t1.team_number
    	   JOIN (SELECT teams.team_number FROM teams LEFT OUTER JOIN taken ON teams.team_number = taken.team_number WHERE ifnull(taken.taken,0) = 0) t3
	         ON t3.team_number != t1.team_number
	   	    AND t3.team_number != t2.team_number
			" . ($secondTeam ? ''  : ' AND t3.team_number > t2.team_number ') . "
       JOIN (SELECT mta.team_number, mta.action_id, count(*) as num_matches,
				   min(mta.earned) as min_earned, min(ifnull(soverride.score, sbasic.score*mta.earned)) as min_points,
				   avg(mta.earned) as avg_earned, avg(ifnull(soverride.score, sbasic.score*mta.earned)) as avg_points,
				   max(mta.earned) as max_earned, max(ifnull(soverride.score, sbasic.score*mta.earned)) as max_points
			  FROM match_team_actions mta
			       LEFT OUTER JOIN action_scores sbasic ON sbasic.action_id = mta.action_id
			                                           AND sbasic.earned IS NULL
			       LEFT OUTER JOIN action_scores soverride ON soverride.action_id = mta.action_id
			                                              AND soverride.earned = mta.earned
			       LEFT OUTER JOIN invalidated i ON i.team_number = mta.team_number
			                                    AND i.match_number = mta.match_number
	         WHERE i.id IS NULL
			 GROUP BY mta.team_number, mta.action_id) as botstats
			JOIN actions a ON a.id = botstats.action_id
			              AND botstats.team_number IN (t1.team_number, t2.team_number, t3.team_number)
			LEFT OUTER JOIN action_scores maxbasic ON maxbasic.action_id = a.id
												  AND maxbasic.earned IS NULL
			LEFT OUTER JOIN action_scores maxoverride ON maxoverride.action_id = a.id
												     AND maxoverride.earned = ifnull(a.alliance_max, a.max)
	 WHERE t1.team_number = " . Sql::val($us) . "
	   $secondTeam
	 GROUP BY team, heading
	 ORDER BY team, order_by
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
			'min_points' => 0,
			'avg_points' => 0,
			'max_points' => 0,
			'headings' => [],
		];
	}
	$teams[ $team['team'] ]['min_points'] += min($team['min_points'],$team['possible_points']);
	$teams[ $team['team'] ]['avg_points'] += min($team['avg_points'],$team['possible_points']);
	$teams[ $team['team'] ]['max_points'] += min($team['max_points'],$team['possible_points']);
	$teams[ $team['team'] ]['headings'][ $team['heading'] ] = [
		'type' => $team['type'],
		'min_earned' => min($team['min_earned'],$team['possible_earned']),
		'avg_earned' => min($team['avg_earned'],$team['possible_earned']),
		'max_earned' => min($team['max_earned'],$team['possible_earned']),
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
    .meter {
        background-color: aqua;
        position: absolute;
        height: 1em;
        z-index: 0;
        top: 4px;
        left: 0;
    }
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
		<th>Rank</th>
		<th>Alliance</th>
		<?php foreach ($headings as $heading => $max) { ?>
			<?php if ($heading) { ?>
				<th><?= $heading ?></th>
			<?php } ?>
		<?php } ?>
		<th>Score</th>
	</tr>
<?php foreach ($teams as $i => $team) { ?>
	<tr>
		<td>#<?= $i+1 ?></td>
		<td><?= $team['team'] ?></td>
		<?php foreach($team['headings'] as $label => $heading) { ?>
			<?php if ($label) { ?>
				<td style="position:relative;">
					<?php if ($heading['type']=='INT') { ?>
						<div class="min" style="left:0; right:<?=valueToRight($label, $heading['min_earned'])?>"></div>
						<div class="avg" style="left:<?=valueToLeft($label, $heading['min_earned'])?>; right:<?=valueToRight($label, $heading['avg_earned'])?>"></div>
						<div class="max" style="left:<?=valueToLeft($label, $heading['avg_earned'])?>; right:<?=valueToRight($label, $heading['max_earned'])?>"></div>
						<?= round($heading['avg_earned'],1) ?>
					<?php } ?>
					<?php if ($heading['type']=='BOOLEAN') { ?>
						<div class="pct" style="left:0; right:<?=100*((1-($heading['avg_earned'])/3))?>%"></div>
						<?= round(($heading['avg_earned'] * 100)/3) ?>%
					<?php } ?>
				</td>
			<?php } ?>
		<?php } ?>
		<td style="position:relative;">
			<?= round($team['avg_points'],1) ?>
		</td>
	</tr>
<?php } ?>
</table>
