<?php

/**
 * PDFEmbed
 * PDFEmbed Hooks
 *
 * @author		Alexia E. Smith
 * @license		LGPLv3 http://opensource.org/licenses/lgpl-3.0.html
 * @package		PDFEmbed
 * @link		https://www.mediawiki.org/wiki/Extension:PDFEmbed
 *
 **/

use MediaWiki\MediaWikiServices;

class PDFEmbed
{

    /**
     * Sets up this extensions parser functions.
     *
     * @access public
     * @param
     *            object Parser object passed as a reference.
     * @return boolean true
     */
    static public function onParserFirstCallInit(Parser &$parser): bool
    {
        $parser->setHook('pdf', 'PDFEmbed::generateTag');

        return true;
    }

    /**
     * disable the cache
     *
     * @param Parser $parser
     */
    static public function disableCache(Parser &$parser)
    {
        // see https://gerrit.wikimedia.org/r/plugins/gitiles/mediawiki/extensions/MagicNoCache/+/refs/heads/master/src/MagicNoCacheHooks.php
        global $wgOut;
        $parser->getOutput()->updateCacheExpiry(0);

        if (method_exists($wgOut, 'disableClientCache')) {
            $wgOut->disableClientCache();
        } else {
            $wgOut->enableClientCache(false);
        }
    }

    /**
     * remove the File: prefix depending on the language or in english default form
     *
     * @param
     *            filename - the filename for which to fix the prefix
     * @return    string - the filename without the File: / Media: or i18n File/Media prefix
     */
    static public function removeFilePrefix($filename): string
    {
        $mwServices = MediaWikiServices::getInstance();

        if (method_exists($mwServices, "getContentLanguage")) {
            $contentLang = $mwServices->getContentLanguage();

            # there are four possible prefixes: 'File' and 'Media' in English and in the wiki's language
            $ns_media_wiki_lang = $contentLang->getFormattedNsText(NS_MEDIA);
            $ns_file_wiki_lang  = $contentLang->getFormattedNsText(NS_FILE);

            if (method_exists($mwServices, "getLanguageFactory")) {
                $langFactory = $mwServices->getLanguageFactory();
                $lang = $langFactory->getLanguage('en');
                $ns_media_lang_en = $lang->getFormattedNsText(NS_MEDIA);
                $ns_file_lang_en  = $lang->getFormattedNsText(NS_FILE);
                $filename = preg_replace("/^($ns_media_wiki_lang|$ns_file_wiki_lang|$ns_media_lang_en|$ns_file_lang_en):/", '', $filename);
            } else {
                $filename = preg_replace("/^($ns_media_wiki_lang|$ns_file_wiki_lang):/", '', $filename);
            }
        }
        return $filename;
    }

