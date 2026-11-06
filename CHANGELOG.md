# Changelog

## 0.2.1 - 2026-11-06

### Fixed
- Logging out ended on an error page. The logout route renders a login
  template, and the app asked that event whether somebody was logged in.
  Only the event of the regular rendering path answers that question, so
  Nextcloud aborted with "Call to undefined method
  BeforeLoginTemplateRenderedEvent::isLoggedIn()". The question is now put
  only where it can be answered.

## 0.2.0 - 2026-11-04

### Added
- The colour of the tint can be chosen. White is the default, so a low
  vibrancy lightens the image and the full one leaves a plain white
  background.

### Changed
- The tint no longer borrows the instance colour from the Theming app.

## 0.1.3 - 2026-11-04

### Fixed
- No setting on the admin page could be saved: the server refused every
  write with HTTP 412 and logged nothing, because the request carried no
  token. Nextcloud wraps `window.fetch` and, when called with a ready-made
  `Request` object, replaces its headers with its own. The URL and the
  options are passed separately now.

## 0.1.2 - 2026-11-04

### Fixed
- The settings page addressed its own save endpoint by absolute URL. Behind a
  reverse proxy that URL can carry a different host or scheme than the page
  itself, in which case the browser treats the save as a foreign request and
  sends no session cookie with it, so the server refuses it. The address is
  relative now.

### Changed
- The failure marker on the settings page now names the reason, for example
  "Failed (HTTP 412)", and stays legible for a few seconds. Without it the
  cause of a refused write could only be read from the browser console.

## 0.1.1 - 2026-11-04

### Fixed
- The settings page reported "Saved" even when the server had refused the
  request, because only the answer body was looked at and never its status.
  The status is now checked, and every answer carries the value as it stands
  in the configuration afterwards, which the page puts back into the field.
  A write that does not take hold is therefore visible at once.
- The dashboard background stayed empty once a user had picked anything but
  the default under Appearance. Only a background image of the user's own now
  takes precedence; a plain background colour no longer suppresses the app.
- The tint was dropped without a trace when the theming colour was not in the
  long hexadecimal form, because one empty colour channel makes the whole CSS
  rule invalid. Short form and unusable values are now handled.

## 0.1.0 - 2026-11-03

First release of the Splashd fork (app id `splashd`), based on Splash 3.2.0
from the Nextcloud repository.

### Added
- Image source "own folder": images are read from a folder of this Nextcloud,
  addressed by account and path. A team folder path works too.
- Image source "images shipped with the app": 30 photos in `img/samples`,
  licensed CC BY 4.0, used as the default so a fresh install shows something.
  Each file carries its licence in its own metadata and nothing else: no
  camera data, no capture time, no GPS coordinates, no editing history. More
  images are shipped than fit into the pool, so every refill draws a fresh
  random selection from them.
- Image source "Openverse", restricted to CC0 and Public Domain Mark. The
  server downloads the images once a day; no key is needed.
- Pool of pre-scaled images per source, so the background can change on every
  visit. New setting for "on every visit" or "once a day".
- Images are scaled to at most 2560x1600 and re-encoded as JPEG, which strips
  EXIF, XMP and IPTC.

### Changed
- Target Nextcloud 35, PHP 8.3 to 8.5.
- `appinfo/info.xml` now validates against the app schema and carries the SPDX
  licence identifier required for apps targeting server 31 or higher.
- The public image endpoint serves only the scaled copies from the app data
  and sends `Cache-Control: no-store` in per-visit mode.
- Controller annotations replaced by PHP attributes where touched.

### Removed
- The remote image sources Unsplash, WallhavenCC, Wikimedia Commons,
  Wikimedia Picture of the Day and Bing Picture of the Day, together with the
  API token setting. Their terms either forbid this kind of use or grant no
  reuse rights, and all of them exposed the visitors of the login page to a
  third party.
- The content security policy no longer needs to allow any foreign domain.


## 3.1.0 - 2025-05-20
### What's Changed
* Add NC 31 support by @cociweb in https://github.com/nextcloud/unsplash/pull/165
* Add Bing Daily Wallpaper by @brucetruth in https://github.com/nextcloud/unsplash/pull/155

### Fixed
* fix(settings): swap deprecated `style()` for `addStyle()` + fix for missing `user.js` resource by @joshtrichards in https://github.com/nextcloud/unsplash/pull/158
* fix(provider): Hide verbose curl output behind `debug` by @joshtrichards in https://github.com/nextcloud/unsplash/pull/162

