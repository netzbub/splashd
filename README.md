# Splashd — changing Nextcloud backgrounds from your own folder

<p align="center">
  <img src="https://img.shields.io/github/v/tag/netzbub/splashd?label=version&color=blue" alt="version">
  <img src="https://img.shields.io/github/license/netzbub/splashd?color=olive&cacheSeconds=3600" alt="license">
  <img src="https://img.shields.io/github/last-commit/netzbub/splashd?color=blueviolet" alt="last commit">
  <img src="https://img.shields.io/github/issues/netzbub/splashd?color=yellow" alt="open issues">
  <img src="https://img.shields.io/badge/Nextcloud-35-0082C9?logo=nextcloud&logoColor=white" alt="Nextcloud 35">
  <img src="https://img.shields.io/badge/PHP-8.3--8.5-777BB4?logo=php&logoColor=white" alt="PHP 8.3 to 8.5">
</p>

Shows a changing background image on the Nextcloud login page and dashboard.

🇩🇪 **Deutsche Fassung: [README.de.md](README.de.md)**

This is a fork of the Nextcloud app [Splash](https://github.com/nextcloud/unsplash)
(app id `unsplash`), brought up to Nextcloud 35 and rebuilt around one idea: the
background must not cost the visitors of a login page their IP address.

## What is different from the original

* **Own folder as image source.** Point the app at a folder of this Nextcloud
  and it uses the images in it.
* **No third-party requests from the browser.** The original put the image URL
  of Unsplash, Wallhaven, Wikimedia or Bing straight into the login
  stylesheet, so every visitor's browser contacted those servers before anyone
  had logged in. Here the server fetches and scales the images, and the login
  page only ever loads from this instance.
* **The remote sources were removed.** Unsplash and Pexels forbid wallpaper
  applications in their API terms, Wallhaven grants no reuse rights at all,
  and Bing images are not licensed for redistribution. The one source left is
  Openverse, limited to CC0 and Public Domain Mark, which need no attribution.
  This app is made using the Openverse API but is not endorsed or certified
  by Openverse.
* **A pool instead of one image.** The provider prepares a set of scaled
  images; the background can therefore change on every single visit, not only
  once a day.
* **Images are re-encoded**, which also strips EXIF, XMP and IPTC — no camera
  model, no GPS position and no author field from the original files is handed
  out.

## Image sources

| Source | Needs a key | Licence |
|:---|:---|:---|
| Own folder | no | yours |
| Images shipped with the app | no | [CC BY 4.0](img/samples/LICENSE.md), netzbub |
| [Openverse](https://openverse.org) | no | CC0 and Public Domain Mark only |
| Login background from Theming | no | yours |

## Settings

Administration settings > Theming, section "Splashd":
<https://your.cloud/settings/admin/theming>

For the own folder, two values are needed: an **account** and a **path** inside
that account's files. A team folder is mounted into the file view of every
member, so pointing the path at a team folder keeps the image source
independent of a single person's own files — as long as the configured account
stays a member of it.

JPEG, PNG and WebP are read, everything else in the folder is ignored. If the
folder is missing or empty, the login background from
Administration settings > Theming is used instead.

## How the images are served

A background job refills the pool once a day. `/apps/splashd/api/image` then
serves one image out of that pool: a random one on every call, or the same one
all day, depending on the setting. The endpoint hands out nothing but the
scaled copies — no file names, no paths, no listing of the source folder.

## Requirements

Nextcloud 35, PHP 8.3 to 8.5, the PHP GD extension for scaling.

## Licence

The code is released under the terms of the [AGPLv3](LICENSE.md), as in the
original. Copyright notices of the original authors are kept, changes are
marked as such in the file headers.

The photographs in `img/samples/` are **not** covered by the AGPL. They are
licensed [CC BY 4.0](img/samples/LICENSE.md) with attribution to netzbub.
