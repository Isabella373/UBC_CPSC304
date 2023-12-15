<?php
session_start();
session_destroy();
header('Location: job_portal.php');
exit();
?>