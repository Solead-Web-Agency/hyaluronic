$(document).ready(function(){
    $(document).on("click", ".mpm-pe-edit-field-name-btn", function (e) {
        showEditFieldNameForm(e, this);
    });
    
    $(document).on("click", ".mpm-pe-close-field-edit", function (e) {
        closeEditFieldNameForm(e, this);
    });
    
    $(document).on("click", ".mpm-pe-save-field-name", function (e) {
        saveEditFieldNameForm(e, this);
    });
    
    $(document).on('click', '#filter_fields .add-extra-field', function(){
        addCustomField();
    });

  $(document).on('click', '.alert-danger-export li a', function(){
    var tab = $(this).attr('data-tab');
    var field = $(this).attr('data-field');

    activeErrorTab(tab, field);

  });

  
  $(document).on('click', ".fields_list .list-group-item", function(){
    if(!$(this).hasClass('active')){
      var tab = $(this).attr('data-tab');
      $('.block_all_fields .field_list_base').removeClass('active');
      $('.block_all_fields .field_list_'+tab).addClass('active');
      $(".fields_list .list-group-item").removeClass('active');
      $(this).addClass('active');
    }
  });

  if( $('#products_add_task').length > 0 ){
    $('#products_add_task').prependTo('#schedule_tasks div[data-tab-id=schedule_tasks]');
  }

  if( $('.schedule_tab').length > 0 ){
    $('form#configuration_form .tab-pane').removeClass('active');
    $('form#configuration_form .nav-tabs li').removeClass('active');
    $('form#configuration_form #schedule_tasks').addClass('active');
    $('form#configuration_form .nav-tabs li a[href=#schedule_tasks]').parent().addClass('active');
  }

  $(document).on('click', '.info_items .info_block .content .subscribe .error, .info_items .info_block .content .subscribe .success', function(){
    $(this).removeClass('error');
    $(this).removeClass('success');
    $(this).val('');
  });

  $(document).on('click', '.welcome_page .start', function(){
    $('form#configuration_form .tab-pane').removeClass('active');
    $('form#configuration_form .nav-tabs li').removeClass('active');
    $('form#configuration_form #export').addClass('active');
    $('form#configuration_form .nav-tabs li a[href=#export]').parent().addClass('active');
  });

  $(document).on('click', '.info_items .info_block .content .subscribe .send', function(){
    $('.info_items .info_block .content .subscribe input').removeClass('error');
    $('.info_items .info_block .content .subscribe input').removeClass('success');
    $.ajax({
      type: "POST",
      url: "index.php",
      dataType: 'json',
      data: {
        ajax	: true,
        token: $('input[name=products_export_token]').val(),
        controller: 'AdminProductsExport',
        action: 'subscribe',
        email: $('.info_items .info_block .content .subscribe input').val()
      },
      success: function(json) {
        if(json['success']){
          $('.info_items .info_block .content .subscribe input').val(json['success']);
          $('.info_items .info_block .content .subscribe input').addClass('success');
        }
        if(json['error']){
          $('.info_items .info_block .content .subscribe input').val(json['error']);
          $('.info_items .info_block .content .subscribe input').addClass('error');
        }
      }
    });
  });

  $(document).on('click', '.version_block .check_updates', function(){
    $('.version_block .module_version .last_version').html('---');
    $.ajax({
      type: "POST",
      url: "index.php",
      dataType: 'json',
      data: {
        ajax	: true,
        token: $('input[name=products_export_token]').val(),
        controller: 'AdminProductsExport',
        action: 'checkVersion',
      },
      success: function(json) {
        if(json['module_version']){
          $('.version_block .module_version .last_version').html(json['module_version']);
          var currentVersion = $.trim($('.version_block .module_version .current_version').html());
          if( json['module_version'] != currentVersion ){
            $('.version_block .update').css('display','inline-block');
            $('.version_block .version_ok').hide();
            $('.version_block .version_not_ok').show();
          }
          else {
            $('.version_block .version_ok').show();
            $('.version_block .version_not_ok').hide();
          }
        }
        if(json['error']){
          $('.version_block .module_version .last_version').html(json['error']);
        }
      }
    });
  });

  $(document).on('click', ".nav-tabs li a", function(){
    var tab = $(this).attr('href');

    if(tab == '#export'){
      $('.content-setting-list').addClass('active');
    }
    else{
      $('.content-setting-list').removeClass('active');
    }

    if(tab == '#support'){
      $('.exportproducts .exportButton').hide();
    }
    else{
      if( $('.exportproducts input[name=automatic]:checked').val() != '1' ){
        $('.exportproducts .exportButton').show();
      }

    }

  });

  $(document).on('change', "input[name='format_file']", function(){
    var checked = $("input[name='format_file']:checked").val();
    if(checked == 'csv'){
      $('.block_csv_settings').removeClass('hide_block');
      $('.block_csv_settings').addClass('show_block');
    }
    else{
      $('.block_csv_settings').addClass('hide_block');
      $('.block_csv_settings').removeClass('show_block');
    }
  });

  replaceUrlFile();

  $(document).on('keyup', 'input[name=name_file]', function(){
    replaceUrlFile();
  });
  $(document).on('change', 'input[name=format_file]', function(){
    replaceUrlFile();
  });

  $(document).on('change', 'input[name=name_export_file]', function(){
    if($('input[name=name_export_file]:checked').val() == 1){
      $('.form_group_name_file').addClass('active_block');
      $('.auto_description_ex').addClass('active_block');
      if($("select[name='feed_target']").val() == 'ftp'){
        $('#export .auto_description_ex').removeClass('active_block');
      }
    }
    else{
      $('.form_group_name_file').removeClass('active_block');
      $('.auto_description_ex').removeClass('active_block');
    }
  });


  $(document).on('change', 'input[name=selection_type_price]', function(){
    $('.price .label_selection_type').removeClass('active');
    $('.price .label_selection_type').css('border-color', '#cccccc');
    $(this).prev().addClass('active');
  });


  $(document).on('change', 'input[name=active_products]', function(){
    if($('#active_products_on:checked').val()){
      $('#inactive_products_off').prop('checked', true);
      $('#inactive_products_on').prop('checked', false);
    }
  });

  $(document).on('change', 'input[name=inactive_products]', function(){
    if($('#inactive_products_on:checked').val()){
      $('#active_products_off').prop('checked', true);
      $('#active_products_on').prop('checked', false);
    }
  });

  $(document).on('change', 'input[name=selection_type_quantity]', function(){
    $('.quantity .label_selection_type').removeClass('active');
    $('.quantity .label_selection_type').css('border-color', '#cccccc');
    $(this).prev().addClass('active');
  });

  $(document).on('change', '.type_visibility_checkbox', function(){
    if($(this).prev().hasClass('active')){
      $(this).prev().removeClass('active');
    }
    else{
      $(this).prev().addClass('active');
    }
  });

  $(document).on('change', '.type_condition_checkbox', function(){
    if($(this).prev().hasClass('active')){
      $(this).prev().removeClass('active');
    }
    else{
      $(this).prev().addClass('active');
    }
  });


  $(document).on('change', 'input[name=format_file]', function(){
    if($('input[name=format_file]:checked').val() !== 'xlsx'){
      $('li[data-value=image_cover]').hide();
    }
    else{
      $('li[data-value=image_cover]').show();
    }
    
    $.each( $('li.field_item'), function (index) {
      if( $('input[name=format_file]:checked').val() == 'xml' ){
          if ($(this).hasClass('edited-xml-name')) {
              $(this).find(".mpm-pe-selected-field-name").text($(this).attr('data-name'));
              $(this).find(".mpm-pe-edit-field-name").val($(this).attr('data-name'));
          } else {
              $(this).find(".mpm-pe-selected-field-name").text($(this).attr('data-xml') + ' ('+$(this).attr('data-name')+')');
              $(this).find(".mpm-pe-edit-field-name").val($(this).attr('data-xml') + ' ('+$(this).attr('data-name')+')');
          }
      }
      else{
          $(this).find(".mpm-pe-selected-field-name").text($(this).attr('data-name'));
      }
    });
  });

  if( $('input[name=format_file]:checked').val() == 'xml' ){
    $.each( $('li.field_item'), function (index) {
        if ($(this).hasClass('edited-xml-name')) {
            $(this).find(".mpm-pe-selected-field-name").text($(this).attr('data-xml'));
            $(this).find(".mpm-pe-edit-field-name").val($(this).attr('data-xml'));
        } else {
            $(this).find(".mpm-pe-selected-field-name").text($(this).attr('data-xml') + ' ('+$(this).attr('data-name')+')');
            $(this).find(".mpm-pe-edit-field-name").val($(this).attr('data-xml') + ' ('+$(this).attr('data-name')+')');
        }
    });
  }


  $(document).on('change', '.select_products', function(){
    var id_shop = $("input[name=id_shop]").val();
    $.ajax({
      url: '../modules/exportproducts/send.php',
      type: 'post',
      data: 'add_product=true&ajax=true&id_product='+$(this).val() + '&id_shop=' + id_shop,
      dataType: 'json'
    });
  });
  
  $(document).on('click', '.product_list input[type="checkbox"].check-all', function(){
      var id_shop = $("input[name=id_shop]").val();
      var checked_products = [];
      
      if ($(this).is(":checked")) {
        $('.product_list .select_products').each(function () {
            checked_products.push($(this).val());
        });
      }
    
      $(this).parents('.form-group').find('.checkbox_table').prop('checked', this.checked);
      checked_products = checked_products.join(",");
      
      $.ajax({
          url: '../modules/exportproducts/send.php',
          type: 'post',
          data: 'add_all_visible_products=true&ajax=true&product_ids='+checked_products + '&id_shop=' + id_shop,
          dataType: 'json'
      });
  });

  $("body").on("mouseenter", ".exportFields .content_fields li",
    function(){
      if($(this).hasClass("isset_hint")){
        $("body").append("<div class='hint_content'>"+$(this).attr('data-hint')+"</div>");
        var top = $(this).offset().top;
        var left = $(this).offset().left;
        $('.hint_content').css({'top':top-15, 'left':left-190});
        $('.hint_content').fadeIn();
      }
    }
  ).on("mouseleave", ".content_fields li",
    function(){
      if($(this).hasClass("isset_hint")){
        $('.hint_content').remove()
      }
    }
  );

  $(document).on('change', '.select_manufacturers', function(){
    var id_shop = $("input[name=id_shop]").val();
    $.ajax({
      url: '../modules/exportproducts/send.php',
      type: 'post',
      data: 'add_manufacturer=true&ajax=true&id_manufacturer='+$(this).val() + '&id_shop=' + id_shop,
      dataType: 'json'
    });
  });

  $(document).on('change', '.select_suppliers', function(){
    var id_shop = $("input[name=id_shop]").val();
    $.ajax({
      url: '../modules/exportproducts/send.php',
      type: 'post',
      data: 'add_supplier=true&ajax=true&id_supplier='+$(this).val() + '&id_shop=' + id_shop,
      dataType: 'json'
    });
  });

  $(document).on('change', '.selection_all', function(){
    $('.export_fields input').prop('checked', this.checked);
  });

  $(document).on('keyup', '#search_field', function(){
    var self = $(this);
    $('.export_fields label').each(function(){
      $(this).parent().css('opacity', '0.5');
      if( $(this).text().indexOf(self.val()) >= 0 ){
        $(this).parent().css('opacity', '1');
      }
    });

  });

  $(document).on('click', '.product_list #show_checked', function(e){
    e.preventDefault();
    $(".product_list .col-lg-6 .search_checkbox_table").val("");
    var id_lang = $("select[name=id_lang]").val();
    var id_shop = $("input[name=id_shop]").val();
    $.ajax({
      url: '../modules/exportproducts/send.php',
      type: 'post',
      data: 'show_checked_products=true&ajax=true' +'&id_shop='+id_shop+'&id_lang='+id_lang,
      dataType: 'json',
      success: function(json) {
        $('.alert-danger, .alert-success').remove();
        $(".product_list .col-lg-6 tbody").replaceWith(json['products']);
      }
    });
  });

  $(document).on('click', '.manufacturer_list #show_checked', function(e){
    e.preventDefault();
    $(".manufacturer_list .col-lg-6 .search_checkbox_table").val("");
    $.ajax({
      url: '../modules/exportproducts/send.php',
      type: 'post',
      data: 'show_checked_manufacturers=true&ajax=true',
      dataType: 'json',
      success: function(json) {
        $('.alert-danger, .alert-success').remove();
        $(".manufacturer_list .col-lg-6 tbody").replaceWith(json['manufacturers']);
      }
    });
  });

  $(document).on('click', '.supplier_list #show_checked', function(e){
    e.preventDefault();
    $(".supplier_list .col-lg-6 .search_checkbox_table").val("");
    $.ajax({
      url: '../modules/exportproducts/send.php',
      type: 'post',
      data: 'show_checked_suppliers=true&ajax=true',
      dataType: 'json',
      success: function(json) {
        $('.alert-danger, .alert-success').remove();
        $(".supplier_list .col-lg-6 tbody").replaceWith(json['suppliers']);
      }
    });
  });

  $(document).on('click', '.product_list #show_all', function(e){
    e.preventDefault();
    $(".product_list .col-lg-6 .search_checkbox_table").val("");
    var id_lang = $("select[name=id_lang]").val();
    var id_shop = $("input[name=id_shop]").val();
    $.ajax({
      url: '../modules/exportproducts/send.php',
      type: 'post',
      data: 'show_all_products=true&ajax=true' + '&id_shop='+id_shop+'&id_lang='+id_lang,
      dataType: 'json',
      success: function(json) {
        $('.alert-danger, .alert-success').remove();
        $(".product_list .col-lg-6 tbody").replaceWith(json['products']);
      }
    });
  });

  $(document).on('click', '.manufacturer_list #show_all', function(e){
    e.preventDefault();
    $(".manufacturer_list .col-lg-6 .search_checkbox_table").val("");
    $.ajax({
      url: '../modules/exportproducts/send.php',
      type: 'post',
      data: 'show_all_manufacturers=true&ajax=true',
      dataType: 'json',
      success: function(json) {
        $('.alert-danger, .alert-success').remove();
        $(".manufacturer_list .col-lg-6 tbody").replaceWith(json['manufacturers']);
      }
    });
  });

  $(document).on('click', '.supplier_list #show_all', function(e){
    e.preventDefault();
    $(".supplier_list .col-lg-6 .search_checkbox_table").val("");
    $.ajax({
      url: '../modules/exportproducts/send.php',
      type: 'post',
      data: 'show_all_suppliers=true&ajax=true',
      dataType: 'json',
      success: function(json) {
        $('.alert-danger, .alert-success').remove();
        $(".supplier_list .col-lg-6 tbody").replaceWith(json['suppliers']);
      }
    });
  });

  $(document).on('keyup', '.product_list .search_checkbox_table', function(e){
    var id_lang = $("select[name=id_lang]").val();
    var id_shop = $("input[name=id_shop]").val();
    var self = $(this);
    var search_query = $(this).val();
    
    if (search_query.length < 2) {
      return false;
    }
    
    $.ajax({
      url: '../modules/exportproducts/send.php',
      type: 'post',
      data: 'search_product=' + search_query +'&id_shop='+id_shop+'&ajax=true&id_lang='+id_lang,
      dataType: 'json',
      success: function(json) {
        $('.alert-danger, .alert-success').remove();
        if (json['products']) {
          self.parents('table').find('tbody').replaceWith(json['products']);
        }
      }
    });
  })

  $(document).on('keyup', '.manufacturer_list .search_checkbox_table', function(e){
    var self = $(this);
    $.ajax({
      url: '../modules/exportproducts/send.php',
      type: 'post',
      data: 'ajax=true&search_manufacturer=' + $(this).val(),
      dataType: 'json',
      success: function(json) {
        $('.alert-danger, .alert-success').remove();
        if (json['manufacturers']) {
          self.parents('table').find('tbody').replaceWith(json['manufacturers']);
        }
      }
    });
  })

  $(document).on('keyup', '.supplier_list .search_checkbox_table', function(e){
    var self = $(this);
    $.ajax({
      url: '../modules/exportproducts/send.php',
      type: 'post',
      data: 'ajax=true&search_supplier=' + $(this).val(),
      dataType: 'json',
      success: function(json) {
        $('.alert-danger, .alert-success').remove();
        if (json['suppliers']) {
          self.parents('table').find('tbody').replaceWith(json['suppliers']);
        }
      }
    });
  });


  // $(document).on('click', '.exportproducts .nav li a[href=#support]', function(e){
  //   window.open('https://addons.prestashop.com/en/contact-us?id_product=18662&ab=1', '_blank');
  // });


  $(document).on('click', 'button.export', function(e){
    exportProducts(0);
  });

  $('.selected_fields').sortable({
    revert:false,
    axis: "y"
  });
  $(document).on('click', '.exportproducts .block_base_fields li', function(e){
    if(e.ctrlKey) {
      $(this).addClass('checked');
    }
    else{
      $('.exportproducts .block_base_fields li').removeClass('checked');
      $(this).addClass('checked');
    }
  });
  $(document).on('click', '.exportproducts .block_combinations_fields li', function(e){
    if(e.ctrlKey) {
      $(this).addClass('checked');
    }
    else{
      $('.exportproducts .block_combinations_fields li').removeClass('checked');
      $(this).addClass('checked');
    }
  });
  $(document).on('click', '.exportproducts .block_specificPrice_fields li', function(e){
    if(e.ctrlKey) {
      $(this).addClass('checked');
    }
    else{
      $('.exportproducts .block_specificPrice_fields li').removeClass('checked');
      $(this).addClass('checked');
    }
  });
  $(document).on('click', '.exportproducts .selected_fields li', function(e){
    if(e.ctrlKey) {
      $(this).addClass('checked');
    }
    else{
      $('.exportproducts .selected_fields li').removeClass('checked');
      $(this).addClass('checked');
    }
  });



  $(document).on('click', '.exportproducts .add_base_filds_all', function(e){

    var tab = $('.list-group-item.active').attr('data-tab');

    $('.exportproducts .field_list_'+tab+'  .block_base_fields li').each(function(e) {
      var el = $(this).clone().removeClass('checked').append('<i class="icon-arrows icon-arrows-select-fields"></i>');
      $('.exportproducts .block_selected_fields .selected_fields').append(el[0]);
      $(this).remove();
    });
  });


  $(document).on('click', '.exportproducts .add_base_filds', function(e){
    var tab = $('.list-group-item.active').attr('data-tab');
    $('.exportproducts .field_list_'+tab+'  .block_base_fields li.checked').each(function(e) {
      var el = $(this).clone().removeClass('checked').append('<i class="icon-arrows icon-arrows-select-fields"></i>');
      $('.exportproducts .block_selected_fields .selected_fields').append(el[0]);
      $(this).remove();
    });
  });

  $(document).on('click', '.exportproducts .remove_base_filds_all', function(e){
    
      //Close open for editing fields
      $(".selected_fields .mpm-pe-active-field-edit").each(function() {
          $(this).find(".mpm-pe-close-field-edit").trigger("click");
      });
      
    $('.exportproducts .selected_fields li').each(function(e) {
      if(!$(this).hasClass('disable_fields')) {
        var tab =  $(this).attr('data-tab');
        var el = $(this).clone().removeClass('checked');
        $('.exportproducts .field_list_'+tab+' .block_base_fields').append(el[0]);
        $(this).remove();
      }
    });
    $('.exportproducts .block_base_fields li .icon-arrows-select-fields').remove();
  });


  $(document).on('click', '.exportproducts .remove_base_filds', function(e){
    
    $(".selected_fields .mpm-pe-active-field-edit.checked .mpm-pe-close-field-edit").trigger("click");
    
    $('.exportproducts .selected_fields li.checked').each(function(e) {
      if(!$(this).hasClass('disable_fields')){
        var tab =  $(this).attr('data-tab');
        var el = $(this).clone().removeClass('checked');
        $('.exportproducts .field_list_'+tab+' .block_base_fields').append(el[0]);
        $(this).remove();
      }
    });
    $('.exportproducts .block_base_fields li .icon-arrows-select-fields').remove();
  });

  $(document).on('keyup', '.exportproducts .search_base_fields', function(){
    var self = $(this);
    var tab = $('.list-group-item.active').attr('data-tab');
    $('.exportproducts .field_list_'+tab+' .block_base_fields li').each(function(){
      if( $(this).text().toLowerCase().indexOf(self.val().toLowerCase()) >= 0 ){
        $(this).show();
      }
      else{
        $(this).hide();
      }
    });
  });

  $(document).on('keyup', '.exportproducts .search_selected_fields', function(){
    var self = $(this);
    $('.exportproducts .selected_fields li').each(function(){
      if( $(this).text().toLowerCase().indexOf(self.val().toLowerCase()) >= 0 ){
        $(this).show();
      }
      else{
        $(this).hide();
      }
    });
  });



  $(document).on('click', '.exportproducts .saveSettingsExport', function(e){
    var data = '';

    if($('input[name=format_file]:checked').val() !== 'xlsx'){
      $.each($('.exportproducts .selected_fields li'), function(i){
        if($(this).attr('data-value') !== 'image_cover'){
          data += '&field['+$(this).attr('data-value')+']='+ $(this).attr('data-name');
    
          if ($(this).hasClass('edited-xml-name')) {
              data += '&edited_xml_names['+$(this).attr('data-value')+']='+ $(this).attr('data-name');
          }
        }
      });
    }
    else{
      $.each($('.exportproducts .selected_fields li'), function(i){
        data += '&field['+$(this).attr('data-value')+']='+ $(this).attr('data-name');
    
        if ($(this).hasClass('edited-xml-name')) {
            data += '&edited_xml_names['+$(this).attr('data-value')+']='+ $(this).attr('data-name');
        }
      });
    }
    
    data += getMpmProductsexportExtraFields();

    $.ajax({
      url: '../modules/exportproducts/send.php',
      type: 'post',
      data: 'saveSettings=true&ajax=true' + $('form.exportproducts').serialize()+data,
      dataType: 'json',
      beforeSend: function(){
        if( $('.progres_bar_ex').length < 1 ){
          $("body").append('<div class="progres_bar_ex"><div class="loading_block"><div class="loading"></div></div></div>');
        }
      },
      complete: function(){
        $(".progres_bar_ex").remove();
      },
      success: function(json) {
        $('.alert-danger, .alert-success').remove();

        if (json['error']) {
          $(document).scrollTop(0);
          $('#content').prepend('<div class="alert alert-danger">' + json['error'] + '</div>');
        }

        if( json['error_list'] ){

          $(".progres_bar_ex").remove();
          $('.alert-danger, .alert-success').remove();
          $(document).scrollTop(0);

          var error_list = json['error_list'];
          var msg = '';
          $.each( error_list, function( key, value ) {
            if(key == 0){
              activeErrorTab(value.tab, value.field);
            }
            msg = msg+'<li><a class="error_tab" data-tab="'+value.tab+'" data-field="'+value.field+'">'+value.msg+'</a></li>';
          });
          $('#bootstrap_products_export').before('<div class="alert alert-danger"><ul class="alert-danger-export">' + msg + '</ul></div>');
        }

        if(json['id']){
          location.href = $("input[name=base_url]").val() + '&settings='+json['id']
        }
      }
    });
  });
  $(document).on('click', '.delete_setting', function(e){
    var id = $(this).attr('id-setting');
    var id_shop = $("input[name=id_shop]").val();
    var shopGroupId = $("input[name=shopGroupId]").val();
    $.ajax({
      url: '../modules/exportproducts/send.php',
      type: 'post',
      data: 'removeSetting=true&ajax=true&id=' + id + '&id_shop=' + id_shop + '&shopGroupId=' + shopGroupId,
      dataType: 'json',
      success: function(json) {
        $('.alert-danger, .alert-success').remove();
        if (json['success']) {
          location.href = $("input[name=base_url]").val();
        }
      }
    });
  });

  $(document).on('change', '.exportproducts input[name=automatic]', function(){
    if( $(this).val() == '1' ){
      $('.exportproducts .exportButton').hide();
    }
    else{
      $('.exportproducts .exportButton').show();
    }
  });

  if( $('.exportproducts input[name=automatic]:checked').val() == '1' ){
    $('.exportproducts .exportButton').hide();
  }
  else{
    $('.exportproducts .exportButton').show();
  }

  $(document).on('change', "select[name='feed_target']", function(){
    if($("select[name='feed_target']").val() == 'ftp'){
      $('.ftp_target').show();
      $('.auto_description_ex').removeClass('active_block');
    }
    else{
      $('.ftp_target').hide();
      if($('input[name=name_export_file]:checked').val() == 1){
        $('.auto_description_ex').addClass('active_block');
      }
    }
  });

  if( $("select[name='feed_target']").val() == 'ftp' ){
    $('.ftp_target').show();
    $('.auto_description_ex').removeClass('active_block');
  }

});

