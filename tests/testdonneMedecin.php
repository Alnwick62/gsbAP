<?php
require_once ("../include/class.pdogsb.inc.php");
$pdoGsb = PdoGsb::getPdoGsb();
$medecin = $pdoGsb->donneMedecin(3);
var_dump($medecin);
?>
