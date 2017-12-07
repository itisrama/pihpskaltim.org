<?php
/**
 * @package		JK Commodity
 * @author		JoomlaKu Team
 * @link		http://www.joomlaku.net
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;

class JKCommodityViewVideotron extends JKView {
	function display($tpl = null) {
		// Get model data.
		$this->jinput->set('tmpl', 'component');
		$this->json		= array_chunk($this->get('Items'), 10);
		$this->items	= reset($this->json);

		//echo "<pre>"; print_r($this->json); echo "</pre>"; die;
		$this->json		= json_encode($this->json);
		$this->city		= $this->get('City');
		$this->markets	= $this->get('Markets');
		$this->date		= $this->get('Dates');
		$this->news		= $this->get('News');

		$document = JFactory::getDocument();
		// Load JS & CSS
		$document->addScript(JK_JS . '/jquery.marquee.min.js');
		$document->addScript(JK_JS . '/videotron.js');
		$document->addStylesheet(JK_CSS . '/videotron.css');
		$document->addStylesheet(JK_CSS . '/videotron.css');

		$this->logo1 = JK_GLOBAL_IMAGES . '/logo_negative.png';
		$this->logo2 = JK_GLOBAL_IMAGES . '/logo_pontianak_negative.png';

		// Display the results
		parent::display($tpl);
	}




}
