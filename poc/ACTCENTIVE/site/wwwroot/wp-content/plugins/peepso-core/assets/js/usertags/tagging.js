( function( $, _, peepso, factory ) {
	var PsTagging = factory( $, _, peepso );

	// register as jquery plugin
	$.fn.ps_tagging = function( method, options ) {
		if ( typeof method === 'object' ) {
			options = method;
		}

		return this.each( function() {
			var elem = $( this ),
				instance = elem.data( 'ps_tagging' );

			if ( ! instance ) {
				instance = new PsTagging( this, options );
				elem.data( 'ps_tagging', instance );
			}

			if ( method === 'val' && typeof options === 'function' ) {
				options( instance.val() );
			} else if ( method === 'reset' ) {
				instance.reset();
			}
		} );
	};

	peepso.observer.addFilter(
		'peepso_postbox_beautifier',
		function( html, $textarea ) {
			var instance = $textarea.data( 'ps_tagging' ),
				value = $textarea.val(),
				tags = instance.tags_added || [];

			return instance.beautifier_update( value, tags );
		},
		10,
		2
	);
} )( jQuery, _, peepso, function( $, _, peepso ) {
	var key_enter = 13;
	var key_esc = 27;
	var key_arrow_up = 38;
	var key_arrow_down = 40;

	var r_tags = /@\[\[(\d+):user:([^\]]+)\]\]/g;
	var r_tag = /@\[\[(\d+):user:([^\]]+)\]\]/;
	var r_hashtag = /(^|#|\s)(#[^#\s]+)/g;
	var r_hashtag_replace = '$1<span class="ps-tag">$2</span>';
	var r_eol = /\n/g;
	var r_eol_replace = '<br>';

	var chr_trigger = '@';

	var evt_namespace = '.ps-tagging';

	var css_textarea = '.ps-tagging-textarea';
	var css_wrapper = '.ps-postbox__input-tag';
	var css_beautifier = '.ps-postbox__input-beautifier';
	var css_hidden = '.ps-tagging-hidden';
	var css_dropdown = '.ps-tagging-dropdown';
	var css_dropdown_item = '.ps-tagging-dropdown-item';
	var css_dropdown_item_active = '.active';
	var css_loading = '.ps-tagging-loading';

	function PsTagging( textarea, options ) {
		this.textarea = textarea;
		this.options = options || {};
		this.init();

		return this;
	}

	PsTagging.prototype.init = function() {
		this.dom_prepare();

		if ( this.textarea.value ) {
			this.parse_tags();
			this.textarea_on_input();
		}

		this.$textarea
			.off( evt_namespace )
			.on( 'focus' + evt_namespace, $.proxy( this.textarea_on_keydown, this ) )
			.on( 'click' + evt_namespace, $.proxy( this.textarea_on_keydown, this ) )
			.on( 'keydown' + evt_namespace, $.proxy( this.textarea_on_keydown, this ) )
			.on( 'keyup' + evt_namespace, $.proxy( this.textarea_on_keyup, this ) )
			.on( 'input' + evt_namespace, $.proxy( this.textarea_on_input, this ) )
			.on( 'blur' + evt_namespace, $.proxy( this.textarea_on_blur, this ) );

		this.$dropdown
			.off( evt_namespace )
			.on(
				'mouseenter' + evt_namespace,
				css_dropdown_item,
				$.proxy( this.dropdown_on_mouseenter, this )
			)
			.on(
				'mousedown' + evt_namespace,
				css_dropdown_item,
				$.proxy( this.dropdown_on_mousedown, this )
			)
			.on(
				'mouseup' + evt_namespace,
				css_dropdown_item,
				$.proxy( this.dropdown_on_mouseup, this )
			);
	};

	PsTagging.prototype.dom_prepare = function() {
		this.$textarea = $( this.textarea );
		this.$textarea.addClass( css_textarea.substr( 1 ) );

		this.$wrapper = this.$textarea.parent( css_wrapper );
		if ( ! this.$wrapper.length ) {
			this.$textarea.wrap( '<div class="' + css_wrapper.substr( 1 ) + '"></div>' );
			this.$wrapper = this.$textarea.parent();
		}

		this.$beautifier = this.$wrapper.children( css_beautifier );
		if ( ! this.$beautifier.length ) {
			this.$beautifier = $( '<div class="' + css_beautifier.substr( 1 ) + '"></div>' );
			this.$beautifier.prependTo( this.$wrapper );
		}

		this.$hidden = this.$wrapper.children( css_hidden );
		if ( ! this.$hidden.length ) {
			this.$hidden = $( '<input type="hidden" class="' + css_hidden.substr( 1 ) + '">' );
			this.$hidden.appendTo( this.$wrapper );
		}

		this.$dropdown = this.$wrapper.children( css_dropdown );
		if ( ! this.$dropdown.length ) {
			this.$dropdown = $( '<div class="' + css_dropdown.substr( 1 ) + '"></div>' );
			this.$dropdown.appendTo( this.$wrapper );
		}
	};

	PsTagging.prototype.parse_tags = function() {
		var value = this.textarea.value;
		var parser = this.options.parser;
		var parser_groups, tags, match, start, value, i;

		if ( parser ) {
			parser_groups = this.options.parser_groups || {};
			value = value.replace( this.options.parser, function() {
				return (
					'@[[' +
					( arguments[ parser_groups.id ] || '' ) +
					':user:' +
					( arguments[ parser_groups.title ] || '' ) +
					']]'
				);
			} );
		}

		tags = value.match( r_tags );
		this.textarea.value = value.replace( r_tags, '$2' );
		this.tags_added = [];

		if ( tags && tags.length ) {
			for ( i = 0; i < tags.length; i++ ) {
				match = tags[ i ].match( r_tag );
				start = value.indexOf( tags[ i ] );
				value = value.replace( tags[ i ], match[ 2 ] );
				this.tags_added.push( {
					id: match[ 1 ],
					name: match[ 2 ],
					start: start,
					length: match[ 2 ].length
				} );
			}
		}
	};

	PsTagging.prototype.reset = function() {
		var value = ( this.textarea.value = '' );
		var tags = ( this.tags_added = [] );

		var that = this;
		setTimeout( function() {
			that.$textarea.trigger( 'keyup' );
			that.$textarea.trigger( 'input' );
		} );

		this.hidden_update( value, tags );
		this.dropdown_hide();
	};

	PsTagging.prototype.val = function() {
		var value = this.$hidden.val();

		if ( typeof this.options.syntax === 'function' ) {
			value = value.replace(
				/@\[\[(\d+):user:([^\]]+)\]\]/g,
				$.proxy( function( all, id, title ) {
					return this.options.syntax( { id: id, title: title } );
				}, this )
			);
		}

		return value;
	};

	PsTagging.prototype.textarea_on_keydown = function( e ) {
		var key = e.keyCode;

		if ( this.dropdown_is_visible ) {
			if ( [ key_enter, key_esc, key_arrow_up, key_arrow_down ].indexOf( key ) >= 0 ) {
				e.preventDefault();
				e.stopPropagation();
			}
		}

		this.prev_sel_start = this.textarea.selectionStart;
		this.prev_sel_end = this.textarea.selectionEnd;
	};

	PsTagging.prototype.textarea_on_keyup = function( e ) {
		var key = e.keyCode;

		if ( this.dropdown_is_visible ) {
			if ( key === key_arrow_up || key === key_arrow_down ) {
				this.dropdown_change_item( key );
				e.preventDefault();
				e.stopPropagation();
			}

			if ( key === key_enter ) {
				this.dropdown_select_item();
				e.preventDefault();
				e.stopPropagation();
			}

			if ( key === key_esc ) {
				this.dropdown_hide();
				e.preventDefault();
				e.stopPropagation();
			}
		}
	};

	PsTagging.prototype.textarea_on_input = function( e ) {
		var value = this.textarea.value,
			delta,
			tag,
			length,
			name,
			tmp,
			index,
			r_match,
			r_replace,
			shift,
			i,
			j;

		// Shift tags position.
		if ( this.tags_added ) {
			// if text is selected (selectionStart !== selectionEnd)
			if ( this.prev_sel_start !== this.prev_sel_end ) {
				for ( i = 0; i < this.tags_added.length; i++ ) {
					tag = this.tags_added[ i ];
					length = tag.start + tag.length;
					if (
						// Intersection.
						( this.prev_sel_start > tag.start && this.prev_sel_start < length ) ||
						( this.prev_sel_end > tag.start && this.prev_sel_end < length ) ||
						// Enclose.
						( tag.start >= this.prev_sel_start && length <= this.prev_sel_end )
					) {
						this.tags_added.splice( i--, 1 );
					}
				}
			}

			delta =
				this.textarea.selectionStart -
				this.prev_sel_start -
				( this.prev_sel_end - this.prev_sel_start );

			for ( i = 0; i < this.tags_added.length; i++ ) {
				tag = this.tags_added[ i ];

				// Tag's start is in right of or exactly at cursor position.
				if ( tag.start >= this.prev_sel_start ) {
					tag.start += delta;
				} else {
					length = tag.start + tag.length;

					// Tag's end is in left of cursor position.
					if ( length < this.prev_sel_start ) {
						// do nothing
						// Cursor position is inside a tag.
					} else if ( length > this.prev_sel_start ) {
						// Not backspace.
						if ( delta > 0 ) {
							this.tags_added.splice( i--, 1 );
							// Backspace.
						} else if ( delta < 0 ) {
							name = value.substring( tag.start, this.prev_sel_start + delta );
							index = name.split( ' ' ).length - 1;
							name = tag.name.split( ' ' );
							name.splice( index, 1 );
							name = name.join( ' ' );

							tmp = tag.name.split( ' ' );
							tmp = tmp.slice( 0, index );
							tmp = tmp.join( ' ' );

							r_match = new RegExp(
								'^([\\s\\S]{' + tag.start + '})([\\s\\S]{' + ( tag.length + delta ) + '})'
							);
							r_replace = '$1' + name;
							this.textarea.value = this.textarea.value.replace( r_match, r_replace );
							this.textarea.setSelectionRange( tag.start + tmp.length, tag.start + tmp.length );

							value = this.textarea.value;
							shift = tag.length - name.length;
							tag.name = name;
							tag.length = name.length;

							for ( j = i + 1; j < this.tags_added.length; j++ ) {
								this.tags_added[ j ].start -= shift;
							}

							if ( ! name.length ) {
								this.tags_added.splice( i--, 1 );
							}

							i = this.tags_added.length;
						}

						// Tag's end is exactly at cursor position... and a backspace is pressed.
					} else if ( delta < 0 ) {
						name = tag.name.split( ' ' );
						name.pop();
						name = name.join( ' ' );

						r_match = new RegExp(
							'^([\\s\\S]{' + tag.start + '})([\\s\\S]{' + ( tag.length + delta ) + '})'
						);
						r_replace = '$1' + name;
						this.textarea.value = this.textarea.value.replace( r_match, r_replace );
						this.textarea.setSelectionRange( tag.start + name.length, tag.start + name.length );

						value = this.textarea.value;
						shift = tag.length - name.length;
						tag.name = name;
						tag.length = name.length;

						for ( j = i + 1; j < this.tags_added.length; j++ ) {
							this.tags_added[ j ].start -= shift;
						}

						if ( ! name.length ) {
							this.tags_added.splice( i--, 1 );
						}

						i = this.tags_added.length;
					}
				}
			}
		}

		// this.beautifier_update( value, this.tags_added || [] );
		this.hidden_update( value, this.tags_added || [] );
		this.dropdown_toggle();
	};

	PsTagging.prototype.textarea_on_blur = function( e ) {};

	PsTagging.prototype.beautifier_update = function( value, tags ) {
		var r_match, r_replace, start, tag, i;

		if ( tags.length ) {
			r_match = '^';
			r_replace = '';
			start = 0;

			for ( i = 0; i < tags.length; i++ ) {
				tag = tags[ i ];
				r_match += '([\\s\\S]{' + ( tag.start - start ) + '})([\\s\\S]{' + tag.length + '})';
				r_replace += '$' + ( i * 2 + 1 ) + '[[[' + tag.name + ']]]';
				start = tag.start + tag.length;
			}

			r_match = new RegExp( r_match );
			value = value.replace( r_match, r_replace );
		}

		// Update tag identifier.
		value = value.replace( /\[\[\[([^\[\]]+)\]\]\]/g, '<ps_span class="ps-tag">$1</ps_span>' );

		return value;
	};

	PsTagging.prototype.hidden_update = _.debounce( function( value, tags ) {
		var r_match, r_replace, start, tag, i;

		if ( tags.length ) {
			r_match = '^';
			r_replace = '';
			start = 0;

			for ( i = 0; i < tags.length; i++ ) {
				tag = tags[ i ];
				r_match += '([\\s\\S]{' + ( tag.start - start ) + '})([\\s\\S]{' + tag.length + '})';
				r_replace += '$' + ( i * 2 + 1 ) + '@[[' + tag.id + ':user:' + tag.name + ']]';
				start = tag.start + tag.length;
			}

			r_match = new RegExp( r_match );
			value = value.replace( r_match, r_replace );
		}

		this.$hidden.val( value );
	}, 50 );

	PsTagging.prototype.dropdown_toggle = _.debounce( function() {
		var cpos = this.textarea.selectionStart,
			substr = this.textarea.value.substr( 0, cpos ),
			index = substr.lastIndexOf( chr_trigger );

		if ( index < 0 || ( index > 0 && ! substr[ index - 1 ].match( /\s/ ) ) || ++index >= cpos ) {
			this.dropdown_hide();
			return;
		}

		substr = substr.substring( index, cpos );
		this.dropdown_fetch( substr, $.proxy( this.dropdown_update, this ) );
	}, 200 );

	PsTagging.prototype.dropdown_fetch = function( query, callback ) {
		if ( typeof this.options.fetcher !== 'function' ) {
			callback( query, [] );
			return;
		}

		if ( this.dropdown_fetching ) {
			return;
		}

		this.dropdown_fetching = true;
		this.$dropdown.find( css_loading ).show();
		this.options.fetcher(
			query,
			$.proxy( function( data, source ) {
				this.$dropdown.find( css_loading ).hide();
				this.dropdown_fetching = false;
				if ( source === 'cache' ) {
					callback( query, data );
				} else {
					this.dropdown_toggle();
				}
			}, this )
		);
	};

	PsTagging.prototype.dropdown_filter = function( query, data ) {
		var added = [];

		if ( this.tags_added && this.tags_added.length ) {
			added = _.map( this.tags_added, function( item ) {
				return '' + item.id;
			} );
		}

		query = query.toLowerCase();
		return _.filter( data, function( item ) {
			return added.indexOf( '' + item.id ) === -1 && item.name.toLowerCase().indexOf( query ) > -1;
		} );
	};

	PsTagging.prototype.dropdown_update = function( query, data ) {
		var html, i;

		data = this.dropdown_filter( query, data );

		if ( ! data.length ) {
			// TODO: not found
			return;
		}

		html = '';
		for ( i = 0; i < data.length; i++ ) {
			html +=
				'<div class="' +
				css_dropdown_item.substr( 1 ) +
				'" data-id="' +
				data[ i ].id +
				'" data-name="' +
				data[ i ].name +
				'">';
			html +=
				'<a href="#" onclick="return false;"><div class="ps-avatar"><img src="' +
				data[ i ].avatar +
				'"></div><span>' +
				data[ i ].name +
				'</span></a>';
			html += '</div>';
		}

		this.dropdown_show( html );
	};

	PsTagging.prototype.dropdown_show = function( html ) {
		this.$dropdown.html( html ).show();
		this.dropdown_is_visible = true;
	};

	PsTagging.prototype.dropdown_show_more = function() {};

	PsTagging.prototype.dropdown_hide = function() {
		this.$dropdown.hide();
		this.dropdown_is_visible = false;
	};

	PsTagging.prototype.dropdown_on_mouseenter = function( e ) {
		this.dropdown_change_item( e );
	};

	PsTagging.prototype.dropdown_on_mousedown = function() {
		this.dropdown_is_clicked = true;
	};

	PsTagging.prototype.dropdown_on_mouseup = function( e ) {
		this.dropdown_select_item( e );
		this.dropdown_is_clicked = false;
		this.dropdown_hide();
	};

	PsTagging.prototype.dropdown_change_item = function( e ) {
		var classname = css_dropdown_item_active.substr( 1 ),
			elem,
			sibs,
			next;

		if ( typeof e !== 'number' ) {
			elem = this.dropdown_selected_item = $( e.target );
			sibs = elem.siblings( css_dropdown_item_active );
			elem.addClass( classname );
			sibs.removeClass( classname );
			return;
		}

		elem = this.$dropdown.children( css_dropdown_item_active );
		if ( ! elem.length ) {
			elem = this.dropdown_selected_item = this.$dropdown
				.children()
				[ e === key_arrow_up ? 'last' : 'first' ]();
			elem.addClass( classname );
			return;
		}

		next = elem[ e === key_arrow_up ? 'prev' : 'next' ]();
		elem.removeClass( classname );
		if ( next.length ) {
			this.dropdown_selected_item = next;
			next.addClass( classname );
		} else {
			this.dropdown_selected_item = false;
		}
	};

	PsTagging.prototype.dropdown_select_item = function( e ) {
		var el = e ? $( e.currentTarget ) : this.dropdown_selected_item,
			id = el.data( 'id' ),
			name = el.data( 'name' ),
			cpos = this.textarea.selectionStart,
			substr = this.textarea.value.substr( 0, cpos ),
			index = substr.lastIndexOf( chr_trigger ),
			re,
			value;

		this.tags_added || ( this.tags_added = [] );
		this.tags_added.push( {
			id: id,
			name: name,
			start: index,
			length: name.length
		} );

		re = new RegExp( '^([\\s\\S]{' + index + '})[\\s\\S]{' + ( cpos - index ) + '}' );
		value = this.textarea.value.replace( re, '$1' + name ) + ' ';
		this.textarea.value = value;
		this.textarea.setSelectionRange( index + name.length + 1, index + name.length + 1 );

		var that = this;
		setTimeout( function() {
			that.$textarea.trigger( 'keyup' );
			that.$textarea.trigger( 'input' );
		} );

		this.hidden_update( value, this.tags_added );
		this.dropdown_hide();
		this.$textarea.focus();
	};

	return PsTagging;
} );