### Other
* chore(routes): eliminate usage of deprecated `registerRoutes()` by @joshtrichards in https://github.com/nextcloud/unsplash/pull/157
* refactor: Use constructor property promotion by @joshtrichards in https://github.com/nextcloud/unsplash/pull/159
* chore: Move to IBootstrap and refactor Application.php by @joshtrichards in https://github.com/nextcloud/unsplash/pull/160

### New Contributors
* @joshtrichards made their first contribution in https://github.com/nextcloud/unsplash/pull/157
* @cociweb made their first contribution in https://github.com/nextcloud/unsplash/pull/165
* @brucetruth made their first contribution in https://github.com/nextcloud/unsplash/pull/155

**Full Changelog**: https://github.com/nextcloud/unsplash/compare/v3.0.3...v3.1.0

## 3.0.3 - 2024-11-09
### What did we do this time?
- Introduced compatibility with nextcloud 30
- Changed default searchterms to nature and colorful
- Improve handling of removed providers or missconfigurations

### Known Issues:
- Preview does not always work

## 3.0.2 - 2024-10-06
### What did we do this time?
- Introduced compatibility with nextcloud 30
- Changed default searchterms to nature and colorful
- Improve handling of removed providers or missconfigurations

### Known Issues:
- Preview does not always work

## 3.0.1 - 2024-09-07
### What did we do this time?
- Introduced compatibility with nextcloud 29
- Fix Login-Route (@Bl4DEx)
- Use Wikimedia as a default provider if the existing one is missing or none choosen

### Known Issues:
- Preview does not always work

## 3.0.0 - 2024-08-31
### What did we do this time?
- Introduced compatibility with nextcloud 28
- Introduced image caching and new image sources
- Added Wikimedia
- Added Wallhaven.cc
- Added support for the new unsplash api (api token required)

### Known Issues:
- Preview does not always work

## 2.2.1 - 2023-06-21
### What did we do this time?
- Fixed Broken Background-Key for Nextcloud 27 Compatibility.

### Known Issues:
- Blur is not beeing applied on dashbard

## 2.2.0 - 2023-02-12
### Added
- Allow an admin to highlight privacy and data protection links on login-screen for better visibility

## 2.1.1 - 2022-11-07
### Fixed
- Fixed Image beeing to dark when tinting was applied

## 2.1.0 - 2022-11-07
### Added
- Added Transifex-Translations - Thanks @p-bo, @rakekniven, @nickvergessen and everyone who added translations!
### Removed
- Removed specific Settings for users. They are now automatically integrated into the new Backgrounds!
- Removed Support for PHP 7.4


## 2.0.1 - 2022-10-04
### Fixed
- Fixed incompatibility with Php 7.3 and below
- Fixed missing theming for totp - Thanks @skjnldsv !

## 2.0.0 - 2022-10-03
### Added
- Support for Wallhaven and Wikimedia
- Blurring
- Tinting
- Added Czech Translation (Thanks @p-bo!)


## 1.2.5 - 2022-05-08
### Changed
- Update French Translation (Thanks @ijeantet!)
- Server Compatibility (Nextcloud 24)

## 1.2.4 - 2021-11-30
### Changed
- Server Compatibility (Nextcloud 23)

## 1.2.3 - 2021-07-11
### Changed
- Update Polish Translation (Thanks @Valdnet!)
- Server Compatibility (Nextcloud 22)

## 1.2.2 - 2021-01-19
### Changed
- Update French Translation (Thanks @ijeantet!)
- Server Compatibility (Nextcloud 21)

## 1.2.1 - 2021-01-19
### Changed
- Use proper splashd-links for dashboard
- Server Compatibility (Nextcloud 20)

## 1.2.0 - 2021-01-16
### Added
- Added the ability to override dashboard backgrounds (#78)
### Changed
- Updated the Settingspage to not use jQuery (#79)
- Rework internal mechanism to push stylesheets (#77)

Thanks @marius-wieschollek!

## 1.1.7 - 2020-10-20
### Changed
- Polish Translation (Thanks @Valdnet!)
- Server Compatibility

## 1.1.6 - 2020-06-05
### Fixed
- external API Calls (Thanks @robinmetral!)(#60)

## 1.1.5 - 2019-10-09
### Changed
- Update Polish Translation (Thanks @Valdnet!)


## 1.1.4 - 2019-10-09
### Changed
- Rename App to Splash
- Server Compatibility


## 1.1.2 - 2018-12-04
### Fixed
- Server Compatibility
- Many improvements to css styles


## 1.1.0 - 2018-04-12
### Added
- Header Background Image
### Changed
- Change the description to fit the app better


## 1.0.6 - 2018-04-10
### Fixed
- Only use Nature Images
