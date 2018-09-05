<?php

if (empty($_REQUEST['code'])) {
	header("Location: /record.php");
} else {
	header("Location: /login.php?code=" . $_REQUEST['code']);
}
