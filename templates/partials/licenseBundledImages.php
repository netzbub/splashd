<?php
/**
 * This file is part of the Splashd app
 * and licensed under the AGPL.
 */

$links = [
    "<a href=\"https://creativecommons.org/licenses/by/4.0/\" rel=\"noreferrer noopener\" target=\"_blank\">CC BY 4.0</a>"
];

print_unescaped($l->t('The images shipped with this app are licensed %s, created by netzbub.', $links));