refreshIntervalId = false;
function exportProducts( pageLimit ) {
  if( pageLimit == 0 ){
    refreshIntervalId = setInterval(function(){ returnExportedProducts($("input[name=id_shop]").val()); }, 3000);
  }

  var data = '';

  if($('input[name=format_file]:checked').val() !== 'xlsx'){
    $.each($('.exportproducts .selected_fields li'), function(i){
      if($(this).attr('data-value') !== 'image_cover'){
        data += '&field['+$(this).attr('data-value')+']='+ $(this).attr('data-name');
    
        if ($(this).hasClass('edited-xml-name')) {
          data += '&edited_xml_names['+$(this).attr('data-value')+']='+ $(this).attr('data-name');
        }
      }
    });
  }
  else{
    $.each($('.exportproducts .selected_fields li'), function(i){
      data += '&field['+$(this).attr('data-value')+']='+ $(this).attr('data-name');
    
        if ($(this).hasClass('edited-xml-name')) {
            data += '&edited_xml_names['+$(this).attr('data-value')+']='+ $(this).attr('data-name');
        }
    });
  }

  data += '&page_limit='+pageLimit;
  data += getMpmProductsexportExtraFields();

  $.ajax({
    url: '../modules/exportproducts/send.php',
    type: 'post',
    data: 'export=true&ajax=true&' + $('form.exportproducts').serialize()+data,
    dataType: 'json',
    beforeSend: function(){
      if( $('.progres_bar_ex').length < 1 ){
        $("body").append('<div class="progres_bar_ex"><div class="loading_block"><div class="loading"></div><div class="exporting_notification"></div></div></div>');
      }
    },
    success: function(json) {
      if( !json ){
        clearInterval(refreshIntervalId);
        $('.alert-danger, .alert-success').remove();
        $(".progres_bar_ex").remove();
        $(document).scrollTop(0);
        $('#bootstrap_products_export').before('<div class="alert alert-danger">Some error occurred please check <a href="../modules/exportproducts/error.log" target="_blank">error.log</a> file or contact us!</div>');
      }

      if (json['error']) {
        $(".progres_bar_ex").remove();
        $('.alert-danger, .alert-success').remove();
        clearInterval(refreshIntervalId);
        $(document).scrollTop(0);

        $('#bootstrap_products_export').before('<div class="alert alert-danger">' + json['error'] + '</div>');
      }
      else {
        if (json['success']) {
          $(".progres_bar_ex").remove();
          $('.alert-danger, .alert-success').remove();
          clearInterval(refreshIntervalId);
          $(document).scrollTop(0);
          $('#bootstrap_products_export').before('<div class="alert alert-success">' + json['success'] + '</div>');

          if( json.file ){
            if($('input[name=format_file]:checked').val() == 'xml'){
              location.href = json.module_url+'/download.php?url=' + json.file;
            }
            else{
              location.href = json.file;
            }
          }
        }
        if( json['error_list'] ){

          $(".progres_bar_ex").remove();
          $('.alert-danger, .alert-success').remove();
          clearInterval(refreshIntervalId);
          $(document).scrollTop(0);

          var error_list = json['error_list'];
          var msg = '';
          $.each( error_list, function( key, value ) {
            if(key == 0){
              activeErrorTab(value.tab, value.field);
            }
            msg = msg+'<li><a class="error_tab" data-tab="'+value.tab+'" data-field="'+value.field+'">'+value.msg+'</a></li>';
          });
          $('#bootstrap_products_export').before('<div class="alert alert-danger"><ul class="alert-danger-export">' + msg + '</ul></div>');
        }
        if( json['page_limit'] ){
          exportProducts(json['page_limit']);
        }
      }
    },
    error: function(){
      clearInterval(refreshIntervalId);
      $('.alert-danger, .alert-success').remove();
      $(".progres_bar_ex").remove();
      $(document).scrollTop(0);
      $('#bootstrap_products_export').before('<div class="alert alert-danger">Some error occurred please check <a href="../modules/exportproducts/error.log" target="_blank">error.log</a> file or contact us!</div>');
    }
  });
}

