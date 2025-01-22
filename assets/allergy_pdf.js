jQuery(document).ready(function ($) {
	let file_frame;

	$('#sr-change-pdf-button').on('click', function (e) {
			e.preventDefault();

			// If the media frame already exists, reopen it.
			if (file_frame) {
					file_frame.open();
					return;
			}

			// Create the media frame.
			file_frame = wp.media({
					title: 'Select or Upload a PDF',
					button: {
							text: 'Use This PDF',
					},
					library: {
							type: 'application/pdf', // Restrict to PDFs only
					},
					multiple: false, // Only allow single file selection
			});

			// When a file is selected, run a callback.
			file_frame.on('select', function () {
					// Get the selected file.
					const attachment = file_frame.state().get('selection').first().toJSON();

					const newPdfUrl = attachment.url; // Get the URL of the selected PDF

					// Update the embedded PDF and link dynamically
					document.getElementById('pdf-object').setAttribute('data', newPdfUrl);
					document.getElementById('pdf-link').setAttribute('href', newPdfUrl);
					document.getElementById('pdf-link').textContent = attachment.filename; // Update the link text with the file name

					// Send AJAX request to update the PDF
					$.ajax({
							url: ajaxurl, // WordPress AJAX URL
							type: 'POST',
							data: {
									action: 'update_allergy_pdf',
									pdf_url: attachment.url, // Pass the PDF URL
							},
							success: function (response) {
									if (response.success) {
											// Update success message
											updateInlineMessage('success', response.data.message);

											// Update the "View Current PDF" link
											const currentPdfLink = $('#sr-view-pdf');
											if (currentPdfLink.length) {
													currentPdfLink.attr('href', attachment.url); // Update href
											} else {
													// If the link doesn't exist, create it dynamically
													$('#sr-pdf').html(`
															<p>
																	<strong>Current PDF:</strong>
																	<a id="sr-view-pdf" href="${attachment.url}" target="_blank">View Current PDF</a>
															</p>
													`);
											}
									} else {
											updateInlineMessage('error', response.data.message);
									}
							},
							error: function () {
									updateInlineMessage('error', 'An error occurred while updating the PDF.');
							},
					});
			});

			// Open the media frame.
			file_frame.open();
	});

	// Function to update inline success or error message
	function updateInlineMessage(type, message) {
			const messageClass = type === 'success' ? 'updated' : 'error';
			const messageHTML = `<div class="${messageClass}"><p>${message}</p></div>`;

			// Remove any existing messages and add the new one
			$('#sr-allergy-message').html(messageHTML);
	}
});
