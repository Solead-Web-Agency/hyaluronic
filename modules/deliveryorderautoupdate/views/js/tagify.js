/**
* 2018-2019 PrestaShop

*  @license
*  @author    PrestaShop SA <contact@prestashop.com>
*
*/


(function ($) {

	$.widget("ui.tagify", {
		options: {
			require: false,
			delimiters: [13, 188],          // what user can type to complete a tag in char codes: [enter], [comma]
			outputDelimiter: ',',           // delimiter for tags in original input field
			cssClass: 'tagify-container',   // CSS class to style the tagify div and tags, see stylesheet
			addTagPrompt: 'add tags',        // placeholder text
			weeklist: true,
			daysOfWeek: [],
		},
		parent: null,
		menu: null,

		_create: function() {
			var self = this,
				el = self.element,
				opts = self.options;
			this.parent = el.parent();
			el.css('float', 'left');
			if (el.attr('data-require'))
				this.options.require = true;
			this.parent.addClass('dateinput');
			this.tags = [];
			// hide text field and replace with a div that contains it's own input field for entering tags
			this.tagInput = $("<input type='text'>")
				.attr( 'placeholder', opts.addTagPrompt )
				.keypress( function(e) {
					var $this = $(this),
					    pressed = e.which;

					for ( i in opts.delimiters ) {

						if (pressed == opts.delimiters[i]) {
							self.add( $this.val() );
							e.preventDefault();
							return false;
						}
					}
				})
				// for some reason, in Safari, backspace is only recognized on keyup
				.keyup( function(e) {
					var $this = $(this),
					    pressed = e.which;

					// if backspace is hit with no input, remove the last tag
					if (pressed == 8) { // backspace
						return;
					}
				});
			this.tagDiv = $(`<div class="${opts.cssClass}"></div>`)
			    // .click( function() {
			    //     $(this).children('input').focus();
			    // })
			    // .append( this.tagInput )
				.insertBefore( el.hide() );
			// if the field isn't empty, parse the field for tags, and prepopulate existing tags
			var initVal = $.trim( el.val() );

			if ( initVal ) {
				var initTags = initVal.split( opts.outputDelimiter );
				$.each( initTags, function(i, tag) {
				    self.add( tag );
				});
			}
			if (this.options.weeklist) {
				this.menu = $('<ul class="uimenu ui-menu ui-widget ui-widget-content ui-corner-all"></ul>');
				this.options.daysOfWeek.forEach((value, i) => {
					item = $(`<li class="ui-menu-item" data-value="${i}"><div>${value}<div/></li>`).click((e) => {
	    				e.stopPropagation();
	    				self.add(i);
	    				self.menu.hide();
	    			});
					this.menu.append(item);
				});
				this.button = $('<i class="process-icon-new openmenu"></i>').click((e) => {
				    e.stopPropagation();
				    self.openMenu();
				}).insertAfter(this.tagDiv)
	            $(document).on('mousedown', e => {
	                if (!this.menu.has($(e.target)).length)
	                    this.menu.hide();
	            });
			}
			if (this.options.require)
				this.checkRequire();

		},

		_setOption: function( key, value ) {
			// options.key = value;
		},
		openMenu: function() {
			this.parent.append(this.menu.show());
		},
		// add a tag, public function
		checkRequire: function() {
			if (this.tags.length < 1)
				this.tagDiv.addClass('require');
			else
				this.tagDiv.removeClass('require');
		},
		add: function(val) {
			let text = val;
			if (this.options.weeklist)
				text = this.options.daysOfWeek[val];
    		var self = this;
			if (text) {
				var removeButton = $("<a href='#'>x</a>")
					.click( function() {
						self.remove( val );
						return false;
					});
				var newTag = $(`<span data-value="${val}"></span>`)
					.text( text )
					.append( removeButton );

				self.tagDiv.append( newTag );
				self.tags.push( val );
				this.element.val(this.serialize());
			}
			if (this.options.require)
				this.checkRequire();
		},

		// remove a tag by index, public function
		// if index is blank, remove the last tag
		remove: function( val ) {
			for (var i = 0; i < this.tags.length; i++) {
				if (val == this.tags[i]) {
					this.tags.splice(i, 1);
					this.tagDiv.find(`span[data-value='${val}']`).remove();
					break;
				}
			}
			this.element.val(this.serialize());
			if (this.options.require)
				this.checkRequire();
		},

		// serialize the tags with the given delimiter, and write it back into the tagified field
		serialize: function() {
			var self = this;
			var delim = self.options.outputDelimiter;
			var tagsStr = self.tags.join( delim );

			// our tags might have deleted entries, remove them here
			var dupes = new RegExp(delim + delim + '+', 'g'); // regex: /,,+/g
			var ends = new RegExp('^' + delim + '|' + delim + '$', 'g');  // regex: /^,|,$/g
			var outputStr = tagsStr.replace( dupes, delim ).replace(ends, '');

			self.element.val(outputStr);
			return outputStr;
		},

		containerDiv: function() {
		    return this.tagDiv;
		},

		destroy: function() {
		    $.Widget.prototype.destroy.apply(this);
			this.tagDiv.remove();
			this.element.show();
		}
	});

})(jQuery);