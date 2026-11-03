# AGENTS.md

Project instructions for coding agents and people working on PDFEmbed, the MediaWiki extension that embeds PDF files with the `<pdf>` tag.

## Layout
* `PDFEmbed.hooks.php` the whole extension: the `pdf` tag hook, target extraction, size handling, file and URL embedding, error messages
* `extension.json` the manifest: version, requirements, configuration defaults, the resource module of the placeholder
* `i18n/*.json` messages; `en.json` is the reference, every key used in the code exists there and in `de.json`
* `resources/` the placeholder stylesheet and image for an empty tag
* `CHANGELOG.md` one section per version, newest first
* `README.md` user documentation: installation, usage, configuration

## Conventions
* PHP 8.1 or newer and MediaWiki 1.35 or newer; no optional parameter before a required one in a signature
* use MediaWikiServices, never deprecated globals except `$wgOut` in disableCache
* every error leaves the tag as a `span.error` with a message from i18n, never a bare message key
* the tag content is parsed as wikitext before use; the target is the href of the first anchor, else the stripped text
* no permission check in the tag; the `embed_pdf` right stays declared for compatibility only
* no site specific hosts, paths or credentials in the repository

## Checks
There is no test suite yet. Before a commit:
```bash
php -l PDFEmbed.hooks.php
python3 -c "import json,sys; [json.load(open(f)) for f in sys.argv[1:]]" extension.json i18n/*.json
```
On a MediaWiki 1.43 instance the examples of https://wiki.bitplan.com/index.php/PDFEmbed cover the file, filepath and external URL cases.

## Issues and releases
* issues and pull requests live on https://github.com/WolfgangFahl/PDFEmbed; each release has a milestone
* a fix references its issue in the commit message
* release: version in `extension.json`, section in `CHANGELOG.md`, tag `x.y.z`, GitHub release with the changelog section
* related lines of the extension and the open merge question: https://github.com/WolfgangFahl/PDFEmbed/issues/27
