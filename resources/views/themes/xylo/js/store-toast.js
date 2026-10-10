(function () {
    if (typeof toastr === 'undefined') {
        return;
    }

    const defaultTitles = {
        success: 'Listo',
        error: 'Ups',
        warning: 'Atención',
        info: 'Info',
    };

    function titleFor(type) {
        const custom = window.__storeToastTitles;
        return (custom && custom[type]) || defaultTitles[type];
    }

    toastr.options = {
        closeButton: true,
        debug: false,
        newestOnTop: true,
        progressBar: true,
        positionClass: 'toast-top-right',
        preventDuplicates: true,
        onclick: null,
        showDuration: 0,
        hideDuration: 220,
        timeOut: 4800,
        extendedTimeOut: 1800,
        showEasing: 'swing',
        hideEasing: 'linear',
        showMethod: 'show',
        hideMethod: 'fadeOut',
        tapToDismiss: true,
    };

    window.StoreNotify = {
        success(message, title) {
            return toastr.success(message, title || titleFor('success'));
        },
        error(message, title) {
            return toastr.error(message, title || titleFor('error'));
        },
        warning(message, title) {
            return toastr.warning(message, title || titleFor('warning'));
        },
        info(message, title) {
            return toastr.info(message, title || titleFor('info'));
        },
    };

    window.showStoreToast = function (type, message, title) {
        if (window.StoreNotify && typeof StoreNotify[type] === 'function') {
            return StoreNotify[type](message, title);
        }
        if (typeof toastr[type] === 'function') {
            return toastr[type](message, title);
        }
    };
})();
