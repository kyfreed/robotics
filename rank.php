<?php
$loggedOutOk = true;
include_once 'classes/init.php';

$PAGE->addHead('<script src="inc/jquery/jquery-3.3.1.js"></script>');



$ahead = array();
$data = Sql::query("
SELECT team_number, taken,
             round(avg(CASE WHEN recent <=2 THEN boxes_moved ELSE NULL END), 1) as boxes_score,
             count(*) as num_matches,
             round(avg(climbed)*100) as climbed_pct,
             round(avg(crossed_baseline)*100) as baseline_pct,
			 max(scale) as max_scale,
			 avg(scale) as avg_scale,
			 max(exchange) as max_exchange,
			 avg(exchange) as avg_exchange
  FROM (
	SELECT mta.team_number, match_number, max(t.taken) as taken
	       , max(CASE a.id WHEN 5 THEN earned ELSE null END) as crossed_baseline
	       , max(CASE a.id WHEN 9 THEN earned ELSE null END) as climbed
	       , max(CASE a.id WHEN 4 THEN earned ELSE null END) as scale
	       , max(CASE a.id WHEN 8 THEN earned ELSE null END) as exchange
	       , sum(case when a.name like '%[]%' then earned else null end)
	         + sum(case WHEN a.id IN (6,7) then earned else null end) as boxes_moved
	       , floor((SELECT ifnull(count(DISTINCT mta2.match_number),0)
	            FROM match_team_actions mta2
	           WHERE mta2.team_number = mta.team_number
	             AND mta2.match_number > mta.match_number)/3)+1 as recent
	  FROM match_team_actions mta JOIN actions a ON mta.action_id = a.id
               LEFT OUTER JOIN taken t ON mta.team_number = t.team_number
	 GROUP BY team_number, match_number
	 ORDER BY mta.team_number, match_number
   ) as x
GROUP BY team_number, taken
ORDER BY boxes_score desc, climbed_pct desc, baseline_pct desc
");
?>
<style>
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
    }
    table td {
        padding: 3px;
    }

</style>
<table style="float:left;">
    <tr>
        <td>Taken</td>
        <td>#</td>
        <th>Team</th>
        <th>Box score</th>
        <th>Climb</th>
        <th>Cross Line</th>
        <th>--Scale--</th>
        <th>-Exchange-</th>
    </tr>
    <?php
    foreach ($data as $i => $record) {
        if (array_search($record['team_number'], $ahead) !== false) {
            //echo 'style="background-color:pink"';
            continue;
        }
        ?>	
        <tr>
            <td><input type="checkbox" value=<?= $record['team_number'] ?> class="taken"
                       <?= $record['taken'] == "1" ? "checked" : "" ?>></td>
            <td><?= $i + 1 ?></td>
            <td><?= $record['team_number'] ?></td>
            <?php
            if ($record['taken'] == "1") {
                for ($i = 0; $i < 5; $i++) {
                    ?>
                    <td></td>
                    <?php
                }
            } else {
                ?>
                <td style="text-align: center;"><?= $record['boxes_score']?>
                    &nbsp;*<?= $record['num_matches'] ?>
                </td>
                <td style="text-align: center; position: relative;">
                    <div class="meter" style="width:<?= $record['climbed_pct'] ?>%">
                        <?= $record['climbed_pct'] ?>%
                    </div>
                </td>
                <td style="text-align: center; position: relative">
                    <div class="meter" style="width:<?= $record['baseline_pct'] ?>%">
                        <?= $record['baseline_pct'] ?>%
                    </div>
                </td>
                <td style="text-align: center; position: relative">
                    <div class="meter" style="width:<?= $record['avg_scale'] * 6 ?>px; border-right:<?= $record['max_scale'] * 6 ?>px solid tan;">
                        <?= round($record['avg_scale'], 1) ?>
                    </div>
                </td>
                <td style="text-align: center; position: relative">
                    <div class="meter" style="width:<?= $record['avg_exchange'] * 4 ?>px; border-right:<?= $record['max_exchange'] * 4 ?>px solid tan;">
                        <?= round($record['avg_exchange'], 1) ?>
                    </div>
                </td>
            </tr>
            <?php
        }
    }
    ?>
</table>
<script>
    $(document).ready(function () {
        $("input.taken").change(function () {
            $.post("taken.php", {team_number: this.value, taken: this.checked ? 1 : 0});
        });
        $.getScript("reload.php?since=<?= @filemtime("_lastsave.txt") ?>");
    });
</script>