# Change log

## v3.0.0

* Make it fully compatible with MediaWiki 1.38. Deal with all deprecated features:
  * Use [`$wgOut->disableClientCache()`](https://doc.wikimedia.org/mediawiki-core/master/php/classOutputPage.html#a65494796ef2a9e3cb0864d8c2185fa70) instead of [`$wgOut->enableClientCache(false)`](https://doc.wikimedia.org/mediawiki-core/master/php/classOutputPage.html#a97ca228e1106736ef2b3b988543af448),
  * Use `RequestContext::getMain()->getUser()` instead of `$wgUser`,
  * Use `MediaWiki\MediaWikiServices::getInstance()->getUserFactory()->newFromName($revUserName)` instead of `User::newFromName($revUserName)`,
  * Use `MediaWiki\MediaWikiServices::getInstance()->getPermissionManager()->userHasRight($user, 'embed_pdf')` instead of `$user->isAllowed('embed_pdf')`,
  * Use `MediaWiki\MediaWikiServices::getInstance()->getRepoGroup()->findFile($filename)` instead of `wfFindFile($filename)`.
* Tidy up the code, and:
  * Remove the unused function `embedObject()` - note the `embed()` function does this when `$iframe=true`,
  * Replace some short names: `$a` with `$anchor`; `$re` with `$matches`.
* According to a filter applied in [`ve.init.mw.ArticleTargetSaver.js`](https://github.com/wikimedia/mediawiki-extensions-VisualEditor/blob/d9e56ef69ac6938417b558dcf1a7f63e8048256d/modules/ve-mw/preinit/ve.init.mw.ArticleTargetSaver.js#L75) (see also [`T65229`](https://phabricator.wikimedia.org/T65229)) the HTML tag `<object>` is not compatible with Visual Editor, so the default value for `$iframe` in the `extension.json` file is switched to `true`.
* Add CSS class `pdf-embed` to the generated (iframe/object) HTML tag.
* Bulgarian Translations provided by Spas Z. Spasov <spas.z.spasov@metalevel.tech>
* Add backwards compatibility. Already tested with MediaWiki versions [1.35](https://bg.trivictoria.org/wiki/Test_PDF_Embed), [1.36](https://vectoria.altclavis.com/wiki/Test_PDF_Embed), [1.38](https://wiki.metalevel.tech/wiki/Embed_PDF_Documents).

## v2.0.2

* Updated extension.json to support MediaWiki 1.30+.

## v2.0.1

* Switched over to iframes to support newer browsers.

## v2.0.0

* Converted to extension registration.
* Forcing a max-width: 100%; on the object container.

## v1.1.2

* Added page parameter.

## v1.1.1

* German Translations provided by kghbln <kontakt@wikihoster.net>

## v1.1

* Fix PHP notices being thrown for some parameters.
* Missing language string.
