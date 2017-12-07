<?php
/**
 * @version    SVN: <svn_id>
 * @package    PdfEmbed
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2, or later.
 */

// No direct access.
defined('_JEXEC') or die();

// Load language file for plugin frontend.
$lang = JFactory::getLanguage();
$lang->load('plg_content_pdf_embed', JPATH_ADMINISTRATOR);

/**
 * Class for Plg_pdf_embed
 *
 * @package  PdfEmbed
 * @since    1.0
 */
class PlgContentPdf_Embed extends JPlugin
{
	/**
	 * takes the parameter for pdf
	 *
	 * @param   string   $context  The context of the content being passed to the plugin.
	 *
	 * @param   object   $row      The article object.  Note $article->text is also available
	 * @param   object   $params   The article params
	 * @param   integer  $page     The 'page' number
	 *
	 * @return   html for the pdf
	 *
	 * @since   1.0
	 */
	public function onContentPrepare($context, $row, $params, $page = 0)
	{
		$document = JFactory::getDocument();
		$document->addStyleSheet(JURI::base() . "plugins/content/pdf_embed/assets/css/style.css");

		$regex  = "#{pdf[\=|\s]?(.+)}#s";
		$regex1 = '/{(pdf=)\s*(.*?)}/i';

		// Find all instances of mambot and put in $matches
		preg_match_all($regex1, $row->text, $matches);

		// Number of mambots
		$count = count($matches[0]);

		for ($i = 0; $i < $count; $i++)
		{
			$r  = str_replace('{pdf=', '', $matches[0][$i]);
			$r  = str_replace('}', '', $r);
			$ex = explode('|', $r);
			$ploc = $ex[0];
			$w   = $ex[1];
			$isWidthInPer = strpos($w, '%');
			$isWidthInPix = strpos($w, 'px');

			// Check if width is in percentage and pixel
			if (!$isWidthInPer && !$isWidthInPix)
			{
				$w = $w . 'px';
			}

			$h = $ex[2];
			$isHeightInPer = strpos($h, '%');
			$isHeightInPix = strpos($h, 'px');

			// Check if width is in percentage and pixel
			if (!$isHeightInPer && !$isHeightInPix)
			{
				$h = $h . 'px';
			}

			if (isset($ex[3]))
			{
				$viewer      = $ex[3];
				$replace   = $this->plg_pdfembed_replacer($ploc, $w, $h, $viewer);
				$row->text = str_replace('{pdf=' . $ex[0] . '|' . $ex[1] . '|' . $ex[2] . '|' . $ex[3] . '}', $replace, $row->text);
			}
			else
			{
				$viewer    = $this->params->def('viewer', 'native');
				$replace   = $this->plg_pdfembed_replacer($ploc, $w, $h, $viewer);
				$row->text = str_replace('{pdf=' . $ex[0] . '|' . $ex[1] . '|' . $ex[2] . '}', $replace, $row->text);
			}
		}

		return true;
	}

	/**
	 * Gets  the pdf in site article
	 *
	 * @param   string   $ploc    Takes the address of pdf location
	 * @param   integer  $w       Takes width of pdf
	 * @param   integer  $h       Takes height of pdf
	 * @param   string   $viewer  Takes the view user want to use
	 *
	 * @return pdf in the article
	 */
	public function plg_pdfembed_replacer($ploc, $w, $h, $viewer)
	{
		switch ($viewer)
		{
			case "google":
				return '<div class ="">
							<iframe src="http://docs.google.com/gview?url=' . $ploc . '&embedded=true" style="width:' . $w . '; height:' . $h . ';"frameborder="1">
							</iframe>
						</div>';

			break;

			case "pdfjs":
				return '<div class ="">
							<iframe src="'
							. JUri::root(true) . '/plugins/content/pdf_embed/assets/viewer/pdfjs/web/viewer.html?file=' . $ploc . '
							" style="width:' . $w . '; height:' . $h . ';"frameborder="1">
							</iframe>
						</div>';
			break;

			default:
			case "native":
				return '<div class ="embed-container native-embed-container">
				<embed src="' . $ploc . '" style="width:' . $w . '; height:' . $h . ';"frameborder="1"/></div>';
			break;
		}
	}
}
