<?php

require 'LonelyCreeper.php';

$broadcasterList = array(
	"GuardsmanBob",
	"EGJD",
	"Bacon_Donut",
	"AnnieBot",
	"TwitchPlaysPokemon"
);

$creeper = new LonelyCreeper($broadcasterList);
$creeper->beginCreeping();

?>
