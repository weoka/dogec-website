<?php
/*
 * PHP Pagination Class
 * @author admin@catchmyfame.com - http://www.catchmyfame.com
 * @version 2.0.0
 * @date October 18, 2011
 * @copyright (c) admin@catchmyfame.com (www.catchmyfame.com)
 * @license CC Attribution-ShareAlike 3.0 Unported (CC BY-SA 3.0) - http://creativecommons.org/licenses/by-sa/3.0/
 * MOD BY Pablo Carrau @ 05/23/2013
 * MOD BY Pablo Carrau @ 08/11/2015
 */
class Paginator{
	var $current_pg     = 1;
	var $ipp_array      = array(10,25,50,100,'All');
	var $default_ipp    = null;
	var $start_prevnext = 5;
	var $mid_range      = 7;
	var $var_pg         = 'pg';
	var $var_ipp        = 'pp';
	var $class_normal   = 'Btn2';
	var $style_normal   = '';
	var $class_inactive = '';
	var $style_inactive = 'display:none;';
	var $class_current  = 'Btn2 alt';
	var $style_current  = '';
	var $text_prev      = '&laquo;';
	var $text_next      = '&raquo;';
	var $text_div       = ' ... ';
	var $text_ipp       = 'Per Page:';
	var $text_page      = 'Page #:';
	var $link_null      = 'javascript:void(0);';
	var $disable_seo    = false;
	var $disable_all    = false;

	var $items_per_pg;
	var $items_total;
	var $num_pgs;
	var $low;
	var $limit;
	var $return;
	var $querystring;

	public function __construct() {
        $this->items_per_pg = (!empty($_GET[$this->var_ipp])) ? $_GET[$this->var_ipp]:$this->default_ipp;

		if(!(isset($_GET[$this->var_pg]))){$_GET[$this->var_pg] = 1;}
		if(!(isset($_GET[$this->var_ipp]))){$_GET[$this->var_ipp] = $this->default_ipp;}
    }

	function Paginator()
	{
		$this->items_per_pg = (!empty($_GET[$this->var_ipp])) ? $_GET[$this->var_ipp]:$this->default_ipp;

		if(!(isset($_GET[$this->var_pg]))){$_GET[$this->var_pg] = 1;}
		if(!(isset($_GET[$this->var_ipp]))){$_GET[$this->var_ipp] = $this->default_ipp;}
	}

