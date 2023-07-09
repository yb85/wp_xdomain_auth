<?php
$PUBPEM_FILE = "./_gen_PUBPEM.php";

session_start();
session_destroy();
if (isset($_GET['full'])) {
    unlink($PUBPEM_FILE);
}
header("Location: ./index.php");
