PDFEmbed
========

[PDFEmbed](https://www.mediawiki.org/wiki/Extension:PDFEmbed) embeds PDF files into a wiki page with the `<pdf>` tag: an uploaded file, a `File:` link, a `{{filepath:}}` call or an external URL.

This repository is the line of PDFEmbed maintained by [Wolfgang Fahl](http://www.bitplan.com/index.php/Wolfgang_Fahl). It started 2022 as a fork of https://gitlab.com/hydrawiki/extensions/PDFEmbed to fix [hydrawiki issue 19](https://gitlab.com/hydrawiki/extensions/PDFEmbed/-/issues/19). Other lines carrying the same name exist, see [issue 27](https://github.com/WolfgangFahl/PDFEmbed/issues/27). Documentation and examples: [PDFEmbed on wiki.bitplan.com](https://wiki.bitplan.com/index.php/PDFEmbed).

Requirements
------------
* MediaWiki 1.35 or newer, tested with 1.43 and PHP 8.3

Installation
------------
Clone into the extensions directory and load the extension in LocalSettings.php:
```bash
cd extensions
git clone https://github.com/WolfgangFahl/PDFEmbed
```
```php
wfLoadExtension('PDFEmbed');
```

Composer installation via `composer.local.json`:

````json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/WolfgangFahl/PDFEmbed"
    }
  ],
  "require": {
    "WolfgangFahl/PDFembed": "*"
  },
  "extra": {
    "merge-plugin": {
      "include": [
        "extensions/*/composer.json",
        "skins/*/composer.json"
      ]
    }
  }
}
````

Usage
-----
```
<pdf>File:Example.pdf</pdf>
<pdf>Example.pdf</pdf>
<pdf width="100%" height="600">{{filepath:Example.pdf|nowiki}}</pdf>
<pdf page="3">https://example.org/path/Example.pdf</pdf>
```
* the tag content is parsed as wikitext first, so templates and parser functions inside it work
* `width` and `height` take pixels or a percentage of the parent, default 800 and 1090
* `page` selects the page shown first; `File.pdf#3` works too
* `iframe="0"` renders an `object` element instead of the default `iframe`
* an empty tag, as the VisualEditor inserts it, shows a placeholder instead of an error

Configuration
-------------
Add to LocalSettings.php below the wfLoadExtension line as needed:
```php
// default size of the container
$wgPdfEmbed['width'] = 800;
$wgPdfEmbed['height'] = 1090;
// iframe (default) or object element
$wgPdfEmbed['iframe'] = true;
// hosts allowed or blocked for external PDFs; an empty white list allows every host not on the black list
$wgPDF['white'] = [];
$wgPDF['black'] = [];
```
The `embed_pdf` right of earlier versions is still declared for sites that configure it; the tag itself does not check it since 3.0.1.

Development
-----------
See [AGENTS.md](AGENTS.md) for the layout, checks and the release procedure. Changes are listed in [CHANGELOG.md](CHANGELOG.md).
