<?php
/**
 * This file is part of the Splashd app
 * and licensed under the AGPL.
 */

script('splashd', 'settings');
style('splashd', 'settings');

?>
<div class="section" id="splashd-settings" data-save="<?= $_['saveSettingsUrl'] ?>"
     data-requestUpdate="<?= $_['requestCustomizationUrl'] ?>">
    <h2>
        <?php p($l->t('Splash: Random Background Images')); ?>
        <span class="msg success"><?php p($l->t('Saved')); ?></span>
        <span class="msg error"><?php p($l->t('Failed')); ?></span>
    </h2>
    <p class="settings-hint">
        <?php p($l->t('You can customize your instance with random background images.')); ?>
        <br>
        <?php p($l->t('Here you can specify where random backgrounds should be used by default.')); ?>
        <?php print_unescaped($this->inc('partials/license')); ?>
    </p>
    <form>
        <div class="splashd-checkboxes">
            <input id="splashd-style-login" name="splashd-style-login" data-setting="style/login"
                   type="checkbox" <?= $_['styleLogin'] ? 'checked' : '' ?>>
            <label for="splashd-style-login"><?php p($l->t('Set random image as login background')); ?></label>
        </div>
        <div class="splashd-checkboxes">
            <input id="splashd-style-dashboard" name="splashd-style-dashboard" data-setting="style/dashboard"
                   type="checkbox" <?= $_['styleDashboard'] ? 'checked' : '' ?>>
            <label for="splashd-style-dashboard"><?php p($l->t('Set random image as dashboard background')); ?></label>
        </div>
        <div class="splashd-providerselect">
            <label class="splashd-label"
                   for="splash-provider-selection"><?php p($l->t('Set the image provider:')); ?></label>
            <select class="splashd-input" id="splash-provider-selection" data-setting="provider/provider"
                    type="select">
                <?php foreach ($_['availableProvider'] as &$value) {
                    echo "<option value='" . $value . "'";
                    if ($value == $_['selectedProvider']) {
                        echo "selected";
                    }
                    echo ">";
                    p($l->t($value));
                    echo "</option>";
                }
                ?>
            </select>
            <p class="settings-hint">
                <?php print_unescaped($this->inc('partials/license' . $_['selectedProvider'])); ?>
            </p>
        </div>
        <div class="splashd-providerselect">
            <label class="splashd-label" for="splash-provider-customization"><?php p($l->t('Keywords:')); ?></label>
            <input class="splashd-input" type="text" id="splash-provider-customization"
                   data-setting="provider/customization"
            <?php
            echo " value='" . $_['selectionCustomization'] . "'>";
            ?>
            <p class="settings-hint">
                <?php p($l->t('Set custom search terms. Separate multiple terms by comma.')); ?>
            </p>
        </div>
        <div
            <?php
            if ($_['selectedProvider'] !== "UnsplashAPI") {
                echo "style=\"display: none;\"";
            }
            ?>
                id="splashd-providertoken" class="splashd-providertoken">
            <label class="splashd-label" for="splash-provider-token"><?php p($l->t('Token:')); ?></label>
            <input class="splashd-input" type="text" id="splash-provider-token" data-setting="provider/token"
            <?php
            echo " value='****'";
            ?>>
            <br>
            <br>
            <p class="settings-hint">
                <?php p($l->t('Set the required token. To get a token, visit:')); ?>
                <br>
                <a class="splashd-documentation-link"
                   href="https://unsplash.com/documentation#creating-a-developer-account"
                   target="_blank"
                   rel="noopener noreferrer"
                ><?php p($l->t('Unsplash: Developer Account Instructions')); ?></a>
                <br>
                <?php
                    // TRANSLATORS The linked instruction page contains a guide on how a user would create an api key. This key has a demo and a production mode, and the demo mode is limited to 50 requests per hour. That is way more than expected usage, so the demo key is fine.
                    p($l->t('On the linked instruction page, register an application. You should not need to apply for production. Use their token here.'));
                    ?>
            </p>
        </div>
        <!-- Button Action is in javascript-->
        <p><?php p($l->t('Preview:')); ?></p>

        <img src="<?php echo $_['imageURL']; ?>" id="splashd-preview" class="splashd-preview">
        <br>
        <input
            <?php
                if (!$_['isCached']) {
                    echo "style=\"display: none;\"";
                }
            ?>
                type="button" id="splash-provider-refresh-cache" data-setting="delete/cache" value="<?php p($l->t('Refresh Cached Image')); ?>">
        <br>
        <h2>
            <?php p($l->t('Splash: Advanced Theming')); ?>
        </h2>
        <p class="settings-hint">
            <?php p($l->t('You can apply effects to your background images.')); ?>
        </p>

        <div class="splashd-checkboxes">
            <input id="splashd-style-tinting" name="splashd-style-tinting" data-setting="style/tint"
                   type="checkbox" <?= $_['styleTint'] ? 'checked' : '' ?>>
            <label for="splashd-style-tinting"><?php p($l->t('Enable Tint')); ?></label>
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
        <div class="splashd-slider">
            <label for="splashd-style-blur"><?php p($l->t('Set the blur of the image')); ?></label>
            <input type="range" name="splashd-style-blur" data-setting="style/strength/blur" min="0" max="25"
                   value="<?= $_['styleStrengthBlur'] ? $_['styleStrengthBlur'] : 0 ?>">
        </div>

        <br>
        <h2>
            <?php p($l->t('Splash: High Visibility')); ?>
        </h2>
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
