<?php

include_once 'classes/init.php';
include_once 'classes/Sql.php';
$PAGE->setTemplate("json");

$q = Sql::query("
    SELECT ifnull(mta.side,'r') as side, a.id as action_id, ifnull(mta.earned, 0) as earned
      FROM actions a LEFT OUTER JOIN match_team_actions mta
                    ON mta.action_id = a.id
                   AND mta.team_number = " . Sql::val($_POST["team"]) . "
                   AND match_number = " . Sql::val($_POST["time"]) . "
     WHERE a.deleted_on IS NULL
");

$result = [];
if ($q) {
    $result["side"] = $q[0]["side"];
    foreach ($q as $a) {
        $result[$a["action_id"]] = $a["earned"];
    }
}
echo json_encode($result);

