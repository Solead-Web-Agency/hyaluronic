(()=>{
/**
 * 2013 - 2024 Payplug SAS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0).
 * It is available through the world-wide-web at this URL:
 * https://opensource.org/licenses/osl-3.0.php
 * If you are unable to obtain it through the world-wide-web, please send an email
 * to contact@payplug.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PayPlug module to newer
 * versions in the future.
 *
 *  @author    PayPlug SAS
 *  @copyright 2013 - 2024 Payplug SAS
 *  @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  @version   3.4.0
 *  International Registered Trademark & Property of PayPlug SAS
 */
getHtmlTags=function(a){var e=document.createElement("div");e.innerHTML=a,allTags=e.getElementsByTagName("*");for(var t=[],n=0,r=allTags.length;n<r;n++){var d=allTags[n].tagName;-1===t.indexOf(d)&&t.push(d)}return t},sanitizePopupHtml=function(a){if(!a.match(/ on\w+="[^"]*"/g)){return tags=this.getHtmlTags(a),["DIV","P","INPUT","BUTTON","A","UL","U","LI","STRONG","SPAN","FORM","SMALL","B","BR"].filter((a=>-1!==tags.indexOf(a))).length===tags.length}};var a={props:{loadedScript:[]},init:function(){},loadScript:function(e,t){if(a.props.loadedScript.includes(e))return t();var n=document.createElement("script");n.type="text/javascript",n.readyState?n.onreadystatechange=function(){"loaded"!==n.readyState&&"complete"!==n.readyState||(a.props.loadedScript.push(e),n.onreadystatechange=null,t())}:n.onload=function(){a.props.loadedScript.push(e),t()},n.src=e,document.getElementsByTagName("head")[0].appendChild(n)}};addLogger=function(a){$.ajax({url:payplug_ajax_url,headers:{"cache-control":"no-cache"},type:"POST",async:!0,cache:!1,dataType:"json",data:{_ajax:1,addLogger:!0,message:a}})},window.payplug_utilities=a})();