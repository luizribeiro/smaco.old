<?php
session_start();
session_unregister("smacoid");
header("Location: index.php?msg=logout");
?>
