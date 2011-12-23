<?php
/** 
 * Add target attribute to external links
 *
 * Based on "External Links" - http://www.jonijnm.es/web/descargas/category/8-external-links.html
 * @Copyright Copyright (C) 2010 - Benjamin, JoniJnm.es
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined( '_JEXEC' ) or die('Restricted access');

jimport( 'joomla.plugin.plugin');
class plgContentExternal_links_target extends JPlugin {

	private $host;
	private $allowed_ext;
	
	function change_external($match)
	{
		if ($this->host == $match[1]) // Same host, so internal link
			return $match[0];
			
		return $this->change($match[0]);	
	}
	
	function change_downloadable($match)
	{
		if (!in_array($match[1], $this->allowed_ext))
			return $match[0];
		return $this->change($match[0]);
	}
	
	
	
	function change($link)
	{
		if (strpos($link, 'target="') !== false) // already has a target?
		{
			if (strpos($link, 'target="') === false)
				$link = preg_replace('/target="[^"]+"/', 'target="_blank"', $link);
		}
		else
			$link = substr($link, 0, -1) . ' target="_blank">';
		
		return $link;
	}

	function onContentPrepare($context, &$row, &$articleParams, $page=0 ) {
	
		// init
		static $paramsComMedia;

		if (!is_object($paramsComMedia)) {
			$paramsComMedia =& JComponentHelper::getParams( 'com_media' );
		}
		$this->allowed_ext = explode(',', $paramsComMedia->get('upload_extensions'));
		$this->host = substr($_SERVER['HTTP_HOST'], 0, 4) == 'www.' ? substr($_SERVER['HTTP_HOST'], 4) : $_SERVER['HTTP_HOST'];

	#var_dump($this);
		if (is_object($row))
			$row->text = $this->process($row->text);
		else
			$row = $this->process($row);
			
		
		return true;
	}
	
	function process($text)
	{
		// External Links	
		if ($this->params->get("external_links"))
		{
			$text = preg_replace_callback('#<a[^>]*?href="https?://(?:www\.)?([^"/>]+)(?:/[^">]*)?"[^>]*>#', array($this, 'change_external'), $text);
		}
		if ($this->params->get("downloadable_links"))
		{
			$text = preg_replace_callback('#<a[^>]*?href="[^">]+\.([a-zA-Z]{3})"[^>]*>#', array($this, 'change_downloadable'), $text); # TODO can only contain preg_quote($this->host) or relative URL
		}
		return $text;
	}
}
?>