function activeErrorTab( tab, field ){
  if(tab){
    $('form#configuration_form .tab-pane').removeClass('active');
    $('form#configuration_form #'+tab).addClass('active');
    $('form#configuration_form .nav-tabs li').removeClass('active');
    $('form#configuration_form .nav-tabs li a[href=#'+tab+']').parent().addClass('active');
  }
  if(field){
    if( field == 'selection_type_price' ) {
      $('input[name=price_value]').focus();
      $('input[name=price_value]').blur();
      $('.block_selection_type.price .label_selection_type ').css('border-color', 'red');
    }
    else if( field == 'selection_type_quantity' ) {
      $('input[name=quantity_value]').focus();
      $('input[name=quantity_value]').blur();
      $('.block_selection_type.quantity .label_selection_type ').css('border-color', 'red');
    }
    else{
      if( field == 'notification_emails' ){
        $('textarea[name='+field+']').focus();
      }
      else{
        $('input[name='+field+']').focus();
      }
    }
  }

}

function returnExportedProducts(id_shop){
  $.ajax({
    url: '../modules/exportproducts/send.php',
    type: 'post',
    data: 'returnCount=true&ajax=true&id_shop='+id_shop,
    dataType: 'json',
    success: function(json) {
      if (json['export_notification']) {
        $('.exporting_notification').html(json['export_notification'])
      }
    }
  });
}

