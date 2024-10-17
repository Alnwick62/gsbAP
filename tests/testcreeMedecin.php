<?php
require_once ("../include/class.pdogsb.inc.php");

$lePdo = PdoGsb::getPdoGsb();

var_dump($lePdo->creeMedecin("y@gmail.com", "YJhd4gR#9UAR2pGA"));
