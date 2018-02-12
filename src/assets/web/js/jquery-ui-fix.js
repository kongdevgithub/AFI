/**
 * Handle jQuery plugin naming conflict between jQuery UI and Bootstrap
 * http://stackoverflow.com/a/19247955/599477
 */
$.widget.bridge('uibutton', $.ui.button);
$.widget.bridge('uitooltip', $.ui.tooltip);