<?php
/**
 * This file is part of the Splashd app
 * and licensed under the AGPL.
 */

use OCP\Util;

if (($_['dashboard'] || $_['login']) && ($_['selectedProvider'] != "" || $_['selectedProvider'] != "Nextcloud Image")):

    Util::addStyle('splashd', 'settings');

    ?>

    <div class="section" id="splashd-settings">
        <h2>
            <?php p($l->t('Random Background Images')); ?>
        </h2>
        <p class="settings-hint">
            <?php p($l->t('%s is customized with random background images.', [$_['label']])); ?>
            <?php if ($_['dashboard']) {
                p($l->t('The images will be displayed as the background if you choose "Default Image" as your background.', [$_['label']]));
            } ?>
            <br>
            <br>
            <?php print_unescaped($this->inc('partials/license' . $_['selectedProvider'])); ?>
        </p>
    </div>

<?php endif; ?>