function showSuccessMessage(msg) {
  $.growl.notice({ title: "", message:msg});
}

function showErrorMessage(msg) {
  $.growl.error({ title: "", message:msg});
}
function replaceUrlFile(){
  var url = $('.href_export_file').attr('data-file-url');
  var name_file = $('input[name=name_file]').val();
  var type = $('input[name=format_file]:checked').val()
  var file_url = url+name_file+'.'+type;
  if(name_file){
    $('.href_export_file').attr('href', file_url);
    $('.href_export_file').html(file_url);
    $('.available_url').show();
  }
  else{
    $('.href_export_file').attr('href', '');
    $('.href_export_file').html('');
    $('.available_url').hide();
  }
  if($("select[name='feed_target']").val() == 'ftp'){
    $('#export .auto_description_ex').removeClass('active_block');
  }
}

function showEditFieldNameForm(e, this_handler)
{
    e.preventDefault();
    var container = $(this_handler).siblings(".mpm-pe-edit-field-name-container");
    var field_name = $(this_handler).siblings(".mpm-pe-selected-field-name");
    var move_icon = $(this_handler).siblings(".icon-arrows-select-fields");
    
    $(this_handler).parents("li").addClass("mpm-pe-active-field-edit");
    container.css("display", "inline-block");
    field_name.hide();
    move_icon.hide();
    $(this_handler).hide();
}

