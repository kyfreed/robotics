<?php
include_once 'classes/init.php';

$data = Sql::query("
SELECT team_number, match_number, side
       , max(modified_on) - INTERVAL 3 HOUR as last_sent
       , count(case when earned > 0 then 1 else null end) as recorded
  FROM match_team_actions 
  WHERE modified_on > '2018-03-03 14:00'
 GROUP BY team_number, match_number, side
 ORDER BY match_number DESC, side, team_number
");

$PAGE->addHead('<script src="inc/jquery/jquery-3.3.1.js"></script>');
?>
<table style="float:left; margin-right:100px;">
    <tr>
        <th>Team</th>
        <th>Match #</th>
        <th>Side</td>
        <th>Data?</th>
        <th>Sent</th>
    </tr>
    <?php foreach ($data as $record) { ?>
        <tr>
            <td><?= $record['team_number'] ?></td>
            <td><?= $record['match_number'] ?></td>
            <td style="text-align: center;"><?= $record['side'] ?></td>
            <td style="text-align: center;"><?= ($record['recorded'] > 2) ? 'Yup' : '' ?></td>
            <td><?= str_replace(date('Y-m-d '), '', $record['last_sent']) ?></td>
        </tr>
    <?php } ?>
</table>

