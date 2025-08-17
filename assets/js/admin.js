let pc_modal = ( show = true ) => {
	if(show) {
		jQuery('#plugin-client-modal').show();
	}
	else {
		jQuery('#plugin-client-modal').hide();
	}
}

jQuery(function($){

	// Suffix Master Live Preview Functionality
	if (typeof SUFFIXMASTER !== 'undefined') {
		initSuffixMasterPreview();
		initResetAllFunctionality();
	}

	function initSuffixMasterPreview() {
		// Check if live preview is enabled
		const livePreviewEnabled = $('#live_preview').is(':checked');

		if (!livePreviewEnabled) {
			return;
		}

		// Create preview container if it doesn't exist
		if ($('#suffix-master-preview').length === 0) {
			$('.cx-settings-wrapper').prepend(`
				<div id="suffix-master-preview" class="notice notice-info" style="margin-bottom: 20px; padding: 15px;">
					<h3>Live Preview</h3>
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

		// Bind input events for live preview
		$('#global_title_suffix, #post_title_suffix, #page_title_suffix, #wc_product_title_suffix, #global_price_suffix, #global_slug_suffix, #wc_slug_suffix').on('input', function() {
			updatePreview();
		});

		// Initial preview update
		updatePreview();
	}

	function updatePreview() {
		const currentYear = new Date().getFullYear();
		const siteName = SUFFIXMASTER.site_name || 'Your Site';

		// Process placeholders
		function processPlaceholders(text) {
			if (!text) return '';
			return text.replace(/{year}/g, currentYear).replace(/{site_name}/g, siteName);
		}

		// Update post title preview (post-specific > global)
		const postTitleSuffix = processPlaceholders($('#post_title_suffix').val()) ||
								processPlaceholders($('#global_title_suffix').val());
		const basePostTitle = 'My Sample Post';
		$('#preview-post-title').text(basePostTitle + (postTitleSuffix ? ' ' + postTitleSuffix : ''));

		// Update page title preview (page-specific > global)
		const pageTitleSuffix = processPlaceholders($('#page_title_suffix').val()) ||
								processPlaceholders($('#global_title_suffix').val());
		const basePageTitle = 'My Sample Page';
		$('#preview-page-title').text(basePageTitle + (pageTitleSuffix ? ' ' + pageTitleSuffix : ''));

		// Update product title preview
		const productTitleSuffix = processPlaceholders($('#wc_product_title_suffix').val());
		const baseProductTitle = 'My Sample Product';
		$('#preview-product-title').text(baseProductTitle + (productTitleSuffix ? ' ' + productTitleSuffix : ''));

		// Update price preview
		const priceSuffix = processPlaceholders($('#global_price_suffix').val());
		const basePrice = '$19.99';
		$('#preview-price').text(basePrice + (priceSuffix ? ' ' + priceSuffix : ''));

		// Update slug preview
		const slugSuffix = processPlaceholders($('#global_slug_suffix').val());
		const baseSlug = 'my-sample-post';
		const slugWithSuffix = baseSlug + (slugSuffix ? '-' + slugSuffix.toLowerCase().replace(/[^a-z0-9]/g, '-') : '');
		$('#preview-slug').text(slugWithSuffix);
	}

	// Toggle live preview on/off
	$('#live_preview').on('change', function() {
		if ($(this).is(':checked')) {
			initSuffixMasterPreview();
		} else {
			$('#suffix-master-preview').remove();
		}
	});

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
							// Clear all form fields
							$('#global_title_suffix, #post_title_suffix, #page_title_suffix, #wc_product_title_suffix, #global_price_suffix, #global_slug_suffix, #wc_slug_suffix').val('');
							$('#content_footer_suffix').val('');

							// Update preview
							updatePreview();

							// Show success message
							$('<div class="notice notice-success is-dismissible"><p>All settings have been reset successfully.</p></div>')
								.insertAfter('.cx-settings-header');

							// Auto-dismiss notice after 3 seconds
							setTimeout(function() {
								$('.notice-success').fadeOut();
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