function closeEditFieldNameForm(e, this_handler)
{
    e.preventDefault();
    var container = $(this_handler).parents(".mpm-pe-edit-field-name-container");
    var field_name = container.siblings(".mpm-pe-selected-field-name");
    var edit_field_name_button = container.siblings(".mpm-pe-edit-field-name-btn");
    var move_icon = container.siblings(".icon-arrows-select-fields");
    
    $(this_handler).parents("li").removeClass("mpm-pe-active-field-edit");
    
    container.hide();
    field_name.show();
    edit_field_name_button.show();
    move_icon.show();
    
    $(this_handler).siblings(".form-group").find(".mpm-pe-edit-field-name").val($(this_handler).parents("li").attr("data-name"));
    $(this_handler).siblings(".form-group").find(".mpm-pe-edit-field-default-val").val($(this_handler).parents("li").attr("data-default-value"));
}

function saveEditFieldNameForm(e, this_handler)
{
    e.preventDefault();
    var container = $(this_handler).parents(".mpm-pe-edit-field-name-container");
    var new_field_name = $(this_handler).siblings(".form-group").children(".mpm-pe-edit-field-name").val();
    var new_field_val  = $(this_handler).siblings(".form-group").children(".mpm-pe-edit-field-default-val").val();
    
    var has_hint = container.parent().hasClass("isset_hint");
    
    if (has_hint) {
        container.siblings(".mpm-pe-selected-field-name").html("<i class='icon-info icon-info-fields'></i>" + new_field_name);
    } else {
        container.siblings(".mpm-pe-selected-field-name").html(new_field_name);
    }
    
    container.parent().attr("data-name", new_field_name);
    container.parent().attr("data-default-value", new_field_val);
    
    $(this_handler).parents("li").removeClass("mpm-pe-active-field-edit");
    $(this_handler).siblings(".mpm-pe-close-field-edit").trigger("click");
    
    var is_xml_format = $('input[name=format_file]:checked').val() == 'xml';
    
    if( is_xml_format){
        container.parent().addClass("edited-xml-name");
    }
}