    /**
     * get the target of the tag from the html the parser made of its content:
     * the href of the first anchor, since the parser turns a bare or linked url
     * into an anchor and may wrap it in a paragraph, else the text without tags
     *
     * @param string $html - the html the parser produced for the tag content
     * @return string - the url or file name the tag points at
     */
    static public function targetOfHtml(string $html): string
    {
        $target = $html;
        if (preg_match('~<a\b[^>]*\bhref\s*=\s*"([^"]*)"~i', $html, $matches)) {
            $target = html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        } else {
            $target = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
        return trim($target);
    }

    /**
     * get a size for the iframe or object: a number of pixels or a percentage
     * of the parent, see https://github.com/WolfgangFahl/PDFEmbed/issues/6
     *
     * @param string $sizeStr - the size as given, e.g. 800, 800px or 100%
     * @return string|null - the size to use in the html attribute, null if invalid
     */
    static public function size(string $sizeStr): ?string
    {
        $size = null;
        $sizeStr = trim($sizeStr);
        if (preg_match('~^(\d+)(px)?$~', $sizeStr, $matches)) {
            $size = $matches[1];
        } elseif (preg_match('~^(\d{1,3})%$~', $sizeStr, $matches)) {
            $size = $matches[1] . '%';
        }
        return $size;
    }

    /**
     * Generates the PDF object tag.
     *
     * @access public
     * @param string|null $obj - the tag content: a file name, a File: link or a url
     * @param array $args - the attributes of the tag: width, height, page, iframe
     * @param Parser $parser - the parser
     * @param PPFrame $frame - the frame the tag is parsed in
     * @return string HTML
     */
    static public function generateTag(?string $obj, array $args, Parser $parser, PPFrame $frame): string
    {
        global $wgPdfEmbed, $wgPDF;
        // disable the cache
        PDFEmbed::disableCache($parser);

        // the parser turns the content into html, e.g. an anchor for a url;
        // we only want the target of that html
        $html = $parser->recursiveTagParse($obj ?? '', $frame);
        $target = self::targetOfHtml($html);

        if ($target === '') {
            // an empty tag, e.g. just inserted in the VisualEditor: a placeholder
            // instead of an error, see https://github.com/WolfgangFahl/PDFEmbed/pull/16
            $parser->getOutput()->addModuleStyles(['ext.pdfembed.styles']);
            return Html::element('div', [
                'class' => 'PDFEmbedPlaceholder',
                'title' => wfMessage('embed_pdf_blank_file')->plain()
            ]);
        }

        if (array_key_exists('width', $args)) {
            $widthStr = $parser->recursiveTagParse($args['width'], $frame);
        } else {
            $widthStr = (string) $wgPdfEmbed['width'];
        }

        if (array_key_exists('height', $args)) {
            $heightStr = $parser->recursiveTagParse($args['height'], $frame);
        } else {
            $heightStr = (string) $wgPdfEmbed['height'];
        }

        if (array_key_exists('page', $args)) {
            $page = intval($parser->recursiveTagParse($args['page'], $frame));
        } else {
            $page = 1;
        }

        $width = self::size($widthStr);
        $height = self::size($heightStr);
        if ($width === null) {
            return self::error("embed_pdf_invalid_width", $widthStr);
        } elseif ($height === null) {
            return self::error("embed_pdf_invalid_height", $heightStr);
        }

        if (array_key_exists('iframe', $args)) {
            $iframe = $parser->recursiveTagParse($args['iframe'], $frame);
        } else {
            $iframe = $wgPdfEmbed['iframe'];
        }

        # if there are no slashes in the name we assume this
        # might be a pointer to a file
        if (preg_match('~^([^\/]+\.pdf)(#[0-9]+)?$~i', $target, $matches)) {
            # re contains the groups
            $filename = $matches[1];
            if (count($matches) == 3) {
                $page = intval(substr($matches[2], 1));
            }

            $filename = self::removeFilePrefix($filename);
            $pdfFile = MediaWikiServices::getInstance()->getRepoGroup()->findFile($filename);

            if ($pdfFile !== false) {
                $url = $pdfFile->getFullUrl();
                return self::embed($url, $width, $height, $page, $iframe);
            } else {
                return self::error('embed_pdf_invalid_file', $filename);
            }
        } else {
            // parse the given url
            $domain = parse_url($target);

            // check that the parsing worked and retrieve a valid host
            // no relative urls are allowed ...
            if ($domain === false) {
                return self::error("embed_pdf_invalid_url", $target);
            }
            if (!isset($domain['host'])) {
                return self::error("embed_pdf_invalid_relative_domain", $target);
            }

            if (isset($wgPDF)) {
                $black = array_map('strtolower', $wgPDF['black'] ?? []);
                $white = array_map('strtolower', $wgPDF['white'] ?? []);

                $host = strtolower($domain['host']);
                $whitelisted = in_array($host, $white);

                if ($white != [] && !$whitelisted) {
                    return self::error("embed_pdf_domain_not_white", $host);
                }

                if (!$whitelisted && in_array($host, $black)) {
                    return self::error("embed_pdf_domain_black", $host);
                }
            }

            # check that url is valid
            if (filter_var($target, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)) {
                return self::embed($target, $width, $height, $page, $iframe);
            } else {
                return self::error('embed_pdf_invalid_url', $target);
            }
        }
    }

    /**
     * Returns an HTML node for the given file as string.
     *
     * @access private
     * @param string $url - the url to embed
     * @param string $width - width of the iframe, pixels or a percentage
     * @param string $height - height of the iframe, pixels or a percentage
     * @param int $page - page of the pdf file
     * @param bool $iframe - True if an iframe should be returned else an object is returned
     * @return string HTML code for iframe.
     */
    static private function embed($url, $width, $height, $page, $iframe): string
    {
        # secure and concatenate the url
        $pdfSafeUrl = htmlentities($url) . '#page=' . $page;
        # check the embed mode and return a proper HTML element
        if ($iframe) {
            return Html::rawElement('iframe', [
                'class' => 'pdf-embed',
                'width' => $width,
                'height' => $height,
                'src' => $pdfSafeUrl,
                'style' => 'max-width: 100%;'
            ]);
        } else {
            # object mode (default)
            return Html::rawElement('object', [
                'class' => 'pdf-embed',
                'width' => $width,
                'height' => $height,
                'data' => $pdfSafeUrl,
                'style' => 'max-width: 100%;',
                'type' => 'application/pdf'
            ], Html::rawElement(
                'a',
                [
                    'href' => $pdfSafeUrl
                ],
                wfMessage('embed_pdf_load')->plain()
            ));
        }
    }

    /**
     * Returns a standard error message.
     *
     * @access private
     * @param
     *            string Error message key to display.
     * @param
     *            params any parameters for the error message
     * @return string HTML error message.
     */
    static private function error($messageKey, ...$params): string
    {
        return Xml::span(wfMessage($messageKey, $params)->plain(), 'error');
    }
}
