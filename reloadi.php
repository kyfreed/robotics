<?php
$started = time();

while (time() < $started + 20) { // Nothing's changed
    $modified = @filemtime("_lastsavei.txt");
    if ($_REQUEST['since'] != $modified) {
        echo "location.reload();";
        exit();
    }
    usleep(20000);
    clearstatcache();
}

echo '$.getScript("reloadi.php?since=' . $_REQUEST['since']. '");';
exit();