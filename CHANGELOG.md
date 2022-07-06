# Change log

## v3.0.0

* Make it fully compatible with MediaWiki 1.38 - deal with all deprecated features:
  * Use `RequestContext::getMain()->getUser()` instead of `$wgUser`,
  * Use `MediaWiki\MediaWikiServices::getInstance()->getUserFactory()->newFromName($revUserName)` instead of `User::newFromName($revUserName)`,
  * Use `MediaWiki\MediaWikiServices::getInstance()->getPermissionManager()->userHasRight($user, 'embed_pdf')` instead of `$user->isAllowed('embed_pdf')`,
  * Use `MediaWiki\MediaWikiServices::getInstance()->getRepoGroup()->findFile($filename)` instead of `wfFindFile($filename)`.
* Tidy up rhe code and remove unused function `embedObject()` (note the `embed()` function does this by default).
* Bulgarian Translations provided by Spas Z. Spasov <spas.z.spasov@gmail.com>

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
