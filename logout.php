<?php
session_start();
session_destroy();
header("Location: /eventix/login.php");
exit();
?>
