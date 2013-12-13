<?php
/**
 * Base class for Scraper
 * 
 * Author : Fajar Chandra
 * Date   : 2013.09.13
 */
 
//require_once "simple_html_dom.php";
require_once "phpQuery-onefile.php";
require_once dirname(__FILE__) . "/../config.php";

abstract class Scraper {
	public $src_url;
	public $src_url_context;
	public $src_html;
	public $html;
	public $result;
	public $date;
	
	function __construct($date = null) {
		$this->src_html = null;
		$this->src_post_data = null;
		$this->html = null;
		$this->result = null;
		$this->date = $date != null ? $date : date("Y-m-d");
	}
	
	public function set_post_data($content = array()) {
		$postdata = http_build_query($content);

		$opts = array('http' =>
			array(
				'method'  => 'POST',
				'header'  => 
					"Content-type: application/x-www-form-urlencoded\n". 
					"User-Agent: {$_SERVER['HTTP_USER_AGENT']}",
				'content' => $postdata
			)
		);

		$this->src_url_context = stream_context_create($opts);
	}
	
	public function clear_post_data() {
		$this->src_url_context = null;
	}
	
	public function load($force = false) {
		$cache_file = CACHE_DIR . get_class($this) . "_{$this->date}.html";
		
		// Check cache
		if(file_exists($cache_file) && filemtime($cache_file) >= time() - (7 * 24 * 3600) && !$force) {
			$this->src_html = file_get_contents($cache_file);
		}
		
		// Load fresh data
		if($this->src_html == null || $force) {
			$this->src_html = file_get_contents($this->src_url, false, $this->src_url_context);
			file_put_contents($cache_file, $this->src_html);
		}
		
		return $this->src_html;
	}
	
	public function parse($force = false) {
		if($this->html != null && !$force)
			return $this->html;
		
		//$this->html = str_get_html($this->src_html); // simple_html_dom
		$this->html = phpQuery::newDocument($this->src_html);
		return $this->html;
	}
	
	public function load_result_cache() {
		$cache_file = CACHE_DIR . get_class($this) . "_{$this->date}.json";
		// Load cache file
		if(file_exists($cache_file) && filemtime($cache_file) >= time() - (7 * 24 * 3600)) {
			$this->result = json_decode(file_get_contents($cache_file));
			return $this->result;
		}
		else {
			return FALSE;
		}
	}
	
	public function save_result_cache() {
		$cache_file = CACHE_DIR . get_class($this) . "_{$this->date}.json";
		return file_put_contents($cache_file, json_encode($this->result));
	}
	
	public function get_day() {
		return substr($this->date, 8, 2);
	}
	
	public function get_month() {
		return substr($this->date, 5, 2);
	}
	
	public function get_year() {
		return substr($this->date, 0, 4);
	}
	
}
