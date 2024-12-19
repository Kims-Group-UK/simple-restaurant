import { registerBlockType } from '@wordpress/blocks';
import { useEffect, useState } from '@wordpress/element';
import { SelectControl, Button } from '@wordpress/components';
import ReactDOM from 'react-dom'; // Import ReactDOM

const JobFilterBlock = () => {
    const [locations, setLocations] = useState([]);
    const [positions, setPositions] = useState([]);
    const [types, setTypes] = useState([]);

    const [selectedLocation, setSelectedLocation] = useState('');
    const [selectedPosition, setSelectedPosition] = useState('');
    const [selectedType, setSelectedType] = useState('');
    const [jobs, setJobs] = useState([]);

		const [visibleId, setVisibleId] = useState(null); // Initially, no job is visible

		// Function to show a specific job content
		const showHide = (id) => {
			setVisibleId(id); // Set the id of the job to be shown
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

    return (
        <div>
						<div className="container">
							<div className="row">
								<div className="col-md-12 col-sm-12">
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
										<Button isPrimary onClick={handleFilter}>Filter Jobs</Button>
								</div>
								<div className="col-md-12 col-sm-12">
									{jobs.length > 0 && (
                	<ul>
                    {jobs.map(job => (
                        <li key={job.id}>
                            <h4>{job.title}</h4>
                            <p dangerouslySetInnerHTML={{ __html: job.excerpt }}></p>
														{ visibleId != job.id ? 	<button key={job.id} onClick={() => showHide(job.id)}>
																View More
														</button> :
															<div
																	key={job.id}
																	id={`sr-job-content-${job.id}`}
																	className="job-content"
																	style={{ display: visibleId === job.id ? 'block' : 'none' }}
															>
																<p dangerouslySetInnerHTML={{ __html: job.content }}></p>
																<a class="sr-j0b-apply" href={job.apply_url}>Apply</a>
															</div>
														}
                        </li>
                    	))}
                	</ul>
            			)}
								</div>
							</div>
					</div>
        </div>
    );
};

// Register the block
registerBlockType('srm/job-filter', {
    title: 'Job Filter',
    category: 'widgets',
    edit: JobFilterBlock, // Edit function uses the JobFilterBlock
    save: () => {
        return null; // Not used for dynamic blocks
    },
});

// Render the Job Filter Block on the frontend
const renderJobFilterBlock = () => {
    const root = document.getElementById('sr-job-filter-root');
    if (root) {
        ReactDOM.render(<JobFilterBlock />, root);
    }
};

document.addEventListener('DOMContentLoaded', renderJobFilterBlock); // Render after DOM is loaded
