<?php include('admin/error.inc'); ?>
<?php
session_start();
session_destroy();
header('Location: login.php');
exit;
?>
