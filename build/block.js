(()=>{"use strict";var e={n:t=>{var n=t&&t.__esModule?()=>t.default:()=>t;return e.d(n,{a:n}),n},d:(t,n)=>{for(var a in n)e.o(n,a)&&!e.o(t,a)&&Object.defineProperty(t,a,{enumerable:!0,get:n[a]})},o:(e,t)=>Object.prototype.hasOwnProperty.call(e,t)};const t=window.React,n=window.wp.blocks,a=window.wp.element,o=window.wp.components,l=window.ReactDOM;var r=e.n(l);const c=()=>{const[e,n]=(0,a.useState)([]),[l,r]=(0,a.useState)([]),[c,s]=(0,a.useState)([]),[i,d]=(0,a.useState)(""),[m,p]=(0,a.useState)(""),[u,_]=(0,a.useState)(""),[b,j]=(0,a.useState)([]),[h,y]=(0,a.useState)(null);return(0,a.useEffect)((()=>{fetch(`${sr_job_ajax_object.ajax_url}?action=get_locations`).then((e=>e.json())).then((e=>n(e))),fetch(`${sr_job_ajax_object.ajax_url}?action=get_positions`).then((e=>e.json())).then((e=>r(e))),fetch(`${sr_job_ajax_object.ajax_url}?action=get_types`).then((e=>e.json())).then((e=>s(e)))}),[]),(0,t.createElement)("div",null,(0,t.createElement)("div",{className:"container"},(0,t.createElement)("div",{className:"row"},(0,t.createElement)("div",{className:"col-md-12 col-sm-12"},(0,t.createElement)(o.SelectControl,{label:"Location",value:i,options:e.map((e=>({label:e.name,value:e.id}))),onChange:d}),(0,t.createElement)(o.SelectControl,{label:"Position",value:m,options:l.map((e=>({label:e.name,value:e.id}))),onChange:p}),(0,t.createElement)(o.SelectControl,{label:"Type",value:u,options:c.map((e=>({label:e.name,value:e.id}))),onChange:_}),(0,t.createElement)(o.Button,{isPrimary:!0,onClick:()=>{const e=new FormData;e.append("action","sr_job_filter_jobs"),e.append("sr-job-filter-nonce",sr_job_ajax_object.sr_job_filter_nonce),i&&e.append("location",i),m&&e.append("position",m),u&&e.append("type",u),fetch(sr_job_ajax_object.ajax_url,{method:"POST",body:e}).then((e=>e.json())).then((e=>j(e)))}},"Filter Jobs")),(0,t.createElement)("div",{className:"col-md-12 col-sm-12"},b.length>0&&(0,t.createElement)("ul",null,b.map((e=>(0,t.createElement)("li",{key:e.id},(0,t.createElement)("h4",null,e.title),(0,t.createElement)("p",{dangerouslySetInnerHTML:{__html:e.excerpt}}),h!=e.id?(0,t.createElement)("button",{key:e.id,onClick:()=>{return t=e.id,void y(t);var t}},"View More"):(0,t.createElement)("div",{key:e.id,id:`sr-job-content-${e.id}`,className:"job-content",style:{display:h===e.id?"block":"none"}},(0,t.createElement)("p",{dangerouslySetInnerHTML:{__html:e.content}}),(0,t.createElement)("a",{class:"sr-j0b-apply",href:e.apply_url},"Apply"))))))))))};(0,n.registerBlockType)("srm/job-filter",{title:"Job Filter",category:"widgets",edit:c,save:()=>null}),document.addEventListener("DOMContentLoaded",(()=>{const e=document.getElementById("sr-job-filter-root");e&&r().render((0,t.createElement)(c,null),e)}))})();