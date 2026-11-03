(function () {
    function initialize() {
        loadMetadata()
    }

    /**
     * @private
     */
    function loadMetadata() {

        var metadata = OC.generateUrl('/apps/splashd/api/metadata');
        let headers = {
            method: 'GET',
            headers: {
                'requesttoken': OC.requestToken,
                'Accept': 'application/json'
            }
        }

        let request = new Request(metadata);

        fetch(request, headers)
            .then(response => response.json())
            .then(data => {
                addMetadataToDOM(data)
            })
            .catch((e) => {
                console.error(e);
            });
    }


    function addMetadataToDOM(data) {

        // Only the Openverse source has something worth showing. The local
        // sources need no attribution and get no panel.
        if (!data.source || data.source.indexOf("Openverse") !== 0) {
            return
        }

        let div = document.createElement("div");
        let info = document.createElement("div");
        let source = document.createElement("p");
        let description = document.createElement("p");
        let author = document.createElement("p");
        let goto = document.createElement("a");


        // First, prepare all styles
        source.classList = "splashd-metadata-text splashd-metadata-title"
        author.classList = "splashd-metadata-text"
        description.classList = "splashd-metadata-text"
        info.classList = "splashd-icon-info"
        //dash-panel for dashboard, guest-box for login
        div.classList = "dash-panel guest-box splashd-floating-bottom-right"


        // Third, add links
        div.href = data.attribution


        // Last: Add content to main div and body

        source.textContent = data.source + ":"
        if (!data.description) {
            if (!data.author) {
                source.textContent = data.source
            }
        }
        goto.appendChild(source)

        if (data.description) {
            description.textContent = data.description
            goto.appendChild(description)
        }

        if (data.author) {
            author.textContent = "- " + data.author
            goto.appendChild(author)
        }

        goto.href = data.attribution


        // Now append both to main div
        div.appendChild(info)
        div.appendChild(goto)

        document.getElementsByTagName("body")[0].appendChild(div)
    }


    initialize();
})();
