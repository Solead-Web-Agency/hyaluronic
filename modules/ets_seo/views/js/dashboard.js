/**
 * 2007-2021 ETS-Soft
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 wesite only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please contact us for extra customization service at an affordable price
 *
 *  @author ETS-Soft <etssoft.jsc@gmail.com>
 *  @copyright  2007-2021 ETS-Soft
 *  @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of ETS-Soft
 */
var etsSeoAdminDashboard = {
    _chartIndexRatio: null,
    _chartFollowRatio: null,
    _chartMetaSettingCompleted: null,
    _chartPageAnalytic: null,
    ajaxXhrAnalysis: null,
    chartIndexRatio: function() {
        var ctx = document.getElementById('canvas-hart-index-ratio').getContext('2d');
        var labels = [];
        var data = [];
        for(var i = 0; i < ets_seo_data_dashboard.chart_index.length; i++)
        {
            labels.push(ets_seo_data_dashboard.chart_index[i].label);
            data.push(ets_seo_data_dashboard.chart_index[i].value);
        }

        etsSeoAdminDashboard._chartIndexRatio = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: ["#32C020", "#FFE958"],
                }],
            },
            options: {
                maintainAspectRatio: false,
                legend: {
                    fullWidth: true,
                    position: 'bottom',
                    padding: {
                        left: 0,
                        right: 0,
                        top: 100,
                        bottom: 0,
                    },
                    labels: {
                        // This more specific font property overrides the global property
                        fontColor: '#333'
                    }
                }
            }
        });

    },

    chartFollowRatio: function() {

        var ctx = document.getElementById('canvas-chart-follow-ratio').getContext('2d');
        var labels = [];
        var data = [];
        for(var i = 0; i < ets_seo_data_dashboard.chart_follow.length; i++)
        {
            labels.push(ets_seo_data_dashboard.chart_follow[i].label);
            data.push(ets_seo_data_dashboard.chart_follow[i].value);
        }
        etsSeoAdminDashboard._chartIndexRatio = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: ["#00AEEA", "#FFE958"],
                }],
            },
            options: {
                maintainAspectRatio: false,
                legend: {
                    fullWidth: true,
                    position: 'bottom',
                    padding: {
                        left: 0,
                        right: 0,
                        top: 100,
                        bottom: 0,
                    },
                    labels: {
                        // This more specific font property overrides the global property
                        fontColor: '#333'
                    }
                }
            }
        });
    },
    chartMetaSettingCompleted: function(){
        var ctx = document.getElementById('canvas-chart-meta-setting-completed').getContext('2d');
        var labels = [];
        var data = [];
        var primaryText = Math.round(ets_seo_data_dashboard.meta_data[0].value / (ets_seo_data_dashboard.meta_data[0].value + ets_seo_data_dashboard.meta_data[1].value) * 100);
        for(var i = 0; i < ets_seo_data_dashboard.meta_data.length; i++)
        {
            labels.push(ets_seo_data_dashboard.meta_data[i].label);
            data.push(ets_seo_data_dashboard.meta_data[i].value);
        }
       etsSeoAdminDashboard._chartMetaSettingCompleted = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: ["#32C020", "#F5F5F5"],
                }],
            },
           options: {
               maintainAspectRatio: false,
               elements: {
                   center: {
                       text: primaryText+'%',
                       color: '#333', // Default is #000000
                       fontStyle: 'Arial', // Default is Arial
                       sidePadding: 20 // Defualt is 20 (as a percentage)
                   },
                   arc: {
                       borderWidth: 0,
                   },
                   borderWidth: 1
               },
               cutoutPercentage: 80,

               legend: {
                   fullWidth: true,
                   position: 'bottom',
                   padding: {
                       left: 0,
                       right: 0,
                       top: 100,
                       bottom: 0,
                   },
                   labels: {
                       // This more specific font property overrides the global property
                       fontColor: '#333'
                   }
               }
           }
        });
        Chart.plugins.register({
            afterDatasetsDraw: function (chartInstance, easing) {
                if (chartInstance.config.type == "doughnut") {
                    var ctx = chartInstance.chart.ctx;
                    var sum = 0;
                    chartInstance.data.datasets.forEach(function (dataset, i) {
                        var meta = chartInstance.getDatasetMeta(i);
                        if (!meta.hidden) {
                            meta.data.forEach(function (element, index) {
                                ctx.fillStyle = 'white';
                                var fontSize = 14;
                                var fontStyle = 'normal';
                                var fontFamily = 'Helvetica Neue';
                                ctx.font = Chart.helpers.fontString(fontSize, fontStyle, fontFamily);
                                //var dataString = chartInstance.data.labels[index];
                                var dataString2 = dataset.data[index];

                                ctx.textAlign = 'center';
                                ctx.textBaseline = 'middle';

                                var padding = 5;
                                var position = element.tooltipPosition();

                                //ctx.fillText(4, position.x, position.y - (fontSize / 2) - padding);
                                //ctx.fillText(dataString2, position.x, position.y - (fontSize / 2) - padding + fontSize);

                                sum += dataset.data[index];
                            });
                        }
                    });

                }
            }

        });

        Chart.pluginService.register({
            beforeDraw: function (chart) {
                if (chart.config.options.elements.center) {
                    //Get ctx from string
                    var ctx = chart.chart.ctx;

                    //Get options from the center object in options
                    var centerConfig = chart.config.options.elements.center;
                    var fontStyle = centerConfig.fontStyle || 'Arial';
                    var txt = centerConfig.text;
                    var color = centerConfig.color || '#000';
                    var sidePadding = centerConfig.sidePadding || 20;
                    var sidePaddingCalculated = (sidePadding / 100) * (chart.innerRadius * 2)
                    //Start with a base font of 30px
                    ctx.font = "40px " + fontStyle;

                    //Get the width of the string and also the width of the element minus 10 to give it 5px side padding
                    var stringWidth = ctx.measureText(txt).width;
                    var elementWidth = (chart.innerRadius * 2) - sidePaddingCalculated;

                    // Find out how much the font can grow in width.
                    var widthRatio = elementWidth / stringWidth;
                    var newFontSize = Math.floor(20 * widthRatio);
                    var elementHeight = (chart.innerRadius * 2);

                    // Pick a new font size so it will not be larger than the height of label.
                    var fontSizeToUse = Math.min(newFontSize, elementHeight);

                    //Set font settings to draw it correctly.
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    var centerX = ((chart.chartArea.left + chart.chartArea.right) / 2);
                    var centerY = ((chart.chartArea.top + chart.chartArea.bottom) / 2);
                    ctx.font = fontSizeToUse + "px " + fontStyle;
                    ctx.fillStyle = color;

                    //Draw text in center
                    ctx.fillText(txt, centerX, centerY);
                }
            }
        });
    },
    chartPageAnalytic: function(){
        nv.addGraph(function() {
            etsSeoAdminDashboard._chartPageAnalytic = nv.models.multiBarHorizontalChart()
                .x(function(d) { return d.label })
                .y(function(d) { return d.value })
                .margin({top: 30, right: 20, bottom: 50, left: 210})
                .showValues(true)           //Show bar value next to each bar.
                .tooltips(true)             //Show tooltips on hover.
                .transitionDuration(350)
                .showControls(true);        //Allow user to switch between "Grouped" and "Stacked" mode.

            etsSeoAdminDashboard._chartPageAnalytic.yAxis
                .tickFormat(d3.format(',d'));
            etsSeoAdminDashboard._chartPageAnalytic.valueFormat(d3.format('d'));

            d3.select('#chart-page-analytics svg')
                .datum(ets_seo_data_dashboard ? ets_seo_data_dashboard.chart_page_analytics.seo_score : [])
                .call(etsSeoAdminDashboard._chartPageAnalytic);

            nv.utils.windowResize(etsSeoAdminDashboard._chartPageAnalytic.update);

            return etsSeoAdminDashboard._chartPageAnalytic;
        });
    },
    setChartMessageNoData: function(){
        Chart.plugins.register({
            afterDraw: function(chart) {
                if (chart.data.datasets.length === 0 || (typeof chart.data.datasets[0].data !== 'undefined' && chart.data.datasets[0].data.length == 0)) {
                    // No data is present
                    var ctx = chart.chart.ctx;
                    var width = chart.chart.width;
                    var height = chart.chart.height
                    chart.clear();

                    ctx.save();
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.font = "20px Arial";
                    ctx.fillText(ets_seo_data_not_found || 'No data found', width / 2, height / 2);
                    ctx.restore();
                }
            }
        });
    },
    ajaxAnalysisPage: function(pages){
        if(!pages || !pages.length){
            return false;
        }
        etsSeoAdminDashboard.ajaxXhrAnalysis = $.ajax({
            url:'',
            type: 'POST',
            dataType: 'json',
            data:{
                etsSeoAnalysisPages: 1,
                dataPages: pages
            },
            beforeSend: function () {

            },
            success: function (res) {
                if(res.success){
                    if(res.stop){
                        showSuccessMessage(res.message);
                        $('#etsSeoModalManualAnalysis').modal('hide');
                        window.location.reload();
                        return false;
                    }
                    var scores = {};
                    var dataPage = res.data.data;
                    for (var i = 0; i < dataPage.length; i++){
                        var score = etsSeoAdminDashboard.analysisPageManually(dataPage[i], res.data.page_type);
                        scores[dataPage[i].id+'_'+dataPage[i].id_lang] = score;
                    }

                    etsSeoAdminDashboard.ajaxPutDataAnalysis({page_type: res.data.page_type, score: scores, pages: pages, stop: res.data.stop}, dataPage.length);
                }
                else{
                    showErrorMessage(res.message);
                    $('#etsSeoModalManualAnalysis').modal('hide');
                }
            },
            complete: function () {

            }
        });
    },
    ajaxPutDataAnalysis: function(scoreData, nbPageUpdated){
        etsSeoAdminDashboard.ajaxXhrAnalysis = $.ajax({
          url: '',
          type: 'POST',
          dataType: 'json',
          data: {
              etsSeoSaveDataAnalysis: 1,
              scoreData: scoreData
          },
          success: function (res) {
              if(res.success){
                  var nbUpdated = $('.js-ets-seo-div-analysis-data .nb_page_updated').attr('data-page');
                  var totalPage = 0;
                  $('#etsSeoModalManualAnalysis input[name="ets_seo_page[]"]:checked').each(function () {
                      totalPage += parseInt($(this).attr('data-total-page'));
                  });
                  nbUpdated = parseInt(nbUpdated)+nbPageUpdated;
                  var totalLeft = parseInt(totalPage) - nbUpdated;
                  $('.js-ets-seo-div-analysis-data .nb_page_updated').html(nbUpdated);
                  $('.js-ets-seo-div-analysis-data .nb_page_updated').attr('data-page',nbUpdated);
                  $('.js-ets-seo-div-analysis-data .nb_page_left').attr('data-page', totalLeft);
                  $('.js-ets-seo-div-analysis-data .nb_page_left').html(totalLeft);
                  if(!res.stop && res.pages.length){
                    etsSeoAdminDashboard.ajaxAnalysisPage(res.pages);
                  }
                  else{
                        showSuccessMessage(res.message);
                        $('#etsSeoModalManualAnalysis').modal('hide');
                        window.location.reload();
                  }
              }
              else{
                  showErrorMessage(res.message);
              }
          }
      });
    },
    analysisPageManually: function (dataPage, pageType) {
        this.resetAnalysisScore();
        var idLang = dataPage.id_lang;

        var content = (dataPage.description_short ? dataPage.description_short : '')+(dataPage.description ? dataPage.description : '');
        etsSEO.rules.pageTitleLength(idLang, dataPage.name);
        etsSEO.rules.internalLink(idLang, content);
        etsSEO.rules.outboundLink(idLang, content);
        etsSEO.rules.singleH1(idLang, content,pageType);
        etsSEO.rules.seoTitleWidth(idLang, dataPage.meta_title || dataPage.name);
        etsSEO.rules.textLength(idLang, content.replace(/<\/?[a-z][^>]*?>/gi, "\n"), '', pageType);
        etsSEO.rules.imageAltAttribute(idLang, content);
        if(dataPage.meta_description)
            etsSEO.rules.metaDescLength(idLang, dataPage.meta_description);
        else{
            if(pageType != 'product' && !dataPage.description_short)
                etsSEO.rules.metaDescLength(idLang, dataPage.description);
            else
                etsSEO.rules.metaDescLength(idLang, dataPage.description_short);
        }

        etsSEO.readability.consecutiveSentences(idLang, content);
        etsSEO.readability.fleschReadingEase(idLang, content);
        etsSEO.readability.notEnoughContent(idLang, content);
        etsSEO.readability.paragraphLength(idLang, content);
        etsSEO.readability.passive_voice(idLang, content);
        etsSEO.readability.sentenceLength(idLang, content);
        etsSEO.readability.subheadingDistribution(idLang, content);
        etsSEO.readability.transitionWords(idLang, content);

        var remailRules = ['keyphrase_length', 'keyphrase_in_subheading', 'keyphrase_in_title',
            'keyphrase_in_page_title', 'keyphrase_in_intro', 'keyphrase_density', 'keyphrase_in_meta_desc',
            'keyphrase_in_slug', 'minor_keyphrase_in_content', 'minor_keyphrase_in_title', 'minor_keyphrase_in_desc',
            'minor_keyphrase_in_page_title', 'minor_keyphrase_acceptance', 'keyphrase_density_individual', 'minor_keyphrase_in_content_individual', 'minor_keyphrase_length'];
        var score = {
            'readability': 0,
            'seo': 0
        };
        $.each(remailRules, function (i, item) {
            etsSEO.seo_score[item][idLang] = 0;
        });
        Object.keys(etsSEO.seo_score).forEach(function (key) {
           score.seo += etsSEO.seo_score[key][idLang];
        });
        Object.keys(etsSEO.readability_score).forEach(function (key) {
            score.readability += etsSEO.readability_score[key][idLang];
        });

        return {
            id_lang: idLang,
            id: dataPage.id,
            score: score
        };
    },
    resetAnalysisScore: function () {
        Object.keys(etsSEO.seo_score).forEach(function (key) {
            Object.keys(ETS_SEO_LANGUAGES).forEach(function (isoCode) {
                etsSEO.seo_score[key][ETS_SEO_LANGUAGES[isoCode]] = 0;
            });
        });
        Object.keys(etsSEO.readability_score).forEach(function (key) {
            Object.keys(ETS_SEO_LANGUAGES).forEach(function (isoCode) {
                etsSEO.readability_score[key][ETS_SEO_LANGUAGES[isoCode]] = 0;
            });
        });
    }
};

