/**
 * Modal Remote
 *
 * usage:
 * ```
 * <a href="<?php echo Url::to(['/']); ?>" class="modal-remote">click me</a>
 * ```
 */
$(document).on('click', '.modal-remote', function (e) {
    e.preventDefault();
    var $modalRemote = $('#modal-remote'),
        url = $(this).attr('href');
    $.ajax({
        url: url,
        beforeSend: function (data) {
            if (!$modalRemote.length) $modalRemote = $('<div class="modal fade" id="modal-remote" tabindex="-1" role="dialog" aria-labelledby="remoteModalLabel" aria-hidden="true" data-backdrop="static"><div class="modal-dialog modal-lg"><div class="modal-content"></div></div></div>');
            $modalRemote.find('.modal-content').html('<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button><h3 id="remoteModalLabel">Loading...</h3></div><div class="modal-body"><div class="modal-remote-indicator"></div></div>');
            $modalRemote.modal();
        },
        success: function (data) {
            var $dom = $(document.createElement('html'));
            $dom[0].innerHTML = data;
            $dom.find('link').remove();
            $modalRemote.find('.modal-header > h3').html($dom.find('title').text());
            $modalRemote.find('.modal-body').html($dom.html());
            var footer = $dom.find('.main-footer').html();
            if (footer) {
                $modalRemote.find('.main-footer').remove();
                $modalRemote.find('.modal-content').append('<div class="modal-footer">' + footer + '</div>');
            }
            //$modalRemote.find('input:text:visible:first').focus();
            //var footer = $data.find('.page-content .form-actions').html();
            //if (footer) {
            //    $modalRemote.find('.modal-content').append('<div class="modal-footer">' + footer + '</div>');
            //}
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            $modalRemote.find('.modal-header > h3').html('Error');
            $modalRemote.find('.modal-body').html(XMLHttpRequest.responseText);
        }
    });
});

$(document).on('click', '.modal-remote-iframe', function (e) {
    e.preventDefault();
    var $modalRemoteIframe = $('#modal-remote-iframe'),
        url = $(this).attr('href'),
        title = $(this).attr('title');

    if (!$modalRemoteIframe.length) $modalRemoteIframe = $('<div class="modal fade" id="modal-remote" tabindex="-1" role="dialog" aria-labelledby="remoteModalLabel" aria-hidden="true" data-backdrop="static"><div class="modal-dialog modal-lg"><div class="modal-content"></div></div></div>');
    $modalRemoteIframe.find('.modal-content').html('<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button><h3 id="remoteModalLabel">' + title + '</h3></div><div class="modal-body"><div class="modal-remote-indicator"></div></div>');
    $modalRemoteIframe.find('.modal-body').html('<iframe src="' + url + '" style="width:100%;" onload="this.style.height = this.contentWindow.document.body.scrollHeight + \'px\';" frameborder="0"></iframe>');
    $modalRemoteIframe.modal();
});

$(document).on('click', '.modal-remote-form', function (e) {
    e.preventDefault();
    var $modalRemote = $('#modal-remote-form'),
        url = $(this).attr('href'),
        grid = $(this).attr('data-grid');
    try {
        var ids = $('#' + grid).yiiGridView('getSelectedRows');
    } catch (err) {
        return;
    }
    if (ids.length === 0) {
        swal({
            title: 'No rows selected!',
            type: 'warning',
            closeOnConfirm: true,
            allowOutsideClick: true
        });
        return;
    }
    $.post({
        url: url,
        data: {
            'ids': ids
        },
        beforeSend: function (data) {
            if (!$modalRemote.length) $modalRemote = $('<div class="modal fade" id="modal-remote" tabindex="-1" role="dialog" aria-labelledby="remoteModalLabel" aria-hidden="true" data-backdrop="static"><div class="modal-dialog modal-lg"><div class="modal-content"></div></div></div>');
            $modalRemote.find('.modal-content').html('<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button><h3 id="remoteModalLabel">Loading...</h3></div><div class="modal-body"><div class="modal-remote-indicator"></div></div>');
            $modalRemote.modal();
        },
        success: function (data) {
            var $dom = $(document.createElement('html'));
            $dom[0].innerHTML = data;
            $dom.find('link').remove();
            $modalRemote.find('.modal-header > h3').html($dom.find('title').text());
            $modalRemote.find('.modal-body').html($dom.html());
            var footer = $dom.find('.ajax-footer').html();
            if (footer) {
                $modalRemote.find('.ajax-footer').remove();
                $modalRemote.find('.modal-content').append('<div class="modal-footer">' + footer + '</div>');
            }
            //$modalRemote.find('input:text:visible:first').focus();
            //var footer = $data.find('.page-content .form-actions').html();
            //if (footer) {
            //    $modalRemote.find('.modal-content').append('<div class="modal-footer">' + footer + '</div>');
            //}
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            $modalRemote.find('.modal-header > h3').html('Error');
            $modalRemote.find('.modal-body').html(XMLHttpRequest.responseText);
        }
    });
});

/**
 * fix select2 search in modal
 * http://stackoverflow.com/a/19574076/599477
 */
$.fn.modal.Constructor.prototype.enforceFocus = function () {
};

/**
 * Cleanup on modal close
 */
$(document).on('click', '.close', function (e) {
    // modal-remote
    setTimeout(function () {
        $('#modal-remote').remove();
    }, 500);
    // tinyMCE editors
    if (typeof tinyMCE !== 'undefined') {
        var i, t = tinyMCE.editors;
        for (i in t) {
            if (t.hasOwnProperty(i)) {
                t[i].remove();
            }
        }
    }
});

/**
 * Remove Fixed Layout on Mobiles
 */
(function ($) {
    var $window = $(window),
        $body = $('body'),
        $navbar = $('#navbar');
    $window.resize(function resize() {
        if ($window.width() < 768) {
            $body.removeClass('fixed');
            $navbar.addClass('navbar-static-top');
            $navbar.removeClass('navbar-fixed-top');
            return;
        }
        $body.addClass('fixed');
        $navbar.addClass('navbar-fixed-top');
        $navbar.removeClass('navbar-static-top');
    }).trigger('resize');
})(jQuery);

/**
 * Override the default yii confirm dialog. This function is
 * called by yii when a confirmation is requested.
 *
 * @param message the message to display
 * @param okCallback triggered when confirmation is true
 * @param cancelCallback callback triggered when cancelled
 */
yii.confirm = function (message, okCallback, cancelCallback) {
    swal({
        title: message,
        type: 'warning',
        showCancelButton: true,
        closeOnConfirm: true,
        allowOutsideClick: true
    }, okCallback);
};

/**
 * Tooltips
 */
$(document).ready(function () {
    $('[data-toggle="tooltip"]').tooltip();
});