(function($) {
  'use strict';

  $(document).ready( function(){

    var blmAjaxLinksListTable = {
		init: function() {
			var timer,
				delay = 500,
				searchLinksTextField = $('#blm-translate-search-input'),
				searchLinksColumn = $('#blm-search-column');
			$('.bl-monitoring-tbl-nav-pages a, .bl-monitoring-sort-column.sortable a, .bl-monitoring-sort-column.sorted a').on('click', function(e) {
				e.preventDefault();
				var query = this.search.substring( 1 );
				var data = {
					'paged': blmAjaxLinksListTable.__query( query, 'paged' ) || '1',
					'order': blmAjaxLinksListTable.__query( query, 'order' ) || 'asc',
					'orderby': blmAjaxLinksListTable.__query( query, 'orderby' ) || 'date',
					'search_field': searchLinksTextField.val() || '',
					'search_column': searchLinksColumn.val() || ''
				};
				blmAjaxLinksListTable.update( data );
			});

			$('input[name=paged]').on('keyup', function(e) {

				if ( 13 === e.which ) {
					e.preventDefault();
				}

				var data = {
					'paged': parseInt( $('input[name=paged]').val() ) || '1',
					'order': $('input[name=order]').val() || 'asc',
					'orderby': $('input[name=orderby]').val() || 'date',
					'search_field': searchLinksTextField.val() || '',
					'search_column': searchLinksColumn.val() || ''
				};

				window.clearTimeout( timer );
				timer = window.setTimeout(function() {
					blmAjaxLinksListTable.update( data );
				}, delay);
			});

            /**
			 * Search Button
			 */
			$('#blm-links-search-btn').click(function( e ) {

				e.preventDefault();
				e.stopImmediatePropagation();
				if( searchLinksTextField.val() == '' ) {
					searchLinksTextField.addClass('blm-field-required');
                    return false;
				}
				if( searchLinksColumn.val() == '' ) {
					searchLinksColumn.addClass('blm-field-required');
					searchLinksTextField.removeClass('blm-field-required');
                    return false;
				}

				if( searchLinksTextField.val() !== '' && searchLinksColumn.val() !== '' ) {
					searchLinksTextField.removeClass('blm-field-required');
					searchLinksColumn.removeClass('blm-field-required');
				}
				var data = {
					'paged': parseInt( $('input[name=paged]').val() ) || '1',
					'order': $('input[name=order]').val() || 'asc',
					'orderby': $('input[name=orderby]').val() || 'date',
					'search_field': searchLinksTextField.val(),
					'search_column': searchLinksColumn.val()
				};
				blmAjaxLinksListTable.update( data );
			});

			/**
			 * Reset Button blm-links-reset-btn
			 */
			
			$('#blm-links-reset-btn').click(function( e ) {
				e.preventDefault();
				e.stopImmediatePropagation();
				$( "#blm-search-column option[value='']" ).attr( 'selected', true );
				searchLinksTextField.val('');
				var data = {
					'paged': parseInt( $('input[name=paged]').val() ) || '1',
					'order': $('input[name=order]').val() || 'asc',
					'orderby': $('input[name=orderby]').val() || 'date',
					'search_field': '',
					'search_column': ''
				};
				blmAjaxLinksListTable.update( data );

			});

			/**
			 * Delete Links
			 */
			$( '.blm-delete-link' ).on( 'click', function( e ) {

				e.preventDefault();
				var link_id = $( this ).attr( 'id' ),
				 	currentRow = $('#tr-'+ link_id ),
					data = {
					'paged': parseInt( $('input[name=paged]').val() ) || '1',
					'order': $('input[name=order]').val() || 'asc',
					'orderby': $('input[name=orderby]').val() || 'date',
					'search_field': searchLinksTextField.val() || '',
					'search_column': searchLinksColumn.val() || '',
					'deletion_id': link_id || ''
				};

				$.confirm({
                    title: 'Remove Link!',
                    content: 'Are you sure you want to remove this link ?',
                    type: 'red',
                    container: 'body',
                    closeIcon: true,
                    buttons: {
                        removeBtn: {
                            text: 'Remove',
                            btnClass: 'btn-red',
                            action: function(){
								currentRow.css({"backgroundColor" : "red", "font-weight":"bold"});
								currentRow.slideUp(500,function() {
									currentRow.remove();
								});
								$( "#blm-search-column option[value='']" ).attr( 'selected', true );
								searchLinksTextField.val('');
								blmAjaxLinksListTable.update( data );
                            },
                        },
                        close: function () {
                        }
                    }
                });
			});
		},
		
		update: function( data ) {
			$.ajax({
				url: BLMP_Plugin_Localize.ajax_url,
				data: $.extend(
					{
						'blm_ajax_custom_list_nonce': BLMP_Plugin_Localize.blm_ajax_nonce_name,
						'action': 'bl_monitoring_list_table_ajax',
					},
					data
				),
				beforeSend: function() {
					$('.top .bulkactions').append( '<div class="page-content" id="loader">'+BLMP_Plugin_Localize.content+'<img src="' + BLMP_Plugin_Localize.imgsrc + '"/></div>' );
					$('#blm-links-search-btn').prop('disabled', true);
				},
				success: function( successResponse ) {
					var response = $.parseJSON( successResponse );
					$('.top .bulkactions #loader').remove();

					if ( response.rows.length ) {
						$('#the-list').html( response.rows );
					}	
					if ( response.column_headers.length ) {
						$('thead tr, tfoot tr').html( response.column_headers );
					}
					if ( response.pagination.bottom.length ) {
						$('.tablenav.top .tablenav-pages').html( $(response.pagination.top).html() );
					}
					if ( response.pagination.top.length ) {
						$('.tablenav.bottom .tablenav-pages').html( $(response.pagination.bottom).html() );
					}
					blmAjaxLinksListTable.init();
					$('#blm-links-search-btn').prop('disabled', false);
				},
			});
			
		},
		__query: function( query, variable ) {
			var vars = query.split("&");
			for ( var i = 0; i < vars.length; i+= 1 ) {
				var pair = vars[ i ].split("=");
				if ( pair[0] == variable ){
					return pair[1];
				}
			}
			return false;
		},
	};

	blmAjaxLinksListTable.init();
  	});

})(jQuery);
