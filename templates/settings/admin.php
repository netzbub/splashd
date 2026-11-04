<?php
/**
 * This file is part of the Splashd app
 * and licensed under the AGPL.
 */

script('splashd', 'settings');
style('splashd', 'settings');

$providerLabels = [
    'OwnFolder' => $l->t('Own folder'),
    'BundledImages' => $l->t('Images shipped with the app'),
    'Openverse' => $l->t('Openverse (CC0 and Public Domain)'),
    'NextcloudImage' => $l->t('Login background from Theming'),
];

?>
<div class="section" id="splashd-settings" data-save="<?= $_['saveSettingsUrl'] ?>"
     data-requestUpdate="<?= $_['requestCustomizationUrl'] ?>">
    <h2>
        <?php p($l->t('Splashd: Changing Background Images')); ?>
        <span class="msg success"><?php p($l->t('Saved')); ?></span>
        <span class="msg error"><?php p($l->t('Failed')); ?></span>
    </h2>
    <p class="settings-hint">
        <?php p($l->t('Shows a changing background image on the login page and the dashboard. Images are prepared on the server, so no request from a visitor\'s browser leaves this instance.')); ?>
    </p>
    <form>
        <div class="splashd-checkboxes">
            <input id="splashd-style-login" name="splashd-style-login" data-setting="style/login"
                   type="checkbox" <?= $_['styleLogin'] ? 'checked' : '' ?>>
            <label for="splashd-style-login"><?php p($l->t('Use as login background')); ?></label>
        </div>
        <div class="splashd-checkboxes">
            <input id="splashd-style-dashboard" name="splashd-style-dashboard" data-setting="style/dashboard"
                   type="checkbox" <?= $_['styleDashboard'] ? 'checked' : '' ?>>
            <label for="splashd-style-dashboard"><?php p($l->t('Use as dashboard background')); ?></label>
        </div>

        <div class="splashd-providerselect">
            <label class="splashd-label" for="splash-provider-selection"><?php p($l->t('Image source:')); ?></label>
            <select class="splashd-input" id="splash-provider-selection" data-setting="provider/provider"
                    type="select">
                <?php foreach ($_['availableProvider'] as $value) { ?>
                    <option value="<?php p($value); ?>" <?= $value === $_['selectedProvider'] ? 'selected' : '' ?>>
                        <?php p($providerLabels[$value] ?? $value); ?>
                    </option>
                <?php } ?>
            </select>
            <p class="settings-hint" id="splashd-provider-license">
                <?php print_unescaped($this->inc('partials/license' . str_replace(' ', '', $_['selectedProvider']))); ?>
            </p>
        </div>

        <div id="splashd-folder" class="splashd-providerselect"
             <?= $_['selectedProvider'] === 'OwnFolder' ? '' : 'style="display: none;"' ?>>
            <label class="splashd-label" for="splashd-folder-account"><?php p($l->t('Account:')); ?></label>
            <input class="splashd-input" type="text" id="splashd-folder-account" data-setting="folder/account"
                   value="<?php p($_['folderAccount']); ?>">
            <p class="settings-hint">
                <?php p($l->t('The account whose file view the folder is read through. For a team folder, use an account that is a member of it.')); ?>
            </p>
            <label class="splashd-label" for="splashd-folder-path"><?php p($l->t('Folder:')); ?></label>
            <input class="splashd-input" type="text" id="splashd-folder-path" data-setting="folder/path"
                   value="<?php p($_['folderPath']); ?>" placeholder="Team/Hintergrundbilder">
            <p class="settings-hint">
                <?php p($l->t('Path inside that account\'s files. JPEG, PNG and WebP are read, other files are ignored.')); ?>
                <br>
                <span id="splashd-folder-count"><?php p($l->n('%n image found in the folder.', '%n images found in the folder.', (int)$_['folderCount'])); ?></span>
            </p>
        </div>

        <div id="splashd-keywords" class="splashd-providerselect"
             <?= $_['isCustomizable'] ? '' : 'style="display: none;"' ?>>
            <label class="splashd-label" for="splash-provider-customization"><?php p($l->t('Keywords:')); ?></label>
            <input class="splashd-input" type="text" id="splash-provider-customization"
                   data-setting="provider/customization" value="<?php p($_['selectionCustomization']); ?>">
            <p class="settings-hint">
                <?php p($l->t('Set custom search terms. Separate multiple terms by comma. One of them is picked per refresh.')); ?>
            </p>
        </div>

        <div class="splashd-providerselect">
            <label class="splashd-label" for="splashd-change-mode"><?php p($l->t('Change the image:')); ?></label>
            <select class="splashd-input" id="splashd-change-mode" data-setting="change/mode" type="select">
                <option value="request" <?= $_['changeMode'] === 'request' ? 'selected' : '' ?>>
                    <?php p($l->t('on every visit')); ?>
                </option>
                <option value="daily" <?= $_['changeMode'] === 'daily' ? 'selected' : '' ?>>
                    <?php p($l->t('once a day')); ?>
                </option>
            </select>
            <p class="settings-hint">
                <?php p($l->t('"On every visit" shows a different image for each login and switches off browser caching of the background.')); ?>
            </p>
        </div>

        <div id="splashd-pool" <?= $_['isCached'] ? '' : 'style="display: none;"' ?>>
            <p class="settings-hint">
                <span id="splashd-pool-count"><?php p($l->n('%n image ready on the server.', '%n images ready on the server.', (int)$_['poolCount'])); ?></span>
            </p>
            <input type="button" id="splash-provider-refresh-cache" data-setting="pool/refill"
                   value="<?php p($l->t('Reload images')); ?>">
        </div>

        <p><?php p($l->t('Preview:')); ?></p>
        <img src="<?php p($_['imageURL']); ?>" id="splashd-preview" class="splashd-preview" alt="">

        <br>
        <h2><?php p($l->t('Splashd: Advanced Theming')); ?></h2>
        <p class="settings-hint">
            <?php p($l->t('You can apply effects to your background images.')); ?>
        </p>

        <div class="splashd-checkboxes">
            <input id="splashd-style-tinting" name="splashd-style-tinting" data-setting="style/tint"
                   type="checkbox" <?= $_['styleTint'] ? 'checked' : '' ?>>
            <label for="splashd-style-tinting"><?php p($l->t('Enable Tint')); ?></label>
        </div>
        <div class="splashd-slider">
            <label for="splashd-style-tint-color"><?php p($l->t('Color of the tint')); ?></label>
            <input
                    id="splashd-style-tint-color"
                    type="color"
                    name="splashd-style-tint-color"
                    data-setting="style/tint/color"
                    value="<?php p($_['styleTintColor']); ?>"
                <?= $_['styleTint'] ? '' : 'disabled' ?>
            >
        </div>
        <div class="splashd-slider">
            <label for="splashd-style-color-strength"><?php p($l->t('Set the vibrancy of the color')); ?></label>
            <input
                    id="splashd-style-color-strength"
                    type="range"
                    name="splashd-style-color-strength"
                    data-setting="style/strength/color"
                    min="0"
                    max="100"
                    value="<?= $_['styleStrengthColor'] ? $_['styleStrengthColor'] : 30 ?>"
                <?= $_['styleTint'] ? '' : 'disabled' ?>
            >
        </div>
        <p class="settings-hint">
            <?php p($l->t('White is the default: a low vibrancy lightens the image, the full one leaves a plain white background.')); ?>
        </p>
        <div class="splashd-slider">
            <label for="splashd-style-blur"><?php p($l->t('Set the blur of the image')); ?></label>
            <input type="range" name="splashd-style-blur" data-setting="style/strength/blur" min="0" max="25"
                   value="<?= $_['styleStrengthBlur'] ? $_['styleStrengthBlur'] : 0 ?>">
        </div>

        <br>
        <h2><?php p($l->t('Splashd: High Visibility')); ?></h2>
        <p class="settings-hint">
            <?php p($l->t('You can enable a High Visibility Mode for Legal Reasons. This will highlight the Privacy and Data Protection links on the login screen.')); ?>
        </p>
        <div class="splashd-checkboxes">
            <input id="splashd-style-highvisibility" name="splashd-style-highvisibility"
                   data-setting="style/login/highvisibility"
                   type="checkbox" <?= $_['styleHighVisibility'] ? 'checked' : '' ?>>
            <label for="splashd-style-highvisibility"><?php p($l->t('Enable High-Visibility Mode')); ?></label>
        </div>
    </form>
</div>