	function paginate()
	{
		if (!in_array('All', $this->ipp_array)) {
			$this->disable_all = true;
		}

		//if(!isset($this->default_ipp)) $this->default_ipp = 25;
		if($_GET[$this->var_ipp] == 'All')
		{
			$this->num_pgs = 1;
			//$this->items_per_pg = $this->default_ipp;
		}
		else
		{
			if(!is_numeric($this->items_per_pg) OR $this->items_per_pg <= 0) $this->items_per_pg = $this->default_ipp;
			$this->num_pgs = ceil($this->items_total/$this->items_per_pg);
		}
		$this->current_pg = (isset($_GET[$this->var_pg])) ? (int) $_GET[$this->var_pg] : 1; // must be numeric > 0
		$prev_pg = $this->current_pg-1;
		$next_pg = $this->current_pg+1;
		if($_GET)
		{
			$args = explode("&",$_SERVER['QUERY_STRING']);
			foreach($args as $arg)
			{
				$keyval = explode("=",$arg);
				if($keyval[0] != $this->var_pg And $keyval[0] != $this->var_ipp) $this->querystring .= "&" . $arg;
			}
		}

		if($_POST)
		{
			foreach($_POST as $key=>$val)
			{
				if($key != $this->var_pg And $key != $this->var_ipp) $this->querystring .= "&$key=$val";
			}
		}

		if (self::is_seo()) {
			if (substr($this->querystring, 0, 1) == '&')
				$this->querystring = '?' . substr($this->querystring, 1);
		}

		if($this->num_pgs > $this->start_prevnext)
		{
			if (!self::is_seo())
				$this->return = ($this->current_pg > 1 And $this->items_total >= $this->start_prevnext) ? "<a style=\"$this->style_normal\" class=\"$this->class_normal\" href=\"" . self::get_url() . "?$this->var_pg=$prev_pg&$this->var_ipp=$this->items_per_pg$this->querystring\">$this->text_prev</a> ":"<span style=\"$this->style_inactive\" class=\"$this->class_inactive\" href=\"" . $this->link_null . "\">$this->text_prev</span> ";
			else
				$this->return = ($this->current_pg > 1 And $this->items_total >= $this->start_prevnext) ? "<a style=\"$this->style_normal\" class=\"$this->class_normal\" href=\"" . self::get_url() . "$prev_pg/$this->items_per_pg/$this->querystring\">$this->text_prev</a> ":"<span style=\"$this->style_inactive\" class=\"$this->class_inactive\" href=\"" . $this->link_null . "\">$this->text_prev</span> ";

			$this->start_range = $this->current_pg - floor($this->mid_range/2);
			$this->end_range = $this->current_pg + floor($this->mid_range/2);

			if($this->start_range <= 0)
			{
				$this->end_range += abs($this->start_range)+1;
				$this->start_range = 1;
			}
			if($this->end_range > $this->num_pgs)
			{
				$this->start_range -= $this->end_range-$this->num_pgs;
				$this->end_range = $this->num_pgs;
			}
			$this->range = range($this->start_range,$this->end_range);

			for($i=1;$i<=$this->num_pgs;$i++)
			{
				if($this->range[0] > 2 And $i == $this->range[0]) $this->return .= $this->text_div;
				// loop through all pgs. if first, last, or in range, display
				if($i==1 Or $i==$this->num_pgs Or in_array($i,$this->range))
				{
					if (!self::is_seo())
						$this->return .= ($i == $this->current_pg And $_GET[$this->var_pg] != 'All') ? "<a title=\"Go To Page $i of $this->num_pgs\" style=\"$this->style_current\" class=\"$this->class_current\" href=\"" . $this->link_null . "\">$i</a> ":"<a style=\"$this->style_normal\" class=\"$this->class_normal\" title=\"Go To Page $i of $this->num_pgs\" href=\"" . self::get_url() . "?$this->var_pg=$i&$this->var_ipp=$this->items_per_pg$this->querystring\">$i</a> ";
					else
						$this->return .= ($i == $this->current_pg And $_GET[$this->var_pg] != 'All') ? "<a title=\"Go To Page $i of $this->num_pgs\" style=\"$this->style_current\" class=\"$this->class_current\" href=\"" . $this->link_null . "\">$i</a> ":"<a style=\"$this->style_normal\" class=\"$this->class_normal\" title=\"Go To Page $i of $this->num_pgs\" href=\"" . self::get_url() . "$i/$this->items_per_pg/$this->querystring\">$i</a> ";
				}
				if($this->range[$this->mid_range-1] < $this->num_pgs-1 And $i == $this->range[$this->mid_range-1]) $this->return .= " ... ";
			}

			if (!self::is_seo()) {
				$this->return .= (($this->current_pg < $this->num_pgs And $this->items_total >= $this->start_prevnext) And ($_GET[$this->var_pg] != 'All') And $this->current_pg > 0) ? "<a style=\"$this->style_normal\" class=\"$this->class_normal\" href=\"" . self::get_url() . "?$this->var_pg=$next_pg&$this->var_ipp=$this->items_per_pg$this->querystring\">$this->text_next</a>\n":"<span style=\"$this->style_inactive\" class=\"$this->class_inactive\" href=\"" . $this->link_null . "\">$this->text_next</span>\n";
				if (!$this->disable_all) {
					$this->return .= ($_GET[$this->var_pg] == 'All') ? "<a style=\"$this->style_current\" class=\"$this->class_current\" style=\"margin-left:10px\" href=\"" . $this->link_null . "\">All</a> \n":"<a style=\"$this->style_normal\" class=\"$this->class_normal\" style=\"margin-left:10px\" href=\"" . self::get_url() . "?$this->var_pg=1&$this->var_ipp=All$this->querystring\">All</a> \n";
				}
			}
			else {
				$this->return .= (($this->current_pg < $this->num_pgs And $this->items_total >= $this->start_prevnext) And ($_GET[$this->var_pg] != 'All') And $this->current_pg > 0) ? "<a style=\"$this->style_normal\" class=\"$this->class_normal\" href=\"" . self::get_url() . "$next_pg/$this->items_per_pg/\">$this->text_next</a>\n":"<span style=\"$this->style_inactive\" class=\"$this->class_inactive\" href=\"" . $this->link_null . "\">$this->text_next</span>\n";
				if (!$this->disable_all) {
					$this->return .= ($_GET[$this->var_pg] == 'All') ? "<a style=\"$this->style_current\" class=\"$this->class_current\" style=\"margin-left:10px\" href=\"" . $this->link_null . "\">All</a> \n":"<a style=\"$this->style_normal\" class=\"$this->class_normal\" style=\"margin-left:10px\" href=\"" . self::get_url() . "1/All/$this->querystring\">All</a> \n";
				}
			}
		}
		else
		{
			for($i=1;$i<=$this->num_pgs;$i++)
			{
				if (!self::is_seo())
					$this->return .= ($i == $this->current_pg) ? "<a style=\"$this->style_current\" class=\"$this->class_current\" href=\"" . $this->link_null . "\">$i</a> ":"<a style=\"$this->style_normal\" class=\"$this->class_normal\" href=\"" . self::get_url() . "?$this->var_pg=$i&$this->var_ipp=$this->items_per_pg$this->querystring\">$i</a> ";
				else
					$this->return .= ($i == $this->current_pg) ? "<a style=\"$this->style_current\" class=\"$this->class_current\" href=\"" . $this->link_null . "\">$i</a> ":"<a style=\"$this->style_normal\" class=\"$this->class_normal\" href=\"" . self::get_url() . "$i/$this->items_per_pg/$this->querystring\">$i</a> ";
			}
			if (!$this->disable_all) {
				if (!self::is_seo()) {
					$this->return .= "<a style=\"$this->style_normal\" class=\"$this->class_normal\" href=\"" . self::get_url() . "?$this->var_pg=1&$this->var_ipp=All$this->querystring\">All</a> \n";
				}
				else {
					$this->return .= "<a style=\"$this->style_normal\" class=\"$this->class_normal\" href=\"" . self::get_url() . "1/All/$this->querystring\">All</a> \n";
				}
			}
		}
		$this->low = ($this->current_pg <= 0) ? 0:($this->current_pg-1) * $this->items_per_pg;
		if($this->current_pg <= 0) $this->items_per_pg = 0;
		$this->limit = ($_GET[$this->var_ipp] == 'All') ? "":" LIMIT $this->low,$this->items_per_pg";
	}
	function display_items_per_page()
	{
		$items = '';
		if(!isset($_GET[$this->var_ipp])) $this->items_per_pg = $this->default_ipp;
		foreach($this->ipp_array as $pp_opt) $items .= ($pp_opt == $this->items_per_pg) ? "<option selected value=\"$pp_opt\">$pp_opt</option>\n":"<option value=\"$pp_opt\">$pp_opt</option>\n";
		if (!self::is_seo())
			return "<span>$this->text_ipp</span>&nbsp;<select onchange=\"window.location='" . self::get_url() . "?$this->var_pg=1&$this->var_ipp='+this[this.selectedIndex].value+'$this->querystring';return false\">$items</select>\n";
		else
			return "<span>$this->text_ipp</span>&nbsp;<select onchange=\"window.location='" . self::get_url() . "1/'+this[this.selectedIndex].value+'/$this->querystring';return false\">$items</select>\n";
	}
	function display_jump_menu()
	{
		$option = '';
		for($i=1;$i<=$this->num_pgs;$i++)
		{
			$option .= ($i==$this->current_pg) ? "<option value=\"$i\" selected>$i</option>\n":"<option value=\"$i\">$i</option>\n";
		}
		if (!self::is_seo())
			return "<span>$this->text_page</span>&nbsp;<select onchange=\"window.location='" . self::get_url() . "?$this->var_pg='+this[this.selectedIndex].value+'&$this->var_ipp=$this->items_per_pg$this->querystring';return false\">$option</select>\n";
		else
			return "<span>$this->text_page</span>&nbsp;<select onchange=\"window.location='" . self::get_url() . "'+this[this.selectedIndex].value+'/$this->items_per_pg/$this->querystring';return false\">$option</select>\n";
	}
	function display_pages()
	{
		return $this->return;
	}

	function is_seo() {
		return (strpos($_SERVER['REQUEST_URI'],'.php') > -1) ? false : (($this->disable_seo) ? false : true);
	}

	function get_url() {
		$strQs   = $_SERVER['QUERY_STRING'];
		$strSelf = $_SERVER['PHP_SELF'];
		$strUri  = $_SERVER['REQUEST_URI'];

		if (self::is_seo()) {
			$strUrl = $strUri;
			$strUrl = strtok($strUrl, '?');
			if (strpos($strQs, 'pg') > -1) {
				$strUrl = dirname($strUrl) . '/';
			}
			if (strpos($strQs, 'pp') > -1) {
				$strUrl = dirname($strUrl) . '/';
			}
		}
		else {
			$strUrl = $strSelf;
		}

		return $strUrl;
	}
}