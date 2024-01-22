jQuery( window ).on( 'elementor:init', function() {
    var $ = jQuery,
        onReady = elementor.modules.controls.Select2.prototype.onReady,
        ControlSelect2Ajax = elementor.modules.controls.Select2.extend({

        linkedFields: {},
        onLoadSelection: null,

        // linkedFieldsData: {},
        onReady: function() {
            var linkedFields = this.getLinkedFields();
            if ( ! linkedFields.length ) {
                return this.updateOptions();
            }

            // Cache the selection as it may be changed based on the linked fields
            this.onLoadSelection = this.getControlValue();

            // Hold the value until the linked fields are loaded
            // the maximum delay time should be 10 seconds in milliseconds
            var startTime = new Date(),
                waitTime = 10000;

            var timer = setInterval( () => {
                var endTime = new Date(),
                    clear = false,
                    fields = 0;

                linkedFields.forEach( (field) => {
                    if ( $(':input[data-setting="' + field + '"]').length ) {
                        fields++;
                    }
                });

                if ( fields === linkedFields.length ) {
                    this.watchFields(linkedFields);
                    clear = true;
                }

                if ( clear || ( endTime.getTime() - startTime.getTime() ) > waitTime ) {
                    clearInterval( timer );

                    if ( fields !== linkedFields.length ) {
                        console.log('The number of linked field was ' + linkedFields.length + ' and loaded ' + fields);
                    }
                }
            }, 2);
        },

        watchFields: function (fields) {
            for( var field in fields ) {
                field = fields[field];
                var input = $(':input[data-setting="' + field + '"]');

                this.linkedFields[field] = input;
                input.data('select2-linked-fields', true);

                input.on('change', (e) => {

                    if ( typeof $(e.currentTarget).data('select2') === 'object' ) {
                        // this.ui.select.val([]).trigger('change');
                        return this.updateOptions();
                    }

                    this.ui.select.val([this.onLoadSelection]).trigger('change');
                });
            }

            this.updateOptions();
        },
        updateOptions: function () {
            let select2options = this.getSelect2Options();
            if(this.ui.select !== undefined){
                this.ui.select.select2( select2options );
            }
            // reorder by selected
            this.ui.select.on('select2:select', function (e) {
              var id = e.params.data.id;
              var option = $(e.target).children('[value="'+id+'"]');
              option.detach();
              $(e.target).append(option).change();
            });
            // Fetch saved values
            this.printSavedValues();
        },
        getLinkedFields: function () {
            var linkedFields = this.model.get('linked_fields') || null;
            if ( ! linkedFields ) {
                return [];
            }

            return 'string' === typeof linkedFields ? [linkedFields] : linkedFields;
        },
        getLinkedData: function () {
            var data = {};
            $.each( this.linkedFields, (name, field) => {
                data[name] = field.val();
            });

            return data;
        },
        getSelect2Placeholder: function getSelect2Placeholder() {
            return this.ui.select.children('option:first[value=""]').text();
        },
        getSelect2Options: function getSelect2Options() {
            var self = this;

            return {
                allowClear: true,
                placeholder: this.getSelect2Placeholder(),
                dir: elementorCommon.config.isRTL ? 'rtl' : 'ltr',
                ajax: {
                    method: 'POST',
                    dataType: 'json',
                    delay: 250,
                    minimumInputLength: 2,
                    url: ajaxurl,
                    data: function (params) {
                        return Object.assign({}, self.getLinkedData(), {
                            search: params.term, // search term
                            action: self.model.get('callback'),
                            page: params.page || 1
                        });
                    },
                    processResults: function (response, params) {
                        params.page = params.page || 1;
                        let limit = self.model.get('query_limit') || 100;
                        self._updateCache( response.data.results );
                        return {
                            results: response.data.results,
                            pagination: {
                                more: (params.page * limit) < response.data.total_count
                            }
                        };

                    },
                    complete: function (response) {
                        //self.ui.select.val([self.onLoadSelection]).trigger('change');
                        // self.applySavedValue();
                    },
                    cache: true
                }
            };
        },

        onBeforeDestroy: function () {
            if (this.ui.select.data('select2')) {
                this.ui.select.select2('destroy');
            }

            this.$el.remove();
        },

        printSavedValues: function () {
            this._fetchRemoteData();
        },

        _fetchRemoteData: function () {
            var savedData = this.onLoadSelection || this.getControlValue();
            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: Object.assign({}, this.getLinkedData(), {
                    action: this.model.get('callback'),
                    saved: savedData
                })
            })
            .then( res => {
                if(this.ui.select !== undefined){
                    this.ui.select.children().remove();
                }

                var data = {},
                    matchValues = [];
                savedData = 'object' !== typeof savedData ? [ savedData ] : savedData;

                $.each( res.data.results, ( id, data ) => {
                    var selected = savedData && savedData.indexOf( String( data.id ) ) !== -1;
                    if ( selected ) {
                        matchValues.push(data.id);
                    }

                    var option = new Option(data.text, data.id, selected, selected);
                    if(this.ui.select !== undefined){
                        this.ui.select.append(option);
                    }

                });
                if ( matchValues ) {
                    this.setInputValue('[data-setting="' + this.model.get("name") + '"]', savedData);
                }

                if(this.ui.select !== undefined){
                    this.ui.select.trigger('change');

                }
            });
        },

        _updateCache: function ( data ) {
            var cache = this.model.get('_cache') || {};

            for ( var key in data ) {
                cache[ data[ key ].id ] = data[ key ].text;
            }

            this.model.set('_cache', cache);
        }
    });

    elementor.addControlView( 'select2ajax', ControlSelect2Ajax );
});
