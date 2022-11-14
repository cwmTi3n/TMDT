"use strict";
/**
 * This is the global js file for Everest Backup plugin.
 */
(function () {
    var lazyLoadIframes = function () {
        var youtubeIframes = document.querySelectorAll('.everest-backup_card .youtube-iframe');
        youtubeIframes.forEach(function (youtubeIframe) {
            var youtubeID = youtubeIframe.getAttribute('data-id');
            if (!youtubeID) {
                return;
            }
            var src = "//www.youtube.com/embed/".concat(youtubeID);
            youtubeIframe.setAttribute('src', src);
        });
    };
    window.addEventListener("load", function () {
        lazyLoadIframes();
    });
})();
//# sourceMappingURL=index.js.map