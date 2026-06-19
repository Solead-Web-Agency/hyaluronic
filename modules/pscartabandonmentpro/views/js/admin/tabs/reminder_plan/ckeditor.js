/**
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
* @author    PrestaShop SA <contact@prestashop.com>
* @copyright 2007-2019 PrestaShop SA
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* International Registered Trademark & Property of PrestaShop SA
*/


getDataAndWrite = (name) => {
	let customText = CKEDITOR.instances[name].getData();
	let previewSection = '.cap-email_custom_content_here';

	if (CKEDITOR.instances[name].element.hasClass('email_unsubscribe')) {
		previewSection = '.cap-email_preview_unsubscribe';
	} else if (CKEDITOR.instances[name].element.hasClass('email_discount')) {
		previewSection = '.cap-email_discount_content_here';
	}

	setPreviewCustomText(previewSection, customText);

	// Reload colors
	let hexaColorPrimary = $('#primary_color .data_input').val();
	let hexaColorSecondary = $('#secondary_color .data_input').val();
	liveEditColor('primary', hexaColorPrimary);
	liveEditColor('secondary', hexaColorSecondary);
	
	$('.cap-editor[name="'+name+'"]').val(customText);
}

isCkEditorAlreadyExist = (instance_name) => {
	let instance = CKEDITOR.instances[instance_name];

	if (instance === undefined) 
		return false;
	else 
		return true;
}

initializeCkEditors = (allEditors) => {
	/*
	* Start ckeditors
	*/
	for (let i = 0; i < allEditors.length; ++i) {
		let name = $(allEditors[i]).attr('name');
		
		if (!isCkEditorAlreadyExist(name)) {	
			CKEDITOR.replace(name);
			setTimeout(function() {
				getDataAndWrite(name);
			}, 500);
			
			CKEDITOR.instances[name].on('change', (e) => {
				getDataAndWrite(name);
			});
		}
	}
}

destroyCkEditors = (allEditors) => {
	/*
	* destroy ckeditors
	*/
	for (let i = 0; i < allEditors.length; ++i) {
		let name = $(allEditors[i]).attr('name');
		if (isCkEditorAlreadyExist(name)) {
			CKEDITOR.instances[name].destroy();
		}
	}
}

$(document).ready(function(){
	let allEditors = $('.cap-editor:first');
	initializeCkEditors(allEditors);
});

$(document).on('click', '#cap_content_conf', function() {
	let allEditors = $('.cap-editor:visible');
	initializeCkEditors(allEditors);
	
	// Load unsubscribeText
	let unsubscribeText = $('#email_unsubscribe_text:visible input');
	showSpecificUnsubscribeText(unsubscribeText);
});

/*
* Load the language content on live preview
*/
$(document).on('change', 'select[name="cap-email-language"]', (e) => {
	let lang = $(e.target).val();
	// Timeout is here to wait the ck-editor being shown 
	setTimeout( () => {
		$('.cap-lang_'+lang+' .ck-editor__editable').trigger('keyup');
		$('.cap-email-socials input.cap-lang_'+lang).trigger('keyup');
	}, 50);
});

/*
* Adding custom content on ckeditor
*/
$(document).on('click', '.email_content_custom', (e) => {
	let elem = e.target;

	if ($(elem).hasClass('material-icons')) {
		elem = $(elem).parent();
	}
	
	let data = $(elem).data('content');
	let type = $(elem).data('type');
	let elemType = '.email_'+type;
	let ckeditorInstance = $('.content_by_lang:visible textarea'+elemType).attr('name');

	CKEDITOR.instances[ckeditorInstance].insertHtml(data);
	customText = CKEDITOR.instances[ckeditorInstance].getData();

	if (type == 'content') {		
		setPreviewCustomText('.cap-email_custom_content_here', customText);
	} else if (type == 'discount'){
		setPreviewCustomText('.cap-email_discount_content_here', customText);
	} else {
		setPreviewCustomText('.cap-email_preview_unsubscribe', customText);
	}
});
