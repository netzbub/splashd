(function () {

    let timers = {};
    let saveUrl = null;

    function initialize() {
        const root = document.getElementById('splashd-settings');
        if (root === null) {
            return;
        }
        saveUrl = root.dataset.save;

        // Text fields save on Enter and when they lose the focus.
        for (const id of ['splash-provider-customization', 'splashd-folder-account', 'splashd-folder-path']) {
            const field = document.getElementById(id);
            if (field === null) {
                continue;
            }
            field.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    save(field.dataset.setting, field.value, 'text');
                }
            });
        }

        const refresh = document.getElementById('splash-provider-refresh-cache');
        if (refresh !== null) {
            refresh.addEventListener('click', () => save(refresh.dataset.setting, '', 'button'));
        }

        for (const setting of document.querySelectorAll('[data-setting]')) {
            const type = setting.getAttribute('type');
            if (type === 'text' || type === 'button') {
                continue;
            }
            setting.addEventListener('change', (e) => {
                let value = e.target.value;
                if (type === 'checkbox') {
                    value = e.target.checked ? 'true' : 'false';
                }
                if (e.target.dataset.setting === 'style/tint') {
                    document.getElementById('splashd-style-color-strength').disabled = value !== 'true';
                }
                save(e.target.dataset.setting, value, type);
            });
        }
    }

    /**
     * Send one setting to the server.
     *
     * @param key setting name
     * @param value new value
     * @param type kind of input, decides how the answer is handled
     * @private
     */
    function save(key, value, type) {
        const options = {
            method: 'POST',
            headers: {
                'requesttoken': OC.requestToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({key, value}),
            redirect: 'error',
        };

        // The URL and the options have to be passed separately. Nextcloud wraps
        // window.fetch, and when the second argument is missing the wrapper
        // supplies its own headers, which replace the ones a ready-made Request
        // object carries. The request token would be lost and the server would
        // refuse the save with HTTP 412.
        fetch(saveUrl, options)
            .then((response) => {
                // A refused request still answers with JSON, so the status has
                // to be looked at before the body is believed.
                if (!response.ok) {
                    const error = new Error('splashd: saving ' + key + ' failed');
                    error.status = response.status;
                    throw error;
                }
                return response.json();
            })
            .then((data) => {
                message('success');
                if (key === 'provider/provider') {
                    // The licence note and the fields that apply are rendered
                    // server side, so take the whole page fresh.
                    window.location.reload();
                    return;
                }
                apply(data);
            })
            .catch((e) => {
                console.error(e);
                message('error', e.status ? 'HTTP ' + e.status : e.message);
            });
    }

    /**
     * Update the parts of the page the answer carries.
     *
     * @private
     */
    function apply(data) {
        // Show the value the server read back, so that a write that did not
        // take hold is visible at once instead of leaving a green "Saved".
        if (data.hasOwnProperty('key') && data.stored !== null && data.stored !== undefined) {
            const field = document.querySelector(`[data-setting="${data.key}"]`);
            if (field !== null) {
                if (field.getAttribute('type') === 'checkbox') {
                    field.checked = data.stored === true || data.stored === 1;
                } else {
                    field.value = data.stored;
                }
            }
        }
        if (data.hasOwnProperty('poolCount')) {
            setCount('splashd-pool-count', data.poolCount,
                t('splashd', '%n image ready on the server.', '%n images ready on the server.', data.poolCount));
        }
        if (data.hasOwnProperty('folderCount')) {
            setCount('splashd-folder-count', data.folderCount,
                t('splashd', '%n image found in the folder.', '%n images found in the folder.', data.folderCount));
        }
        if (data.hasOwnProperty('imageURL')) {
            const preview = document.getElementById('splashd-preview');
            if (preview !== null) {
                preview.src = data.imageURL;
            }
        }
    }

    function setCount(id, count, text) {
        const element = document.getElementById(id);
        if (element !== null) {
            element.textContent = text;
        }
    }

    /**
     * Translate with a plural form, falling back to the plain count when the
     * translation helper is not available.
     *
     * @private
     */
    function t(app, singular, plural, count) {
        if (typeof window.n === 'function') {
            return window.n(app, singular, plural, count);
        }
        return (count === 1 ? singular : plural).replace('%n', count);
    }

    /**
     * Show the save success or failure marker.
     *
     * @private
     */
    function message(type, detail) {
        const element = document.querySelector(`#splashd-settings .msg.${type}`);
        if (element === null) {
            return;
        }
        // Keep the translated label and append what went wrong, so the reason
        // can be read off the page instead of the browser console.
        if (element.dataset.label === undefined) {
            element.dataset.label = element.textContent.trim();
        }
        element.textContent = detail ? `${element.dataset.label} (${detail})` : element.dataset.label;
        element.classList.add('active');
        clearTimeout(timers[type]);
        timers[type] = setTimeout(() => element.classList.remove('active'), detail ? 15000 : 1000);
    }

    initialize();
})();
