<?php
/**
 * Jativi Parser for Indovision.tv EPG
 * 
 * Author : Fajar Chandra
 * Date   : 2013.09.13
 */

require_once dirname(__FILE__) . "/../lib/scraper.php";

class IndovisionScraper extends Scraper {
	
	function __construct($date = null) {
		parent::__construct($date);
		
		$this->src_url = "http://www.indovision.tv/schedule";
	}
	
	public function parse($force = false) {
		if($this->html != null && !$force)
			return $this->html;
			
		$html_part = array();
		// Get schedule table
		//preg_match("/<table id=\"schedule_result_table\".+\\/table>/i", str_replace("\n", "", $this->src_html), $html_part);
		//preg_match("/<table id=\"schedule_result_table\".+?\\/table>/i", str_replace("\n", "", $this->src_html), $html_part); // PHP 5.2 jar2.net error
		
		// Replace problematic sequences
		//$html_part[0] = preg_replace("/(title=\"[^=>]*?)\"([^=>]*?)\"([^=>]*?\")/", "$1&quot;$2&quot;$3", $html_part[0]); // quotes in attribute
		//$html_part[0] = preg_replace("/colspan=(\d+)/", "colspan=\"$1\"", $html_part[0]); // missing quotes
		//$html_part[0] = preg_replace("/&(\w*?)([^;\w]|\s)/", "&amp;$1$2", $html_part[0]); // ampersands without &amp;
		//$html_part[0] = preg_replace("/id='chover'/", "class='chover'", $html_part[0]); // duplicate IDs
		//$html_part[0] = preg_replace("/<(.+?)([^\s])\\/>/", "<$1$2 />", $html_part[0]); // no whitespace before short tag closing
		/*$html_part[0] = preg_replace("/<((?!img).+?) .+?>/i", "<$1>", $html_part[0]); // remove attributes, except for <img> tags*/
		/*$html_part[0] = preg_replace("/<(a) .+?>/i", "<$1>", $html_part[0]); // remove attributes on <a> tags */
		
		//$this->html = str_get_html($html_part[0]); // simple_html_dom
		//$this->html = phpQuery::newDocument($html_part[0]);
		$this->html = phpQuery::newDocument($this->src_html);
		echo gettype($this->html);
		return $this->html;
	}
	
	public function scrap($force = false) {
		// Set parameters & load
		$this->set_post_data(array(
			'fchannel' => "",
			'fdate' => $this->date,
			'submit' => "GO",
		));
		$this->load($force);
		$this->parse($force);
		
		// Check if EPG is already scraped
		if($this->result != null && !$force)
			return $this->result;
			
		// Check if EPG cache is available
		if($this->load_result_cache() !== FALSE && !$force)
			return $this->result;
			
		// Get offset time
		$start_time = $this->html['table thead tr:eq(1) th:eq(1)']->text();
		$start_hr = substr($start_time, 0, 2);
		$start_min = substr($start_time, 3, 2);
		$offset = mktime($start_hr, $start_min, 0, $this->get_month(), $this->get_day(), $this->get_year());
		
		// Prepare result
		$this->result = array();
		
		// Get channels
		//$channels = pq('tbody tr', $this->html);
		$channels = $this->html['table#schedule_result_table tbody tr'];
		//echo $channels . "\n\n";
		
		foreach($channels as $ch) {
			$pqch = pq($ch);
			$st_image = $pqch['img']->attr('src');
			$st_id = substr($st_image, strrpos($st_image, "/")+1, strrpos($st_image, ".")-(strrpos($st_image, "/")+1));
			$st_name = $pqch['img']->attr('alt');
			$st_channel = str_replace("ch. ", "", $pqch['span']->text());
			
			$ch_rs = array(
				'i' => $st_id,
				'c' => (int)$st_channel,
				'n' => $st_name,
				'l' => $st_image,
				'p' => array(),
			);
			
			$last_offset = $offset;
			$programs = $pqch['td'];
			foreach($programs as $program) {
				$pqpr = pq($program);
				$duration = $pqpr->attr('colspan') * 60;
				if($pqpr['a']->length() == 0) {
					$last_offset += $duration;
					continue;
				}
				
				$pr_name = $pqpr['a']->text();
				
				array_push($ch_rs['p'], array(
					's' => $last_offset,
					'e' => $last_offset + $duration,
					't' => utf8_encode($pr_name),
					'd' => "",
				));
				$last_offset += $duration;
			}
			
			array_push($this->result, $ch_rs);
		}
		
		$this->save_result_cache();
		return $this->result;
	}
}
