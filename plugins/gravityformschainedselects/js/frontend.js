/**
 * GF Chained Selects Frontend
 */

( function( $ ) {

    window.GFChainedSelects = function( formId, fieldId, hideInactive, alignment ) {

        var self = this;

        self.formId       = formId;
        self.fieldId      = fieldId;
        self.hideInactive = hideInactive;
        self.alignment    = alignment;

        var $field = $( '#field_' + self.formId + '_' + self.fieldId );

        self.$selects  = $field.find( 'select' );
        self.$complete = $field.find( '.gf_chain_complete' );

        self.isDoingConditionalLogic = false;

        self.init = function() {

            gform.addAction( 'gform_input_change', function( elem, formId, fieldId ) {
                if( self.$selects.index( elem ) != - 1 ) {
                    var inputId = $( elem ).attr( 'name' ).split( '_' )[1]; // converts "input_4.1" to "4.1"
                    self.populateNextChoices( inputId, elem.value, $( elem ) );
                }
            }, 9 );

            self.$selects.filter( function() {
                var $select = $( this );
                return $select.hasClass( 'gf_no_options' ) || $select.find( 'option' ).length <= 1;
            } ).toggleSelect( true, self.hideInactive );
                //.prop( 'disabled', true ).hide();

            /*var $lastSelect = self.$selects.last();
            self.toggleCompleted( $lastSelect.hasClass( 'gf_no_options' ) || $lastSelect.val() );*/

            gform.addFilter( 'gform_is_value_match', function( isMatch, formId, rule ) {
                return self.isValueMatch( isMatch, formId, rule );
            } );

        };

        self.populateNextChoices = function( inputId, selectedValue, $select ) {

            var nextInputId = self.getNextInputId( inputId ),
                $nextSelect = self.$selects.filter( '[name="input_' + nextInputId + '"]' );

            // if there is no $nextSelect, we're at the end of our chain
            if( $nextSelect.length <= 0 ) {
                self.resetSelects( $select, true );
                self.resizeSelects();
                return;
            } else {
                self.resetSelects( $select );
            }

            if( ! selectedValue ) {
                return;
            }

            if( self.hideInactive ) {

                var $currentSelect = $( '[name="input_' + inputId + '" ]' ),
                    $spinner       = new gfAjaxSpinner( $currentSelect, gformChainedSelectData.spinner, 'display:inline-block;vertical-align:middle;margin:-1px 0 0 6px;' );

            } else {

                var $loadingOption  = $( '<option value="">' + gformChainedSelectData.strings.loading + '...</option>' ),
                    dotCount        = 2,
                    loadingInterval = setInterval( function() {
                        $loadingOption.text( gformChainedSelectData.strings.loading + ( new Array( dotCount ).join( '.' ) )  );
                        dotCount = dotCount > 3 ? 0 : dotCount + 1;
                    }, 250 );

                $loadingOption.prependTo( $nextSelect ).prop( 'selected', true );
                $nextSelect.css( { minWidth: $nextSelect.width() } );
                $loadingOption.text( gformChainedSelectData.strings.loading + '.' );

            }

            $.post( gformChainedSelectData.ajaxUrl, {
                action:   'gform_get_next_chained_select_choices',
                input_id: inputId,
                form_id:  self.formId,
                field_id: self.fieldId,
                value:    self.getChainedSelectsValue(),
                nonce:    gformChainedSelectData.nonce
            }, function( response ) {

                if( self.hideInactive ) {

                    $spinner.destroy();

                } else {

                    clearInterval( loadingInterval );
                    $loadingOption.remove();

                }

                if( ! response ) {
                    return;
                }

                var choices       = $.parseJSON( response ),
                    optionsMarkup = '';

                $nextSelect.find( 'option:not(:first)' ).remove();

                if( choices.length <= 0 ) {

                    self.resetSelects( $select, true );

                } else {

                    var hasSelectedChoice = false;

                    $.each( choices, function( i, choice ) {
                    	var selected = choice.isSelected ? 'selected="selected"' : '';

                    	if ( selected )  {
                    	    hasSelectedChoice = true;
                        }

                        optionsMarkup += '<option value="' + choice.value + '"' + selected + '>' + choice.text + '</option>';
                    } );

                    $nextSelect.show().append( optionsMarkup );

                    // the placeholder will be selected by default, rather than removing it and re-adding, just force the noOptions option to be selected
                    if( choices[0].noOptions ) {

                        var $noOption = $nextSelect.find( 'option:last-child' ).clone(),
                            $nextSelects = $nextSelect.parents( 'span' ).nextAll().find( 'select' );

                        $nextSelects.append( $noOption );

                        $nextSelects.add( $nextSelect )
                            .addClass( 'gf_no_options' )
                            .find( 'option:last-child' ).prop( 'selected', true );

                        //self.toggleCompleted( true );

                    } else {
                        $nextSelect
                            .removeClass( 'gf_no_options' )
                            //.prop( 'disabled', false ).show();
                            .toggleSelect( false, self );

                        if ( hasSelectedChoice ) {
                            $nextSelect.change();
                        }
                    }

                }

                self.resizeSelects();

            } );

        };

        self.getChainedSelectsValue = function() {

            var value = {};

            self.$selects.each( function() {
                var inputId = $( this ).attr( 'name' ).split( '_' )[1]; // converts "input_4.1" to "4.1"
                value[ inputId ] = $( this ).val();
            } );

            return value;
        };

        self.getNextInputId = function( currentInputId ) {

            var index     = parseInt( currentInputId.split( '.' )[1] ),
                nextIndex = index + 1;

            if( nextIndex % 10 == 0 ) {
                nextIndex++;
            }

            return parseInt( currentInputId ) + '.' + ( nextIndex );
        };

        self.resetSelects = function( $currentSelect, isComplete ) {

            var currentIndex = self.$selects.index( $currentSelect ),
                $nextSelects = self.$selects.filter( ':gt(' + currentIndex + ')' );

            $nextSelects
                .toggleSelect( true, self.hideInactive )
                .find( 'option:not(:first)' )
                .remove()
                .val( '' )
                .change();

        };

        self.resizeSelects = function() {

            if( self.alignment != 'vertical' ) {
                return;
            }

            // reset width so it will be determined by its contents
            self.$selects.width( 'auto' );

            var width = 0;

            self.$selects.each( function() {
                if( $( this ).width() > width ) {
                    width = $( this ).width();
                }
            } );

            self.$selects.width( width + 'px' );

        };

        self.toggleCompleted = function( isComplete ) {
            if( isComplete ) {
                self.$complete.fadeIn();
            } else {
                self.$complete.fadeOut();
            }
        };

        self.isValueMatch = function( isMatch, formId, rule ) {

            if( formId != self.formId || rule.fieldId != self.fieldId || self.isDoingConditionalLogic ) {
                return isMatch;
            }

            self.isDoingConditionalLogic = true;

            rule = $.extend( {}, rule );

            var valueObj   = self.getChainedSelectsValue(),
                fieldValue = Object.keys( valueObj ).map( function( key ) { return valueObj[ key  ]; } ),
                ruleValue = rule.value.split( '/' );

            for( var i = 0; i < ruleValue.length; i++ ) {
                if( ruleValue[i] == '*' ) {
                    ruleValue[i] = fieldValue[i];
                }
            }

            ruleValue  = ruleValue.join( '/' );
            fieldValue = fieldValue.join( '/' );

            isMatch = gf_matches_operation( ruleValue, fieldValue, rule.operator );

            self.isDoingConditionalLogic = false;

            return isMatch;
        };

        $.fn.toggleSelect = function( disabled, hideInactive ) {
            this.prop( 'disabled', disabled );
            if( typeof hideInactive != 'undefined' && hideInactive ) {
                if( disabled ) {
                    this.hide();
                } else {
                    this.show();
                }
            }
            return this;
        };

        self.init();

    };

    function gfAjaxSpinner( elem, imageSrc, inlineStyles ) {

        var imageSrc     = typeof imageSrc == 'undefined' ? '/images/ajax-loader.gif': imageSrc,
            inlineStyles = typeof inlineStyles != 'undefined' ? inlineStyles : '';

        this.elem = elem;
        this.image = '<img class="gfspinner" src="' + imageSrc + '" style="' + inlineStyles + '" />';

        this.init = function() {
            this.spinner = jQuery(this.image);
            jQuery( this.elem ).after(this.spinner);
            return this;
        };

        this.destroy = function() {
            jQuery( this.spinner ).remove();
        };

        return this.init();
    }

} )( jQuery );