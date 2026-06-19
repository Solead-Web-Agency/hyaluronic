/**
* 2007-2022 PrestaShop
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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2022 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*
* Don't forget to prefix your containers with your own identifier
* to avoid any conflicts with others containers.
*/






$(document).ready(function() { 

 $('#loader').hide();

 $( "button" ).click(function() {
    $(".exlcusion_form").addClass("blur");
    $(".soumission_form").addClass("blur"); 
     $('#loader').show();
});

	//cache les élements au début
    var urlcourante = document.location.href; 
    var url = new URL(urlcourante);
    var element = url.searchParams.get("oesfp_element_type");
    var action = url.searchParams.get("action");
    if(element > 0 ){
        $('.exlcusion_form').show();
        $('#exlcusion_form').addClass('selected active');
        $('.indexnow_form').hide();
        $('.soumission_form').hide();
        $('.help_form').hide();
        $('#indexnow_form').removeClass('active');   ;
    }
    else if(action == "soumission"){
         $('.soumission_form').show();
        $('#soumission_form').addClass('selected active');
        $('.exlcusion_form').hide();
        $('.indexnow_form').hide();
        $('.help_form').hide();
        $('#exlcusion_form').removeClass('active');
        $('#indexnow_form').removeClass('active');
        $('#help_form').removeClass('active');
    }
    else if(action == "exclusion"){
         $('.exlcusion_form').show();
        $('#exlcusion_form').addClass('selected active');
        $('.soumission_form').hide();
        $('.indexnow_form').hide();
        $('.help_form').hide();
        $('#soumission_form').removeClass('active');
        $('#indexnow_form').removeClass('active');
        $('#help_form').removeClass('active');
       
    }
	else if(action == "key"){
         $('.indexnow_form').show();
        $('#indexnow_form').addClass('selected active');
        $('.soumission_form').hide();
        $('.exlcusion_form').hide();
        $('.help_form').hide();
        $('#soumission_form').removeClass('active');
        $('#exlcusion_form').removeClass('active');
        $('#help_form').removeClass('active');
    }
    else if(action == "help"){
         $('.help_form').show();
        $('#help_form').addClass('selected active');
        $('.soumission_form').hide();
        $('.exlcusion_form').hide();
        $('.indexnow_form').hide();
        $('#soumission_form').removeClass('active');
        $('#exlcusion_form').removeClass('active');
        $('#indexnow_form').removeClass('active');
    }
    else{
        $('.indexnow_form').show();
        $('#indexnow_form').addClass('selected active');
        $('.soumission_form').hide();
        $('.exlcusion_form').hide();
        $('.help_form').hide();
        $('#soumission_form').removeClass('active');
        $('#exlcusion_form').removeClass('active');
        $('#help_form').removeClass('active');
    }

	$('nav.productTabs').find('a.nav-optiongroup').click(function() {

        var id = $(this).attr('id');
        console.log(id);
        $('.nav-optiongroup').removeClass('active');
        $(this).addClass('selected active');
        $(this).siblings().removeClass('active');
        $('.tab-optiongroup').hide();
        $('.'+id).show();
         $('#loader').show();

    });


    $('.nocheckbox').on('click', function(e) {
        console.log(this.checked);
        if (this.checked == true)
    {
       this.checked = false;
    }
    else{
        this.checked = true;
    }
    });


     $('#resetFilter').on('click', function(e) {
     	$(".exlcusion_form").addClass("blur");
     $('#loader').show();
        document.getElementById("selected_category").value = "";
        document.getElementById("selected_name").value = "2";
 
    });







});

function oesfpLoadTab( element_type) {
    $(".exlcusion_form").addClass("blur");
     $('#loader').show();
    var url = admin_module_url+'&oesfp_element_type='+element_type;
    window.location.href = url;
}

function oesfpLoadKey() {
    var url = admin_module_url+'&action=key';
    window.location.href = url;
}


function oesfpLoadExclusion() {
    var url = admin_module_url+'&action=exclusion';
    window.location.href = url;
}

function oesfpLoadSoumission() {
    var url = admin_module_url+'&action=soumission';
    window.location.href = url;
}

function oesfpLoadHelp() {
    var url = admin_module_url+'&action=help';
    window.location.href = url;
}


function opartToggleExclude(source,allcheck=false) {

    
    $('input[name^="NoIndexNow["]').prop('checked', source.checked);
}

function opartToggleSend(source) {
    $('input[name^="SendIndexNow["]').prop('checked', source.checked);
}

function opartToggleExcludeSupplier(id) {
    if (document.getElementById("SupplierIndexNow["+id+"]").checked == true)
    {
        document.getElementById("SupplierIndexNow["+id+"]").checked = false;
    }
    else{
        document.getElementById("SupplierIndexNow["+id+"]").checked = true;
    }
    
}

function opartToggleExcludeManufacturer(id) {
    if (document.getElementById("ManufacturerIndexNow["+id+"]").checked == true)
    {
        document.getElementById("ManufacturerIndexNow["+id+"]").checked = false;
    }
    else{
        document.getElementById("ManufacturerIndexNow["+id+"]").checked = true;
    }
    
}

function opartToggleExcludeCategory(id) {
    if (document.getElementById("CategoryIndexNow["+id+"]").checked == true)
    {
        document.getElementById("CategoryIndexNow["+id+"]").checked = false;
    }
    else{
        document.getElementById("CategoryIndexNow["+id+"]").checked = true;
    }
    
}

function opartToggleExcludeCms(id) {
    if (document.getElementById("CmsIndexNow["+id+"]").checked == true)
    {
        document.getElementById("CmsIndexNow["+id+"]").checked = false;
    }
    else{
        document.getElementById("CmsIndexNow["+id+"]").checked = true;
    }
    
}

function opartToggleExcludeProduct(id) {
    console.log(document.getElementById("ProductIndexNow["+id+"]").checked);

    if (document.getElementById("ProductIndexNow["+id+"]").checked == true)
    {
        document.getElementById("ProductIndexNow["+id+"]").checked = false;
    }
    else{
        document.getElementById("ProductIndexNow["+id+"]").checked = true;
    }
    
}


function oniGoToPage(pageNumber) {
	 $(".exlcusion_form").addClass("blur");
     $('#loader').show();
    $('#selected_page').val(pageNumber);
    $('#applyFilter').trigger('click');
}


function oniGoToItemNumber(itemNumber) {
    $(".exlcusion_form").addClass("blur");
     $('#loader').show();
     $('#item_number').val(itemNumber);
    $('#applyFilter').trigger('click');
}

function Stop(){
     if (document.getElementById("allcheck").checked == true)
    {
        document.getElementById("allcheck").checked = false;
    }
    else{
        document.getElementById("allcheck").checked = true;
    }
}

function opartAllCheck(){
    if (document.getElementById("allcheck").checked == true)
    {
        document.getElementById("allcheck").checked = false;
    }
    else{
        document.getElementById("allcheck").checked = true;
    }

    var checkboxs = document.getElementById("allcheck");
    opartToggleExclude(checkboxs,true);

}


function loaderindexnow(pageNumber) {
	 $(".soumission_form").addClass("blur");
     $('#loader').show();
}