$(function(){

    etsSeoAdminDashboard.setChartMessageNoData();
    etsSeoAdminDashboard.chartIndexRatio();
    etsSeoAdminDashboard.chartFollowRatio();
    etsSeoAdminDashboard.chartPageAnalytic();
    etsSeoAdminDashboard.chartMetaSettingCompleted();

    $(document).on('click', '.js-ets-seo-tab-chart-page-analysis', function(){

        $('.js-ets-seo-tab-chart-page-analysis').removeClass('active');
        $(this).addClass('active');
        var tab = $(this).attr('data-tab');

        if(etsSeoAdminDashboard._chartPageAnalytic)
        {
            var data = ets_seo_data_dashboard ? ets_seo_data_dashboard.chart_page_analytics.seo_score : [];
            if(tab == 'seo-score')
            {
                data = ets_seo_data_dashboard ? ets_seo_data_dashboard.chart_page_analytics.seo_score : [];
            }
            else{
                data = ets_seo_data_dashboard ? ets_seo_data_dashboard.chart_page_analytics.readability_score : [];
            }
            d3.select('#chart-page-analytics svg')
                .datum(data)
                .call(etsSeoAdminDashboard._chartPageAnalytic);
            nv.utils.windowResize(etsSeoAdminDashboard._chartPageAnalytic.update);
        }
    });

    $(document).on('click', '.js-ets-seo-get-modal-analysis', function (e) {
        e.preventDefault();
        if($('#etsSeoModalManualAnalysis').length) {
            $('#etsSeoModalManualAnalysis').modal('hide');
            $('#etsSeoModalManualAnalysis').remove();
        }
        var $this = $(this);

        $.ajax({
            url: '',
            type: 'GET',
            dataType: 'json',
            data: {
                etsSeoGetAnalysisModal: 1,
            },
            beforeSend: function () {
                $this.addClass('loading');
                $this.prop('disabled', true);
            },
            success: function (res) {
                if(res.success){
                    $('#content.bootstrap').append(res.modal_html);
                    $('#etsSeoModalManualAnalysis').modal({backdrop: 'static', keyboard: false});
                    $('#etsSeoModalManualAnalysis').modal('show');
                }
            },
            complete: function () {
                $this.removeClass('loading');
                $this.prop('disabled', false);
            }
        });
        return false;
    });

    $(document).on('click', '#etsSeoModalManualAnalysis input[name=ets_seo_page_all]', function () {
        if($(this).is(':checked')){
            $('#etsSeoModalManualAnalysis input[name="ets_seo_page[]"]').prop('checked', true);
        }
        else{
            $('#etsSeoModalManualAnalysis input[name="ets_seo_page[]"]').prop('checked', false);
        }
    });

    $(document).on('change', '#etsSeoModalManualAnalysis input[name="ets_seo_page[]"]', function () {
        if($(this).is(':checked')){
            if($('#etsSeoModalManualAnalysis input[name="ets_seo_page[]"]').length == $('#etsSeoModalManualAnalysis input[name="ets_seo_page[]"]:checked').length){
                $('#etsSeoModalManualAnalysis input[name=ets_seo_page_all]').prop('checked', true);
            }
            else
                $('#etsSeoModalManualAnalysis input[name=ets_seo_page_all]').prop('checked', false);
        }
        else{
            $('#etsSeoModalManualAnalysis input[name=ets_seo_page_all]').prop('checked', false);
        }
    });

    $(document).on('click', '#etsSeoModalManualAnalysis .js-ets-seo-analysis-manually', function (e) {
        e.preventDefault();
        var formData = $('#etsSeoModalManualAnalysis form').serializeArray();
        var dataPages = [];
        $.each(formData, function (i, el) {
            if(el.name == 'ets_seo_page[]'){
                dataPages.push(el.value);
            }
        });
        var $this = $(this);
        etsSeoAdminDashboard.ajaxAnalysisPage(dataPages);
        $('#etsSeoModalManualAnalysis .box-select-page').addClass('hide');
        $('#etsSeoModalManualAnalysis .box-analysis').removeClass('hide');
        var totalPage = 0;
        $('#etsSeoModalManualAnalysis input[name="ets_seo_page[]"]:checked').each(function () {
            totalPage += parseInt($(this).attr('data-total-page'));
        });
        $('#etsSeoModalManualAnalysis .js-ets-seo-div-analysis-data .nb_page_left').attr('data-page', totalPage);
        $('#etsSeoModalManualAnalysis .js-ets-seo-div-analysis-data .nb_page_left').html(totalPage);
        return false;
    });

    $(document).on('click', '#etsSeoModalManualAnalysis .js-ets-seo-cancel-analysis', function (e) {
        e.preventDefault();
        if(etsSeoAdminDashboard.ajaxXhrAnalysis && etsSeoAdminDashboard.ajaxXhrAnalysis.readyState != 4){
            etsSeoAdminDashboard.ajaxXhrAnalysis.abort();
        }
        return false;
    });
})

