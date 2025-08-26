let suffmasm_modal = ( show = true ) => {
	if(show) {
		jQuery('#suffix-master-modal').show();
	}
	else {
		jQuery('#suffix-master-modal').hide();
	}
}

jQuery(function($){

	// Suffix Master Live Preview Functionality
	if (typeof SUFFIXMASTER !== 'undefined') {
		// Wait for the page to be fully loaded before initializing
		$(document).ready(function() {
			setTimeout(function() {
				initSuffixMasterPreview();
				initResetAllFunctionality();
			}, 100);
		});

		// Also try to initialize when the window is fully loaded
		$(window).on('load', function() {
			setTimeout(function() {
				if ($('#suffix_master_tools-live_preview').is(':checked') && $('#suffix-master-preview').length === 0) {
					initSuffixMasterPreview();
				}
			}, 200);
		});

		// Manual trigger for debugging - can be called from browser console
		window.initSuffixMasterPreview = function() {
			initSuffixMasterPreview();
		};
	}

	function initSuffixMasterPreview() {
		// Check if we're on the settings page
		// if ($('.cx-settings-wrapper').length === 0 && $('.wrap').length === 0) {
		// 	return;
		// }

		// Check if live preview is enabled
		const livePreviewEnabled = $('#suffix_master_tools-live_preview').is(':checked');

		if (!livePreviewEnabled) {
			// Still bind the toggle event even if disabled
			bindLivePreviewToggle();
			return;
		}

		// Create preview container if it doesn't exist
		if ($('#suffix-master-preview').length === 0) {
			// Try multiple container selectors
			let container = $('.cx-settings-wrapper');
			if (container.length === 0) {
				container = $('.wrap');
			}
			if (container.length === 0) {
				container = $('body');
			}

			container.prepend(`
				<div id="suffix-master-preview" class="notice notice-info" style="margin-bottom: 20px; padding: 15px;">
					<h3>🔍 Live Preview</h3>
					<div id="preview-content">
						<p><strong>Sample Post Title:</strong> <span id="preview-post-title">My Sample Post</span></p>
						<p><strong>Sample Page Title:</strong> <span id="preview-page-title">My Sample Page</span></p>
						<p><strong>Sample Product Title:</strong> <span id="preview-product-title">My Sample Product</span></p>
						<p><strong>Sample Price:</strong> <span id="preview-price">$19.99</span></p>
						<p><strong>Sample Slug:</strong> <span id="preview-slug">my-sample-post</span></p>
					</div>
				</div>
			`);
		}

		// Bind input events for live preview with delay to ensure elements exist
		setTimeout(function() {
			bindInputEvents();
		}, 200);

		// Bind toggle event
		bindLivePreviewToggle();

		// Initial preview update
		updatePreview();
	}

	function bindInputEvents() {
		// Define the correct field selectors with section prefixes
		const fieldSelectors = [
			'#suffix_master_global-global_title_suffix',
			'#suffix_master_global-post_title_suffix',
			'#suffix_master_global-page_title_suffix',
			'#suffix_master_global-global_slug_suffix',
			'#suffix_master_woocommerce-wc_product_title_suffix',
			'#suffix_master_woocommerce-global_price_suffix',
			'#suffix_master_woocommerce-wc_slug_suffix'
		].join(', ');

		// Unbind existing events to prevent duplicates
		$(fieldSelectors).off('input.livepreview keyup.livepreview');

		// Bind input events for live preview
		$(fieldSelectors).on('input.livepreview', function() {
			updatePreview();
		});

		// Also bind to keyup for better responsiveness
		$(fieldSelectors).on('keyup.livepreview', function() {
			updatePreview();
		});

		// Debug: Log which fields were found
		console.log('Live Preview: Bound to', $(fieldSelectors).length, 'input fields');
		console.log('Field selectors:', fieldSelectors);

		// Debug: Check each field individually
		const individualFields = [
			'suffix_master_global-global_title_suffix',
			'suffix_master_global-post_title_suffix',
			'suffix_master_global-page_title_suffix',
			'suffix_master_global-global_slug_suffix',
			'suffix_master_woocommerce-wc_product_title_suffix',
			'suffix_master_woocommerce-global_price_suffix',
			'suffix_master_woocommerce-wc_slug_suffix'
		];

		individualFields.forEach(function(fieldId) {
			const element = $('#' + fieldId);
			console.log('Field #' + fieldId + ':', element.length ? 'FOUND' : 'NOT FOUND');
		});
	}

	function bindLivePreviewToggle() {
		// Toggle live preview on/off
		$('#suffix_master_tools-live_preview').off('change.livepreview').on('change.livepreview', function() {
			if ($(this).is(':checked')) {
				initSuffixMasterPreview();
			} else {
				$('#suffix-master-preview').remove();
			}
		});
	}

	function updatePreview() {
		// Check if preview container exists
		if ($('#suffix-master-preview').length === 0) {
			return;
		}

		const currentYear = new Date().getFullYear();
		const siteName = (typeof SUFFIXMASTER !== 'undefined' && SUFFIXMASTER.site_name) ? SUFFIXMASTER.site_name : 'Your Site';

		// Process placeholders
		function processPlaceholders(text) {
			if (!text) return '';
			return text.replace(/{year}/g, currentYear).replace(/{site_name}/g, siteName);
		}

		// Helper function to safely get input values
		function getInputValue(selector) {
			const element = $(selector);
			return element.length ? element.val() : '';
		}

		// Update post title preview (post-specific > global)
		const postTitleSuffix = processPlaceholders(getInputValue('#suffix_master_global-post_title_suffix')) ||
								processPlaceholders(getInputValue('#suffix_master_global-global_title_suffix'));
		const basePostTitle = 'My Sample Post';
		$('#preview-post-title').text(basePostTitle + (postTitleSuffix ? ' ' + postTitleSuffix : ''));

		// Update page title preview (page-specific > global)
		const pageTitleSuffix = processPlaceholders(getInputValue('#suffix_master_global-page_title_suffix')) ||
								processPlaceholders(getInputValue('#suffix_master_global-global_title_suffix'));
		const basePageTitle = 'My Sample Page';
		$('#preview-page-title').text(basePageTitle + (pageTitleSuffix ? ' ' + pageTitleSuffix : ''));

		// Update product title preview
		const productTitleSuffix = processPlaceholders(getInputValue('#suffix_master_woocommerce-wc_product_title_suffix'));
		const baseProductTitle = 'My Sample Product';
		$('#preview-product-title').text(baseProductTitle + (productTitleSuffix ? ' ' + productTitleSuffix : ''));

		// Update price preview
		const priceSuffix = processPlaceholders(getInputValue('#suffix_master_woocommerce-global_price_suffix'));
		const basePrice = '$19.99';
		$('#preview-price').text(basePrice + (priceSuffix ? ' ' + priceSuffix : ''));

		// Update slug preview (handle both global and WC slug suffixes)
		const globalSlugSuffix = processPlaceholders(getInputValue('#suffix_master_global-global_slug_suffix'));
		const wcSlugSuffix = processPlaceholders(getInputValue('#suffix_master_woocommerce-wc_slug_suffix'));
		const slugSuffix = wcSlugSuffix || globalSlugSuffix; // WC specific takes priority
		const baseSlug = 'my-sample-post';
		const slugWithSuffix = baseSlug + (slugSuffix ? '-' + slugSuffix.toLowerCase().replace(/[^a-z0-9]/g, '-') : '');
		$('#preview-slug').text(slugWithSuffix);

		// Debug logging (only if debug mode is enabled)
		if (typeof SUFFIXMASTER !== 'undefined' && SUFFIXMASTER.debug) {
			console.log('Live Preview Updated:', {
				postTitle: basePostTitle + (postTitleSuffix ? ' ' + postTitleSuffix : ''),
				pageTitle: basePageTitle + (pageTitleSuffix ? ' ' + pageTitleSuffix : ''),
				productTitle: baseProductTitle + (productTitleSuffix ? ' ' + productTitleSuffix : ''),
				price: basePrice + (priceSuffix ? ' ' + priceSuffix : ''),
				slug: slugWithSuffix
			});
		}
	}



	function initResetAllFunctionality() {
		$('#suffix-master-reset-all').on('click', function(e) {
			e.preventDefault();

			if (confirm('Are you sure you want to reset all Suffix Master settings? This action cannot be undone.')) {
				// Show loading state
				$(this).prop('disabled', true).text('Resetting...');

				// AJAX call to reset settings
				$.ajax({
					url: SUFFIXMASTER.ajaxurl,
					type: 'POST',
					data: {
						action: 'suffix_master_reset_all',
						_wpnonce: SUFFIXMASTER._wpnonce
					},
					success: function(response) {
						if (response.success) {
							// Clear all form fields (using correct IDs with section prefixes)
							$('#suffix_master_global-global_title_suffix, #suffix_master_global-post_title_suffix, #suffix_master_global-page_title_suffix, #suffix_master_global-global_slug_suffix').val('');
							$('#suffix_master_woocommerce-wc_product_title_suffix, #suffix_master_woocommerce-global_price_suffix, #suffix_master_woocommerce-wc_slug_suffix').val('');
							$('#suffix_master_global-content_footer_suffix').val('');

							// Update preview
							updatePreview();

							// Show success message
							$('<div class="notice notice-success is-dismissible"><p>All settings have been reset successfully.</p></div>')
								.insertAfter('.cx-heading');

							// Auto-dismiss notice after 3 seconds
							setTimeout(function() {
								$('.notice-success').fadeOut();
								location.reload();
							}, 3000);
						} else {
							alert('Error resetting settings: ' + (response.data || 'Unknown error'));
						}
					},
					error: function() {
						alert('Error resetting settings. Please try again.');
					},
					complete: function() {
						$('#suffix-master-reset-all').prop('disabled', false).text('Reset All Settings');
					}
				});
			}
		});
	}

	// Copy report functionality
	$('#suffix-master-report-copy').on('click', function(e) {
		e.preventDefault();
		const reportText = $('#report').val();

		// Create temporary textarea to copy text
		const tempTextarea = $('<textarea>');
		$('body').append(tempTextarea);
		tempTextarea.val(reportText).select();
		document.execCommand('copy');
		tempTextarea.remove();

		// Show feedback
		$(this).text('Copied!');
		setTimeout(() => {
			$(this).html('<span class="dashicons dashicons-admin-page"></span> Copy Report');
		}, 2000);
	});

})