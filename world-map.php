<?php 
error_reporting(E_ALL);

include_once "./includes/stats-base.php";
include_once "./includes/stats-countries.php";

$page_title = "World Map";

$demo = new CountriesClass();
$demo->draw_world_map_from_xml($page_title);

$content = $demo->highlighted_world_map;
$title = $demo->page_title;

include_once "./includes/html/demo-template.inc";
