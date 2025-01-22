/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "react":
/*!************************!*\
  !*** external "React" ***!
  \************************/
/***/ ((module) => {

module.exports = window["React"];

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry needs to be wrapped in an IIFE because it needs to be isolated against other modules in the chunk.
(() => {
/*!**********************!*\
  !*** ./src/block.js ***!
  \**********************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);

const {
  registerBlockType
} = wp.blocks;
const {
  InspectorControls
} = wp.blockEditor;
const {
  PanelBody,
  SelectControl
} = wp.components;
const {
  useState,
  useEffect
} = wp.element;
const JobFilterBlock = ({
  attributes,
  setAttributes
}) => {
  const [locations, setLocations] = useState([]);
  const [positions, setPositions] = useState([]);
  const [types, setTypes] = useState([]);
  const [selectedLocation, setSelectedLocation] = useState('');
  const [selectedPosition, setSelectedPosition] = useState('');
  const [selectedType, setSelectedType] = useState('');
  const [jobs, setJobs] = useState([]);
  const [visibleId, setVisibleId] = useState(null);
  const {
    layout
  } = attributes || {}; // Destructure layout attribute
  // Ensure to handle updates to layout using setAttributes
  const handleLayoutChange = newLayout => {
    setAttributes({
      layout: newLayout
    });
  };
  useEffect(() => {
    fetch(`${sr_job_ajax_object.ajax_url}?action=get_locations`).then(response => response.json()).then(data => setLocations(data));
    fetch(`${sr_job_ajax_object.ajax_url}?action=get_positions`).then(response => response.json()).then(data => setPositions(data));
    fetch(`${sr_job_ajax_object.ajax_url}?action=get_types`).then(response => response.json()).then(data => setTypes(data));
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
      body: data
    }).then(response => response.json()).then(data => setJobs(data));
  };
  const toggleVisibility = id => {
    setVisibleId(visibleId === id ? null : id);
  };
  return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(react__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(InspectorControls, null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(PanelBody, {
    title: "Layout Settings"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(SelectControl, {
    label: "Layout",
    value: layout,
    options: [{
      label: 'Left-to-Right',
      value: 'left-right'
    }, {
      label: 'Right-to-Left',
      value: 'right-left'
    }, {
      label: 'Stacked',
      value: 'stacked'
    }],
    onChange: newLayout => handleLayoutChange(newLayout)
  }))), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: `container layout-${layout}`
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "row"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "col-md-4 col-sm-12"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "sr-job-filter"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(SelectControl, {
    label: "Location",
    value: selectedLocation,
    options: locations.map(location => ({
      label: location.name,
      value: location.id
    })),
    onChange: setSelectedLocation
  }), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(SelectControl, {
    label: "Position",
    value: selectedPosition,
    options: positions.map(position => ({
      label: position.name,
      value: position.id
    })),
    onChange: setSelectedPosition
  }), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(SelectControl, {
    label: "Type",
    value: selectedType,
    options: types.map(type => ({
      label: type.name,
      value: type.id
    })),
    onChange: setSelectedType
  }), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
    className: "button button-primary",
    onClick: handleFilter
  }, "Filter Jobs"))), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "col-md-8 col-sm-12"
  }, jobs.length > 0 && (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "sr-job-results"
  }, jobs.map(job => (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "col",
    key: job.id
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("h2", {
    className: "has-large-font-size",
    dangerouslySetInnerHTML: {
      __html: job.title
    }
  }), visibleId !== job.id ? (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(react__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", {
    className: "has-medium-font-size",
    dangerouslySetInnerHTML: {
      __html: job.excerpt
    }
  }), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
    onClick: () => toggleVisibility(job.id)
  }, "View More")) : (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    id: `sr-job-content-${job.id}`,
    className: "job-content"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", {
    dangerouslySetInnerHTML: {
      __html: job.content
    }
  }), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("a", {
    className: "sr-job-apply",
    href: job.apply_url
  }, "Apply")))))))));
};
registerBlockType('sr/job-filter', {
  title: 'Job Filter',
  category: 'widgets',
  attributes: {
    layout: {
      type: 'string',
      default: 'stacked'
    }
  },
  edit: JobFilterBlock,
  save: () => null // Dynamic block
});
document.addEventListener('DOMContentLoaded', function () {
  const root = document.getElementById('sr-job-filter-root');
  // Check if the root element exists
  if (root) {
    // Get the block attributes stored in data-attributes
    const attributes = JSON.parse(root.getAttribute('data-attributes'));

    // Render the JobFilterBlock with attributes passed as props
    ReactDOM.render((0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(JobFilterBlock, {
      attributes: attributes
    }), root);
  }
});
})();

/******/ })()
;
//# sourceMappingURL=block.js.map