<?php
/**
 * PDFEmbed
 * PDFHandler Media Handler Class
 *
 * @author		Alexia E. Smith
 * @license		LGPLv3 http://opensource.org/licenses/lgpl-3.0.html
 * @package		PDFEmbed
 * @link		http://www.mediawiki.org/wiki/Extension:PDFEmbed
 *
 **/

class PDFHandler {
	/**
	 * Sets up this extensions parser functions.
	 *
	 * @access	public
	 * @param	object	Parser object passed as a reference.
	 * @return	boolean	true
	 */
	static public function onParserFirstCallInit(Parser &$parser) {
		$parser->setHook('pdf', 'PDFEmbed::generateTag');

		return true;
	}

	/**
	 * Generates the PDF object tag.
	 *
	 * @access	public
	 * @param	string	Namespace prefixed article of the PDF file to display.
	 * @param	array	Arguments on the tag.
	 * @param	object	Parser object.
	 * @param	object	PPFrame object.
	 * @return	string	HTML
	 */
	static public function generateTag($file, $args = [], Parser $parser, PPFrame $frame) {
		global $wgPdfEmbed, $wgUser;
		$parser->disableCache();

		if (!$wgUser->isAllowed('pdf')) {
			return self::error('embed_pdf_no_permission');
		}

		if (empty($file)) {
			return self::error('embed_pdf_blank_file');
		}

		$file = wfFindFile(Title::newFromText($file));

		$width  = ($args['width'] > 0 ? intval($args['width']) : intval($wgPdfEmbed['width']));
		$height = ($args['height'] > 0 ? intval($args['height']) : intval($wgPdfEmbed['height']));

		if ($file !== false) {
			return self::embed($file, $width, $height);
		} else {
			return self::error('embed_pdf_invalid_file');
		}
	}

	/**
	 * Returns a standard error message.
	 *
	 * @access	public
	 * @param	object	File object.
	 * @param	integer	Width of the object.
	 * @param	integer	Height of the object.
	 * @return	string	HTML object.
	 */
	static private function embed(File $file, $width, $height) {
		return "<object width='{$width}' height='{$height}' style='max-width: 100%;' data='".urlencode($file->getFullUrl())." type='application/pdf'></object>";
	}

	/**
	 * Returns a standard error message.
	 *
	 * @access	public
	 * @param	string	Error message key to display.
	 * @return	string	HTML error message.
	 */
	static private function error($messageKey) {
		return Xml::span(wfMessage($messageKey)->plain(), 'error');
	}
}