function addCustomField()
{
    var id = parseInt(Math.random() * 10000);
    
    var extra_field = "<li data-tab='exportTabOrdersData'  class='mpm-pe-extra-field' data-name='Extra field' id='extra_field_" + id + "' data-value='extra_field_" + id + "'>";
    extra_field += "<span class='mpm-pe-selected-field-name'>Custom Extra field</span>";
    extra_field += "<i class='icon-pencil mpm-pe-edit-field-name-btn'></i>";
    extra_field += "<div class='form-inline mpm-pe-edit-field-name-container mpm-pe-edit-field-value-container'>";
    extra_field += "<div class='form-group'>";
    extra_field += "<input type='text' class='form-control mpm-pe-edit-field-name' placeholder='Custom field name' value='Custom Extra field' />";
    extra_field += "</div>";
    extra_field += "<div class='form-group'>";
    extra_field += "<input type='text' class='mpm-pe-edit-field-default-val' placeholder='Default field value'/>";
    extra_field += "</div>";
    extra_field += "<span class='mpm-pe-save-field-name'><i class='icon-check'></i></span>";
    extra_field += "<span class='mpm-pe-close-field-edit'><i class='icon-times'></i></span>";
    extra_field += "</div>";
    extra_field += "<i class='icon-arrows icon-arrows-select-fields'></i>";
    extra_field += "</li>";
    
    $(".selected_fields.ui-sortable").append(extra_field);
    
    $("#extra_field_" + id + " .mpm-pe-edit-field-name-btn").trigger("click");
}

function getMpmProductsexportExtraFields() {
    var data = '';
    $(".mpm-pe-extra-field").each(function() {
        var id = $(this).attr('data-value');
        data += '&extra_fields[' + id + '][id]='+ $(this).attr('data-value');
        data += '&extra_fields[' + id + '][name]='+ $(this).attr('data-name');
        data += '&extra_fields[' + id + '][value]='+ $(this).find('.mpm-pe-edit-field-default-val').val();
    });
    
    return data;
}