document.addEventListener("webviewerloaded", function () {
    const COUNTRY_CODE = {
        fr: 'https://www.potinsnumeriques.fr',
        be: 'https://www.potinsnumeriques.fr'
    };

    if (window.location !== window.parent.location) {
        PDFViewerApplication.initializedPromise.then(function () {
            var completion = 0;
            var totalHeight = null;
            var screenHeight = null;
            var params = window.location.search.split('countryCode=');
            var viewer = document.getElementById('viewerContainer');

            if (params[1] && COUNTRY_CODE[params[1]]) {
                viewer.addEventListener('scroll', scrollCompletion);
                PDFViewerApplication.eventBus.on('scalechanged', scrollCompletion);
                PDFViewerApplication.eventBus.on('print', () => postMixpanelTriggerMessage('print'));
                PDFViewerApplication.eventBus.on('download', () => postMixpanelTriggerMessage('download'));

                PDFViewerApplication.eventBus.on('scrollmodechanged', event => {
                    switch (event.mode) {
                        default:
                        case 0:
                            PDFViewerApplication.eventBus.on('scalechanged', scrollCompletion);
                            PDFViewerApplication.eventBus.off('pagechanging', presentationCompletion);
                            viewer.addEventListener('scroll', scrollCompletion);
                            break;
                        case 3:
                            PDFViewerApplication.eventBus.off('scalechanged', scrollCompletion);
                            PDFViewerApplication.eventBus.on('pagechanging', presentationCompletion);
                            viewer.removeEventListener('scroll', scrollCompletion);
                            presentationCompletion();
                            break;
                    }
                });

                function scrollCompletion() {
                    totalHeight = viewer.scrollHeight;
                    screenHeight = viewer.clientHeight;

                    checkCompletion(viewer.scrollTop);
                }

                function presentationCompletion() {
                    totalHeight = PDFViewerApplication.pagesCount;
                    screenHeight = 0;

                    checkCompletion(PDFViewerApplication.page);
                }

                function checkCompletion(progression) {
                    var currentCompletion = (Math.ceil(((progression + screenHeight) / totalHeight) * 100));

                    if (currentCompletion > completion) {
                        completion = currentCompletion > 100 ? 100 : currentCompletion;
                        parent.postMessage({ completion }, COUNTRY_CODE[params[1]]);
                    }
                }

                function postMixpanelTriggerMessage(action) {
                    parent.postMessage({ action }, COUNTRY_CODE[params[1]]);
                }
            }
        });
    }
});
