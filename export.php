<?php
include_once 'classes/init.php';
$PAGE->setTemplate("json");

$data = Sql::query("
SELECT team_number, match_number
       , max(case action_id when 2 then earned else null end) as xbox_on_other_switch
       , max(case action_id when 3 then earned else null end) as xbox_on_our_switch
       , max(case action_id when 4 then earned else null end) as xbox_on_scale
       , max(case action_id when 5 then earned else null end) as crossed_baseline
       , max(case action_id when 6 then earned else null end) as box_on_our_switch
       , max(case action_id when 7 then earned else null end) as box_on_scale
       , max(case action_id when 8 then earned else null end) as xbox_thru_exchange
       , max(case action_id when 9 then earned else null end) as climbed
       , max(case action_id when 10 then earned else null end) as parked
       , max(case action_id when 14 then earned else null end) as xfloor_feeder
       , max(case action_id when 15 then earned else null end) as xportal_feeder
       , max(case action_id when 16 then earned else null end) as xthrows
       , max(case action_id when 17 then earned else null end) as xplaces
       , max(case action_id when 18 then earned else null end) as xpicks_box_on_side
       , max(case action_id when 19 then earned else null end) as fell
       , max(case action_id when 20 then earned else null end) as levitated
  FROM match_team_actions 
  WHERE modified_on > '2018-03-02 14:00'
 GROUP BY team_number, match_number
");

echo 'team_number,match_number,xbox_on_other_switch,xbox_on_our_switch,xbox_on_scale,crossed_baseline,box_on_our_switch,box_on_scale,xbox_thru_exchange,climbed,parked,xfloor_feeder,xportal_feeder,xthrows,xplaces,xpicks_box_on_side,fell,levitated' . "\n";

foreach ($data as $record) {
	echo $record['team_number'] . ',' . $record['match_number'] . ',';
	echo $record['xbox_on_other_switch'] . ',';
	echo $record['xbox_on_our_switch'] . ',';
	echo $record['xbox_on_scale'] . ',';
	echo $record['crossed_baseline'] . ',';
	echo $record['box_on_our_switch'] . ',';
	echo $record['box_on_scale'] . ',';
	echo $record['xbox_thru_exchange'] . ',';
	echo $record['climbed'] . ',';
	echo $record['parked'] . ',';
	echo $record['xfloor_feeder'] . ',';
	echo $record['xportal_feeder'] . ',';
	echo $record['xthrows'] . ',';
	echo $record['xplaces'] . ',';
	echo $record['xpicks_box_on_side'] . ',';
	echo $record['fell'] . ',';
	echo $record['levitated'] . "\n";
}

 header('Content-Type: text/csv; charset=utf-8');
 header('Content-Disposition: attachment; filename=scouting.csv'); 