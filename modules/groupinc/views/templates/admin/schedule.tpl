{**
* Price increment/reduction by groups, categories and more
*
* NOTICE OF LICENSE
*
* This product is licensed for one customer to use on one installation (test stores and multishop included).
* Site developer has the right to modify this module to suit their needs, but can not redistribute the module in
* whole or in part. Any other use of this module constitues a violation of the user agreement.
*
* DISCLAIMER
*
* NO WARRANTIES OF DATA SAFETY OR MODULE SECURITY
* ARE EXPRESSED OR IMPLIED. USE THIS MODULE IN ACCORDANCE
* WITH YOUR MERCHANT AGREEMENT, KNOWING THAT VIOLATIONS OF
* PCI COMPLIANCY OR A DATA BREACH CAN COST THOUSANDS OF DOLLARS
* IN FINES AND DAMAGE A STORES REPUTATION. USE AT YOUR OWN RISK.
*
*  @author    idnovate
*  @copyright 2022 idnovate
*  @license   See above
*}

<input type="hidden" id="schedule" name="schedule" value=""/>
<div id="scheduleContainer"></div>
<script>
    var businessHoursManager = $("#scheduleContainer").businessHours({
        {if $schedule != ''}operationTime:{$schedule|escape:'quotes':'UTF-8'},{/if}
        weekdays:[{l s='\'Monday\',\'Tuesday\',\'Wednesday\',\'Thursday\',\'Friday\',\'Saturday\',\'Sunday\'' mod='groupinc'}],
        defaultOperationTimeFrom:"00:00",
        defaultOperationTimeTill:"23:59",
        postInit:function(){
            $('.operationTimeFrom, .operationTimeTill').timepicker({
            'timeFormat': 'H:i',
            'step': 15
            });
        },
        dayTmpl:'<div class="dayContainer" style="width: 80px;"><div class="weekday"></div>' +
            '<div data-original-title="" class="colorBox"><input type="checkbox" class="invisible operationState"></div>' +
            '<div class="operationDayTimeContainer">' +
                '<div class="operationTime input-group"><span class="input-group-addon"><i class="icon icon-sun"></i></span><input type="text" name="startTime" class="mini-time form-control operationTimeFrom" value=""></div>' +
                '<div class="operationTime input-group"><span class="input-group-addon"><i class="icon icon-moon"></i></span><input type="text" name="endTime" class="mini-time form-control operationTimeTill" value=""></div>' +
                '</div></div>'
    });
    $('document').ready(function() {
        $("input#schedule").val(JSON.stringify(businessHoursManager.serialize()));

        $('.dayContainer .operationState').change(function() {
            $("input#schedule").val(JSON.stringify(businessHoursManager.serialize()));
        });

        $('.dayContainer .mini-time').change(function() {
            $("input#schedule").val(JSON.stringify(businessHoursManager.serialize()));
        });
    });
</script>
