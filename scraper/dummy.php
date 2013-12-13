<?php
/**
 * Jativi Parser for Indovision.tv EPG
 * 
 * Author : Fajar Chandra
 * Date   : 2013.09.13
 */

require_once dirname(__FILE__) . "/../lib/scraper.php";

class DummyScraper extends Scraper {
	
	function __construct($date = null) {
		parent::__construct($date);
		
		$this->src_url = "php://input";
	}
	
	public function scrap() {
		$this->result = array(
			array(
				'i' => "sample-station",
				'c' => 123,
				'n' => "Sample Station",
				'l' => BASE_URL . "asset/station/default.png",
				'p' => array(
					array(
						's' => time(),
						'e' => time() + 1800,
						't' => "Sample Program",
						'd' => "This is a sample program, 30 mins",
						'i' => BASE_URL . "asset/program/default_small.png",
						'l' => BASE_URL . "asset/program/default_big.png",
					),
				),
			),
		);
		
		return $this->result;
	}
}
