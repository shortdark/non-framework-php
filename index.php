<?php 
error_reporting(0);

include_once "./includes/stats-base.php";
include_once "./includes/stats-countries.php";

$page_title="Pie Chart and Table";

$demo = new CountriesClass();
$demo->draw_country_list_from_xml($page_title);

$content = $demo->country_table_pie;
$title = $demo->page_title;

include_once "./includes/html/demo-template.inc";
