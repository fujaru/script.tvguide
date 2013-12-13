<?php
/**
 * Main EPG Controller
 * 
 * Author : Fajar Chandra
 * Date   : 2013.09.13
 */
 
require_once "config.php";
require_once "scraper/indovision.php";
require_once "scraper/dummy.php";

date_default_timezone_set("Asia/Jakarta");
//header("Cache-Control: no-cache, must-revalidate");

$date = @$_GET['d'];
// Set $date with default value if invalid date specified
if(preg_match("/^\\d{4}\\-\\d{2}\\-\\d{2}$/", $date) == 0)
	$date = date("Y-m-d");
	
$format = @$_GET['format'] == null ? 'plain' : $_GET['format'];

$epg_indovision = new IndovisionScraper($date);
$epg_indovision->scrap();

$result = array_merge($epg_indovision->result);

switch($format) {
	case 'json':
		header("Content-type: text/json");
		echo json_encode($result);
		break;
		
	default:
	case 'plain':
		header("Content-type: text/plain");
		echo print_r($result);
		break;
}
