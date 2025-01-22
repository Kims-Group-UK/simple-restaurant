
	const { registerBlockType } = wp.blocks;
	const { InspectorControls } = wp.blockEditor;
	const { PanelBody, SelectControl } = wp.components;
	const { useState, useEffect } = wp.element;

	const JobFilterBlock = ({ attributes, setAttributes }) => {
			const [locations, setLocations] = useState([]);
			const [positions, setPositions] = useState([]);
			const [types, setTypes] = useState([]);
			const [selectedLocation, setSelectedLocation] = useState('');
			const [selectedPosition, setSelectedPosition] = useState('');
			const [selectedType, setSelectedType] = useState('');
			const [jobs, setJobs] = useState([]);
			const [visibleId, setVisibleId] = useState(null);
			const { layout } = attributes || {}; // Destructure layout attribute
			// Ensure to handle updates to layout using setAttributes
			const handleLayoutChange = (newLayout) => {
				setAttributes({ layout: newLayout });
			};

			useEffect(() => {
					fetch(`${sr_job_ajax_object.ajax_url}?action=get_locations`)
							.then(response => response.json())
							.then(data => setLocations(data));

					fetch(`${sr_job_ajax_object.ajax_url}?action=get_positions`)
							.then(response => response.json())
							.then(data => setPositions(data));

					fetch(`${sr_job_ajax_object.ajax_url}?action=get_types`)
							.then(response => response.json())
							.then(data => setTypes(data));
			}, []);

			const handleFilter = () => {
					const data = new FormData();
					data.append('action', 'sr_job_filter_jobs');
					data.append('sr-job-filter-nonce', sr_job_ajax_object.sr_job_filter_nonce);
					if (selectedLocation) data.append('location', selectedLocation);
					if (selectedPosition) data.append('position', selectedPosition);
					if (selectedType) data.append('type', selectedType);

					fetch(sr_job_ajax_object.ajax_url, {
							method: 'POST',
							body: data,
					})
							.then(response => response.json())
							.then(data => setJobs(data));
			};

			const toggleVisibility = (id) => {
					setVisibleId(visibleId === id ? null : id);
			};

			return (
					<>
							{/* Inspector Controls */}
							<InspectorControls>
									<PanelBody title="Layout Settings">
											<SelectControl
													label="Layout"
													value={layout}
													options={[
															{ label: 'Left-to-Right', value: 'left-right' },
															{ label: 'Right-to-Left', value: 'right-left' },
															{ label: 'Stacked', value: 'stacked' },
													]}
													onChange={(newLayout) => handleLayoutChange(newLayout)}
											/>
									</PanelBody>
							</InspectorControls>

							{/* Block Content */}
							<div className={`container layout-${layout}`}>
									<div className="row">
											{/* Filters */}
											<div className="col-md-4 col-sm-12">
												<div className="sr-job-filter">
													<SelectControl
															label="Location"
															value={selectedLocation}
															options={locations.map(location => ({ label: location.name, value: location.id }))}
															onChange={setSelectedLocation}
													/>
													<SelectControl
															label="Position"
															value={selectedPosition}
															options={positions.map(position => ({ label: position.name, value: position.id }))}
															onChange={setSelectedPosition}
													/>
													<SelectControl
															label="Type"
															value={selectedType}
															options={types.map(type => ({ label: type.name, value: type.id }))}
															onChange={setSelectedType}
													/>
													<button className="button button-primary" onClick={handleFilter}>
															Filter Jobs
													</button>
												</div>
											</div>

											{/* Job Results */}
											<div className="col-md-8 col-sm-12">
													{jobs.length > 0 && (
															<div className="sr-job-results">
																	{jobs.map(job => (
																			<div className="col" key={job.id}>
																					<h2 className="has-large-font-size" dangerouslySetInnerHTML={{ __html: job.title }}></h2>
																					{visibleId !== job.id ? (<>
																							<p className="has-medium-font-size" dangerouslySetInnerHTML={{ __html: job.excerpt }} />
																								<button onClick={() => toggleVisibility(job.id)}>View More</button>
																						</>
																					) : (
																							<div id={`sr-job-content-${job.id}`} className="job-content">
																									<p dangerouslySetInnerHTML={{ __html: job.content }} />
																									<a className="sr-job-apply" href={job.apply_url}>Apply</a>
																							</div>
																					)}
																			</div>
																	))}
															</div>
													)}
											</div>
									</div>
							</div>
					</>
			);
	};

	registerBlockType('sr/job-filter', {
			title: 'Job Filter',
			category: 'widgets',
			attributes: {
					layout: {
							type: 'string',
							default: 'stacked',
					},
			},
			edit: JobFilterBlock,
			save: () => null, // Dynamic block
	});

	document.addEventListener('DOMContentLoaded', function () {
		const root = document.getElementById('sr-job-filter-root');
		// Check if the root element exists
		if (root) {
				// Get the block attributes stored in data-attributes
				const attributes = JSON.parse(root.getAttribute('data-attributes'));

				// Render the JobFilterBlock with attributes passed as props
				ReactDOM.render(<JobFilterBlock attributes={attributes} />, root);
		}
	});
