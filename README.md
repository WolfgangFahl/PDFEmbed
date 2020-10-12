PDFEmbed
========

This is a "fork" of https://gitlab.com/hydrawiki/extensions/PDFEmbed. It tries to fix
https://gitlab.com/hydrawiki/extensions/PDFEmbed/-/issues/19

[PDFEmbed](https://www.mediawiki.org/wiki/Extension:PDFEmbed) allows Adobe Acrobat PDF files to be embedded into a wiki article using <pdf></pdf> tags. The PDF file extension is automatically added and necessarily default permissions are configured. Future functionality will allow this extension to act as a media handler for PDF files.


Installation
------------
To install this extension, add the following to the end of the LocalSettings.php file:
```php
//PDFEmbed
wfLoadExtension('PDFEmbed');
```

Configuration
---------------------

If the default configuration needs to be altered add these settings to the LocalSettings.php file below the require:
```php
//Default width for the PDF object container.
$wgPdfEmbed['width'] = 800;

//Default height for the PDF object container.
$wgPdfEmbed['height'] = 1090;
```
