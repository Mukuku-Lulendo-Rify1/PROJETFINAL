<?php
session_start();
session_destroy();
header('Location: conn.php');
exit;
?>