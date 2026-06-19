/**
* 2007-2023 Helloshop
*
* Tracking Center
*
*  @author    Helloshop <modules@helloshop.com>
*  @copyright 2007-2023 Helloshop
*  @license   license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*  @Website: http://www.Helloshop.com
*/

(function ($) {

    $.widget("ui.track", {
        options: {
            onSelect: null,
            dateinput: false,
        },
        parent: null,
        menu: null,
        _create: function() {
            var self = this,
                el = self.element,
                opts = self.options;
            this.parent = el.parent();
            this.parent.addClass('dateinput');
            this.tags = [];
            this.value = this.element.html().trim();
            this.v16 = parseInt(el.attr('js-v16'));

            // this.tagDiv = $("<div></div>")
            //     .addClass( opts.cssClass )
            //  .insertAfter( el.hide() );
            this.confirm = $('<button class="btn btn-default"><i class="icon-save"></i></button>').click(e => {
                e.preventDefault();
                e.stopPropagation();
                val = this.input.val();
                if (this.options.onSelect) {
                    this.options.onSelect(val, this.element);
                }
                else
                    this.element.html(val);
                this.closeMenu();
            });
            this.close = $('<button class="btn btn-default"><i class="icon-remove"></i></button>').click(e => {
                e.preventDefault();
                e.stopPropagation();
                this.closeMenu()
            });
            this.menu = $('<ul class="uimenu ui-menu ui-widget ui-widget-content ui-corner-all"></ul>');
            this.input = $(`<input class="form-control$" type="text" style="width:100%" name="H" min="0" max="23" value="0"/>`).on('keypress', e => {
                if (e.keyCode === 13) {
                    e.preventDefault();
                    val = this.input.val();
                    if (this.options.onSelect) {
                        this.options.onSelect(val, this.element);
                    }
                    else
                        this.element.html(val);
                    this.closeMenu();
                }
            });
            if (this.v16) {
                if (this.options.dateinput) this.input.datetimepicker({
                    timeFormat: 'hh:mm:ss tt',
                    dateFormat: 'yy-mm-dd',
                });
            } else {
                if (this.options.dateinput) this.input.datetimepicker();
            }
            this.hcontainer = $(`<div class="input-container"></div>`);
            this.hcontainer.append(this.input);
            this.menu.append(this.hcontainer);
            this.menu.append(this.confirm);
            this.menu.append(this.close);
            this.button = $('<i class="icon-pencil openmenu"></i>').insertAfter(this.element);
            let listener = this.button;
            if (this.button.parent().hasClass('editable')) {
                listener = this.button.parent();
            }
            listener.click((e) => {
                e.stopPropagation();
                self.openMenu();
            })
            $(document).on('mousedown', e => {
                if (this.options.dateinput) {
                    if (!this.menu.has($(e.target)).length && !$('#ui-datepicker-div').has($(e.target)).length)
                        this.closeMenu();
                } else {
                    if (!this.menu.has($(e.target)).length)
                        this.closeMenu();
                }
            });
            this.update();
        },
        update: function() {
            this.value = this.element.html().trim();
            this.value = this.value == '-' ? '' : this.value;
            this.input.val(this.value);
        },
        openMenu: function() {
            this.update();
            this.parent.append(this.menu.css('display', 'flex'));
            this.input.focus();
        },
        closeMenu: function() {
            this.menu.hide();
        },
        setOptions: function(options) {
            this.options = options;
        }
    });

})(jQuery);

$(document).ready(function() {
    var panel = $('#editCarrier').children();
    var editConnector = $('#editConnector').children();
    var editIssue = $('#editIssue').children();
    var editIssueStatus = $('#editIssueStatus').children();
    $(document).on('mousedown', e => {
        if (panel.length && !$.contains(panel.get(0), e.target)) {
            $('#editCarrier').append(panel);
        }
        if (editConnector.length && !$.contains(editConnector.get(0), e.target)) {
            $('#editConnector').append(editConnector);
        }
        if (editIssue.length && !$.contains(editIssue.get(0), e.target)) {
            $('#editIssue').append(editIssue);
        }
        if (editIssueStatus.length && !$.contains(editIssueStatus.get(0), e.target)) {
            $('#editIssueStatus').append(editIssueStatus);
        }
    });
    $(document).on('click', '.uimenu .closeMenu', function(e) {
        e.preventDefault();
        if ($(this).closest('.uimenu').is(panel))
            $('#editCarrier').append(panel);
        if ($(this).closest('.uimenu').is(editConnector))
            $('#editConnector').append(editConnector);
        if ($(this).closest('.uimenu').is(editIssue))
            $('#editConnector').append(editIssue);
        if ($(this).closest('.uimenu').is(editIssueStatus))
            $('#editConnector').append(editIssueStatus);
    })
})
