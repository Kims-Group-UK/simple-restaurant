(()=>{"use strict";const e=window.React,{registerBlockType:t}=wp.blocks,{InspectorControls:a}=wp.blockEditor,{PanelBody:l,SelectControl:n}=wp.components,{useState:o,useEffect:r}=wp.element,s=({attributes:t,setAttributes:s})=>{const[c,i]=o([]),[m,u]=o([]),[d,b]=o([]),[p,j]=o(""),[_,h]=o(""),[E,g]=o(""),[f,v]=o([]),[y,N]=o(null),{layout:w}=t||{};return r((()=>{fetch(`${sr_job_ajax_object.ajax_url}?action=get_locations`).then((e=>e.json())).then((e=>i(e))),fetch(`${sr_job_ajax_object.ajax_url}?action=get_positions`).then((e=>e.json())).then((e=>u(e))),fetch(`${sr_job_ajax_object.ajax_url}?action=get_types`).then((e=>e.json())).then((e=>b(e)))}),[]),(0,e.createElement)(e.Fragment,null,(0,e.createElement)(a,null,(0,e.createElement)(l,{title:"Layout Settings"},(0,e.createElement)(n,{label:"Layout",value:w,options:[{label:"Left-to-Right",value:"left-right"},{label:"Right-to-Left",value:"right-left"},{label:"Stacked",value:"stacked"}],onChange:e=>(e=>{s({layout:e})})(e)}))),(0,e.createElement)("div",{className:`container layout-${w}`},(0,e.createElement)("div",{className:"row"},(0,e.createElement)("div",{className:"col-md-4 col-sm-12"},(0,e.createElement)("div",{className:"sr-job-filter"},(0,e.createElement)(n,{label:"Location",value:p,options:c.map((e=>({label:e.name,value:e.id}))),onChange:j}),(0,e.createElement)(n,{label:"Position",value:_,options:m.map((e=>({label:e.name,value:e.id}))),onChange:h}),(0,e.createElement)(n,{label:"Type",value:E,options:d.map((e=>({label:e.name,value:e.id}))),onChange:g}),(0,e.createElement)("button",{className:"button button-primary",onClick:()=>{const e=new FormData;e.append("action","sr_job_filter_jobs"),e.append("sr-job-filter-nonce",sr_job_ajax_object.sr_job_filter_nonce),p&&e.append("location",p),_&&e.append("position",_),E&&e.append("type",E),fetch(sr_job_ajax_object.ajax_url,{method:"POST",body:e}).then((e=>e.json())).then((e=>v(e)))}},"Filter Jobs"))),(0,e.createElement)("div",{className:"col-md-8 col-sm-12"},f.length>0&&(0,e.createElement)("div",{className:"sr-job-results"},f.map((t=>(0,e.createElement)("div",{className:"col",key:t.id},(0,e.createElement)("h2",{className:"has-large-font-size"},(0,e.createElement)("strong",null,t.title)),y!==t.id?(0,e.createElement)(e.Fragment,null,(0,e.createElement)("p",{className:"has-medium-font-size",dangerouslySetInnerHTML:{__html:t.excerpt}}),(0,e.createElement)("button",{onClick:()=>{return e=t.id,void N(y===e?null:e);var e}},"View More")):(0,e.createElement)("div",{id:`sr-job-content-${t.id}`,className:"job-content"},(0,e.createElement)("p",{dangerouslySetInnerHTML:{__html:t.content}}),(0,e.createElement)("a",{className:"sr-job-apply",href:t.apply_url},"Apply"))))))))))};t("sr/job-filter",{title:"Job Filter",category:"widgets",attributes:{layout:{type:"string",default:"stacked"}},edit:s,save:()=>null}),document.addEventListener("DOMContentLoaded",(function(){const t=document.getElementById("sr-job-filter-root");if(t){const a=JSON.parse(t.getAttribute("data-attributes"));ReactDOM.render((0,e.createElement)(s,{attributes:a}),t)}}))})();