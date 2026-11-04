<?php
/**
 * This file is part of the Splashd app
 * and licensed under the AGPL.
 */

$links = [
    "<a href=\"https://openverse.org\" rel=\"noreferrer noopener\" target=\"_blank\">Openverse</a>",
    "<a href=\"https://creativecommons.org/publicdomain/zero/1.0/\" rel=\"noreferrer noopener\" target=\"_blank\">CC0</a>",
    "<a href=\"https://creativecommons.org/publicdomain/mark/1.0/\" rel=\"noreferrer noopener\" target=\"_blank\">Public Domain Mark</a>"
];

print_unescaped($l->t('Images are taken from %1$s, restricted to %2$s and %3$s, which require no attribution. The server downloads them once a day; the visitors\' browsers never contact Openverse.', $links));
print_unescaped(' ');
print_unescaped($l->t('This app is made using the Openverse API but is not endorsed or certified by Openverse.'));
