<?php
include_once ("include/class.pdogsb.inc.php");
include_once ("include/fct.inc.php");

if (!isset($_GET['action'])) {
    $_GET['action'] = 'portabilite';
}
$action = $_GET['action'];

switch ($action) {
    case 'portabilite':
        if (isset($_SESSION['id'])) {
            $sessionId = $_SESSION['id'];
            portabilite($sessionId);
        } else {
            echo "Session ID peut pas.";
        }
        include('vues/v_portabilite.php');
        break;
    default:
        include('vues/v_connexion.php');
        break;
}
?>
