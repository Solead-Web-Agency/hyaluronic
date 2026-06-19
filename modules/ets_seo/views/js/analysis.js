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

var etsSEO = {
    activeControllers: [
        'AdminCmsContent',
        'AdminMeta',
        'AdminCategories',
        'AdminManufacturers',
        'AdminSuppliers',
        'AdminProducts'],
    showMessageAnalysis: false,
    timeoutKeyup: 1000,
    timeoutTyping: null,
    timeoutTypingFocusKey: null,
    timeoutTypingMetaTitle: null,
    timeoutTypingMetaDesc: null,
    timeoutTypingFriendlyUrl: null,
    keyPhraseGlobal: {},
    minorKeyPhraseSucess: [],
    seo_score: {
        outbound_link: {},
        internal_link: {},
        text_length: {},
        keyphrase_length: {},
        keyphrase_in_subheading: {},
        keyphrase_in_title: {},
        keyphrase_in_page_title: {},
        keyphrase_in_intro: {},
        keyphrase_density: {},
        image_alt_attribute: {},
        seo_title_width: {},
        meta_description_length: {},
        keyphrase_in_meta_desc: {},
        keyphrase_in_slug: {},
        minor_keyphrase_in_content: {},
        minor_keyphrase_in_title: {},
        minor_keyphrase_in_desc: {},
        minor_keyphrase_in_page_title: {},
        minor_keyphrase_acceptance: {},
        single_h1: {},
        keyphrase_density_individual: {},
        minor_keyphrase_in_content_individual: {},
        minor_keyphrase_length: {}
    },
    readability_score: {
        not_enough_content: {},
        sentence_length: {},
        flesch_reading_ease: {},
        paragraph_length: {},
        consecutive_sentences: {},
        subheading_distribution: {},
        transition_words: {},
        passive_voice: {}
    },
    content_analysis: {},
    initSeoScore: function () {
        Object.keys(ETS_SEO_LANGUAGES).forEach(function (key) {
            Object.keys(etsSEO.seo_score).forEach(function (k) {
                etsSEO.seo_score[k][ETS_SEO_LANGUAGES[key]] = 0;
            });
            Object.keys(etsSEO.readability_score).forEach(function (k) {
                etsSEO.readability_score[k][ETS_SEO_LANGUAGES[key]] = 0;
            });
        });
    },
    setSeoScore: function (rule, id_lang, score) {

        etsSEO.seo_score[rule][id_lang] = score;
        etsSEO.setScoreToFormData();
    },
    setReadabilityScore: function (rule, id_lang, score) {
        etsSEO.readability_score[rule][id_lang] = score;
        etsSEO.setScoreToFormData();
    },
    setScoreToFormData: function () {
        $('#ets_seo_score_data').val(JSON.stringify({
            seo_score: etsSEO.seo_score,
            readability_score: etsSEO.readability_score
        }));

        $('#ets_seo_content_analysis').val(JSON.stringify(etsSEO.content_analysis));
        etsSEO.setPreviewAnalysis();
    },
    prefixInput: function () {
        var prefix = {
            meta_title: '',
            meta_desc: '',
            link_rewrite: '',
            content: '',
            title: '',
            short_desc: '',
            price: '',
            category: '',
            brand: '',
            discount_price: ''

        };
        if (ETS_SEO_CONTROLLER == 'AdminProducts') {
            prefix.meta_title = '#form_step5_meta_title_';
            prefix.title = '#form_step1_name_';
            prefix.meta_desc = '#form_step5_meta_description_';
            prefix.link_rewrite = '#form_step5_link_rewrite_';
            prefix.content = '#form_step1_description_';
            prefix.short_desc = '#form_step1_description_short_';
            prefix.price = '#form_step2_price_ttc';
            prefix.brand = '#form_step1_id_manufacturer';
            prefix.discount_price = '#js-specific-price-list';
            prefix.category = 'input[name="ignore"][class="default-category"]';
        } else if (ETS_SEO_CONTROLLER == 'AdminCmsContent') {
            if (ETS_SEO_IS_CMS_CATEGORY) {
                prefix.meta_title = ETS_SEO_DEFINED.is176 ? '#cms_page_category_meta_title_' : '#meta_title_';
                prefix.meta_desc = ETS_SEO_DEFINED.is176 ? '#cms_page_category_meta_description_' : '#meta_description_';
                prefix.link_rewrite = ETS_SEO_DEFINED.is176 ? '#cms_page_category_friendly_url_' : '#link_rewrite_';
                prefix.content = ETS_SEO_DEFINED.is176 ? '#cms_page_category_description_' : '#description_';
                prefix.short_desc = ETS_SEO_DEFINED.is176 ? '#cms_page_category_description_' : '#description_';
                prefix.title = ETS_SEO_DEFINED.is176 ? '#cms_page_category_name_' : '#name_';
            } else {
                prefix.meta_title = ETS_SEO_DEFINED.is176 ? '#cms_page_meta_title_' : '#head_seo_title_';
                prefix.meta_desc = ETS_SEO_DEFINED.is176 ? '#cms_page_meta_description_' : '#meta_description_';
                prefix.link_rewrite = ETS_SEO_DEFINED.is176 ? '#cms_page_friendly_url_' : '#link_rewrite_';
                prefix.content = ETS_SEO_DEFINED.is176 ? '#cms_page_content_' : '#content_';
                prefix.title = ETS_SEO_DEFINED.is176 ? '#cms_page_title_' : '#name_';
                prefix.category = ETS_SEO_DEFINED.is176 ? 'input[name="cms_page[page_category_id]"]' : 'select[name="id_cms_category"]';
            }

        } else if (ETS_SEO_CONTROLLER == 'AdminMeta') {

            prefix.meta_title = ETS_SEO_DEFINED.is176 ? '#meta_page_title_' : '#title_';
            prefix.title = ETS_SEO_DEFINED.is176 ? '#meta_page_title_' : '#title_';
            prefix.meta_desc = ETS_SEO_DEFINED.is176 ? '#meta_meta_description_' : '#description_';
            prefix.short_desc = ETS_SEO_DEFINED.is176 ? '' : '';
            prefix.link_rewrite = ETS_SEO_DEFINED.is176 ? '#meta_url_rewrite_' : '#url_rewrite_';
            prefix.content = ETS_SEO_DEFINED.is176 ? '' : '';
        } else if (ETS_SEO_CONTROLLER == 'AdminCategories') {
            if ($('form[name=root_category]').length) {
                prefix.title = ETS_SEO_DEFINED.is176 ? '#root_category_name_' : '#name_';
                prefix.meta_title = ETS_SEO_DEFINED.is176 ? '#root_category_meta_title_' : '#meta_title_';
                prefix.meta_desc = ETS_SEO_DEFINED.is176 ? '#root_category_meta_description_' : '#meta_description_';
                prefix.link_rewrite = ETS_SEO_DEFINED.is176 ? '#root_category_link_rewrite_' : '#link_rewrite_';
                prefix.content = ETS_SEO_DEFINED.is176 ? '#root_category_description_' : '#description_';
                prefix.short_desc = ETS_SEO_DEFINED.is176 ? '#root_category_description_' : '#description_';
            } else {
                prefix.title = ETS_SEO_DEFINED.is176 ? '#category_name_' : '#name_';
                prefix.meta_title = ETS_SEO_DEFINED.is176 ? '#category_meta_title_' : '#meta_title_';
                prefix.meta_desc = ETS_SEO_DEFINED.is176 ? '#category_meta_description_' : '#meta_description_';
                prefix.link_rewrite = ETS_SEO_DEFINED.is176 ? '#category_link_rewrite_' : '#link_rewrite_';
                prefix.content = ETS_SEO_DEFINED.is176 ? '#category_description_' : '#description_';
                prefix.short_desc = ETS_SEO_DEFINED.is176 ? '#category_description_' : '#description_';
            }

        } else if (ETS_SEO_CONTROLLER == 'AdminManufacturers') {

            prefix.title = ETS_SEO_DEFINED.is176 ? '#manufacturer_name_' : '#name_';
            prefix.meta_title = ETS_SEO_DEFINED.is176 ? '#manufacturer_meta_title_' : '#meta_title_';
            prefix.meta_desc = ETS_SEO_DEFINED.is176 ? '#manufacturer_meta_description_' : '#meta_description_';
            prefix.link_rewrite = ETS_SEO_DEFINED.is176 ? '#manufacturer_link_rewrite_' : '#link_rewrite_';
            prefix.content = ETS_SEO_DEFINED.is176 ? '#manufacturer_description_' : '#description_';
            prefix.short_desc = ETS_SEO_DEFINED.is176 ? '#manufacturer_short_description_' : '#short_description_';
        } else if (ETS_SEO_CONTROLLER == 'AdminSuppliers') {

            prefix.title = ETS_SEO_DEFINED.isSf ? '#supplier_name_' : '#name_';
            prefix.meta_title = ETS_SEO_DEFINED.isSf ? '#supplier_meta_title_' : '#meta_title_';
            prefix.meta_desc = ETS_SEO_DEFINED.isSf ? '#supplier_meta_description_' : '#meta_description_';
            prefix.link_rewrite = ETS_SEO_DEFINED.isSf ? '#link_rewrite_' : '#link_rewrite_';
            prefix.content = ETS_SEO_DEFINED.isSf ? '#supplier_description_' : '#description_';
            prefix.short_desc = ETS_SEO_DEFINED.isSf ? '#supplier_description_' : '#description_';
        }

        return prefix;
    },
    getMinorKeyphrase: function (id_lang) {
        var minor_key_phrase = $('.ets_seotop1_step_seo input.input-minor-keyphrase-il-' + id_lang).val();
        if (!minor_key_phrase) {
            return [];
        }
        try {
            minor_key_phrase = JSON.parse(minor_key_phrase);
            if (minor_key_phrase.length) {
                var minor = [];
                $.each(minor_key_phrase, function (i, el) {
                    minor.push(el.value);
                });
                return minor;
            }
        } catch (e) {
            return [];
        }
        return [];
    },
    analysisContent: function (id_lang, content) {

        content = content || '';
        var text = content.replace(/<\/?[a-z][^>]*?>/gi, "\n");
        var key_phrase = $('.ets_seotop1_step_seo .input-key-phrase-il-' + id_lang).val();
        etsSEO.rules.outboundLink(id_lang, content);
        etsSEO.rules.internalLink(id_lang, content);
        etsSEO.rules.textLength(id_lang, text);
        etsSEO.rules.singleH1(id_lang, content);
        etsSEO.analysisKeypharse(id_lang, key_phrase, content);

        //Readability
        etsSEO.readability.notEnoughContent(id_lang, text);
        etsSEO.readability.sentenceLength(id_lang, text);
        etsSEO.readability.fleschReadingEase(id_lang, text);
        etsSEO.readability.paragraphLength(id_lang, content);
        etsSEO.readability.consecutiveSentences(id_lang, text);
        etsSEO.readability.subheadingDistribution(id_lang, content);
        etsSEO.readability.transitionWords(id_lang, text);
        etsSEO.readability.passive_voice(id_lang, text);
        etsSEO.analysisMinorKeyphrase(id_lang);
        var prefix = etsSEO.prefixInput();
        if (prefix.meta_desc && $(prefix.meta_desc + id_lang).length) {
            etsSEO.rules.metaDescLength(id_lang, $(prefix.meta_desc + id_lang).val());
        }

        etsSEO.changePreview(id_lang);
    },

    analysisKeypharse: function (id_lang, key_phrase, content) {
        if(typeof key_phrase === 'undefined')
            key_phrase = '';
        etsSEO.rules.keyPhraseLength(id_lang, key_phrase);
        var meta_title = etsSEO.getMetaTitle(id_lang, true);
        var meta_desc = etsSEO.getMetaDesc(id_lang, true);
        etsSEO.rules.keyPhraseInTitle(id_lang, key_phrase, meta_title);
        etsSEO.rules.keyphraseInMetaDesc(id_lang, key_phrase, meta_desc);
        etsSEO.rules.seoTitleWidth(id_lang, meta_title);
        var text_intro = etsSEO.getFirstParagraph(content);
        etsSEO.rules.keyphraseInIntroduction(id_lang, key_phrase, text_intro);
        var text = content.replace(/<\/?[a-z][^>]*?>/gi, "\n");
        etsSEO.rules.keyphraseDensity(id_lang, key_phrase, text);
        etsSEO.rules.keyphraseInSubheading(id_lang, key_phrase, content);
        etsSEO.rules.imageAltAttribute(id_lang, key_phrase, content);
    },

    //Get links in text content
    getLinks: function (str) {
        var tmp = document.createElement('div');
        tmp.innerHTML = str;
        var contentHTML = tmp.getElementsByTagName('a');
        var links = [];
        $.each(contentHTML, function () {
            if ($(this).attr('href'))
                links.push($(this).attr('href'));
        });

        return links;
    },
    getLinksNoFollowed: function (str) {
        var tmp = document.createElement('div');
        tmp.innerHTML = str;
        var contentHTML = tmp.getElementsByTagName('a');
        var links = [];
        $.each(contentHTML, function () {
            if ($(this).attr('href') && $(this).attr('rel') == 'nofollow')
                links.push($(this).attr('href'));
        });

        return links;
    },
    //Analysis rules
    rules: {
        outboundLink: function (id_lang, content) {
            content = content || '';
            if (!content) {
                etsSEO.removeAnalysisMessage('#analysis-result--list-' + id_lang,
                    'outbound_link');
                if(ETS_SEO_CONTROLLER == 'AdminMeta'){
                    etsSEO.setSeoScore('outbound_link', id_lang, 9);
                }
                else
                    etsSEO.setSeoScore('outbound_link', id_lang, 0);
                return;
            }
            var links = etsSEO.getLinks(content);
            var noFollowedLinks = etsSEO.getLinksNoFollowed(content);
            var comp = new RegExp(location.host);
            var listLinks = [];
            var listNoFollowedLinks = [];
            $.each(links, function (i, link) {
                if (!comp.test(link)) { //Is outbound link
                    listLinks.push(link);
                }
            });
            $.each(noFollowedLinks, function (i, link) {
                if (!comp.test(link)) { //Is outbound link
                    listNoFollowedLinks.push(link);
                }
            });

            if (!listLinks.length && !listNoFollowedLinks.length) {
                etsSEO.getAnalysisMessage(
                    '#analysis-result--list-' + id_lang,
                    'outbound_link',
                    ETS_SEO_DEFINED.seo_analysis_rules.outbound_link.error,
                    'error'
                );
                etsSEO.setSeoScore('outbound_link', id_lang, 3);
            } else if (!listLinks.length && listNoFollowedLinks.length) {
                etsSEO.getAnalysisMessage(
                    '#analysis-result--list-' + id_lang,
                    'outbound_link',
                    ETS_SEO_DEFINED.seo_analysis_rules.outbound_link.all_nofollowed,
                    'warning'
                );
                etsSEO.setSeoScore('outbound_link', id_lang, 7);
            } else if (listLinks.length && listNoFollowedLinks.length) {
                etsSEO.getAnalysisMessage(
                    '#analysis-result--list-' + id_lang,
                    'outbound_link',
                    ETS_SEO_DEFINED.seo_analysis_rules.outbound_link.both,
                    'success'
                );
                etsSEO.setSeoScore('outbound_link', id_lang, 8);
            } else {
                etsSEO.getAnalysisMessage(
                    '#analysis-result--list-' + id_lang,
                    'outbound_link',
                    ETS_SEO_DEFINED.seo_analysis_rules.outbound_link.success,
                    'success'
                );
                etsSEO.setSeoScore('outbound_link', id_lang, 9);
            }
        },
        internalLink: function (id_lang, content) {
            content = content || '';
            if (!content) {
                if(ETS_SEO_CONTROLLER == 'AdminMeta'){
                    etsSEO.setSeoScore('internal_link', id_lang, 9);
                }
                else
                    etsSEO.setSeoScore('internal_link', id_lang, 0);
                etsSEO.removeAnalysisMessage('#analysis-result--list-' + id_lang,
                    'internal_link');
                return;
            }
            var links = etsSEO.getLinks(content);
            var noFollowedLinks = etsSEO.getLinksNoFollowed(content);
            var comp = new RegExp(location.host);
            var listLinks = [];
            var listNoFollowedLinks = [];
            $.each(links, function (i, link) {
                if (comp.test(link)) { //Is internal link
                    listLinks.push(link);
                }
            });
            $.each(noFollowedLinks, function (i, link) {
                if (comp.test(link)) { //Is internal link
                    listNoFollowedLinks.push(link);
                }
            });

            if (!listLinks.length && !listNoFollowedLinks.length) {
                etsSEO.getAnalysisMessage(
                    '#analysis-result--list-' + id_lang,
                    'internal_link',
                    ETS_SEO_DEFINED.seo_analysis_rules.internal_link.error,
                    'error'
                );

                etsSEO.setSeoScore('internal_link', id_lang, 3);
            } else if (!listLinks.length && listNoFollowedLinks.length) {
                etsSEO.getAnalysisMessage(
                    '#analysis-result--list-' + id_lang,
                    'internal_link',
                    ETS_SEO_DEFINED.seo_analysis_rules.internal_link.all_nofollowed,
                    'warning'
                );

                etsSEO.setSeoScore('internal_link', id_lang, 7);
            } else if (listLinks.length && listNoFollowedLinks.length) {
                etsSEO.getAnalysisMessage(
                    '#analysis-result--list-' + id_lang,
                    'internal_link',
                    ETS_SEO_DEFINED.seo_analysis_rules.internal_link.both,
                    'warning'
                );

                etsSEO.setSeoScore('internal_link', id_lang, 8);
            } else {
                etsSEO.getAnalysisMessage(
                    '#analysis-result--list-' + id_lang,
                    'internal_link',
                    ETS_SEO_DEFINED.seo_analysis_rules.internal_link.success,
                    'success'
                );
                etsSEO.setSeoScore('internal_link', id_lang, 9);
            }
        },
        singleH1: function (id_lang, content, pageName) {
            content = content || '';
            pageName = pageName || '';
            if (!content) {
                etsSEO.removeAnalysisMessage('#analysis-result--list-' + id_lang,
                    'single_h1');
                if(ETS_SEO_CONTROLLER == 'AdminMeta' || pageName == 'meta'){
                    etsSEO.setSeoScore('single_h1', id_lang, 9);
                }
                else
                    etsSEO.setSeoScore('single_h1', id_lang, -999);
                return;
            }

            var tmp = document.createElement('div');
            tmp.innerHTML = content;
            var h1 = tmp.getElementsByTagName('h1');
            if (h1.length) {
                etsSEO.getAnalysisMessage(
                    '#analysis-result--list-' + id_lang,
                    'single_h1',
                    ETS_SEO_DEFINED.seo_analysis_rules.single_h1.error,
                    'error'
                );
                etsSEO.setSeoScore('single_h1', id_lang, 1);
            } else {
                etsSEO.setSeoScore('single_h1', id_lang, 9);
                etsSEO.removeAnalysisMessage('#analysis-result--list-' + id_lang,
                    'single_h1');
            }
        },

        //Text length
        textLength: function (id_lang, content, page_type, pageName) {
            content = content || '';
            pageName = pageName || '';
            if (ETS_SEO_CONTROLLER == 'AdminMeta' || pageName == 'meta') {
                etsSEO.setSeoScore('text_length', id_lang, 9);
                etsSEO.removeAnalysisMessage('#analysis-result--list-' + id_lang,
                    'text_length');
                return;
            }
            //Taxonomy page	>250 words
            //Regular post or page > 300 words, category: 100 words
            //Cornerstone content page > 900 words
            //Value: taxonomy, regular , cornerstone

            var min_length = 300;
            if (ETS_SEO_CONTROLLER == 'AdminCategories' || (ETS_SEO_CONTROLLER == 'AdminCmsContent' && ETS_SEO_IS_CMS_CATEGORY)) {
                page_type = 'category';
            }
            switch (page_type) {
                case 'taxonomy':
                    min_length = 250;
                    break;
                case 'regular':
                    min_length = 300;
                    break;
                case 'cornerstone':
                    min_length = 900;
                    break;
                case 'category':
                    min_length = 100;
                    break;
                default:
                    min_length = 300;
            }
            var text_length = content.length ? content.trim().split(/\s+/).length : 0;

            if (text_length >= min_length) {
                if (ETS_SEO_DEFINED.seo_analysis_rules.text_length.success.short_code['[text_length]']) {
                    ETS_SEO_DEFINED.seo_analysis_rules.text_length.success.short_code['[text_length]'].number = text_length;
                }
                etsSEO.getAnalysisMessage(
                    '#analysis-result--list-' + id_lang,
                    'text_length',
                    ETS_SEO_DEFINED.seo_analysis_rules.text_length.success,
                    'success'
                );
                etsSEO.setSeoScore('text_length', id_lang, 9);
            } else {

                if (ETS_SEO_DEFINED.seo_analysis_rules.text_length.error.short_code['[text_length]']) {
                    ETS_SEO_DEFINED.seo_analysis_rules.text_length.error.short_code['[text_length]'].number = text_length;
                }
                if (ETS_SEO_DEFINED.seo_analysis_rules.text_length.error.short_code['[min_length]']) {
                    ETS_SEO_DEFINED.seo_analysis_rules.text_length.error.short_code['[min_length]'].number = min_length;
                }

                etsSEO.getAnalysisMessage(
                    '#analysis-result--list-' + id_lang,
                    'text_length',
                    ETS_SEO_DEFINED.seo_analysis_rules.text_length.error,
                    'error'
                );
                if (page_type == 'category') {
                    if (text_length < 30) {
                        etsSEO.setSeoScore('text_length', id_lang, -10);
                    } else if (text_length >= 30 && text_length <= 50) {
                        etsSEO.setSeoScore('text_length', id_lang, 3);
                    } else if (text_length > 50 && text_length < 100) {
                        etsSEO.setSeoScore('text_length', id_lang, 6);
                    }
                } else {

                    if (text_length <= 99) {
                        etsSEO.setSeoScore('text_length', id_lang, -20);
                    } else if (text_length >= 100 && text_length <= 199) {
                        etsSEO.setSeoScore('text_length', id_lang, -10);
                    } else if (text_length >= 200 && text_length <= 249) {
                        etsSEO.setSeoScore('text_length', id_lang, 3);
                    } else if (text_length >= 250 && text_length <= 299) {
                        etsSEO.setSeoScore('text_length', id_lang, 6);
                    }
                }

            }

        },
        keyPhraseLength: function (id_lang, str) {
            str = str || '';
            if (!str) {
                etsSEO.getAnalysisMessage(
                    '#analysis-result--list-' + id_lang,
                    'keyphrase_length',
                    ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_length.error,
                    'error'
                );

                etsSEO.setSeoScore('keyphrase_length', id_lang, -9999);
            } else {
                var words_length = str.trim().split(/\s+/g).length;
                etsSEO.getAnalysisMessage(
                    '#analysis-result--list-' + id_lang,
                    'keyphrase_length',
                    ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_length.success,
                    'success'
                );
                if (words_length < 5) {
                    etsSEO.setSeoScore('keyphrase_length', id_lang, 9);
                } else if (words_length >= 5 && words_length <= 8) {

                    if (ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_length.too_long.short_code['[count_length]']) {
                        ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_length.too_long.short_code['[count_length]'].number = words_length;
                    }
                    etsSEO.setSeoScore('keyphrase_length', id_lang, 6);
                    etsSEO.getAnalysisMessage(
                        '#analysis-result--list-' + id_lang,
                        'keyphrase_length',
                        ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_length.too_long,
                        'warning'
                    );
                } else {

                    if (ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_length.too_long.short_code['[count_length]']) {
                        ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_length.too_long.short_code['[count_length]'].number = words_length;
                    }
                    etsSEO.getAnalysisMessage(
                        '#analysis-result--list-' + id_lang,
                        'keyphrase_length',
                        ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_length.too_long,
                        'warning'
                    );
                    etsSEO.setSeoScore('keyphrase_length', id_lang, 3);
                }
            }
        },
        keyPhraseInTitle: function (id_lang, key_phrase, title) {
            key_phrase = key_phrase || '';
            title = title || '';
            var prefix = etsSEO.prefixInput();
            var pageTitle = prefix.title ? (ETS_SEO_CONTROLLER != 'AdminManufacturers' && ETS_SEO_CONTROLLER != 'AdminSuppliers' ? $(prefix.title + id_lang).val() : $(prefix.title.slice(0, -1)).val()) : '';

            etsSEO.rules.keyPhraseInPageTitle(id_lang, key_phrase, pageTitle);
            if ((!title || ETS_SEO_FORCE_USE_META_TEMPLATE) && key_phrase) {
                title = etsSEO.getMetaTitle(id_lang);
            }
            if (!key_phrase || !title) {

                etsSEO.setSeoScore('keyphrase_in_title', id_lang, 0);
                etsSEO.removeAnalysisMessage('#analysis-result--list-' + id_lang,
                    'keyphrase_in_title');
                return;
            }
            title = etsSEO.renderMetaData(title, id_lang, true);
            var myPattern = new RegExp('(?:^|\\s)' + etsSEO.escapeRegExp(key_phrase.trim()) + '(?:$|\\s|[^\\w])', 'gi');
            /*if(title.trim().length == key_phrase.trim().length){
                myPattern = new RegExp(etsSEO.escapeRegExp(key_phrase.trim()), 'gi');
            }*/
            var matchResult = title.trim().match(myPattern);
            if (matchResult !== null) {

                var pattern2 = new RegExp('(?:^|\\s)' + etsSEO.escapeRegExp(key_phrase.trim()) + '(?:$|\\s|[^\\w])', 'i');
                /*if(title.trim().length == key_phrase.trim().length){
                    pattern2 = new RegExp(etsSEO.escapeRegExp(key_phrase.trim()), 'gi');
                }*/
                var firstResult = title.match(pattern2);
                if (firstResult !== null && firstResult.index == 0) {
                    etsSEO.getAnalysisMessage(
                        '#analysis-result--list-' + id_lang,
                        'keyphrase_in_title',
                        ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_in_title.success,
                        'success'
                    );

                    etsSEO.setSeoScore('keyphrase_in_title', id_lang, 9);
                } else {
                    etsSEO.getAnalysisMessage(
                        '#analysis-result--list-' + id_lang,
                        'keyphrase_in_title',
                        ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_in_title.warning,
                        'warning'
                    );
                    etsSEO.setSeoScore('keyphrase_in_title', id_lang, 6);
                }

            } else {
                if (ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_in_title.error.short_code['[keyphrase]']) {
                    ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_in_title.error.short_code['[keyphrase]'].string = key_phrase;
                }
                etsSEO.getAnalysisMessage(
                    '#analysis-result--list-' + id_lang,
                    'keyphrase_in_title',
                    ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_in_title.error,
                    'error'
                );
                etsSEO.setSeoScore('keyphrase_in_title', id_lang, 2);
            }
        },
        keyPhraseInPageTitle: function (id_lang, key_phrase, title) {

            key_phrase = key_phrase || '';
            title = title || '';
            if (!key_phrase || !title) {
                etsSEO.setSeoScore('keyphrase_in_page_title', id_lang, 0);
                etsSEO.removeAnalysisMessage('#analysis-result--list-' + id_lang,
                    'keyphrase_in_page_title');
                return;
            }
            title = etsSEO.renderMetaData(title, id_lang, true);
            var myPattern = new RegExp('(?:^|\\s)' + etsSEO.escapeRegExp(key_phrase.trim()) + '(?:$|\\s|[^\\w])', 'gi');
            /*if(title.trim().length == key_phrase.trim().length){
                myPattern = new RegExp(etsSEO.escapeRegExp(key_phrase.trim()), 'gi');
            }*/
            var matchResult = title.trim().match(myPattern);

            if (matchResult !== null) {

                var pattern2 = new RegExp('(?:^|\\s)' + etsSEO.escapeRegExp(key_phrase.trim()) + '(?:$|\\s|[^\\w])', 'i');
                /*if(title.trim().length == key_phrase.trim().length){
                    pattern2 = new RegExp(etsSEO.escapeRegExp(key_phrase.trim()), 'gi');
                }*/
                var firstResult = title.match(pattern2);

                if (firstResult !== null && firstResult.index == 0) {
                    etsSEO.getAnalysisMessage(
                        '#analysis-result--list-' + id_lang,
                        'keyphrase_in_page_title',
                        ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_in_page_title.success,
                        'success'
                    );

                    etsSEO.setSeoScore('keyphrase_in_page_title', id_lang, 9);
                } else {
                    etsSEO.getAnalysisMessage(
                        '#analysis-result--list-' + id_lang,
                        'keyphrase_in_page_title',
                        ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_in_page_title.warning,
                        'warning'
                    );
                    etsSEO.setSeoScore('keyphrase_in_page_title', id_lang, 6);
                }

            } else {
                if (ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_in_page_title.error.short_code['[keyphrase]']) {
                    ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_in_page_title.error.short_code['[keyphrase]'].string = key_phrase;
                }
                etsSEO.getAnalysisMessage(
                    '#analysis-result--list-' + id_lang,
                    'keyphrase_in_page_title',
                    ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_in_page_title.error,
                    'error'
                );
                etsSEO.setSeoScore('keyphrase_in_page_title', id_lang, 2);
            }
        },
        pageTitleLength: function (id_lang, title) {
            title = title || '';
            if (!title || !title.trim().length) {
                etsSEO.getAnalysisMessage(
                    '#analysis-result--list-' + id_lang,
                    'page_title_length',
                    ETS_SEO_DEFINED.seo_analysis_rules.page_title_length.empty,
                    'error'
                );
                return;
            }
            if (title.trim().length > 65) {
                etsSEO.getAnalysisMessage(
                    '#analysis-result--list-' + id_lang,
                    'page_title_length',
                    ETS_SEO_DEFINED.seo_analysis_rules.page_title_length.too_long,
                    'error'
                );
            } else {
                etsSEO.getAnalysisMessage(
                    '#analysis-result--list-' + id_lang,
                    'page_title_length',
                    ETS_SEO_DEFINED.seo_analysis_rules.page_title_length.success,
                    'success'
                );
            }
        },
        keyphraseInIntroduction: function (id_lang, key_phrase, intro) {
            key_phrase = key_phrase || '';
            intro = intro || '';

            if (!intro) {
                if(ETS_SEO_CONTROLLER == 'AdminMeta'){
                    etsSEO.setSeoScore('keyphrase_in_intro', id_lang, 9);
                }
                else
                    etsSEO.setSeoScore('keyphrase_in_intro', id_lang, 0);
                etsSEO.removeAnalysisMessage('#analysis-result--list-' + id_lang,
                    'keyphrase_in_intro');
                return;
            }
            if (!key_phrase) {
                etsSEO.removeAnalysisMessage('#analysis-result--list-' + id_lang,
                    'keyphrase_in_intro');
                return;
            }
            if (intro && key_phrase) {

                var myPattern = new RegExp('(?:^|\\s)' + etsSEO.escapeRegExp(key_phrase.trim()) + '(?:$|\\s|[^\\w])', 'gi');
                var matchResult = intro.trim().match(myPattern);

                if (matchResult !== null) {
                    etsSEO.getAnalysisMessage(
                        '#analysis-result--list-' + id_lang,
                        'keyphrase_in_intro',
                        ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_in_intro.success,
                        'success'
                    );
                    etsSEO.setSeoScore('keyphrase_in_intro', id_lang, 9);
                } else {
                    etsSEO.getAnalysisMessage(
                        '#analysis-result--list-' + id_lang,
                        'keyphrase_in_intro',
                        ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_in_intro.error,
                        'error'
                    );

                    etsSEO.setSeoScore('keyphrase_in_intro', id_lang, 3);
                }
            }
        },
        keyphraseInSubheading: function (id_lang, key_phrase, content) {
            key_phrase = key_phrase || '';
            content = content || '';
            if (!content || !key_phrase) {
                if(!content && ETS_SEO_CONTROLLER == 'AdminMeta'){
                    etsSEO.setSeoScore('keyphrase_in_subheading', id_lang, 9);
                }
                else {
                    etsSEO.setSeoScore('keyphrase_in_subheading', id_lang, 0);
                }
                etsSEO.removeAnalysisMessage('#analysis-result--list-' + id_lang,
                    'keyphrase_in_subheading');
                return;
            }

            var tmp = document.createElement('div');
            tmp.innerHTML = content;
            var keyphraseArray = key_phrase.toLowerCase().split(/\s+/);
            var totalSubheading = 0;
            var totalSubheadingreflectKeyphrase = 0;
            for (var i = 2; i <= 3; i++) {
                var headings = tmp.getElementsByTagName('h' + i);
                totalSubheading += headings.length;
                if (headings && headings.length) {
                    for (var k = 0; k < headings.length; k++) {

                        var headingArrayContent = headings[k].innerText.toLowerCase().split(/\s+/);
                        if (keyphraseArray.length > 1) {
                            var tmpContain = [];
                            for (var t = 0; t < headingArrayContent.length; t++) {
                                if (keyphraseArray.indexOf(headingArrayContent[t]) !== -1 && tmpContain.indexOf(headingArrayContent[t]) === -1) {
                                    tmpContain.push(headingArrayContent[t]);
                                    if (tmpContain.length >= 2) {
                                        break;
                                    }
                                }
                            }
                            if (tmpContain.length > 1) {
                                totalSubheadingreflectKeyphrase++;
                            }
                        } else {
                            if (headingArrayContent.indexOf(keyphraseArray[0]) !== -1) {
                                totalSubheadingreflectKeyphrase++;
                            }
                        }
                    }
                }
            }

            if (totalSubheading) {
                if (totalSubheading > 1) {
                    var ratio = totalSubheadingreflectKeyphrase / totalSubheading * 100;
                    if (ratio < 30) {
                        etsSEO.getAnalysisMessage(
                            '#analysis-result--list-' + id_lang,
                            'keyphrase_in_subheading',
                            ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_in_subheading.too_little,
                            'error'
                        );
                        etsSEO.setSeoScore('keyphrase_in_subheading', id_lang, 3);
                    } else if (ratio > 75) {
                        etsSEO.getAnalysisMessage(
                            '#analysis-result--list-' + id_lang,
                            'keyphrase_in_subheading',
                            ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_in_subheading.too_much,
                            'error'
                        );
                        etsSEO.setSeoScore('keyphrase_in_subheading', id_lang, 3);
                    } else {
                        if (ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_in_subheading.good.short_code['[count]']) {
                            ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_in_subheading.good.short_code['[count]'].number = totalSubheadingreflectKeyphrase;
                        }
                        etsSEO.getAnalysisMessage(
                            '#analysis-result--list-' + id_lang,
                            'keyphrase_in_subheading',
                            ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_in_subheading.good,
                            'success'
                        );
                        etsSEO.setSeoScore('keyphrase_in_subheading', id_lang, 9);
                    }
                } else {
                    if (!totalSubheadingreflectKeyphrase) {
                        etsSEO.getAnalysisMessage(
                            '#analysis-result--list-' + id_lang,
                            'keyphrase_in_subheading',
                            ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_in_subheading.too_little,
                            'error'
                        );
                        etsSEO.setSeoScore('keyphrase_in_subheading', id_lang, 3);
                    } else {
                        if (ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_in_subheading.good.short_code['[count]']) {
                            ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_in_subheading.good.short_code['[count]'].number = totalSubheadingreflectKeyphrase;
                        }
                        etsSEO.getAnalysisMessage(
                            '#analysis-result--list-' + id_lang,
                            'keyphrase_in_subheading',
                            ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_in_subheading.good,
                            'success'
                        );
                        etsSEO.setSeoScore('keyphrase_in_subheading', id_lang, 9);
                    }
                }
            } else {
                etsSEO.setSeoScore('keyphrase_in_intro', id_lang, 9);
                etsSEO.removeAnalysisMessage('#analysis-result--list-' + id_lang,
                    'keyphrase_in_subheading');
            }
        },
        keyphraseDensity: function (id_lang, key_phrase, content) {
            key_phrase = key_phrase || '';
            content =  content || '';
            if (!content) {
                if(ETS_SEO_CONTROLLER == 'AdminMeta'){
                    etsSEO.setSeoScore('keyphrase_density', id_lang, 9);
                }
                else
                    etsSEO.setSeoScore('keyphrase_density', id_lang, 0);
                etsSEO.removeAnalysisMessage('#analysis-result--list-' + id_lang,
                    'keyphrase_density');
                return;
            }
            if (!key_phrase) {
                etsSEO.setSeoScore('keyphrase_density', id_lang, 0);
                etsSEO.setSeoScore('keyphrase_density_individual', id_lang, 0);
                etsSEO.removeAnalysisMessage('#analysis-result--list-' + id_lang,
                    'keyphrase_density');
                etsSEO.removeAnalysisMessage('#analysis-result--list-' + id_lang,
                    'keyphrase_density_individual');
                return;
            }
            //The focus keyphrase should be found minimum of 2 times

            if (key_phrase && content) {
                var textArray = content.trim().split(/\s+/);
                var counter = 0;
                var good = false;
                var myPattern = new RegExp('(?:^|\\s)' + etsSEO.escapeRegExp(key_phrase.trim()) + '(?:$|\\s|[^\\w])', 'gi');
                var keyphrase_density = content.match(myPattern);
                var wordsLength = content.split(/ /g).length;

                var recommend_keyphrase_length = Math.ceil(wordsLength * 0.03);
                if (recommend_keyphrase_length < 3) {
                    recommend_keyphrase_length = 3;
                }
                var recommend_keyphrase_length_min = Math.ceil(wordsLength * 0.003);
                if (recommend_keyphrase_length_min < 3) {
                    recommend_keyphrase_length_min = 3;
                }

                if (keyphrase_density != null) {
                    counter = keyphrase_density.length;
                    if (counter >= 3) {
                        var ratio = counter / wordsLength * 100;
                        if (ratio > 0.3 && ratio <= 3) {
                            if (ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_density.success.short_code['[count_word]']) {
                                ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_density.success.short_code['[count_word]'].number = counter;
                            }

                            etsSEO.getAnalysisMessage(
                                '#analysis-result--list-' + id_lang,
                                'keyphrase_density',
                                ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_density.success,
                                'success'
                            );

                            etsSEO.setSeoScore('keyphrase_density', id_lang, 9);
                        } else if (ratio > 3 && ratio <= 4 && counter > recommend_keyphrase_length) {
                            if (ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_density.more_than.short_code['[count_word]']) {
                                ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_density.more_than.short_code['[count_word]'].number = counter;
                            }
                            if (ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_density.more_than.short_code['[recommended_keyphrase_length]']) {
                                ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_density.more_than.short_code['[recommended_keyphrase_length]'].number = recommend_keyphrase_length;
                            }
                            etsSEO.getAnalysisMessage(
                                '#analysis-result--list-' + id_lang,
                                'keyphrase_density',
                                ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_density.more_than,
                                'error'
                            );

                            etsSEO.setSeoScore('keyphrase_density', id_lang, -50);
                        } else if (ratio > 0 && ratio <= 0.3 && counter < recommend_keyphrase_length_min) {

                            if (ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_density.error.short_code['[count_word]']) {
                                ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_density.error.short_code['[count_word]'].number = counter;
                            }
                            if (ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_density.error.short_code['[recommended_keyphrase_length]']) {
                                ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_density.error.short_code['[recommended_keyphrase_length]'].number = recommend_keyphrase_length_min;
                            }
                            etsSEO.getAnalysisMessage(
                                '#analysis-result--list-' + id_lang,
                                'keyphrase_density',
                                ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_density.error,
                                'error'
                            );

                            etsSEO.setSeoScore('keyphrase_density', id_lang, 4);
                        } else if (counter > recommend_keyphrase_length && ratio > 4) {
                            if (ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_density.more_than.short_code['[count_word]']) {
                                ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_density.more_than.short_code['[count_word]'].number = counter;
                            }
                            if (ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_density.more_than.short_code['[recommended_keyphrase_length]']) {
                                ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_density.more_than.short_code['[recommended_keyphrase_length]'].number = recommend_keyphrase_length;
                            }
                            etsSEO.getAnalysisMessage(
                                '#analysis-result--list-' + id_lang,
                                'keyphrase_density',
                                ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_density.more_than,
                                'error'
                            );

                            etsSEO.setSeoScore('keyphrase_density', id_lang, 4);
                        } else {
                            if (ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_density.success.short_code['[count_word]']) {
                                ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_density.success.short_code['[count_word]'].number = counter;
                            }
                            etsSEO.getAnalysisMessage(
                                '#analysis-result--list-' + id_lang,
                                'keyphrase_density',
                                ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_density.success,
                                'success'
                            );
                            etsSEO.setSeoScore('keyphrase_density', id_lang, 9);
                        }
                    } else {
                        if (ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_density.error.short_code['[count_word]']) {
                            ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_density.error.short_code['[count_word]'].number = counter;
                        }
                        if (ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_density.error.short_code['[recommended_keyphrase_length]']) {
                            ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_density.error.short_code['[recommended_keyphrase_length]'].number = recommend_keyphrase_length_min;
                        }
                        etsSEO.getAnalysisMessage(
                            '#analysis-result--list-' + id_lang,
                            'keyphrase_density',
                            ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_density.error,
                            'error'
                        );

                        etsSEO.setSeoScore('keyphrase_density', id_lang, 4);
                    }

                } else {
                    if (ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_density.error.short_code['[count_word]']) {
                        ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_density.error.short_code['[count_word]'].number = counter;
                    }
                    if (ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_density.error.short_code['[recommended_keyphrase_length]']) {
                        ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_density.error.short_code['[recommended_keyphrase_length]'].number = recommend_keyphrase_length_min;
                    }
                    etsSEO.getAnalysisMessage(
                        '#analysis-result--list-' + id_lang,
                        'keyphrase_density',
                        ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_density.error,
                        'error'
                    );

                    etsSEO.setSeoScore('keyphrase_density', id_lang, 4);

                }

            }
            etsSEO.rules.keyphraseIndividual(id_lang, key_phrase, content)
        },
        keyphraseIndividual: function (id_lang, key_phrase, content) {
            key_phrase = key_phrase || '';
            content =  content || '';
            if (!content || !key_phrase) {
                etsSEO.removeAnalysisMessage('#analysis-result--list-' + id_lang,
                    'keyphrase_density_individual');
                if(!content && ETS_SEO_CONTROLLER == 'AdminMeta'){
                    etsSEO.setSeoScore('single_h1', id_lang, 9);
                }
                else
                    etsSEO.setSeoScore('keyphrase_density_individual', id_lang, 0);
                return false;
            }

            if (key_phrase.trim().indexOf(' ') == -1) {
                etsSEO.removeAnalysisMessage('#analysis-result--list-' + id_lang,
                    'keyphrase_density_individual');
                etsSEO.setSeoScore('keyphrase_density_individual', id_lang, 9);
                return;
            }
            var subContent = content.replace(new RegExp('(?:^|\\s)' + etsSEO.escapeRegExp(key_phrase.trim()) + '(?:$|\\s|[^\\d\\W])', 'ig'), '');
            var keyphraseIndividuals = key_phrase.trim().split(/\s+/);
            var wordsLength = subContent.split(/ /g).length;
            var recommendedLength = Math.ceil(wordsLength * 0.003);
            if (recommendedLength < 1) {
                recommendedLength = 1;
            }
            var errorItems = [];

            for (var i = 0; i < keyphraseIndividuals.length; i++) {
                var desityAppearLength = (subContent.match(new RegExp(etsSEO.escapeRegExp(keyphraseIndividuals[i]), 'g')) || []).length;
                if (desityAppearLength < recommendedLength) {
                    errorItems.push({key: keyphraseIndividuals[i], count: desityAppearLength});
                    break;
                }
            }

            if (errorItems.length) {
                if (ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_density_individual.error.short_code['[keyphrase_individual]']) {
                    ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_density_individual.error.short_code['[keyphrase_individual]'].string = errorItems[0].key;
                }
                if (ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_density_individual.error.short_code['[recommended_keyphrase_length]']) {
                    ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_density_individual.error.short_code['[recommended_keyphrase_length]'].number = recommendedLength;
                }
                etsSEO.getAnalysisMessage(
                    '#analysis-result--list-' + id_lang,
                    'keyphrase_density_individual',
                    ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_density_individual.error,
                    'error'
                );
                etsSEO.setSeoScore('keyphrase_density_individual', id_lang, 3);
            } else {
                etsSEO.getAnalysisMessage(
                    '#analysis-result--list-' + id_lang,
                    'keyphrase_density_individual',
                    ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_density_individual.success,
                    'success'
                );
                etsSEO.setSeoScore('keyphrase_density_individual', id_lang, 9);
            }
        },
        imageAltAttribute: function (id_lang, key_phrase, content) {
            content = content || '';
            if (!content) {
                if(ETS_SEO_CONTROLLER == 'AdminMeta'){
                    etsSEO.setSeoScore('image_alt_attribute', id_lang, 9);
                }
                else
                    etsSEO.setSeoScore('image_alt_attribute', id_lang, 0);
                etsSEO.removeAnalysisMessage('#analysis-result--list-' + id_lang,
                    'image_alt_attribute');
                return;
            }
            var productImageContent = '';
            if (typeof ETS_SEO_PRODUCT_IMAGE !== "undefined" && ETS_SEO_PRODUCT_IMAGE) {
                var images = ETS_SEO_PRODUCT_IMAGE[id_lang];
                if (images.length) {
                    $.each(images, function (i, el) {
                        if (el.legend !== undefined)
                            productImageContent += '<img alt="' + el.legend + '" >';
                    });
                }
            }

            var tmp = document.createElement('div');
            tmp.innerHTML = content + productImageContent;

            var imgTags = tmp.getElementsByTagName('img');

            if (imgTags.length) {
                var has_alt = 0;
                var alt_has_keyphrase = 0;
                var myPattern = new RegExp('(?:^|\\s)' + etsSEO.escapeRegExp(key_phrase.trim()) + '(?:$|\\s|[^\\d\\W])', 'i');

                for (var i = 0; i < imgTags.length; i++) {
                    if ($(imgTags[i]).attr('alt')) {
                        has_alt++;
                        if (key_phrase) {
                            if ($(imgTags[i]).attr('alt').match(myPattern) !== null) {
                                alt_has_keyphrase++;
                            }
                        }

                    }
                }

                if (!has_alt) {
                    etsSEO.getAnalysisMessage(
                        '#analysis-result--list-' + id_lang,
                        'image_alt_attribute',
                        ETS_SEO_DEFINED.seo_analysis_rules.image_alt_attribute.no_alt,
                        'warning'
                    );
                    etsSEO.setSeoScore('image_alt_attribute', id_lang, 4);
                } else if (has_alt && !alt_has_keyphrase && key_phrase) {
                    etsSEO.getAnalysisMessage(
                        '#analysis-result--list-' + id_lang,
                        'image_alt_attribute',
                        ETS_SEO_DEFINED.seo_analysis_rules.image_alt_attribute.alt_no_keyphrase,
                        'warning'
                    );

                    etsSEO.setSeoScore('image_alt_attribute', id_lang, 6);
                } else {
                    etsSEO.getAnalysisMessage(
                        '#analysis-result--list-' + id_lang,
                        'image_alt_attribute',
                        ETS_SEO_DEFINED.seo_analysis_rules.image_alt_attribute.success,
                        'success'
                    );
                    var ratio = alt_has_keyphrase / has_alt;

                    if (has_alt >= 5 && ratio < 0.3) {
                        etsSEO.setSeoScore('image_alt_attribute', id_lang, 6);
                    } else if (has_alt >= 5 && ratio > 0.7) {
                        etsSEO.setSeoScore('image_alt_attribute', id_lang, 6);
                    } else if (has_alt < 5 && ratio >= 0.3 && ratio <= 0.75) {
                        etsSEO.setSeoScore('image_alt_attribute', id_lang, 9);
                    } else if (has_alt < 5 && alt_has_keyphrase) {
                        etsSEO.setSeoScore('image_alt_attribute', id_lang, 9);
                    }

                }
            } else {
                etsSEO.getAnalysisMessage(
                    '#analysis-result--list-' + id_lang,
                    'image_alt_attribute',
                    ETS_SEO_DEFINED.seo_analysis_rules.image_alt_attribute.error,
                    'error'
                );

                etsSEO.setSeoScore('image_alt_attribute', id_lang, 3);
            }
        },

        seoTitleWidth: function (id_lang, seo_title) {
            seo_title = seo_title || '';
            var prefix = etsSEO.prefixInput();
            var pageTitle = prefix.title ? (ETS_SEO_CONTROLLER != 'AdminManufacturers' && ETS_SEO_CONTROLLER != 'AdminSuppliers' ? $(prefix.title + id_lang).val() : $(prefix.title.slice(0, -1)).val()) : '';
            //etsSEO.rules.pageTitleLength(id_lang, pageTitle);
            if (!seo_title || ETS_SEO_FORCE_USE_META_TEMPLATE) {
                seo_title = etsSEO.getMetaTitle(id_lang);
            }

            if (!seo_title) {
                etsSEO.setSeoScore('seo_title_width', id_lang, 1);
                etsSEO.getAnalysisMessage(
                    '#analysis-result--list-' + id_lang,
                    'seo_title_width',
                    ETS_SEO_DEFINED.seo_analysis_rules.seo_title_width.error,
                    'error'
                );
                return;
            }
            seo_title = etsSEO.renderMetaData(seo_title, id_lang, true);
            //The text width should from 400px to 600px
            if (seo_title) {
                //var text_width = etsSEO.getTextWidth(seo_title);
                seo_title = seo_title.replace(/\r\n/gi, '\n').replace(/\n/gi, '').replace(/\s+/gi, ' ').trim();
                var text_width = seo_title.length;

                if (text_width >= 30 && text_width <= 60) {
                    etsSEO.getAnalysisMessage(
                        '#analysis-result--list-' + id_lang,
                        'seo_title_width',
                        ETS_SEO_DEFINED.seo_analysis_rules.seo_title_width.success,
                        'success'
                    );
                    etsSEO.setSeoScore('seo_title_width', id_lang, 9);
                } else if (text_width < 30) {
                    etsSEO.getAnalysisMessage(
                        '#analysis-result--list-' + id_lang,
                        'seo_title_width',
                        ETS_SEO_DEFINED.seo_analysis_rules.seo_title_width.success,
                        'success'
                    );
                    etsSEO.setSeoScore('seo_title_width', id_lang, 9);
                } else if (text_width > 60) {
                    etsSEO.getAnalysisMessage(
                        '#analysis-result--list-' + id_lang,
                        'seo_title_width',
                        ETS_SEO_DEFINED.seo_analysis_rules.seo_title_width.too_long,
                        'error'
                    );
                    etsSEO.setSeoScore('seo_title_width', id_lang, 3);
                }
            } else {
                etsSEO.getAnalysisMessage(
                    '#analysis-result--list-' + id_lang,
                    'seo_title_width',
                    ETS_SEO_DEFINED.seo_analysis_rules.seo_title_width.error,
                    'error'
                );
                etsSEO.setSeoScore('seo_title_width', id_lang, 1);
            }

        },

        metaDescLength: function (id_lang, desc) {
            desc = desc || '';
            //The description length should between 120 and 156 characters
            if (!desc || ETS_SEO_FORCE_USE_META_TEMPLATE) {
                desc = etsSEO.getMetaDesc(id_lang);
            }

            if (!desc || !desc.length) {
                etsSEO.getAnalysisMessage(
                    '#analysis-result--list-' + id_lang,
                    'meta_description_length',
                    ETS_SEO_DEFINED.seo_analysis_rules.meta_description_length.error,
                    'error'
                );
                etsSEO.setSeoScore('meta_description_length', id_lang, 1);
                return;
            }
            desc = etsSEO.renderMetaData(desc, id_lang, false);
            var desc_length = desc.replace(/<\/?[a-z][^>]*?>/gi, " ").replace(/\r\n/gi, '\n').replace(/\n/gi, '').replace(/\s+/gi, ' ').trim().length;
            if (desc_length < 120) {
                etsSEO.getAnalysisMessage(
                    '#analysis-result--list-' + id_lang,
                    'meta_description_length',
                    ETS_SEO_DEFINED.seo_analysis_rules.meta_description_length.warning,
                    'warning'
                );
                etsSEO.setSeoScore('meta_description_length', id_lang, 3);
            } else if (desc_length >= 120 && desc_length <= 156) {
                etsSEO.getAnalysisMessage(
                    '#analysis-result--list-' + id_lang,
                    'meta_description_length',
                    ETS_SEO_DEFINED.seo_analysis_rules.meta_description_length.success,
                    'success'
                );
                etsSEO.setSeoScore('meta_description_length', id_lang, 9);
            } else {

                etsSEO.getAnalysisMessage(
                    '#analysis-result--list-' + id_lang,
                    'meta_description_length',
                    ETS_SEO_DEFINED.seo_analysis_rules.meta_description_length.over_limited,
                    'warning'
                );
                etsSEO.setSeoScore('meta_description_length', id_lang, 6);
            }
        },

        keyphraseInMetaDesc: function (id_lang, key_phrase, desc) {
            key_phrase = key_phrase || '';
            desc = desc || '';
            if (key_phrase && (!desc || ETS_SEO_FORCE_USE_META_TEMPLATE)) {
                desc = etsSEO.getMetaDesc(id_lang);
            }

            if (!key_phrase || !desc) {
                etsSEO.setSeoScore('keyphrase_in_meta_desc', id_lang, 0);
                etsSEO.removeAnalysisMessage('#analysis-result--list-' + id_lang,
                    'keyphrase_in_meta_desc');
                return;
            }
            desc = etsSEO.renderMetaData(desc, id_lang, false);
            var myPattern = new RegExp('(?:^|\\s)' + etsSEO.escapeRegExp(key_phrase.trim()) + '(?:$|\\s|[^\\d\\W])', 'gi');
            if(desc.trim().length == key_phrase.trim().length){
                myPattern = new RegExp(etsSEO.escapeRegExp(key_phrase.trim()), 'gi');
            }
            var descMatch = desc.match(myPattern);
            if (descMatch !== null && descMatch.length <= 2) {

                etsSEO.getAnalysisMessage(
                    '#analysis-result--list-' + id_lang,
                    'keyphrase_in_meta_desc',
                    ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_in_meta_desc.success,
                    'success'
                );

                etsSEO.setSeoScore('keyphrase_in_meta_desc', id_lang, 9);
            } else if (descMatch !== null && descMatch.length > 2) {

                if (ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_in_meta_desc.more_than.short_code['[number]']) {
                    ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_in_meta_desc.more_than.short_code['[number]'].number = descMatch.length;
                }
                etsSEO.getAnalysisMessage(
                    '#analysis-result--list-' + id_lang,
                    'keyphrase_in_meta_desc',
                    ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_in_meta_desc.more_than,
                    'error'
                );
                etsSEO.setSeoScore('keyphrase_in_meta_desc', id_lang, 3);
            } else {

                etsSEO.getAnalysisMessage(
                    '#analysis-result--list-' + id_lang,
                    'keyphrase_in_meta_desc',
                    ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_in_meta_desc.error,
                    'error'
                );

                etsSEO.setSeoScore('keyphrase_in_meta_desc', id_lang, 3);
            }
        },

        keyphraseInSlug: function (id_lang, key_phrase, slug) {
            key_phrase = key_phrase || '';
            slug = slug || '';
            if (!key_phrase || !slug) {
                etsSEO.setSeoScore('keyphrase_in_slug', id_lang, 0);
                etsSEO.removeAnalysisMessage('#analysis-result--list-' + id_lang,
                    'keyphrase_in_slug');
                return;
            }

            var slugArray = slug.toLowerCase().match(/[0-9a-z'\-]+/gi);
            var kpArray = key_phrase.toLowerCase().match(/[0-9a-z'\-]+/gi);
            slugArray = slugArray.join('-').split('-'); //new code
            var matched = $.grep(slugArray, function (element) {
                return $.inArray(element, kpArray) !== -1;
            });

            if (matched.length == kpArray.length) {
                etsSEO.getAnalysisMessage(
                    '#analysis-result--list-' + id_lang,
                    'keyphrase_in_slug',
                    ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_in_slug.success,
                    'success'
                );
                etsSEO.setSeoScore('keyphrase_in_slug', id_lang, 9);
            } else if (matched.length) {
                etsSEO.getAnalysisMessage(
                    '#analysis-result--list-' + id_lang,
                    'keyphrase_in_slug',
                    ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_in_slug.good,
                    'success'
                );
                etsSEO.setSeoScore('keyphrase_in_slug', id_lang, 9);
            } else {
                etsSEO.getAnalysisMessage(
                    '#analysis-result--list-' + id_lang,
                    'keyphrase_in_slug',
                    ETS_SEO_DEFINED.seo_analysis_rules.keyphrase_in_slug.warning,
                    'warning'
                );
                etsSEO.setSeoScore('keyphrase_in_slug', id_lang, 6);
            }
        },
        minorKeyphraseLength: function (id_lang, minor_keyphrase) {
            minor_keyphrase = minor_keyphrase || '';
            if (!minor_keyphrase || !minor_keyphrase.length) {
                etsSEO.setSeoScore('minor_keyphrase_length', id_lang, 3);
                return;
            }
            var minor_errors = [];
            $.each(minor_keyphrase, function (i, item) {
                if (item.trim().split(/ /g).length > 4) {
                    minor_errors.push(item);
                }
            });
            if (minor_errors.length) {
                if (ETS_SEO_DEFINED.seo_analysis_rules.minor_keyphrase_length.too_long.short_code['[count_length]']) {
                    ETS_SEO_DEFINED.seo_analysis_rules.minor_keyphrase_length.too_long.short_code['[count_length]'].number = minor_errors[0].trim().split(/ /g).length;
                }
                if (ETS_SEO_DEFINED.seo_analysis_rules.minor_keyphrase_length.too_long.short_code['[minor_keyphrase]']) {
                    ETS_SEO_DEFINED.seo_analysis_rules.minor_keyphrase_length.too_long.short_code['[minor_keyphrase]'].string = minor_errors[0];
                }
                etsSEO.getAnalysisMessage(
                    '#analysis-result--list-' + id_lang,
                    'minor_keyphrase_length',
                    ETS_SEO_DEFINED.seo_analysis_rules.minor_keyphrase_length.too_long,
                    'warning'
                );
                etsSEO.setSeoScore('minor_keyphrase_length', id_lang, 3);
            } else {
                etsSEO.getAnalysisMessage(
                    '#analysis-result--list-' + id_lang,
                    'minor_keyphrase_length',
                    ETS_SEO_DEFINED.seo_analysis_rules.minor_keyphrase_length.success,
                    'success'
                );
                etsSEO.setSeoScore('minor_keyphrase_length', id_lang, 9);
            }
        },
        minorKeyphraseInContent: function (id_lang, minor_keyphrase, content) {
            minor_keyphrase = minor_keyphrase || [];
            content =  content || '';
            // minor_keyphrase mus be an array
            if (!minor_keyphrase || !content || !minor_keyphrase.length || ETS_SEO_CONTROLLER == 'AdminMeta') {
                etsSEO.setSeoScore('minor_keyphrase_in_content', id_lang, 9);
                if(!content && ETS_SEO_CONTROLLER == 'AdminMeta'){
                    etsSEO.setSeoScore('minor_keyphrase_in_content_individual', id_lang, 9);
                }
                else
                    etsSEO.setSeoScore('minor_keyphrase_in_content_individual', id_lang, 0);
                etsSEO.removeAnalysisMessage('#analysis-result--list-' + id_lang,
                    'minor_keyphrase_in_content');
                etsSEO.removeAnalysisMessage('#analysis-result--list-' + id_lang,
                    'minor_keyphrase_in_content_individual');
                return;
            }
            var minor_success = [];
            var minor_errors = [];
            var minor_over_limited = [];
            var minor_less_than = [];
            var wordsLength = content.split(/ /g).length;
            var minorKeyLengthRecommended = Math.ceil(wordsLength * 0.03);
            if (minorKeyLengthRecommended < 3) {
                minorKeyLengthRecommended = 3;
            }
            var minorKeyLengthRecommendedMin = Math.ceil(wordsLength * 0.003);
            if (minorKeyLengthRecommendedMin < 3) {
                minorKeyLengthRecommendedMin = 3;
            }

            $.each(minor_keyphrase, function (i, item) {
                var myPattern = new RegExp('(?:^|\\s)' + etsSEO.escapeRegExp(item) + '(?:$|\\s|[^\\d\\W])', 'gi');
                var resultMatched = content.match(myPattern);
                if (resultMatched != null && resultMatched.length) {
                    var counter = resultMatched.length;
                    if (counter >= 3) {
                        var ratio = counter / wordsLength * 100;
                        if (ratio > 0.3 && ratio <= 3) {
                            minor_success.push(item);
                        } else if (ratio > 3 && ratio <= 4 && counter > minorKeyLengthRecommended) {
                            minor_over_limited.push({key: item, count: resultMatched.length});
                        } else if (ratio > 0 && ratio <= 0.3 && counter < minorKeyLengthRecommendedMin) {
                            minor_less_than.push({key: item, count: resultMatched.length});
                        } else if (counter > minorKeyLengthRecommended && ratio > 4) {
                            minor_over_limited.push({key: item, count: resultMatched.length});
                        } else {
                            minor_success.push(item);
                        }
                    } else {
                        minor_less_than.push({key: item, count: resultMatched.length});
                    }
                } else {
                    minor_errors.push(item);
                }
            });

            if (minor_errors.length || minor_over_limited.length || minor_less_than.length) {
                if (minor_success.length) {
                    etsSEO.setSeoScore('minor_keyphrase_in_content', id_lang, 5);
                } else {
                    etsSEO.setSeoScore('minor_keyphrase_in_content', id_lang, 3);
                }
                if (minor_errors.length) {
                    if (ETS_SEO_DEFINED.seo_analysis_rules.minor_keyphrase_in_content.error.short_code['[minor_keyphrase]']) {
                        ETS_SEO_DEFINED.seo_analysis_rules.minor_keyphrase_in_content.error.short_code['[minor_keyphrase]'].string = minor_errors.join(', ');
                    }
                    etsSEO.getAnalysisMessage(
                        '#analysis-result--list-' + id_lang,
                        'minor_keyphrase_in_content',
                        ETS_SEO_DEFINED.seo_analysis_rules.minor_keyphrase_in_content.error,
                        'error'
                    );
                } else if (minor_less_than.length) {
                    if (ETS_SEO_DEFINED.seo_analysis_rules.minor_keyphrase_in_content.less_than.short_code['[minor_keyphrase]']) {
                        ETS_SEO_DEFINED.seo_analysis_rules.minor_keyphrase_in_content.less_than.short_code['[minor_keyphrase]'].string = minor_less_than[0].key;
                    }
                    if (ETS_SEO_DEFINED.seo_analysis_rules.minor_keyphrase_in_content.less_than.short_code['[count_word]']) {
                        ETS_SEO_DEFINED.seo_analysis_rules.minor_keyphrase_in_content.less_than.short_code['[count_word]'].number = minor_less_than[0].count;
                    }
                    if (ETS_SEO_DEFINED.seo_analysis_rules.minor_keyphrase_in_content.less_than.short_code['[recommended_minor_keyphrase_length]']) {
                        ETS_SEO_DEFINED.seo_analysis_rules.minor_keyphrase_in_content.less_than.short_code['[recommended_minor_keyphrase_length]'].number = minorKeyLengthRecommendedMin;
                    }
                    etsSEO.getAnalysisMessage(
                        '#analysis-result--list-' + id_lang,
                        'minor_keyphrase_in_content',
                        ETS_SEO_DEFINED.seo_analysis_rules.minor_keyphrase_in_content.less_than,
                        'error'
                    );
                } else {
                    if (ETS_SEO_DEFINED.seo_analysis_rules.minor_keyphrase_in_content.over_limited.short_code['[minor_keyphrase]']) {
                        ETS_SEO_DEFINED.seo_analysis_rules.minor_keyphrase_in_content.over_limited.short_code['[minor_keyphrase]'].string = minor_over_limited[0].key;
                    }
                    if (ETS_SEO_DEFINED.seo_analysis_rules.minor_keyphrase_in_content.over_limited.short_code['[count_word]']) {
                        ETS_SEO_DEFINED.seo_analysis_rules.minor_keyphrase_in_content.over_limited.short_code['[count_word]'].number = minor_over_limited[0].count;
                    }
                    if (ETS_SEO_DEFINED.seo_analysis_rules.minor_keyphrase_in_content.over_limited.short_code['[recommended_minor_keyphrase_length]']) {
                        ETS_SEO_DEFINED.seo_analysis_rules.minor_keyphrase_in_content.over_limited.short_code['[recommended_minor_keyphrase_length]'].number = minorKeyLengthRecommended;
                    }
                    etsSEO.getAnalysisMessage(
                        '#analysis-result--list-' + id_lang,
                        'minor_keyphrase_in_content',
                        ETS_SEO_DEFINED.seo_analysis_rules.minor_keyphrase_in_content.over_limited,
                        'error'
                    );
                }
            } else {
                etsSEO.getAnalysisMessage(
                    '#analysis-result--list-' + id_lang,
                    'minor_keyphrase_in_content',
                    ETS_SEO_DEFINED.seo_analysis_rules.minor_keyphrase_in_content.success,
                    'success'
                );
                etsSEO.setSeoScore('minor_keyphrase_in_content', id_lang, 9);
            }
            etsSEO.rules.minorKeyphraseIndividual(id_lang, minor_keyphrase, content)

        },
        minorKeyphraseIndividual: function (id_lang, minor_keyphrase, content) {
            minor_keyphrase = minor_keyphrase || [];
            content = content || '';
            if (!minor_keyphrase || !content || !minor_keyphrase.length) {
                etsSEO.removeAnalysisMessage('#analysis-result--list-' + id_lang,
                    'minor_keyphrase_in_content_individual');
                etsSEO.setSeoScore('minor_keyphrase_in_content_individual', id_lang, 9);
                return false;
            }
            var errorItems = [];
            var individualValid = [];
            for (var k = 0; k < minor_keyphrase.length; k++) {
                var minorItem = minor_keyphrase[k];
                if (minorItem.trim().indexOf(' ') !== -1) {
                    individualValid.push(minorItem);
                    var subContent = content.replace(new RegExp('(?:^|\\s)' + etsSEO.escapeRegExp(minorItem.trim()) + '(?:$|\\s|[^\\d\\W])', 'ig'), '');
                    var keyphraseIndividuals = minorItem.trim().split(/\s+/);
                    var wordsLength = subContent.split(/ /g).length;
                    var recommendedLength = Math.ceil(wordsLength * 0.003);
                    if (recommendedLength < 1) {
                        recommendedLength = 1;
                    }
                    for (var i = 0; i < keyphraseIndividuals.length; i++) {
                        var desityAppearLength = (subContent.match(new RegExp(etsSEO.escapeRegExp(keyphraseIndividuals[i]), 'g')) || []).length;
                        if (desityAppearLength < recommendedLength) {
                            errorItems.push({key: keyphraseIndividuals[i], count: desityAppearLength});
                            break;
                        }
                    }
                }
                if (errorItems.length) {
                    break;
                }

            }
            if (!individualValid.length) {
                etsSEO.removeAnalysisMessage('#analysis-result--list-' + id_lang,
                    'minor_keyphrase_in_content_individual');
                etsSEO.setSeoScore('minor_keyphrase_in_content_individual', id_lang, 9);
                return false;
            }

            if (errorItems.length) {
                if (ETS_SEO_DEFINED.seo_analysis_rules.minor_keyphrase_in_content_individual.error.short_code['[keyphrase_individual]']) {
                    ETS_SEO_DEFINED.seo_analysis_rules.minor_keyphrase_in_content_individual.error.short_code['[keyphrase_individual]'].string = errorItems[0].key;
                }
                if (ETS_SEO_DEFINED.seo_analysis_rules.minor_keyphrase_in_content_individual.error.short_code['[recommended_keyphrase_length]']) {
                    ETS_SEO_DEFINED.seo_analysis_rules.minor_keyphrase_in_content_individual.error.short_code['[recommended_keyphrase_length]'].number = recommendedLength;
                }
                etsSEO.getAnalysisMessage(
                    '#analysis-result--list-' + id_lang,
                    'minor_keyphrase_in_content_individual',
                    ETS_SEO_DEFINED.seo_analysis_rules.minor_keyphrase_in_content_individual.error,
                    'error'
                );
                etsSEO.setSeoScore('minor_keyphrase_in_content_individual', id_lang, 3);
            } else {
                etsSEO.getAnalysisMessage(
                    '#analysis-result--list-' + id_lang,
                    'minor_keyphrase_in_content_individual',
                    ETS_SEO_DEFINED.seo_analysis_rules.minor_keyphrase_in_content_individual.success,
                    'success'
                );
                etsSEO.setSeoScore('minor_keyphrase_in_content_individual', id_lang, 9);
            }
        },
        minorKeyphraseInMetaTitle: function (id_lang, minor_keyphrase, title) {
            minor_keyphrase = minor_keyphrase || [];
            title = title || '';
            if (!title || ETS_SEO_FORCE_USE_META_TEMPLATE) {
                title = etsSEO.getMetaTitle(id_lang);
            }

            if (!minor_keyphrase || !title || !minor_keyphrase.length) {
                if (minor_keyphrase && !title) {
                    var prefix = etsSEO.prefixInput();
                    var pageTitle = prefix.title ? (ETS_SEO_CONTROLLER != 'AdminManufacturers' && ETS_SEO_CONTROLLER != 'AdminSuppliers' ? $(prefix.title + id_lang).val() : $(prefix.title.slice(0, -1)).val()) : '';
                    etsSEO.rules.minorKeyphraseInPageTitle(id_lang, minor_keyphrase, pageTitle);
                }
                etsSEO.setSeoScore('minor_keyphrase_in_title', id_lang, 9);
                etsSEO.removeAnalysisMessage('#analysis-result--list-' + id_lang,
                    'minor_keyphrase_in_title');
                return;
            }
            var minor_success = [];
            var minor_errors = [];
            var over_limited = [];
            title = etsSEO.renderMetaData(title, id_lang, true);
            $.each(minor_keyphrase, function (i, item) {
                var myPattern = new RegExp('(?:^|\\s)' + etsSEO.escapeRegExp(item) + '(?:$|\\s|[^\\d\\W])', 'gi');
                if(title.trim().length == item.trim().length){
                    myPattern = new RegExp(etsSEO.escapeRegExp(item.trim()), 'gi');
                }
                var resultMatched = title.match(myPattern);
                if (resultMatched != null && resultMatched.length) {
                    if (resultMatched.length > 2 && !over_limited.length) {
                        over_limited.push({key: item, count: resultMatched.length});
                    }
                    minor_success.push(item);
                } else {
                    minor_errors.push(item);
                }
            });

            etsSEO.removeAnalysisMessage('#analysis-result--list-' + id_lang,
                'minor_keyphrase_acceptance');
            etsSEO.removeAnalysisMessage('#analysis-result--list-' + id_lang,
                'minor_keyphrase_in_title');
            etsSEO.removeAnalysisMessage('#analysis-result--list-' + id_lang,
                'minor_keyphrase_in_page_title');

            if (minor_errors.length || over_limited.length) {
                if (minor_success.length) {
                    etsSEO.setSeoScore('minor_keyphrase_in_title', id_lang, 5);
                } else {
                    etsSEO.setSeoScore('minor_keyphrase_in_title', id_lang, 3);
                }
                if (minor_errors.length) {
                    var listMinorSucess = etsSEO.getMinorKeyphraseInMetaTileDesc(id_lang);
                    minor_errors = etsSEO.differenceOf2Arrays(minor_errors, listMinorSucess);
                    if (minor_errors.length) {

                        if (ETS_SEO_DEFINED.seo_analysis_rules.minor_keyphrase_in_title.error.short_code['[minor_keyphrase]']) {
                            ETS_SEO_DEFINED.seo_analysis_rules.minor_keyphrase_in_title.error.short_code['[minor_keyphrase]'].string = minor_errors.join(', ');
                        }
                        etsSEO.getAnalysisMessage(
                            '#analysis-result--list-' + id_lang,
                            'minor_keyphrase_in_title',
                            ETS_SEO_DEFINED.seo_analysis_rules.minor_keyphrase_in_title.error,
                            'error'
                        );
                    } else if (minor_keyphrase.length == listMinorSucess.length) {
                        etsSEO.getAnalysisMessage(
                            '#analysis-result--list-' + id_lang,
                            'minor_keyphrase_acceptance',
                            ETS_SEO_DEFINED.seo_analysis_rules.minor_keyphrase_acceptance.success,
                            'success'
                        );
                    }
                } else {
                    if (ETS_SEO_DEFINED.seo_analysis_rules.minor_keyphrase_in_title.over_limited.short_code['[minor_keyphrase]']) {
                        ETS_SEO_DEFINED.seo_analysis_rules.minor_keyphrase_in_title.over_limited.short_code['[minor_keyphrase]'].string = over_limited[0].key;
                    }
                    if (ETS_SEO_DEFINED.seo_analysis_rules.minor_keyphrase_in_title.over_limited.short_code['[count_word]']) {
                        ETS_SEO_DEFINED.seo_analysis_rules.minor_keyphrase_in_title.over_limited.short_code['[count_word]'].string = over_limited[0].count;
                    }
                    etsSEO.getAnalysisMessage(
                        '#analysis-result--list-' + id_lang,
                        'minor_keyphrase_in_title',
                        ETS_SEO_DEFINED.seo_analysis_rules.minor_keyphrase_in_title.over_limited,
                        'error'
                    );
                }

            } else {
                etsSEO.removeAnalysisMessage('#analysis-result--list-' + id_lang,
                    'minor_keyphrase_in_title');
                etsSEO.removeAnalysisMessage('#analysis-result--list-' + id_lang,
                    'minor_keyphrase_in_page_title');
                etsSEO.removeAnalysisMessage('#analysis-result--list-' + id_lang,
                    'minor_keyphrase_in_desc');
                etsSEO.getAnalysisMessage(
                    '#analysis-result--list-' + id_lang,
                    'minor_keyphrase_acceptance',
                    ETS_SEO_DEFINED.seo_analysis_rules.minor_keyphrase_acceptance.success,
                    'success'
                );
                etsSEO.setSeoScore('minor_keyphrase_in_title', id_lang, 9);
            }
            etsSEO.setSeoScore('minor_keyphrase_acceptance', id_lang, 9);
            etsSEO.setSeoScore('minor_keyphrase_in_page_title', id_lang, 9);

        },
        minorKeyphraseInPageTitle: function (id_lang, minor_keyphrase, title) {

            minor_keyphrase = minor_keyphrase || [];
            title = title || '';
            if (!minor_keyphrase || !title || !minor_keyphrase.length) {
                etsSEO.setSeoScore('minor_keyphrase_in_page_title', id_lang, 9);
                etsSEO.removeAnalysisMessage('#analysis-result--list-' + id_lang,
                    'minor_keyphrase_in_page_title');
                return;
            }
            var minor_success = [];
            var minor_errors = [];
            var over_limited = [];
            title = etsSEO.renderMetaData(title, id_lang, true);
            $.each(minor_keyphrase, function (i, item) {
                var myPattern = new RegExp('(?:^|\\s)' + etsSEO.escapeRegExp(item) + '(?:$|\\s|[^\\d\\W])', 'gi');
                if(title.trim().length == item.trim().length){
                    myPattern = new RegExp(etsSEO.escapeRegExp(key_phrase.trim()), 'gi');
                }
                var resultMatched = title.match(myPattern);
                if (resultMatched != null && resultMatched.length) {
                    if (resultMatched.length > 2 && !over_limited.length) {
                        over_limited.push({key: item, count: resultMatched.length});
                    }
                    minor_success.push(item);
                } else {
                    minor_errors.push(item);
                }
            });

            etsSEO.removeAnalysisMessage('#analysis-result--list-' + id_lang,
                'minor_keyphrase_acceptance');
            etsSEO.removeAnalysisMessage('#analysis-result--list-' + id_lang,
                'minor_keyphrase_in_page_title');

            if (minor_errors.length || over_limited.length) {
                if (minor_success.length) {
                    etsSEO.setSeoScore('minor_keyphrase_in_page_title', id_lang, 5);
                } else {
                    etsSEO.setSeoScore('minor_keyphrase_in_page_title', id_lang, 3);
                }
                if (minor_errors.length) {
                    var listMinorSucess = etsSEO.getMinorKeyphraseInMetaTileDesc(id_lang);
                    minor_errors = etsSEO.differenceOf2Arrays(minor_errors, listMinorSucess);

                    if (minor_errors.length) {

                        if (ETS_SEO_DEFINED.seo_analysis_rules.minor_keyphrase_in_page_title.error.short_code['[minor_keyphrase]']) {
                            ETS_SEO_DEFINED.seo_analysis_rules.minor_keyphrase_in_page_title.error.short_code['[minor_keyphrase]'].string = minor_errors.join(', ');
                        }
                        etsSEO.getAnalysisMessage(
                            '#analysis-result--list-' + id_lang,
                            'minor_keyphrase_in_page_title',
                            ETS_SEO_DEFINED.seo_analysis_rules.minor_keyphrase_in_page_title.error,
                            'error'
                        );
                    } else if (minor_keyphrase.length == listMinorSucess.length) {
                        etsSEO.getAnalysisMessage(
                            '#analysis-result--list-' + id_lang,
                            'minor_keyphrase_acceptance',
                            ETS_SEO_DEFINED.seo_analysis_rules.minor_keyphrase_acceptance.success,
                            'success'
                        );
                    }
                } else {
                    if (ETS_SEO_DEFINED.seo_analysis_rules.minor_keyphrase_in_page_title.over_limited.short_code['[minor_keyphrase]']) {
                        ETS_SEO_DEFINED.seo_analysis_rules.minor_keyphrase_in_page_title.over_limited.short_code['[minor_keyphrase]'].string = over_limited[0].key;
                    }
                    if (ETS_SEO_DEFINED.seo_analysis_rules.minor_keyphrase_in_page_title.over_limited.short_code['[count_word]']) {
                        ETS_SEO_DEFINED.seo_analysis_rules.minor_keyphrase_in_page_title.over_limited.short_code['[count_word]'].string = over_limited[0].count;
                    }
                    etsSEO.getAnalysisMessage(
                        '#analysis-result--list-' + id_lang,
                        'minor_keyphrase_in_page_title',
                        ETS_SEO_DEFINED.seo_analysis_rules.minor_keyphrase_in_page_title.over_limited,
                        'error'
                    );
                }

            } else {

                etsSEO.removeAnalysisMessage('#analysis-result--list-' + id_lang,
                    'minor_keyphrase_in_title');
                etsSEO.removeAnalysisMessage('#analysis-result--list-' + id_lang,
                    'minor_keyphrase_in_page_title');
                etsSEO.removeAnalysisMessage('#analysis-result--list-' + id_lang,
                    'minor_keyphrase_in_desc');
                etsSEO.getAnalysisMessage(
                    '#analysis-result--list-' + id_lang,
                    'minor_keyphrase_acceptance',
                    ETS_SEO_DEFINED.seo_analysis_rules.minor_keyphrase_acceptance.success,
                    'success'
                );
                etsSEO.setSeoScore('minor_keyphrase_in_page_title', id_lang, 9);
            }
            etsSEO.setSeoScore('minor_keyphrase_acceptance', id_lang, 9);

        },
        minorKeyphraseInMetaDesc: function (id_lang, minor_keyphrase, desc) {
            minor_keyphrase = minor_keyphrase || [];
            desc = desc || '';
            if (!desc || ETS_SEO_FORCE_USE_META_TEMPLATE) {
                desc = etsSEO.getMetaDesc(id_lang);
            }

            if (!minor_keyphrase || !desc || !minor_keyphrase.length) {
                etsSEO.setSeoScore('minor_keyphrase_in_desc', id_lang, 9);
                etsSEO.removeAnalysisMessage('#analysis-result--list-' + id_lang,
                    'minor_keyphrase_in_desc');
                return;
            }
            var minor_success = [];
            var minor_errors = [];
            var over_limited = [];
            desc = etsSEO.renderMetaData(desc, id_lang, false);
            $.each(minor_keyphrase, function (i, item) {
                var myPattern = new RegExp('(?:^|\\s)' + etsSEO.escapeRegExp(item) + '(?:$|\\s|[^\\d\\W])', 'gi');
                if(desc.trim().length == item.trim().length){
                    myPattern = new RegExp(etsSEO.escapeRegExp(key_phrase.trim()), 'gi');
                }
                var resultMatched = desc.match(myPattern);
                if (resultMatched != null && resultMatched.length) {
                    if (resultMatched.length > 2 && !over_limited.length) {
                        over_limited.push({key: item, count: resultMatched.length});
                    }
                    minor_success.push(item);
                } else {
                    minor_errors.push(item);
                }
            });

            if (minor_errors.length || over_limited.length) {
                if (minor_success.length) {
                    etsSEO.setSeoScore('minor_keyphrase_in_desc', id_lang, 5);
                } else {
                    etsSEO.setSeoScore('minor_keyphrase_in_desc', id_lang, 3);
                }
                if (minor_errors.length) {
                    if (ETS_SEO_DEFINED.seo_analysis_rules.minor_keyphrase_in_desc.error.short_code['[minor_keyphrase]']) {
                        ETS_SEO_DEFINED.seo_analysis_rules.minor_keyphrase_in_desc.error.short_code['[minor_keyphrase]'].string = minor_errors.join(', ');
                    }
                    etsSEO.getAnalysisMessage(
                        '#analysis-result--list-' + id_lang,
                        'minor_keyphrase_in_desc',
                        ETS_SEO_DEFINED.seo_analysis_rules.minor_keyphrase_in_desc.error,
                        'error'
                    );
                } else {
                    if (ETS_SEO_DEFINED.seo_analysis_rules.minor_keyphrase_in_desc.over_limited.short_code['[minor_keyphrase]']) {
                        ETS_SEO_DEFINED.seo_analysis_rules.minor_keyphrase_in_desc.over_limited.short_code['[minor_keyphrase]'].string = over_limited[0].key;
                    }
                    if (ETS_SEO_DEFINED.seo_analysis_rules.minor_keyphrase_in_desc.over_limited.short_code['[count_word]']) {
                        ETS_SEO_DEFINED.seo_analysis_rules.minor_keyphrase_in_desc.over_limited.short_code['[count_word]'].number = over_limited[0].count;
                    }

                    etsSEO.getAnalysisMessage(
                        '#analysis-result--list-' + id_lang,
                        'minor_keyphrase_in_desc',
                        ETS_SEO_DEFINED.seo_analysis_rules.minor_keyphrase_in_desc.over_limited,
                        'error'
                    );
                }

            } else {
                etsSEO.getAnalysisMessage(
                    '#analysis-result--list-' + id_lang,
                    'minor_keyphrase_in_desc',
                    ETS_SEO_DEFINED.seo_analysis_rules.minor_keyphrase_in_desc.success,
                    'success'
                );
                etsSEO.setSeoScore('minor_keyphrase_in_desc', id_lang, 9);
            }
        },
    },

    //Readability rules
    readability: {
        notEnoughContent: function (id_lang, content) {
            //Minimum 50 characters
            content = content || '';
            if (!content || content.replace(/\s/g, '').length < 50) {
                etsSEO.getAnalysisMessage(
                    '#analysis-result--list-readablity-' + id_lang,
                    'not_enough_content',
                    ETS_SEO_DEFINED.readability_rules.not_enough_content.error,
                    'error'
                );
                etsSEO.setReadabilityScore('not_enough_content', id_lang, -9999);
            } else {
                etsSEO.removeAnalysisMessage(
                    '#analysis-result--list-readablity-' + id_lang,
                    'not_enough_content'
                );
                etsSEO.setReadabilityScore('not_enough_content', id_lang, 9);
            }
        },

        sentenceLength: function (id_lang, content) {
            content = content || '';
            if (!content) {
                etsSEO.setReadabilityScore('sentence_length', id_lang, 0);
                etsSEO.removeAnalysisMessage('#analysis-result--list-readablity-' + id_lang,
                    'sentence_length');
            }
            //Maximum 25% sentence more than 20 words
            var sentences = content.split(/[.!?][ |\n]/g);
            var total_sentence = sentences.length;
            var total_sentence_max_20_words = 0;
            for (var i = 0; i < sentences.length; i++) {
                if (sentences[i].split(/\s+/).length <= 20) {
                    total_sentence_max_20_words++;
                }
            }
            var percent_good = total_sentence_max_20_words / total_sentence * 100;
            var percent_bad = 100 - percent_good;
            if (percent_bad <= 25) {
                etsSEO.getAnalysisMessage(
                    '#analysis-result--list-readablity-' + id_lang,
                    'sentence_length',
                    ETS_SEO_DEFINED.readability_rules.sentence_length.success,
                    'success'
                );
                etsSEO.setReadabilityScore('sentence_length', id_lang, 9);
            } else if (percent_bad > 25 && percent_bad <= 30) {
                if (ETS_SEO_DEFINED.readability_rules.sentence_length.error.short_code['[number]']) {
                    ETS_SEO_DEFINED.readability_rules.sentence_length.error.short_code['[number]'].number = Math.round(100 - percent_good);
                }
                etsSEO.getAnalysisMessage(
                    '#analysis-result--list-readablity-' + id_lang,
                    'sentence_length',
                    ETS_SEO_DEFINED.readability_rules.sentence_length.error,
                    'warning'
                );

                etsSEO.setReadabilityScore('sentence_length', id_lang, 6);
            } else {
                if (ETS_SEO_DEFINED.readability_rules.sentence_length.error.short_code['[number]']) {
                    ETS_SEO_DEFINED.readability_rules.sentence_length.error.short_code['[number]'].number = Math.round(100 - percent_good);
                }
                etsSEO.getAnalysisMessage(
                    '#analysis-result--list-readablity-' + id_lang,
                    'sentence_length',
                    ETS_SEO_DEFINED.readability_rules.sentence_length.error,
                    'error'
                );
                etsSEO.setReadabilityScore('sentence_length', id_lang, 3);
            }
        },

        fleschReadingEase: function (id_lang, content) {
            content = content || '';
            if (!content) {
                etsSEO.setReadabilityScore('flesch_reading_ease', id_lang, 0);
                etsSEO.removeAnalysisMessage('#analysis-result--list-readablity-' + id_lang,
                    'flesch_reading_ease');
                return;
            }
            var iso_code = null;
            Object.keys(ETS_SEO_LANGUAGES).forEach(function (key) {
                if (ETS_SEO_LANGUAGES[key] == id_lang) {
                    iso_code = key;
                }
            });
            if (iso_code !== 'en') {
                etsSEO.getAnalysisMessage(
                    '#analysis-result--list-readablity-' + id_lang,
                    'flesch_reading_ease',
                    ETS_SEO_DEFINED.readability_rules.flesch_reading_ease.success,
                    'success'
                );
                etsSEO.setReadabilityScore('flesch_reading_ease', id_lang, 9);
                return;
            }

            /*0-50 score : red
            *50-60 score: orange
            *60-100 score: green
            *=========== RANKING ============
            * 90 - 100	very easy to read, easily understood by an average 11 - year - old student
            * 80 - 90	easy to read
            * 70 - 80	fairly easy to read
            * 60 - 70	easily understood by 13 - to 15 - year - old students
            * 50 - 60	fairly difficult to read
            * 30 - 50	difficult to read, best understood by college graduates
            * 0 - 30	very difficult to read, best understood by university graduates
            */
            var score = Math.round(etsSEO.fleschReadingEase.score(content).score);
            if (score < 30) {
                if (ETS_SEO_DEFINED.readability_rules.flesch_reading_ease.error.short_code['[score]']) {
                    ETS_SEO_DEFINED.readability_rules.flesch_reading_ease.error.short_code['[score]'].number = score;
                }
                etsSEO.getAnalysisMessage(
                    '#analysis-result--list-readablity-' + id_lang,
                    'flesch_reading_ease',
                    ETS_SEO_DEFINED.readability_rules.flesch_reading_ease.error,
                    'error'
                );
                etsSEO.setReadabilityScore('flesch_reading_ease', id_lang, 3);
            } else if (score >= 50 && score <= 60) {
                if (ETS_SEO_DEFINED.readability_rules.flesch_reading_ease.warning.short_code['[score]']) {
                    ETS_SEO_DEFINED.readability_rules.flesch_reading_ease.warning.short_code['[score]'].number = score;
                }
                etsSEO.getAnalysisMessage(
                    '#analysis-result--list-readablity-' + id_lang,
                    'flesch_reading_ease',
                    ETS_SEO_DEFINED.readability_rules.flesch_reading_ease.warning,
                    'warning'
                );
                etsSEO.setReadabilityScore('flesch_reading_ease', id_lang, 6);
            } else {
                if (ETS_SEO_DEFINED.readability_rules.flesch_reading_ease.success.short_code['[score]']) {
                    ETS_SEO_DEFINED.readability_rules.flesch_reading_ease.success.short_code['[score]'].number = score;
                }
                etsSEO.getAnalysisMessage(
                    '#analysis-result--list-readablity-' + id_lang,
                    'flesch_reading_ease',
                    ETS_SEO_DEFINED.readability_rules.flesch_reading_ease.success,
                    'success'
                );
                etsSEO.setReadabilityScore('flesch_reading_ease', id_lang, 9);
            }
        },

        paragraphLength: function (id_lang, content) {
            content = content || '';
            if (!content) {
                etsSEO.setReadabilityScore('paragraph_length', id_lang, 0);
                etsSEO.removeAnalysisMessage('#analysis-result--list-readablity-' + id_lang,
                    'paragraph_length');
                return;
            }
            //Each paragraph should not over 150 words
            var tmp = document.createElement('div');
            tmp.innerHTML = content;
            var contentHTML = tmp.getElementsByTagName('p');
            var paragraph_over_150_words = 0;
            var paragraph_over_200_words = 0;
            for (var i = 0; i < contentHTML.length; i++) {
                var text_content_length = contentHTML[i].textContent.split(/\s+/).length;
                if (text_content_length > 150) {
                    paragraph_over_150_words++;
                    if (text_content_length > 200) {
                        paragraph_over_200_words++;
                    }
                }
            }
            if (paragraph_over_200_words) {
                if (ETS_SEO_DEFINED.readability_rules.paragraph_length.error.short_code['[number]'])
                    ETS_SEO_DEFINED.readability_rules.paragraph_length.error.short_code['[number]'].number = paragraph_over_150_words;

                etsSEO.getAnalysisMessage(
                    '#analysis-result--list-readablity-' + id_lang,
                    'paragraph_length',
                    ETS_SEO_DEFINED.readability_rules.paragraph_length.error,
                    'error'
                );

                etsSEO.setReadabilityScore('paragraph_length', id_lang, 3);
            } else if (paragraph_over_150_words) {
                if (ETS_SEO_DEFINED.readability_rules.paragraph_length.warning.short_code['[number]'])
                    ETS_SEO_DEFINED.readability_rules.paragraph_length.warning.short_code['[number]'].number = paragraph_over_150_words;

                etsSEO.getAnalysisMessage(
                    '#analysis-result--list-readablity-' + id_lang,
                    'paragraph_length',
                    ETS_SEO_DEFINED.readability_rules.paragraph_length.warning,
                    'warning'
                );
                etsSEO.setReadabilityScore('paragraph_length', id_lang, 6);
            } else {
                etsSEO.getAnalysisMessage(
                    '#analysis-result--list-readablity-' + id_lang,
                    'paragraph_length',
                    ETS_SEO_DEFINED.readability_rules.paragraph_length.success,
                    'success'
                );
                etsSEO.setReadabilityScore('paragraph_length', id_lang, 9);
            }
        },

        consecutiveSentences: function (id_lang, content) {
            content = content || '';
            if (!content) {
                etsSEO.setReadabilityScore('consecutive_sentences', id_lang, 0);
                etsSEO.removeAnalysisMessage('#analysis-result--list-readablity-' + id_lang,
                    'consecutive_sentences');
                return;
            }

            //If The text contains >=3 consecutive sentences starting with the same word, it not good
            var sentences = content.split(/[.!?][ |\n]/g);
            var first_word_of_sentence = [];
            var firstWordOfSentence = '';
            var countWord = 0;
            var consecutive = [];

            for (var i = 0; i < sentences.length; i++) {
                if (sentences[i]) {
                    var first_word = sentences[i].replace(/\r|\n/, '').trim().split(/\s+/)[0];
                    var parttent = /[a-zA-Z0-9]/;
                    if (!parttent.test(first_word)) {
                        continue;
                    }
                    first_word = first_word.toLowerCase();
                    if (firstWordOfSentence && firstWordOfSentence == first_word) {
                        countWord++;
                    } else {
                        firstWordOfSentence = first_word;
                        countWord = 1;
                    }

                    if (countWord >= 3 && consecutive.indexOf(first_word) == -1) {
                        consecutive.push(first_word);
                    }

                }

            }

            if (consecutive.length) {
                if (ETS_SEO_DEFINED.readability_rules.consecutive_sentences.error.short_code['[number]'])
                    ETS_SEO_DEFINED.readability_rules.consecutive_sentences.error.short_code['[number]'].number = consecutive.length;
                etsSEO.getAnalysisMessage(
                    '#analysis-result--list-readablity-' + id_lang,
                    'consecutive_sentences',
                    ETS_SEO_DEFINED.readability_rules.consecutive_sentences.error,
                    'error'
                );

                etsSEO.setReadabilityScore('consecutive_sentences', id_lang, 3);
            } else {
                etsSEO.getAnalysisMessage(
                    '#analysis-result--list-readablity-' + id_lang,
                    'consecutive_sentences',
                    ETS_SEO_DEFINED.readability_rules.consecutive_sentences.success,
                    'success'
                );
                etsSEO.setReadabilityScore('consecutive_sentences', id_lang, 9);
            }
        },

        subheadingDistribution: function (id_lang, content) {
            content = content || '';
            if (!content) {
                etsSEO.setReadabilityScore('subheading_distribution', id_lang, 0);
                etsSEO.removeAnalysisMessage('#analysis-result--list-readablity-' + id_lang,
                    'subheading_distribution');
                return;
            }
            var tmp = document.createElement('div');
            tmp.innerHTML = content;

            var has_heading = false;
            for (var i = 1; i <= 6; i++) {
                var heading = tmp.getElementsByTagName('h' + i);
                if (heading.length) {
                    has_heading = true;
                    break;
                }
            }

            if (has_heading) {
                etsSEO.getAnalysisMessage(
                    '#analysis-result--list-readablity-' + id_lang,
                    'subheading_distribution',
                    ETS_SEO_DEFINED.readability_rules.subheading_distribution.good,
                    'success'
                );
                etsSEO.setReadabilityScore('subheading_distribution', id_lang, 9);
            } else {
                etsSEO.getAnalysisMessage(
                    '#analysis-result--list-readablity-' + id_lang,
                    'subheading_distribution',
                    ETS_SEO_DEFINED.readability_rules.subheading_distribution.success,
                    'success'
                );

                etsSEO.setReadabilityScore('subheading_distribution', id_lang, 9);
            }
        },

        transitionWords: function (id_lang, content) {
            content = content || '';
            if (!content) {
                etsSEO.setReadabilityScore('transition_words', id_lang, 0);
                etsSEO.removeAnalysisMessage('#analysis-result--list-readablity-' + id_lang,
                    'transition_words');
                return;
            }
            var iso_code = null;
            Object.keys(ETS_SEO_LANGUAGES).forEach(function (key) {
                if (ETS_SEO_LANGUAGES[key] == id_lang) {
                    iso_code = key;
                }
            });
            if (iso_code !== 'en') {
                etsSEO.getAnalysisMessage(
                    '#analysis-result--list-readablity-' + id_lang,
                    'transition_words',
                    ETS_SEO_DEFINED.readability_rules.transition_words.success,
                    'success'
                );
                etsSEO.setReadabilityScore('transition_words', id_lang, 9);
                return;
            }

            var has_transition_word = [];
            var sentences = content.split(/[.!?][ |\n]/g);

            Object.keys(ETS_SEO_DEFINED.transition_words[iso_code]).forEach(function (key) {
                var words = ETS_SEO_DEFINED.transition_words[iso_code][key].split(',');
                for (var t = 0; t < words.length; t++) {
                    var myPattern = new RegExp('(?:^|\\s)' + words[t].trim() + '(?:$|\\s|[^\\d\\W])', 'gi');
                    for (var k = 0; k < sentences.length; k++) {
                        if (sentences[k].toLowerCase().match(myPattern) !== null) {

                            if (has_transition_word.indexOf(k) == -1) {
                                has_transition_word.push(k);
                            }
                        }
                    }
                }

            });

            var percent_match = has_transition_word.length / sentences.length * 100;

            if (percent_match >= 30) {
                etsSEO.getAnalysisMessage(
                    '#analysis-result--list-readablity-' + id_lang,
                    'transition_words',
                    ETS_SEO_DEFINED.readability_rules.transition_words.success,
                    'success'
                );

                etsSEO.setReadabilityScore('transition_words', id_lang, 9);
            } else if (percent_match >= 20 && percent_match <= 30) {
                if (ETS_SEO_DEFINED.readability_rules.transition_words.little.short_code['[count]'])
                    ETS_SEO_DEFINED.readability_rules.transition_words.little.short_code['[count]'].number = has_transition_word.length;
                etsSEO.getAnalysisMessage(
                    '#analysis-result--list-readablity-' + id_lang,
                    'transition_words',
                    ETS_SEO_DEFINED.readability_rules.transition_words.little,
                    'warning'
                );
                etsSEO.setReadabilityScore('transition_words', id_lang, 6);
            } else if (percent_match > 0) {
                if (ETS_SEO_DEFINED.readability_rules.transition_words.too_little.short_code['[count]'])
                    ETS_SEO_DEFINED.readability_rules.transition_words.too_little.short_code['[count]'].number = has_transition_word.length;
                etsSEO.getAnalysisMessage(
                    '#analysis-result--list-readablity-' + id_lang,
                    'transition_words',
                    ETS_SEO_DEFINED.readability_rules.transition_words.too_little,
                    'error'
                );
                etsSEO.setReadabilityScore('transition_words', id_lang, 3);
            } else {
                etsSEO.getAnalysisMessage(
                    '#analysis-result--list-readablity-' + id_lang,
                    'transition_words',
                    ETS_SEO_DEFINED.readability_rules.transition_words.error,
                    'error'
                );
                etsSEO.setReadabilityScore('transition_words', id_lang, 0);
            }

        },

        passive_voice: function (id_lang, content) {
            content = content || '';
            if (!content) {
                etsSEO.setReadabilityScore('passive_voice', id_lang, 0);
                etsSEO.removeAnalysisMessage('#analysis-result--list-readablity-' + id_lang,
                    'passive_voice');
                return;
            }
            var iso_code = null;
            Object.keys(ETS_SEO_LANGUAGES).forEach(function (key) {
                if (ETS_SEO_LANGUAGES[key] == id_lang) {
                    iso_code = key;
                }
            });
            if (iso_code !== 'en') {
                return;
            }
            var sentences = content.split(/[.!?][ |\n]/g).length;
            var passive_voice = 0;
            for (var k = 0; k < sentences.length; k++) {
                var matched = content.match(/\b((be(en)?)|(w(as|ere))|(is)|(a(er|m)))(.+(en|ed))([\s]|\.)/g);
                if (matched !== null) {
                    passive_voice++;
                }
            }

            var percent_match = passive_voice / sentences.length * 100;
            if (percent_match > 15) {
                if (ETS_SEO_DEFINED.readability_rules.passive_voice.error.short_code['[number]']) {
                    ETS_SEO_DEFINED.readability_rules.passive_voice.error.short_code['[number]'].number = Math.round(percent_match);
                }
                etsSEO.getAnalysisMessage(
                    '#analysis-result--list-readablity-' + id_lang,
                    'passive_voice',
                    ETS_SEO_DEFINED.readability_rules.passive_voice.error,
                    'success'
                );
                etsSEO.setReadabilityScore('passive_voice', id_lang, 3);

            } else if (percent_match <= 15 && percent_match >= 10) {
                if (ETS_SEO_DEFINED.readability_rules.passive_voice.error.short_code['[number]']) {
                    ETS_SEO_DEFINED.readability_rules.passive_voice.error.short_code['[number]'].number = Math.round(percent_match);
                }
                etsSEO.getAnalysisMessage(
                    '#analysis-result--list-readablity-' + id_lang,
                    'passive_voice',
                    ETS_SEO_DEFINED.readability_rules.passive_voice.error,
                    'warning'
                );
                etsSEO.setReadabilityScore('passive_voice', id_lang, 6);
            } else {
                etsSEO.getAnalysisMessage(
                    '#analysis-result--list-readablity-' + id_lang,
                    'passive_voice',
                    ETS_SEO_DEFINED.readability_rules.passive_voice.success,
                    'success'
                );
                etsSEO.setReadabilityScore('passive_voice', id_lang, 9);
            }

        }

    },

    fleschReadingEase: {
        score: function (text) {
            return {
                score: 206.835 - (1.015 * etsSEO.fleschReadingEase.avgWordsSentance(text)) - (84.6 * etsSEO.fleschReadingEase.avgSyllablesWord(text)),
                gradingLevel: etsSEO.fleschReadingEase.gradingLevel(text)
            };
        },

        gradingLevel: function (text) {
            return ((.39 * etsSEO.fleschReadingEase.avgWordsSentance(text)) + (11.8 * etsSEO.fleschReadingEase.avgSyllablesWord(text)) - 15.59);
        },

        avgWordsSentance: function (text) {
            var sentences = text.split(/[.!?][ |\n]/g).length;
            var words = text.split(/ /g).length;
            return words / sentences;
        },

        avgSyllablesWord: function (text) {
            var words = text.split(/ /g);
            var syllables = 0;
            for (var i = 0; i < words.length; i++) {
                syllables = syllables + etsSEO.fleschReadingEase.countSyllables(words[i]);
            }

            return syllables / words.length;
        },

        countSyllables: function (word) {
            word = word.toLowerCase();
            if (word.length <= 3) {
                return 1;
            }

            word = word.replace(/(?:[^laeiouy]es|ed|[^laeiouy]e)$/, '');
            word = word.replace(/^y/, '');
            word = word.replace(/-/g, '');

            if (word.match(/[aeiouy]{1,2}/g))
                return word.match(/[aeiouy]{1,2}/g).length;

            return 1;
        }
    },

    getFirstParagraph: function (text) {
        var tmp = document.createElement('div');
        tmp.innerHTML = text;
        var contentHTML = tmp.getElementsByTagName('p');
        if (contentHTML.length) {
            return contentHTML[0].textContent;
        }
        return '';
    },

    //Get result analysis message
    getAnalysisMessage: function (prefix_el_to_append, rule, message, type) {

        if (ETS_SEO_CONTROLLER == 'AdminCmsContent' || ETS_SEO_CONTROLLER == 'AdminMeta') {
            prefix_el_to_append = prefix_el_to_append.replace('seo-step-', '');
        }
        var text = message.text;
        if (message.short_code) {
            var textTitle = '';
            Object.keys(message.short_code).forEach(function (key) {
                if (message.short_code[key].type == 'link') {
                    if (key == '[link_support]') {
                        textTitle = message.short_code[key].text;
                    }
                    if (key == '[link_doc]') {
                        text = text.replace(new RegExp(etsSEO.escapeRegExp(key), 'gi'), '<span class="analysis-text-action">' + message.short_code[key].text + '</span>');
                    } else {
                        var dataRule = rule;

                        text = text.replace(new RegExp(etsSEO.escapeRegExp(key), 'gi'), '<a href="#" class="js-ets-seo-show-explain-rule" data-rule="' + dataRule + '" data-text="' + textTitle + '"><span class="ets-seo-link-explain-rule">' + message.short_code[key].text + '</span></a>');
                    }
                } else if (message.short_code[key].type == 'number') {
                    text = text.replace(new RegExp(etsSEO.escapeRegExp(key), 'gi'), '<span class="number">' + message.short_code[key].number + '</span>');
                } else if (message.short_code[key].type == 'string') {
                    text = text.replace(new RegExp(etsSEO.escapeRegExp(key), 'gi'), '<span class="string">' + message.short_code[key].string + '</span>');
                }

            });
        }

        etsSEO.hideAnalysisMessage(prefix_el_to_append + '-error', rule);
        etsSEO.hideAnalysisMessage(prefix_el_to_append + '-warning', rule);
        etsSEO.hideAnalysisMessage(prefix_el_to_append + '-success', rule);
        if (type) {
            $(prefix_el_to_append + '-' + type).append('<li class="' + rule + '">' + text + '</li>');
            if ($(prefix_el_to_append + '-' + type).parent().hasClass('hide')) {
                $(prefix_el_to_append + '-' + type).parent().removeClass('hide');
            }

            var langMatches = prefix_el_to_append.match(/\d+$/);
            if(langMatches.length && typeof  langMatches[0] !== 'undefined'){
                var id_lang = langMatches[0];
                if(typeof etsSEO.content_analysis[id_lang] === 'undefined') {
                    etsSEO.content_analysis[id_lang] = {};
                }
                etsSEO.content_analysis[id_lang][rule] = {el:prefix_el_to_append, type: type, text: text};
            }

        }
    },

    removeAnalysisMessage: function (prefix_el_to_append, rule) {
        etsSEO.hideAnalysisMessage(prefix_el_to_append + '-error', rule);
        etsSEO.hideAnalysisMessage(prefix_el_to_append + '-warning', rule);
        etsSEO.hideAnalysisMessage(prefix_el_to_append + '-success', rule);
        var langMatches = prefix_el_to_append.match(/\d+$/);
        if(langMatches.length && typeof  langMatches[0] !== 'undefined'){
            var id_lang = langMatches[0];
            if(typeof etsSEO.content_analysis[id_lang] !== 'undefined' && typeof etsSEO.content_analysis[id_lang][rule] !== 'undefined') {
                etsSEO.content_analysis[id_lang][rule] = null;
            }
        }
    },

    hideAnalysisMessage: function (list, rule) {
        $(list).find('li.' + rule).remove();
        if (!$(list).find('li').length) {
            $(list).parent().addClass('hide');
        }
    },

    getTextWidth: function (text) {
        var canvas = document.createElement("canvas");
        var context = canvas.getContext("2d");
        context.font = '20px arial';
        var metrics = context.measureText(text);
        return metrics.width;
    },

    initTabSeo: function (id_lang, runAnalysis) {

        if(ETS_SEO_ENABLE_AUTO_ANALYSIS && runAnalysis){
            var prefix = etsSEO.prefixInput();
            var content = '';
            if (prefix.short_desc && $(prefix.short_desc + id_lang).length) {
                content += $(prefix.short_desc + id_lang).val();
            }
            if (prefix.content && prefix.short_desc != prefix.content && $(prefix.content + id_lang).length) {
                content += $(prefix.content + id_lang).val();
            }
            etsSEO.analysisContent(id_lang, content);
            etsSEO.showSuccessMessageAnalysis();
        }
        else{
            if(ETS_SEO_SCORE_DATA){
                //
                var scoreAnalysis = {};
                if(typeof ETS_SEO_SCORE_DATA[id_lang] !== 'undefined' && ETS_SEO_SCORE_DATA[id_lang].score_analysis)
                    scoreAnalysis = JSON.parse(ETS_SEO_SCORE_DATA[id_lang].score_analysis);
                var seoScore = scoreAnalysis.seo_score || {};
                var readabilityScore = scoreAnalysis.readability_score || {};
                Object.keys(etsSEO.seo_score).forEach(function (key) {
                    etsSEO.seo_score[key][id_lang] = seoScore[key] || 0;
                });
                Object.keys(etsSEO.readability_score).forEach(function (key) {
                    etsSEO.readability_score[key][id_lang] = readabilityScore[key] || 0;
                });
                if(typeof ETS_SEO_SCORE_DATA[id_lang] !== 'undefined' && ETS_SEO_SCORE_DATA[id_lang].content_analysis){
                    var contentAnalysis = ETS_SEO_SCORE_DATA[id_lang].content_analysis;
                    if(typeof ETS_SEO_SCORE_DATA[id_lang].content_analysis == 'string'){
                        contentAnalysis = JSON.parse(ETS_SEO_SCORE_DATA[id_lang].content_analysis);
                    }
                    if(contentAnalysis){

                        Object.keys(contentAnalysis).forEach(function (key) {
                            if(contentAnalysis[key])
                                $(contentAnalysis[key].el+'-'+contentAnalysis[key].type).append('<li class="'+key+'">'+contentAnalysis[key].text+'</li>');
                        });
                    }

                }
            }
        }
    },

    changePreview: function (id_lang) {

        var prefix = etsSEO.prefixInput();
        var meta_title = $(prefix.meta_title + id_lang).val();

        if (!meta_title || ETS_SEO_FORCE_USE_META_TEMPLATE) {
            meta_title = etsSEO.getMetaTitle(id_lang);
        }
        if (!meta_title) {
            if ($(prefix.title + id_lang).length) {
                meta_title = $(prefix.title + id_lang).val();
            } else if ($(prefix.title.replace(/^_+|_+$/, '')).length) {
                meta_title = $(prefix.title.replace(/^_+|_+$/, '')).val();
            } else if ($('#form_step1_name_' + id_lang).length) {
                meta_title = $('#form_step1_name_' + id_lang).val();
            }
            if (ETS_SEO_CONTROLLER == 'AdminSuppliers' || ETS_SEO_CONTROLLER == 'AdminManufacturers') {
                if ($('#manufacturer_name').length) {
                    meta_title = $('#manufacturer_name').val();
                } else {
                    meta_title = $('#name').val();
                }
            }
        }
        var meta_desc = $(prefix.meta_desc + id_lang).val();

        if (!meta_desc || ETS_SEO_FORCE_USE_META_TEMPLATE) {
            meta_desc = etsSEO.getMetaDesc(id_lang);
        }
        if (!meta_desc) {
            if ($(prefix.short_desc + id_lang).length) {
                meta_desc = $(prefix.short_desc + id_lang).val();
            } else if ($('#form_step1_description_short_' + id_lang).length) {
                meta_desc = $('#form_step1_description_short_' + id_lang).val();
            }
            if (!meta_desc && ETS_SEO_CONTROLLER == 'AdminManufacturers') {
                meta_desc = $(prefix.content + id_lang).val();
            }
        }
        var link_rewrite = $(prefix.link_rewrite + id_lang).val() || '';
        var key_phrase = $('.input-key-phrase-il-' + id_lang).first().val() || '';

        var page_title = '';
        if (prefix.title && prefix.title != prefix.meta_title) {

            if ($(prefix.title + id_lang).length) {
                page_title = $(prefix.title + id_lang).val();
            } else if ($(prefix.title.replace(/^_+|_+$/, '')).length) {
                page_title = $(prefix.title.replace(/^_+|_+$/, '')).val();
            }

            if (!page_title) {
                page_title = '';
            }
        }
        if (ETS_SEO_CONTROLLER == 'AdminMeta') {
            page_title = $(prefix.meta_title + id_lang).val();
        }
        var price = '';
        var description = '';
        var description2 = '';
        var category = '';
        var brand = '';
        var discount_price = '';
        var priceNb = 0;
        if (prefix.price) {
            price = $(prefix.price).val() ? parseFloat($(prefix.price).val()) : 0;
            if ($('#combinations').length) {
                $('.attribute-default').each(function () {
                    if ($(this).is(':checked')) {
                        var id_combination = $(this).attr('data-id');
                        var impact_price = $('#attribute_' + id_combination + ' input.attribute_priceTE').val();
                        price = impact_price ? price + parseFloat(impact_price) : price;
                    }
                });
            }
            priceNb = price;
            formatCurrencyCldr(price, function (v) {
                price = v;
            });
        }

        if (prefix.short_desc) {
            description = $(prefix.short_desc + id_lang).val();
        }
        if (prefix.content) {
            description2 = $(prefix.content + id_lang).val();
        }
        if (prefix.category) {
            if ($(prefix.category + ':checked').length) {
                var listCategories = [];
                $(prefix.category + ':checked').each(function () {
                    listCategories.push($(this).parent('label').text());
                });
                category = listCategories.toString();
            } else if ($(prefix.category + ' option:selected').length) {
                var listCategories = [];
                $(prefix.category + ' option:selected').each(function () {
                    listCategories.push($(this).text());
                });
                category = listCategories.toString();
            }

        }
        if(prefix.brand){
            brand = $(prefix.brand).find('option:selected').text();
        }
        if(prefix.discount_price){
            var discountText = $(prefix.discount_price).find('tbody tr:first-child>td:nth-child(8)').text();

            if(discountText.indexOf(currency.sign) !== -1){
                var matchAmount = discountText.match(/[0-9\.]+/);

                if(matchAmount && priceNb){
                    var afterDiscount = parseFloat(priceNb+'') - parseFloat(matchAmount[0]) ;

                    formatCurrencyCldr(afterDiscount, function (v) {
                        discount_price = v;
                    });
                }
            }
            else{
                var matchPercent = discountText.match(/[0-9\.]+/);
                if(matchPercent && priceNb){
                    var afterDiscount = parseFloat(priceNb+'') - (parseFloat(priceNb+'') * parseFloat(matchPercent[0])/100);

                    formatCurrencyCldr(afterDiscount, function (v) {
                        discount_price = v;
                    });
                }
            }
        }
        if(!discount_price){
            discount_price = price;
        }

        if (meta_title) {
            meta_title = etsSEO.getSeoMetaData(meta_title, true, {name: page_title, price: price, category: category, brand: brand,discount_price:discount_price});
        }
        if (meta_desc) {
            meta_desc = etsSEO.getSeoMetaData(meta_desc, false, {
                name: page_title,
                price: price,
                description: description,
                description2: description2,
                category: category,
                brand: brand,
                discount_price: discount_price
            });
        }
        var textTitle = meta_title ? meta_title.replace(/<\/?[a-z][^>]*?>/gi, " ") : '';
        textTitle = textTitle.replace(/\r\n/gi, "\n");
        textTitle = textTitle.replace(/\n/gi, ' ');
        textTitle = textTitle.replace(/\s\s+/gi, ' ').trim();
        if (textTitle && textTitle.length > 60) {
            meta_title = textTitle.substring(0, 60) + '...';
        }

        var textDesc = meta_desc ? meta_desc.replace(/<\/?[a-z][^>]*?>/gi, " ") : '';
        textDesc = textDesc.replace(/\r\n/gi, "\n");
        textDesc = textDesc.replace(/\n/gi, ' ');
        textDesc = textDesc.replace(/\s\s+/gi, ' ').trim();
        if (textDesc.length > 160) {
            meta_desc = textDesc.substring(0, 160) + '...';
        }

        if (meta_desc) {
            meta_desc = meta_desc.replace(/<\/?[a-z][^>]*?>/gi, " ").replace(new RegExp(etsSEO.escapeRegExp(key_phrase.toLowerCase()), 'gi'), '<strong>$&</strong>');
        }

        $('#ets-seo-snippet-preview-' + id_lang + ' .snippet-preview--title>.text').html(meta_title);
        if (typeof PS_ALLOW_ACCENTED_CHARS_URL === 'undefined') {
            PS_ALLOW_ACCENTED_CHARS_URL = false;
        }
        if (link_rewrite) {
            $('#ets-seo-snippet-preview-' + id_lang + ' .snippet-preview--baseurl>.text>.slug').html(str2url(link_rewrite, 'UTF-8', 0));
        }

        $('#ets-seo-snippet-preview-' + id_lang + ' .snippet-preview--desc>.text').html(meta_desc);
    },

    getSeoMetaData: function (str, is_title, param) {
        if (typeof ETS_SEO_META_CODES !== 'undefined') {
            var meta_codes = is_title ? ETS_SEO_META_CODES.title : ETS_SEO_META_CODES.desc;

            Object.keys(meta_codes).forEach(function (key) {
                var value = meta_codes[key].value || '';
                if (typeof meta_codes[key].type !== 'undefined' && meta_codes[key].type == "title" && typeof param.name !== 'undefined') {
                    value = param.name;
                } else if (typeof meta_codes[key].type !== 'undefined' && meta_codes[key].type == "price" && typeof param.price !== 'undefined') {
                    value = param.price;
                } else if (typeof meta_codes[key].type !== 'undefined' && meta_codes[key].type == "category" && typeof param.category !== 'undefined') {
                    value = param.category;
                } else if (typeof meta_codes[key].type !== 'undefined' && meta_codes[key].type == "desc" && typeof param.description !== 'undefined') {
                    value = param.description;
                } else if (typeof meta_codes[key].type !== 'undefined' && meta_codes[key].type == "desc2" && typeof param.description2 !== 'undefined') {
                    value = param.description2;
                }else if (typeof meta_codes[key].type !== 'undefined' && meta_codes[key].type == "brand" && typeof param.brand !== 'undefined') {
                    value = param.brand;
                }
                else if (typeof meta_codes[key].type !== 'undefined' && meta_codes[key].type == "discount_price" && typeof param.discount_price !== 'undefined') {
                    value = param.discount_price || '';
                }

                if(value){
                    value = value.toString();

                    value = value.replace(/<\/?[a-z][^>]*?>/gi, "");

                    value = value.replace(/\r\n/gi, "\n").trim("\n").trim();
                    str = str.replace(new RegExp(key, 'gi'), value);
                }
                else{
                    str = str.replace(new RegExp(key, 'gi'), '');
                }

            });
        }
        return str;
    },

    getIdLang: function (id_input) {
        var arrayIdInput = id_input.split('_');
        return arrayIdInput[arrayIdInput.length - 1];
    },

    initSaveScore: function () {
        if (!ETS_SEO_DEFINED.id_current_page) {
            return;
        }
        var scores = JSON.parse($('#ets_seo_score_data').val());
        /**/
        var totalScore = 0;
        Object.keys(scores.seo_score).forEach(function(key){
            totalScore += scores.seo_score[key]['1'];
        });
        var contentAnalysis = $('#ets_seo_content_analysis').val();
        if(contentAnalysis){
            contentAnalysis = JSON.parse(contentAnalysis);
        }
        var data = {
            etsSeoSaveScore: 1,
            id: ETS_SEO_DEFINED.id_current_page,
            page_type: ETS_SEO_CONTROLLER,
            readability_score: scores.readability_score,
            seo_score: scores.seo_score,
            content_analysis: contentAnalysis,
            is_cms_category: ETS_SEO_IS_CMS_CATEGORY
        };
        $.ajax({
            url: ETS_SEO_LINK_AJAX_BO,
            type: 'POST',
            dataType: 'json',
            data: data,
            success: function (res) {
                //console.log(res);
            }
        })
    },
    renderMetaData: function (text, id_lang, is_title) {

        var prefix = etsSEO.prefixInput();
        var page_title = '';
        if (prefix.title && prefix.title != prefix.meta_title) {

            if ($(prefix.title + id_lang).length) {
                page_title = $(prefix.title + id_lang).val();
            } else if ($(prefix.title.replace(/^_+|_+$/, '')).length) {
                page_title = $(prefix.title.replace(/^_+|_+$/, '')).val();
            }

            if (!page_title) {
                page_title = '';
            }
        }
        if (ETS_SEO_CONTROLLER == 'AdminMeta') {
            page_title = $(prefix.meta_title + id_lang).val();
        }
        var price = '';
        var description = '';
        var description2 = '';
        var category = '';
        var discount_price = '';
        var priceNb = 0;
        var brand = '';
        if (prefix.price) {
            price = $(prefix.price).val() ? parseFloat($(prefix.price).val()) : 0;
            if ($('#combinations').length) {
                $('.attribute-default').each(function () {
                    if ($(this).is(':checked')) {
                        var id_combination = $(this).attr('data-id');
                        var impact_price = $('#attribute_' + id_combination + ' input.attribute_priceTE').val();
                        price = impact_price ? price + parseFloat(impact_price) : price;
                    }
                });
            }
            priceNb = price;
            formatCurrencyCldr(price, function (v) {
                price = v;
            });
        }
        if (prefix.short_desc) {
            description = $(prefix.short_desc + id_lang).val();
        }
        if (prefix.content) {
            description2 = $(prefix.content + id_lang).val();
        }
        if (prefix.category) {
            if ($(prefix.category + ':checked').length) {
                var listCategories = [];
                $(prefix.category + ':checked').each(function () {
                    listCategories.push($(this).parent('label').text());
                });
                category = listCategories.toString();
            }
        }
        if(prefix.discount_price){
            var discountText = $(prefix.discount_price).find('tbody tr:first-child>td:nth-child(8)').text();

            if(discountText.indexOf(currency.sign) !== -1){
                var matchAmount = discountText.match(/[0-9\.]+/);

                if(matchAmount && priceNb){
                    var afterDiscount = parseFloat(priceNb+'') - parseFloat(matchAmount[0]) ;

                    formatCurrencyCldr(afterDiscount, function (v) {
                        discount_price = v;
                    });
                }
            }
            else{
                var matchPercent = discountText.match(/[0-9\.]+/);
                if(matchPercent && priceNb){
                    var afterDiscount = parseFloat(priceNb+'') - (parseFloat(priceNb+'') * parseFloat(matchPercent[0])/100);

                    formatCurrencyCldr(afterDiscount, function (v) {
                        discount_price = v;
                    });
                }
            }
        }
        if(prefix.brand){
            brand = $(prefix.brand).find('option:selected').text();
        }
        if(!discount_price){
            discount_price = price;
        }

        if (text) {
            text = etsSEO.getSeoMetaData(text, is_title, {
                name: page_title,
                price: price,
                category: category,
                description: description,
                description2: description2,
                discount_price: discount_price,
                brand: brand
            });
        }
        return text;
    },
    differenceOf2Arrays: function (array1, array2) {
        return array1.filter(function (obj) {
            return array2.indexOf(obj) == -1;
        });
    },
    getMinorKeyphraseInMetaTileDesc: function (id_lang) {
        var minorKeyphrase = etsSEO.getMinorKeyphrase(id_lang);
        var listMinorSuccess = [];
        if (minorKeyphrase.length > 0) {
            var prefix = etsSEO.prefixInput();
            var title = prefix.title ? (ETS_SEO_CONTROLLER != 'AdminManufacturers' && ETS_SEO_CONTROLLER != 'AdminSuppliers' ? $(prefix.title + id_lang).val() : $(prefix.title.slice(0, -1)).val()) : '';
            var meta_title = prefix.meta_title && $(prefix.meta_title + id_lang).length ? $(prefix.meta_title + id_lang).val() : '';

            $.each(minorKeyphrase, function (i, item) {
                var myPattern = new RegExp('(?:^|\\s)' + etsSEO.escapeRegExp(item) + '(?:$|\\s|[^\\d\\W])', 'gi');
                var matchedTitle = title.match(myPattern);
                var matchedMetaTitle = meta_title.match(myPattern);

                if ((matchedTitle !== null && matchedTitle.length) || (matchedMetaTitle !== null && matchedMetaTitle.length)) {
                    listMinorSuccess.push(item);
                }
            });
        }
        return listMinorSuccess;
    },
    insertAtCaret: function (element, data) {

        var areaId = element.attr('id');
        var text = data;
        var txtarea = element[0]; //document.getElementById(areaId);
        var scrollPos = txtarea.scrollTop;
        var strPos = 0;
        var br = ((txtarea.selectionStart || txtarea.selectionStart == '0') ?
            "ff" : (document.selection ? "ie" : false));
        if (br == "ie") {
            txtarea.focus();
            var range = document.selection.createRange();
            range.moveStart('character', -txtarea.value.length);
            strPos = range.text.length;
        } else if (br == "ff") strPos = txtarea.selectionStart;

        var front = (txtarea.value).substring(0, strPos);
        var back = (txtarea.value).substring(strPos, txtarea.value.length);
        txtarea.value = front + text + back;
        strPos = strPos + text.length;
        if (br == "ie") {
            txtarea.focus();
            var range = document.selection.createRange();
            range.moveStart('character', -txtarea.value.length);
            range.moveStart('character', strPos);
            range.moveEnd('character', 0);
            range.select();
        } else if (br == "ff") {
            txtarea.selectionStart = strPos;
            txtarea.selectionEnd = strPos;
            txtarea.focus();
        }
        txtarea.scrollTop = scrollPos;
    },

    updateSnippet: function (inSnippet) {
        if (!ETS_SEO_LANGUAGES) {
            return;
        }
        Object.keys(ETS_SEO_LANGUAGES).forEach(function (key) {
            var id_lang = ETS_SEO_LANGUAGES[key];
            var input = etsSEO.prefixInput();
            if (inSnippet) {
                $(input.meta_title + id_lang).val($('#ets_seo_meta_title_' + id_lang).val());
                $(input.meta_desc + id_lang).val($('#ets_seo_meta_description_' + id_lang).val());
                $(input.link_rewrite + id_lang).val($('#ets_seo_link_rewrite_' + id_lang).val());
            } else {
                if ($(input.meta_title + id_lang).length) {
                    $('input[id*="ets_seo_meta_title_' + id_lang + '"]').val($(input.meta_title + id_lang).val());
                    if ($('input[id*="ets_seo_meta_title_' + id_lang + '"]').next('.js-text-count').length) {
                        $('input[id*="ets_seo_meta_title_' + id_lang + '"]').next('.js-text-count').find('.js-ets-seo-current-length').html($(input.meta_title + id_lang).val().length);
                    }
                }
                if ($(input.meta_desc + id_lang).length) {
                    $('#ets_seo_meta_description_' + id_lang).val($(input.meta_desc + id_lang).val());
                    if ($('textarea[id*="ets_seo_meta_description_' + id_lang + '"]').next('.js-text-count').length) {
                        $('textarea[id*="ets_seo_meta_description_' + id_lang + '"]').next('.js-text-count').find('.js-ets-seo-current-length').html($(input.meta_desc + id_lang).val().length);
                    }
                }
                if ($(input.link_rewrite + id_lang).length) {
                    $('#ets_seo_link_rewrite_' + id_lang).val($(input.link_rewrite + id_lang).val());
                }
            }
            etsSEO.changePreview(id_lang);
        });
    },
    analysisMinorKeyphrase: function (idLang) {

        var prefix = etsSEO.prefixInput();
        if ((prefix.content && prefix.short_desc) || prefix.meta_title || prefix.meta_desc) {
            if(typeof idLang !== 'undefined' && idLang){
                etsSEO.analysisMinorKeyphraseItem(idLang,prefix);
            }
            else{
                $('input.js-ets-seo-tagify').each(function () {
                    var id_lang = $(this).attr('data-idlang');
                    etsSEO.analysisMinorKeyphraseItem(id_lang,prefix);
                });
            }
        }
    },
    analysisMinorKeyphraseItem: function(id_lang, prefix){

        var minor_keyphrase = etsSEO.getMinorKeyphrase(id_lang);
        var content = '';
        var title = '';
        var desc = '';
        if (prefix.short_desc) {
            content += $(prefix.short_desc + id_lang).length ? $(prefix.short_desc + id_lang).val() : '';
        }
        if (prefix.content) {
            content += $(prefix.content + id_lang).length ? $(prefix.content + id_lang).val() : '';
        }
        if (prefix.meta_title) {
            title = $(prefix.meta_title + id_lang).length ? $(prefix.meta_title + id_lang).val() : '';
        }
        if (prefix.meta_desc) {
            desc = $(prefix.meta_desc + id_lang).length ? $(prefix.meta_desc + id_lang).val() : '';
        }
        var text = content.replace(/<\/?[a-z][^>]*?>/gi, "\n");
        etsSEO.rules.minorKeyphraseLength(id_lang, minor_keyphrase);
        etsSEO.rules.minorKeyphraseInContent(id_lang, minor_keyphrase, text);
        etsSEO.rules.minorKeyphraseInMetaTitle(id_lang, minor_keyphrase, title);
        etsSEO.rules.minorKeyphraseInMetaDesc(id_lang, minor_keyphrase, desc);
    },
    setOpacityPreviewAnalysis: function (type, id_lang, level) {
        for (var i = 1; i <= 5; i++) {
            if (i <= level) {
                $('.js-ets-seo-preview-analysis .' + type + ' .js-ets-seo-processing-lang-' + id_lang + ' .processing .level-' + i).css('opacity', '1');
            } else if (i == level + 1) {
                $('.js-ets-seo-preview-analysis .' + type + ' .js-ets-seo-processing-lang-' + id_lang + ' .processing .level-' + i).css('opacity', '0.6');
            } else {
                $('.js-ets-seo-preview-analysis .' + type + ' .js-ets-seo-processing-lang-' + id_lang + ' .processing .level-' + i).css('opacity', '0.3');
            }

        }
    },

    setDataPreviewAnalysis: function (type, id_lang, seo_score, isIndex, keyPhrase, minorKeyphrase) {
        //Set color
        var setup = false;
        var textStatus = '';
        var classStatus = '';
        var overallScore = 0;
        if (type == 'seo-processing') {
            if (!isIndex) {
                setup = true;
                classStatus = 'grey-noindex';
                textStatus = ETS_SEO_MESSAGE.no_index;
            } else if (keyPhrase != '0' && !keyPhrase && !minorKeyphrase.length) {
                setup = true;
                classStatus = 'grey-nokeyphrase';
                textStatus = ETS_SEO_MESSAGE.no_focus_keyphrase;
            }
            overallScore = Math.round(seo_score / (Object.keys(etsSEO.seo_score).length * 9) * 10);
        } else {
            overallScore = Math.round(seo_score / (Object.keys(etsSEO.readability_score).length * 9) * 10);
        }

        if (setup) {
            //Do nothing
        } else if (overallScore <= 4) {
            classStatus = 'red';
            textStatus = ETS_SEO_MESSAGE.not_good;
        } else if (overallScore > 4 && overallScore <= 7) {
            classStatus = 'orange';
            textStatus = ETS_SEO_MESSAGE.acceptance;
        } else {
            classStatus = 'green';
            textStatus = ETS_SEO_MESSAGE.excellent;
        }
        if ($('.js-ets-seo-preview-analysis .' + type + ' .js-ets-seo-processing-lang-' + id_lang + ' .processing').hasClass('excuting')) {
            return;
        }
        if (classStatus) {
            if (type == 'readability-processing' && ETS_SEO_CONTROLLER == 'AdminMeta') {
                $('.js-ets-seo-preview-analysis .' + type + ' .js-ets-seo-processing-lang-' + id_lang + ' .processing').addClass('grey-darken');
            } else {
                $('.js-ets-seo-preview-analysis .' + type + ' .js-ets-seo-processing-lang-' + id_lang + ' .processing').addClass(classStatus);
            }
        } else {
            $('.js-ets-seo-preview-analysis .' + type + ' .js-ets-seo-processing-lang-' + id_lang + ' .processing').addClass('grey-darken');
        }

        if (textStatus) {
            if (type == 'readability-processing' && ETS_SEO_CONTROLLER == 'AdminMeta') {
                $('.js-ets-seo-preview-analysis .' + type + ' .js-ets-seo-processing-lang-' + id_lang + ' .sub-title').html(ETS_SEO_MESSAGE.not_analysis);
            } else {
                $('.js-ets-seo-preview-analysis .' + type + ' .js-ets-seo-processing-lang-' + id_lang + ' .sub-title').html(textStatus);
            }
        } else {
            if (type == 'readability-processing' && ETS_SEO_CONTROLLER == 'AdminMeta') {
                $('.js-ets-seo-preview-analysis .' + type + ' .js-ets-seo-processing-lang-' + id_lang + ' .sub-title').html(ETS_SEO_MESSAGE.not_analysis);
            }
        }

        //Set opacity
        if (overallScore < 2) {
            etsSEO.setOpacityPreviewAnalysis(type, id_lang, 1);
        } else if (overallScore >= 2 && overallScore < 4) {
            etsSEO.setOpacityPreviewAnalysis(type, id_lang, 1);
        } else if (overallScore >= 4 && overallScore < 6) {
            etsSEO.setOpacityPreviewAnalysis(type, id_lang, 2);
        } else if (overallScore >= 6 && overallScore < 8) {
            etsSEO.setOpacityPreviewAnalysis(type, id_lang, 3);
        } else if (overallScore >= 8 && overallScore < 10) {
            etsSEO.setOpacityPreviewAnalysis(type, id_lang, 4);
        } else {
            etsSEO.setOpacityPreviewAnalysis(type, id_lang, 5);
        }
    },
    setPreviewAnalysis: function () {
        var seo_analysis = $('#ets_seo_score_data').val() ? JSON.parse($('#ets_seo_score_data').val()) : null;

        if (!seo_analysis) {
            return;
        }
        $('.js-ets-seo-preview-analysis .processing').removeClass('red');
        $('.js-ets-seo-preview-analysis .processing').removeClass('orange');
        $('.js-ets-seo-preview-analysis .processing').removeClass('green');
        $('.js-ets-seo-preview-analysis .processing').removeClass('grey');
        $('.js-ets-seo-preview-analysis .processing').removeClass('violet');
        $('.js-ets-seo-preview-analysis .processing').removeClass('yellow');
        $('.js-ets-seo-preview-analysis .processing').removeClass('grey-nokeyphrase');
        $('.js-ets-seo-preview-analysis .processing').removeClass('grey-noindex');

        Object.keys(ETS_SEO_LANGUAGES).forEach(function (isocode) {
            var seo_score = 0;
            Object.keys(seo_analysis.seo_score).forEach(function (k) {
                seo_score += parseInt(seo_analysis.seo_score[k][ETS_SEO_LANGUAGES[isocode]]);

            });
            var readability_score = 0;
            Object.keys(seo_analysis.readability_score).forEach(function (k) {
                readability_score += parseInt(seo_analysis.readability_score[k][ETS_SEO_LANGUAGES[isocode]]);
            });
            var indexData = $('#ets_seo_allow_search_engine_show_post-' + ETS_SEO_LANGUAGES[isocode]).val();
            var isIndex = 0;
            if (indexData == '1' || indexData == '0') {
                isIndex = parseInt(indexData);
            } else if (indexData == '2') {
                var configValue = $('#ets_seo_allow_search_engine_show_post-' + ETS_SEO_LANGUAGES[isocode]).attr('data-value');
                if (configValue == '0' || configValue == '1') {
                    isIndex = parseInt(configValue);
                }
            }
            var keyPhrase = '';
            if ($('input[name="ets_seo_key_phrase[' + ETS_SEO_LANGUAGES[isocode] + ']"]').length) {
                keyPhrase = $('input[name="ets_seo_key_phrase[' + ETS_SEO_LANGUAGES[isocode] + ']"]').val();
            }
            var minorKeyPhrase = etsSEO.getMinorKeyphrase(ETS_SEO_LANGUAGES[isocode]);

            etsSEO.setDataPreviewAnalysis('seo-processing', ETS_SEO_LANGUAGES[isocode], seo_score, isIndex, keyPhrase, minorKeyPhrase);
            etsSEO.setDataPreviewAnalysis('readability-processing', ETS_SEO_LANGUAGES[isocode], readability_score, isIndex, keyPhrase, minorKeyPhrase);
        });
    },

    showSuccessMessageAnalysis: function (notSaveScore ) {
        notSaveScore = notSaveScore || false;
        if (etsSEO.showMessageAnalysis) {
            return;
        }
        var show = 0;
        $('textarea[id*="meta_description_"], form input[id*="meta_description_"]').each(function () {
            if ($(this).length) {
                show = 1;
            }
        });
        if (!show) {
            return;
        }
        if(!notSaveScore){
            var intervalInit = setInterval(function () {

                if($('#ets_seo_score_data').val()){
                    clearInterval(intervalInit);
                    etsSEO.initSaveScore();
                }
            }, 200);
        }
        etsSEO.showMessageAnalysis = true;
        $('.js-ets-seo-preview-analysis .processing').removeClass('excuting');
        if(ETS_SEO_DEFINED.id_current_page){
            if(!$('#ets_seo_score_data').val()){
                var intvSeoScore = setInterval(function () {
                    if($('#ets_seo_score_data').val()){
                        etsSEO.setPreviewAnalysis();
                        clearInterval(intvSeoScore);
                    }
                }, 200);
            }
            else{
                etsSEO.setPreviewAnalysis();
            }
        }
        else
            etsSEO.setPreviewAnalysis();
    },
    changePlaceholderMeta: function () {
        if (ETS_SEO_CONTROLLER == 'AdminMeta') {
            return;
        }

        $('input[id*="meta_title_"], input[id*="meta_page_title_"]').each(function () {
            var id = $(this).attr('id');
            if (id.indexOf('ets_seo_social') < 0) {
                var id_lang = etsSEO.getIdLang(id);
                if (ETS_SEO_DEFINED.meta_tamplate_configured[id_lang] && ETS_SEO_DEFINED.meta_tamplate_configured[id_lang].title) {
                    $(this).attr('placeholder', ETS_SEO_MESSAGE.placeholder_meta);
                } else {
                    $(this).attr('placeholder', ETS_SEO_DEFINED.placeholder_meta.title);
                }
            }

        });
        $('textarea[id*="meta_description_"], form input[id*="meta_description_"]').each(function () {
            var id = $(this).attr('id');
            if (id.indexOf('ets_seo_social') < 0) {
                var id_lang = etsSEO.getIdLang(id);
                if (ETS_SEO_DEFINED.meta_tamplate_configured[id_lang] && ETS_SEO_DEFINED.meta_tamplate_configured[id_lang].desc) {
                    $(this).attr('placeholder', ETS_SEO_MESSAGE.placeholder_meta);
                } else {
                    $(this).attr('placeholder', ETS_SEO_DEFINED.placeholder_meta.desc);
                }
            }

        });
    },

    getMetaTitle: function (id_lang, get_origin) {
        var prefix = etsSEO.prefixInput();
        var title = '';
        if (prefix.meta_title && $(prefix.meta_title + id_lang).length && get_origin) {
            title = $(prefix.meta_title + id_lang).val();
        }
        if (!title || ETS_SEO_FORCE_USE_META_TEMPLATE) {
            if ($('#ets_seo_meta_template_title_' + id_lang).length && $('#ets_seo_meta_template_title_' + id_lang).val()) {
                title = $('#ets_seo_meta_template_title_' + id_lang).val();
            }
        }
        if (!title) {
            title = prefix.meta_title ? (ETS_SEO_CONTROLLER != 'AdminManufacturers' && ETS_SEO_CONTROLLER != 'AdminSuppliers' ? $(prefix.meta_title + id_lang).val() : $(prefix.meta_title.slice(0, -1)).val()) : '';

        }
        if (!title) {
            title = prefix.title ? (ETS_SEO_CONTROLLER != 'AdminManufacturers' && ETS_SEO_CONTROLLER != 'AdminSuppliers' ? $(prefix.title + id_lang).val() : $(prefix.title.slice(0, -1)).val()) : '';
        }
        if (title) {
            title = etsSEO.renderMetaData(title, id_lang, true);
        }

        return title;

    },
    getMetaDesc: function (id_lang, get_origin) {
        var prefix = etsSEO.prefixInput();
        var desc = '';
        if (prefix.meta_desc && $(prefix.meta_desc + id_lang).length && get_origin) {
            desc = $(prefix.meta_desc + id_lang).val().replace(/<\/?[a-z][^>]*?>/gi, "\n");
        }
        if(!desc && prefix.meta_desc && $(prefix.meta_desc + id_lang).length){
            desc = $(prefix.meta_desc + id_lang).val().replace(/<\/?[a-z][^>]*?>/gi, "\n");
        }

        if (!desc || ETS_SEO_FORCE_USE_META_TEMPLATE ) {

            if ($('#ets_seo_meta_template_desc_' + id_lang).length && $('#ets_seo_meta_template_desc_' + id_lang).val()) {
                desc = $('#ets_seo_meta_template_desc_' + id_lang).val().replace(/<\/?[a-z][^>]*?>/gi, "\n");
            }
        }
        if (!desc) {
            if (prefix.short_desc && $(prefix.short_desc + id_lang).length) {
                desc = $(prefix.short_desc + id_lang).val().replace(/<\/?[a-z][^>]*?>/gi, "\n");
            }
        }
        if (!desc && (ETS_SEO_CONTROLLER == 'AdminManufacturers' || ETS_SEO_CONTROLLER == 'AdminProducts')) {
            desc = $(prefix.content + id_lang).val();
            if(desc)
                desc = desc.replace(/<\/?[a-z][^>]*?>/gi, " ").replace(/\r\n/gi, '\n').replace(/\n/gi, '').replace(/\s+/gi, ' ').trim().substr(0, 120);
        }
        if (desc) {
            desc = etsSEO.renderMetaData(desc, id_lang, false);
        }

        return desc;
    },
    escapeRegExp: function (text) {
        return text.replace(/[-[\]{}()*+?.,\\^$|#\s]/g, '\\$&').toLowerCase();
    },
    disableMetaInput: function () {
        if(ETS_SEO_CONTROLLER != 'AdminMeta' && ETS_SEO_FORCE_USE_META_TEMPLATE){
            var prefix = etsSEO.prefixInput();
            Object.keys(ETS_SEO_LANGUAGES).forEach(function (key) {
                var id_lang =ETS_SEO_LANGUAGES[key];
                if ($('#ets_seo_meta_template_title_' + id_lang).length && $('#ets_seo_meta_template_title_' + id_lang).val() && prefix.meta_title && $(prefix.meta_title+id_lang).length) {
                    var fakeInputMetaTitle = '<input type="text" readonly="readonly" class="'+$(prefix.meta_title+id_lang).attr('class')+' ets_seo_tmp_input ets_seo_meta_title_tmp_'+id_lang+'" value="'+ $('#ets_seo_meta_template_title_' + id_lang).val()+'" />';
                    $(prefix.meta_title+id_lang).hide();
                    $(prefix.meta_title+id_lang).before(fakeInputMetaTitle);
                    if(ETS_SEO_LANG_ID_ACTIVE == id_lang)
                        $(prefix.meta_title+id_lang).closest('.form-group').addClass('disable_codeseo');
                    var alertTitle = '<div class="alert alert-warning">'+ETS_SEO_MESSAGE.warning_title_use_meta_template+'</div>';
                    if($(prefix.meta_title+id_lang).parent('.translation-field').length){
                        $(prefix.meta_title+id_lang).parent('.translation-field').append(alertTitle);
                    }
                    else if($(prefix.meta_title+id_lang).parent('.js-locale-input').length){
                        $(prefix.meta_title+id_lang).parent('.js-locale-input').parents('.locale-input-group').after(alertTitle);
                    }
                    else if($(prefix.meta_title+id_lang).parent('.col-lg-9').length){
                        $(prefix.meta_title+id_lang).parent('.col-lg-9').append(alertTitle);
                    }

                    $('#ets_seo_meta_title_'+ id_lang).hide();
                    $('#ets_seo_meta_title_'+ id_lang).before(fakeInputMetaTitle);
                    $('#ets_seo_meta_title_'+ id_lang).closest('.form-group').addClass('disable_codeseo');
                }
                if ($('#ets_seo_meta_template_desc_' + id_lang).length && $('#ets_seo_meta_template_desc_' + id_lang).val() && prefix.meta_desc && $(prefix.meta_desc+id_lang).length) {
                    $(prefix.meta_desc+id_lang).hide();
                    var fakeInputMetaDesc = '<textarea readonly="readonly" class="'+$(prefix.meta_desc+id_lang).attr('class')+' ets_seo_tmp_input ets_seo_meta_desc_tmp_'+id_lang+'">'+$('#ets_seo_meta_template_desc_' + id_lang).val()+'</textarea>';
                    $(prefix.meta_desc+id_lang).before(fakeInputMetaDesc);
                    if(ETS_SEO_LANG_ID_ACTIVE == id_lang)
                        $(prefix.meta_desc+id_lang).closest('.form-group').addClass('disable_codeseo');
                    var alertDesc = '<div class="alert alert-warning">'+ETS_SEO_MESSAGE.warning_desc_use_meta_template+'</div>';
                    if($(prefix.meta_desc+id_lang).parent('.translation-field').length){
                        $(prefix.meta_desc+id_lang).parent('.translation-field').append(alertDesc);
                    }
                    else if($(prefix.meta_desc+id_lang).parent('.js-locale-input').length){
                        $(prefix.meta_desc+id_lang).parent('.js-locale-input').parents('.locale-input-group').after(alertDesc);
                    }
                    else if($(prefix.meta_desc+id_lang).parent('.col-lg-9').length){
                        $(prefix.meta_desc+id_lang).parent('.col-lg-9').append(alertDesc);
                    }
                    $('#ets_seo_meta_description_'+ id_lang).hide();
                    $('#ets_seo_meta_description_'+ id_lang).before(fakeInputMetaTitle);
                    $('#ets_seo_meta_description_'+ id_lang).closest('.form-group').addClass('disable_codeseo');
                }
            });
        }
    },
    disableMetaInputs: function(idProduct){
        if(ETS_SEO_CONTROLLER != 'AdminMeta' && ETS_SEO_FORCE_USE_META_TEMPLATE){
            var prefix_meta_title = '#meta_title_'+idProduct+'_';
            var prefix_meta_description = '#meta_description_'+idProduct+'_';
            Object.keys(ETS_SEO_LANGUAGES).forEach(function (key) {
                var id_lang =ETS_SEO_LANGUAGES[key];
                if ($('#ets_pmn_seo_metatitle_' + id_lang).length && $('#ets_pmn_seo_metatitle_' + id_lang).val() && prefix_meta_title && $(prefix_meta_title+id_lang).length) {
                    var fakeInputMetaTitle = '<input type="text" readonly="readonly" class="'+$(prefix_meta_title+id_lang).attr('class')+' ets_seo_tmp_input ets_seo_meta_title_tmp_'+id_lang+'" value="'+ $('#ets_pmn_seo_metatitle_' + id_lang).val()+'" />';
                    $(prefix_meta_title+id_lang).hide();
                    $(prefix_meta_title+id_lang).before(fakeInputMetaTitle);
                    if(ETS_SEO_LANG_ID_ACTIVE == id_lang)
                        $(prefix_meta_title+id_lang).closest('.form-group').addClass('disable_codeseo');
                    var alertTitle = '<div class="alert alert-warning">'+ETS_SEO_MESSAGE.warning_title_use_meta_template+'</div>';
                    if($(prefix_meta_title+id_lang).parent('.translation-field').length){
                        $(prefix_meta_title+id_lang).parent('.translation-field').append(alertTitle);
                    }
                    else if($(prefix_meta_title+id_lang).parent('.js-locale-input').length){
                        $(prefix_meta_title+id_lang).parent('.js-locale-input').parents('.locale-input-group').after(alertTitle);
                    }
                    else if($(prefix_meta_title+id_lang).parent('.col-lg-11').length){
                        $(prefix_meta_title+id_lang).parent('.col-lg-11').append(alertTitle);
                    }
                }
                if ($('#ets_pmn_seo_metadescription_' + id_lang).length && $('#ets_pmn_seo_metadescription_' + id_lang).val() && prefix_meta_description && $(prefix_meta_description+id_lang).length) {
                    $(prefix_meta_description+id_lang).hide();
                    var fakeInputMetaDesc = '<textarea readonly="readonly" class="'+$(prefix_meta_description+id_lang).attr('class')+' ets_seo_tmp_input ets_seo_meta_desc_tmp_'+id_lang+'">'+$('#ets_pmn_seo_metadescription_' + id_lang).val()+'</textarea>';
                    $(prefix_meta_description+id_lang).before(fakeInputMetaDesc);
                    if(ETS_SEO_LANG_ID_ACTIVE == id_lang)
                        $(prefix_meta_description+id_lang).closest('.form-group').addClass('disable_codeseo');
                    var alertDesc = '<div class="alert alert-warning">'+ETS_SEO_MESSAGE.warning_desc_use_meta_template+'</div>';
                    if($(prefix_meta_description+id_lang).parent('.translation-field').length){
                        $(prefix_meta_description+id_lang).parent('.translation-field').append(alertDesc);
                    }
                    else if($(prefix_meta_description+id_lang).parent('.js-locale-input').length){
                        $(prefix_meta_description+id_lang).parent('.js-locale-input').parents('.locale-input-group').after(alertDesc);
                    }
                    else if($(prefix_meta_description+id_lang).parent('.col-lg-11').length){
                        $(prefix_meta_description+id_lang).parent('.col-lg-11').append(alertDesc);
                    }
                }
            });
        }
    },
    disableMetaInputs: function(idProduct){
        if(ETS_SEO_CONTROLLER != 'AdminMeta' && ETS_SEO_FORCE_USE_META_TEMPLATE){
            var prefix_meta_title = '#meta_title_'+idProduct+'_';
            var prefix_meta_description = '#meta_description_'+idProduct+'_';
            Object.keys(ETS_SEO_LANGUAGES).forEach(function (key) {
                var id_lang =ETS_SEO_LANGUAGES[key];
                if ($('#ets_pmn_seo_metatitle_' + id_lang).length && $('#ets_pmn_seo_metatitle_' + id_lang).val() && prefix_meta_title && $(prefix_meta_title+id_lang).length) {
                    var fakeInputMetaTitle = '<input type="text" readonly="readonly" class="'+$(prefix_meta_title+id_lang).attr('class')+' ets_seo_tmp_input ets_seo_meta_title_tmp_'+id_lang+'" value="'+ $('#ets_pmn_seo_metatitle_' + id_lang).val()+'" />';
                    $(prefix_meta_title+id_lang).hide();
                    $(prefix_meta_title+id_lang).before(fakeInputMetaTitle);
                    if(ETS_SEO_LANG_ID_ACTIVE == id_lang)
                        $(prefix_meta_title+id_lang).closest('.form-group').addClass('disable_codeseo');
                    var alertTitle = '<div class="alert alert-warning">'+ETS_SEO_MESSAGE.warning_title_use_meta_template+'</div>';
                    if($(prefix_meta_title+id_lang).parent('.translation-field').length){
                        $(prefix_meta_title+id_lang).parent('.translation-field').append(alertTitle);
                    }
                    else if($(prefix_meta_title+id_lang).parent('.js-locale-input').length){
                        $(prefix_meta_title+id_lang).parent('.js-locale-input').parents('.locale-input-group').after(alertTitle);
                    }
                    else if($(prefix_meta_title+id_lang).parent('.col-lg-11').length){
                        $(prefix_meta_title+id_lang).parent('.col-lg-11').append(alertTitle);
                    }
                }
                if ($('#ets_pmn_seo_metadescription_' + id_lang).length && $('#ets_pmn_seo_metadescription_' + id_lang).val() && prefix_meta_description && $(prefix_meta_description+id_lang).length) {
                    $(prefix_meta_description+id_lang).hide();
                    var fakeInputMetaDesc = '<textarea readonly="readonly" class="'+$(prefix_meta_description+id_lang).attr('class')+' ets_seo_tmp_input ets_seo_meta_desc_tmp_'+id_lang+'">'+$('#ets_pmn_seo_metadescription_' + id_lang).val()+'</textarea>';
                    $(prefix_meta_description+id_lang).before(fakeInputMetaDesc);
                    if(ETS_SEO_LANG_ID_ACTIVE == id_lang)
                        $(prefix_meta_description+id_lang).closest('.form-group').addClass('disable_codeseo');
                    var alertDesc = '<div class="alert alert-warning">'+ETS_SEO_MESSAGE.warning_desc_use_meta_template+'</div>';
                    if($(prefix_meta_description+id_lang).parent('.translation-field').length){
                        $(prefix_meta_description+id_lang).parent('.translation-field').append(alertDesc);
                    }
                    else if($(prefix_meta_description+id_lang).parent('.js-locale-input').length){
                        $(prefix_meta_description+id_lang).parent('.js-locale-input').parents('.locale-input-group').after(alertDesc);
                    }
                    else if($(prefix_meta_description+id_lang).parent('.col-lg-11').length){
                        $(prefix_meta_description+id_lang).parent('.col-lg-11').append(alertDesc);
                    }
                }
            });
        }
    },
    checkInputMetaTemplate: function (id_lang) {
        var prefix = etsSEO.prefixInput();

        if($(prefix.meta_title+id_lang).length){
            if($(prefix.meta_title+id_lang).parent().find('.ets_seo_tmp_input').length)
                $(prefix.meta_title+id_lang).closest('.form-group').addClass('disable_codeseo');
            else
                $(prefix.meta_title+id_lang).closest('.form-group').removeClass('disable_codeseo');
        }
        if($(prefix.meta_desc+id_lang).length){
            if($(prefix.meta_desc+id_lang).parent().find('.ets_seo_tmp_input').length)
                $(prefix.meta_desc+id_lang).closest('.form-group').addClass('disable_codeseo');
            else
                $(prefix.meta_desc+id_lang).closest('.form-group').removeClass('disable_codeseo');
        }
        if($('#ets_seo_meta_title_'+id_lang).length){
            if($('#ets_seo_meta_title_'+id_lang).parent().find('.ets_seo_tmp_input').length)
                $('#ets_seo_meta_title_'+id_lang).closest('.form-group').addClass('disable_codeseo');
            else
                $('#ets_seo_meta_title_'+id_lang).closest('.form-group').removeClass('disable_codeseo');
        }
        if($('#ets_seo_meta_description_'+id_lang).length){
            if($('#ets_seo_meta_description_'+id_lang).parent().find('.ets_seo_tmp_input').length)
                $('#ets_seo_meta_description_'+id_lang).closest('.form-group').addClass('disable_codeseo');
            else
                $('#ets_seo_meta_description_'+id_lang).closest('.form-group').removeClass('disable_codeseo');
        }
    }

};

(function ($) {
    var etsSeoCHeckMCE = 0;
    var etsSeoMCELoaded = 0;
    setTimeout(function () {
        etsSEO.disableMetaInput();
    },300);
    var checkInitTinyMce = setInterval(function () {
        if (typeof tinyMCE !== 'undefined' && tinyMCE.activeEditor && ETS_SEO_ENABLE_AUTO_ANALYSIS) {
            etsSeoMCELoaded++;
            $('textarea.autoload_rte').each(function () {
                var mceId = $(this).attr('id');
                if(typeof tinyMCE.EditorManager.get(mceId) !== 'undefined'){
                    tinyMCE.EditorManager.get(mceId).on('keyup, change', function (e) {
                        tinyMCE.triggerSave();

                        clearTimeout(etsSEO.timeoutTyping);
                        etsSEO.timeoutTyping = setTimeout(function () {
                            var text_content = '';
                            var id_lang = etsSEO.getIdLang(mceId);
                            if (ETS_SEO_CONTROLLER == 'AdminProducts') {
                                var desc = $('#form_step1_description_' + id_lang).val() || '';
                                var desc_short = $('#form_step1_description_short_' + id_lang).val() || '';
                                text_content = desc_short + desc;

                            } else if (ETS_SEO_CONTROLLER == 'AdminManufacturers') {
                                var desc = $(etsSEO.prefixInput().content + id_lang).val() || '';
                                var desc_short = $(etsSEO.prefixInput().short_desc + id_lang).val() || '';

                                text_content = desc_short + desc;

                            } else if (ETS_SEO_CONTROLLER == 'AdminCmsContent' || ETS_SEO_CONTROLLER == 'AdminCategories' || ETS_SEO_CONTROLLER == 'AdminSuppliers') {
                                text_content = $(etsSEO.prefixInput().content + id_lang).val();

                            } else if (ETS_SEO_CONTROLLER == 'AdminMeta') {
                                text_content = '';
                            }

                            etsSEO.analysisContent(
                                id_lang,
                                text_content
                            );
                        }, etsSEO.timeoutKeyup);
                    });
                }
            });

            //Onload
            clearInterval(checkInitTinyMce);

            if (etsSeoMCELoaded == 1) {
                setTimeout(function () {
                    etsSEO.showSuccessMessageAnalysis();
                    //etsSEO.initSaveScore();
                }, 2000);

            }


        } else {
            etsSeoCHeckMCE++;
            if (etsSeoCHeckMCE >= 150) {
                clearInterval(checkInitTinyMce);
            }
        }


    }, 200);

    if (((ETS_SEO_CONTROLLER == 'AdminCmsContent' && ETS_SEO_IS_CMS_CATEGORY) || ETS_SEO_CONTROLLER == 'AdminMeta') && ETS_SEO_ENABLE_AUTO_ANALYSIS) {
        $(document).on('keyup', 'input[id^="name_"], input[id^="cms_page_category_name_"]', function () {
            clearTimeout(etsSEO.timeoutTyping);
            var $this = $(this);
            etsSEO.timeoutTyping = setTimeout(function () {
                var id_lang = etsSEO.getIdLang($this.attr('id'));
                etsSEO.changePreview(id_lang);
            }, etsSEO.timeoutKeyup);
        });
    }

    if(ETS_SEO_ENABLE_AUTO_ANALYSIS) {
        $(document).on('keyup', 'textarea[id^="description_"], textarea[id^="cms_page_category_description_"]', function () {
            clearTimeout(etsSEO.timeoutTyping);
            var $this = $(this);
            etsSEO.timeoutTyping = setTimeout(function () {
                var text_content = $this.val();
                var id_lang = etsSEO.getIdLang($this.attr('id'));
                etsSEO.analysisContent(
                    id_lang,
                    text_content
                );
                etsSEO.changePreview(id_lang);
            }, etsSEO.timeoutKeyup);
        });
    }

    $(document).on('click', '.ets_seotop1_step_seo .js-btn-preview-mode', function () {
        var mode = $(this).attr('data-mode');
        if (mode == 'mobile') {
            $('.ets_seotop1_step_seo .snippet-preview--desktop:not(.hide)').addClass('hide');
            $('.ets_seotop1_step_seo .snippet-preview--mobile').removeClass('hide');

            $('.ets_seotop1_step_seo .js-btn-preview-mode[data-mode="mobile"]:not(.active)').addClass('active');
            $('.ets_seotop1_step_seo .js-btn-preview-mode[data-mode="desktop"]').removeClass('active');
        } else {
            $('.ets_seotop1_step_seo .snippet-preview--mobile:not(.hide)').addClass('hide');
            $('.ets_seotop1_step_seo .snippet-preview--desktop').removeClass('hide');

            $('.ets_seotop1_step_seo .js-btn-preview-mode[data-mode="desktop"]:not(.active)').addClass('active');
            $('.ets_seotop1_step_seo .js-btn-preview-mode[data-mode="mobile"]').removeClass('active');
        }

    });

    /* Product page ========*/
    $(document).on('change', '#form_switch_language', function (e) {
        var lang = $(this).val();
        $('.ets_seotop1_step_seo .multilang-field.lang-' + lang).removeClass('hide');
        $('.ets_seotop1_step_seo .multilang-field:not(.lang-' + lang + ')').addClass('hide');
        $('.js-locale-btn').html(lang);
        etsSEO.checkInputMetaTemplate(ETS_SEO_LANGUAGES[lang]);
    });

    //Key phrase change
    if(ETS_SEO_ENABLE_AUTO_ANALYSIS) {
        $(document).on('keyup', '.ets_seotop1_step_seo .input-key-phrase', function () {
            clearTimeout(etsSEO.timeoutTyping);
            var $this = $(this);
            etsSEO.timeoutTyping = setTimeout(function () {
                var id_lang = $this.attr('data-idlang');
                var key_phrase = $this.val();
                var prefix = etsSEO.prefixInput();
                etsSEO.keyPhraseGlobal[id_lang] = key_phrase;
                $('.ets_seotop1_step_seo .input-key-phrase-il-' + id_lang).val($this.val());
                var text_content = '';
                if (ETS_SEO_CONTROLLER == 'AdminProducts') {
                    text_content = $('#form_step1_description_short_' + id_lang).val() + $('#form_step1_description_' + id_lang).val()
                } else if (ETS_SEO_CONTROLLER == 'AdminMeta') {
                    text_content = '';
                } else if (ETS_SEO_CONTROLLER == 'AdminManufacturers') {
                    text_content = $(etsSEO.prefixInput().short_desc + id_lang).val() + $(etsSEO.prefixInput().content + id_lang).val();
                } else {
                    text_content = $(etsSEO.prefixInput().content + id_lang).val()
                }
                var link_rewrite = prefix.link_rewrite ? $(prefix.link_rewrite + id_lang).val() : '';
                etsSEO.keyPhraseGlobal = key_phrase;
                etsSEO.analysisKeypharse(id_lang, key_phrase, text_content);
                etsSEO.rules.keyphraseInSlug(id_lang, key_phrase, link_rewrite);
                etsSEO.changePreview(id_lang);
            }, etsSEO.timeoutKeyup);

        });
    }

    $(document).on('focusout', '.ets_seotop1_step_seo .input-key-phrase', function () {

        var id_lang = $(this).attr('data-idlang');
        var minors = etsSEO.getMinorKeyphrase(id_lang);
        if (minors && minors.indexOf($(this).val().trim()) !== -1) {
            $(this).val('');
            showErrorMessage(ETS_SEO_MESSAGE.focus_keyphrase_same_minor_keyphrase);
        }
    });
    if(ETS_SEO_ENABLE_AUTO_ANALYSIS) {
        $(document).on('keyup', '.ets_seotop1_step_seo .input-key-phrase', function (e) {
            if (e.which == 13) {
                clearTimeout(etsSEO.timeoutTypingFocusKey);
                var $this = $(this);
                etsSEO.timeoutTypingFocusKey = setTimeout(function () {
                    var id_lang = $this.attr('data-idlang');
                    var minors = etsSEO.getMinorKeyphrase(id_lang);
                    if (minors && minors.indexOf($this.val().trim()) !== -1) {
                        $this.val('');
                        showErrorMessage(ETS_SEO_MESSAGE.focus_keyphrase_same_minor_keyphrase);
                    }
                }, etsSEO.timeoutKeyup);

            }
        });
        $(document).on('keyup', 'input[id^="form_step1_name"]', function () {
            clearTimeout(etsSEO.timeoutTyping);
            var $this = $(this);
            etsSEO.timeoutTyping = setTimeout(function () {
                var arrayIdInput = $this.attr('id').split('_');
                var id_lang = arrayIdInput[arrayIdInput.length - 1];
                var key_phrase = $('.input-key-phrase-il-' + id_lang).first().val();
                var text_content = $('#form_step1_description_short_' + id_lang).val() + $('#form_step1_description_' + id_lang).val();
                etsSEO.analysisKeypharse(id_lang, key_phrase, text_content);
                etsSEO.changePreview(id_lang);
            }, etsSEO.timeoutKeyup);
        });

        $(document).on('keyup', 'input[id^="category_name_"]', function () {
            clearTimeout(etsSEO.timeoutTyping);
            var $this = $(this);
            etsSEO.timeoutTyping = setTimeout(function () {
                var arrayIdInput = $this.attr('id').split('_');
                var id_lang = arrayIdInput[arrayIdInput.length - 1];
                var key_phrase = $('.input-key-phrase-il-' + id_lang).first().val();
                var text_content = $('#category_description_' + id_lang).val();

                etsSEO.analysisKeypharse(id_lang, key_phrase, text_content);
                etsSEO.changePreview(id_lang);
            }, etsSEO.timeoutKeyup);
        });

        //Seo tab
        $(document).on('keyup', 'input[name^="' + etsSEO.prefixInput().title + '"]', function () {
            clearTimeout(etsSEO.timeoutTyping);
            var $this = $(this);
            etsSEO.timeoutTyping = setTimeout(function () {
                var id_lang = etsSEO.getIdLang($this.attr('id'));
                if (id_lang) {
                    var prefix = etsSEO.prefixInput();
                    if (prefix.meta_title && $(prefix.meta_title + id_lang).length) {
                        etsSEO.rules.seoTitleWidth(id_lang, $(prefix.meta_title + id_lang).val());
                    }
                    etsSEO.changePreview(id_lang);
                }
            }, etsSEO.timeoutKeyup);

        });
    }

    if (etsSEO.prefixInput().title && ETS_SEO_ENABLE_AUTO_ANALYSIS) {
        $(document).on('keyup', 'input[id^=' + etsSEO.prefixInput().title.replace('#', '') + ']', function () {
            clearTimeout(etsSEO.timeoutTyping);
            var $this = $(this);
            etsSEO.timeoutTyping = setTimeout(function () {
                var arrayIdInput = $this.attr('id').split('_');
                var id_lang = arrayIdInput[arrayIdInput.length - 1];
                var key_phrase = $('.input-key-phrase-il-' + id_lang).first().val();
                var prefix = etsSEO.prefixInput();
                var text_content = '';
                if (prefix.short_desc && prefix.short_desc != prefix.content && $(prefix.short_desc + id_lang).length) {
                    text_content += $(prefix.short_desc + id_lang).val();
                }

                if (prefix.content && prefix.short_desc != prefix.content && $(prefix.content + id_lang).length) {
                    text_content += $(prefix.content + id_lang).val();
                }
                etsSEO.analysisKeypharse(id_lang, key_phrase, text_content);
                etsSEO.changePreview(id_lang);
            }, etsSEO.timeoutKeyup);

        });
    }

    $(document).on('change', '[id^=ets_seo_allow_search_engine_show_post]', function () {
        etsSEO.setPreviewAnalysis();
    });
    if (ETS_SEO_CONTROLLER == 'AdminManufacturers') {
        $(document).on('keyup', 'input[id*=manufacturer_name]', function () {
            clearTimeout(etsSEO.timeoutTyping);
            etsSEO.timeoutTyping = setTimeout(function () {
                Object.keys(ETS_SEO_LANGUAGES).forEach(function (key) {
                    var id_lang = ETS_SEO_LANGUAGES[key];
                    var key_phrase = $('.input-key-phrase-il-' + id_lang).first().val();
                    var prefix = etsSEO.prefixInput();
                    var text_content = '';
                    if (prefix.short_desc && prefix.short_desc != prefix.content && $(prefix.short_desc + id_lang).length) {
                        text_content += $(prefix.short_desc + id_lang).val();
                    }

                    if (prefix.content && prefix.short_desc != prefix.content && $(prefix.content + id_lang).length) {
                        text_content += $(prefix.content + id_lang).val();
                    }

                    etsSEO.analysisKeypharse(id_lang, key_phrase, text_content);
                    etsSEO.changePreview(id_lang);
                });

            }, etsSEO.timeoutKeyup);

        });
    }

    if ((ETS_SEO_CONTROLLER == 'AdminManufacturers' || ETS_SEO_CONTROLLER == 'AdminSuppliers') && ETS_SEO_ENABLE_AUTO_ANALYSIS) {
        $(document).on('keyup', 'input[name=name]', function () {
            clearTimeout(etsSEO.timeoutTyping);
            etsSEO.timeoutTyping = setTimeout(function () {
                Object.keys(ETS_SEO_LANGUAGES).forEach(function (key) {
                    var id_lang = ETS_SEO_LANGUAGES[key];
                    var key_phrase = $('.input-key-phrase-il-' + id_lang).first().val();
                    var prefix = etsSEO.prefixInput();
                    var text_content = '';
                    if (prefix.short_desc && prefix.short_desc != prefix.content && $(prefix.short_desc + id_lang).length) {
                        text_content += $(prefix.short_desc + id_lang).val();
                    }

                    if (prefix.content && prefix.short_desc != prefix.content && $(prefix.content + id_lang).length) {
                        text_content += $(prefix.content + id_lang).val();
                    }
                    etsSEO.analysisKeypharse(id_lang, key_phrase, text_content);
                    etsSEO.changePreview(id_lang);
                });
            }, etsSEO.timeoutKeyup);

        });
    }

    if(ETS_SEO_ENABLE_AUTO_ANALYSIS) {
        //Meta title change
        $.each(['input[id*="meta_title_"]',
            'input[id*="meta_page_title_"]',
            'input[id*="head_seo_title_"]',
        ], function (_i, el) {
            $(document).on('keyup change', el, function () {
                clearTimeout(etsSEO.timeoutTypingMetaTitle);
                var $this = $(this);

                etsSEO.timeoutTypingMetaTitle = setTimeout(function () {
                    var meta_title = $this.val();
                    var arrayIdInput = $this.attr('id').split('_');
                    var id_lang = arrayIdInput[arrayIdInput.length - 1];
                    $('#ets_seo_meta_title_' + id_lang).val(meta_title);

                    etsSEO.rules.seoTitleWidth(id_lang, meta_title);
                    var key_phrase = $('.input-key-phrase-il-' + id_lang).first().val();
                    etsSEO.rules.keyPhraseInTitle(id_lang, key_phrase, meta_title);
                    var minor_key_phrase = etsSEO.getMinorKeyphrase(id_lang);
                    etsSEO.rules.minorKeyphraseInMetaTitle(id_lang, minor_key_phrase, meta_title);
                    etsSEO.changePreview(id_lang);
                }, etsSEO.timeoutKeyup);

            });
        });
        //Meta description change
        $.each(['textarea[id*="meta_description_"]',
            'form input[id*="meta_description_"]'
        ], function (i, el) {
            $(document).on('keyup change', el, function () {
                clearTimeout(etsSEO.timeoutTypingMetaDesc);
                var $this = $(this);
                etsSEO.timeoutTypingMetaDesc = setTimeout(function () {
                    var meta_desc = $this.val();
                    var arrayIdInput = $this.attr('id').split('_');
                    var id_lang = arrayIdInput[arrayIdInput.length - 1];
                    var key_phrase = $('.input-key-phrase-il-' + id_lang).first().val();
                    $('#ets_seo_meta_description_' + id_lang).val(meta_desc);
                    etsSEO.rules.metaDescLength(id_lang, meta_desc);
                    etsSEO.rules.keyphraseInMetaDesc(id_lang, key_phrase, meta_desc);
                    var minor_key_phrase = etsSEO.getMinorKeyphrase(id_lang);
                    etsSEO.rules.minorKeyphraseInMetaDesc(id_lang, minor_key_phrase, meta_desc);
                    etsSEO.changePreview(id_lang);
                }, etsSEO.timeoutKeyup);

            });
        });


        //Friendly URL change
        $.each(['input[id*="link_rewrite_"]',
            'input[id*="friendly_url_"]',
            'input[id*="url_rewrite_"]',
        ], function (_i, el) {
            $(document).on('keyup', el, function () {
                clearTimeout(etsSEO.timeoutTypingFriendlyUrl);
                var $this = $(this);
                etsSEO.timeoutTypingFriendlyUrl = setTimeout(function () {
                    var link_rewrite = $this.val();
                    var arrayIdInput = $this.attr('id').split('_');
                    var id_lang = arrayIdInput[arrayIdInput.length - 1];
                    var key_phrase = $('.input-key-phrase-il-' + id_lang).first().val();

                    etsSEO.rules.keyphraseInSlug(id_lang, key_phrase, link_rewrite);
                    etsSEO.changePreview(id_lang);
                    $('#ets_seo_link_rewrite_' + id_lang).val(link_rewrite);
                }, etsSEO.timeoutKeyup);

            });
        });

    }
    //On add short code
    $(document).on('click', '.js-ets-seo-add-meta-code', function () {
        var meta_code = $(this).attr('data-code');
        var textValue = '';
        if (meta_code) {
            var meta_box = $(this).parent('.ets_seo_meta_code');
            if (!meta_box.prev('.input-group').length && (meta_box.parent().find('input').length || meta_box.parent().find('textarea').length)) {
                if(!meta_box.parent().find('input, textarea').parent().find('.ets_seo_tmp_input').length){
                    etsSEO.insertAtCaret(meta_box.parent().find('input, textarea'), meta_code);

                    if (ETS_SEO_CONTROLLER !== 'AdminEtsSeoSearchAppearanceContentType') {
                        meta_box.parent().find('input, textarea').change();
                        etsSEO.changePreview(etsSEO.getIdLang(meta_box.parent().find('input, textarea').attr('id')));
                    }
                }

            } else if (meta_box.prev('.input-group').length) {
                if (meta_box.prev('.input-group').find('input, textarea').length > 1) {
                    var meta_input = meta_box.prev('.input-group').find('input[id$="_' + ETS_SEO_LANG_ID_ACTIVE + '"], textarea[id$="_' + ETS_SEO_LANG_ID_ACTIVE + '"]');
                    if(!meta_input.parent().find('.ets_seo_tmp_input').length){
                        etsSEO.insertAtCaret(meta_input, meta_code);
                        if (ETS_SEO_CONTROLLER !== 'AdminEtsSeoSearchAppearanceContentType') {
                            meta_input.change();
                            etsSEO.changePreview(etsSEO.getIdLang(meta_input.attr('id')));
                        }
                    }

                } else {
                    var idInput = meta_box.prev('.input-group').find('input,textarea');
                    if(!idInput.parent().find('.ets_seo_tmp_input').length){
                        etsSEO.insertAtCaret(idInput, meta_code);
                        if (ETS_SEO_CONTROLLER !== 'AdminEtsSeoSearchAppearanceContentType') {
                            idInput.change();
                            etsSEO.changePreview(etsSEO.getIdLang(meta_box.prev('.input-group').find('input,textarea').attr('id')));
                        }
                    }

                }

            }

        }
        var inSnippet = false;
        if ($(this).parent('.ets_seo_meta_code').hasClass('meta_code_snippet')) {
            inSnippet = true;
        }
        etsSEO.updateSnippet(inSnippet);
        return false;
    });


    //Change language
    $(document).on('click', '.translatable-field ul.dropdown-menu>li>a', function () {
        if (etsSEO.activeControllers.indexOf(ETS_SEO_CONTROLLER) !== -1 && $('.ets_seotop1_step_seo').length) {
            var id_lang = $(this).attr('href').replace(/javascript:hideOtherLanguage\(|\);/g, '');

            $('.ets_seotop1_step_seo .multilang-field.lang-' + id_lang).removeClass('hide');
            $('.ets_seotop1_step_seo .multilang-field:not(.lang-' + id_lang + ')').addClass('hide');

            ETS_SEO_LANG_ID_ACTIVE = id_lang;
            etsSEO.checkInputMetaTemplate(id_lang);
        }
    });

    $(document).on('click', '.js-ets-seo-btn-group-lang a', function () {

        var id_lang = $(this).attr('href').replace(/javascript:hideOtherLanguage\(|\);/g, '');

        $('.ets_seotop1_step_seo .multilang-field.lang-' + id_lang).removeClass('hide');
        $('.ets_seotop1_step_seo .multilang-field:not(.lang-' + id_lang + ')').addClass('hide');

        ETS_SEO_LANG_ID_ACTIVE = id_lang;
        etsSEO.checkInputMetaTemplate(id_lang);
    });

    //Presta >= 176 change language
    $(document).on('click', '.locale-input-group .js-locale-item', function () {
        var id_lang = ETS_SEO_LANGUAGES[$(this).attr('data-locale')];
        if ($('#form_switch_language').length) {
            var locale = $(this).attr('data-locale');
            $('#form_switch_language option[value="' + locale + '"]').prop('selected', true);
            $('#form_switch_language').change();
            $('.js-locale-btn').html(locale);
            ETS_SEO_LANG_ID_ACTIVE = id_lang;
            etsSEO.checkInputMetaTemplate(id_lang);
            return;
        }
        if (etsSEO.activeControllers.indexOf(ETS_SEO_CONTROLLER) !== -1) {
            $('.ets_seotop1_step_seo .multilang-field.lang-' + id_lang).removeClass('hide');
            $('.ets_seotop1_step_seo .multilang-field:not(.lang-' + id_lang + ')').addClass('hide');

            ETS_SEO_LANG_ID_ACTIVE = id_lang;
            etsSEO.checkInputMetaTemplate(id_lang);
        }
    });
    $(document).on('click', '.translationsLocales .nav-link', function () {
        var id_lang = ETS_SEO_LANGUAGES[$(this).attr('data-locale')];
        if ($('#form_switch_language').length) {
            var locale = $(this).attr('data-locale');
            $('#form_switch_language option[value="' + locale + '"]').prop('selected', true);
            $('#form_switch_language').change();
            $('.js-locale-btn').html(locale);
            ETS_SEO_LANG_ID_ACTIVE = id_lang;
            etsSEO.checkInputMetaTemplate(id_lang);
            return;
        }
        if (etsSEO.activeControllers.indexOf(ETS_SEO_CONTROLLER) !== -1) {
            $('.ets_seotop1_step_seo .multilang-field.lang-' + id_lang).removeClass('hide');
            $('.ets_seotop1_step_seo .multilang-field:not(.lang-' + id_lang + ')').addClass('hide');

            ETS_SEO_LANG_ID_ACTIVE = id_lang;
            etsSEO.checkInputMetaTemplate(id_lang);
        }
    });

    $(document).on('change', '.ets_seo_advanced_select2', function () {
        var data = $(this).val();

        $(this).parent().find('.ets-seo-select2-value').val(data.toString());
    });

    /* End product page */

    $(document).on('click', '.js-ets-seo-tab-customize', function () {
        Object.keys(ETS_SEO_LANGUAGES).forEach(function (key) {
            var id_lang = ETS_SEO_LANGUAGES[key];
            etsSEO.changePreview(id_lang);
        });
    });

    $(document).on('click', '.js-ets-seo-btn-toggle-snippet-meta', function () {
        if ($('.js-ets-seo-box-snippet-meta').hasClass('hide')) {
            $('.js-ets-seo-box-snippet-meta').removeClass('hide');
        } else {
            $('.js-ets-seo-box-snippet-meta').addClass('hide');
        }
        etsSEO.updateSnippet(false);
        return false;
    });

    $(document).on('keyup', 'input[id^="ets_seo_meta_title_"]', function () {
        clearTimeout(etsSEO.timeoutTyping);
        var $this = $(this);
        etsSEO.timeoutTyping = setTimeout(function () {
            var meta_title = $this.val();
            var id_lang = etsSEO.getIdLang($this.attr('id'));

            $('input[id*="meta_title_' + id_lang + '"], input[id*="meta_page_title_' + id_lang + '"], input[id*="head_seo_title_' + id_lang + '"], input[id^="title_"]').val(meta_title);
            etsSEO.changePreview(id_lang);
        }, etsSEO.timeoutKeyup);

    });

    $(document).on('keyup', 'input[id^="ets_seo_link_rewrite_"]', function () {
        clearTimeout(etsSEO.timeoutTyping);
        var $this = $(this);
        etsSEO.timeoutTyping = setTimeout(function () {
            var link_rewtire = $this.val();
            var id_lang = etsSEO.getIdLang($this.attr('id'));
            $('input[id*="link_rewrite_' + id_lang + '"], input[id*="friendly_url_' + id_lang + '"], input[id*="url_rewrite_' + id_lang + '"]').val(link_rewtire);
            etsSEO.changePreview(id_lang);
        }, etsSEO.timeoutKeyup);
    });

    $(document).on('keyup', 'textarea[id^="ets_seo_meta_description_"]', function () {
        clearTimeout(etsSEO.timeoutTyping);
        var $this = $(this);
        etsSEO.timeoutTyping = setTimeout(function () {
            var meta_desc = $this.val();
            var id_lang = etsSEO.getIdLang($this.attr('id'));
            $('textarea[id*="meta_description_' + id_lang + '"], form input[id*="meta_description_' + id_lang + '"]').val(meta_desc);
            etsSEO.changePreview(id_lang);
        }, etsSEO.timeoutKeyup);
    });

    $(document).on('click', '.js-ets-seo-show-seo-analysis-tab', function () {
        $('a[href="#step_ets_seo_analysis"]').tab('show');
        $('a[href="#category-seo-analysis"]').tab('show');
        $('.js-ets-seo-customize-item').removeClass('active');
        $('.js-ets-seo-tab-analysis').addClass('active');
        $('a[href="#ets_seo_analysis_tabs"]').tab('show');

        $('#step_ets_seo_analysis .js-ets-seo-show-seo-analysis-tab').hide();
        $('#category-seo-analysis .js-ets-seo-show-seo-analysis-tab').hide();
        $('.ets-seo-right-column .js-ets-seo-show-seo-analysis-tab').hide();

        return false;
    });

    $(document).on('click', '.ets_seo_categories a.js-ets-seo-tab-customize', function () {
        var tabActive = $(this).attr('href');
        if (tabActive == '#step_ets_seo_analysis' || tabActive == '#category-seo-analysis' || tabActive == '#ets_seo_analysis_tabs') {
            $('#step_ets_seo_analysis .js-ets-seo-show-seo-analysis-tab').hide();
            $('#category-seo-analysis .js-ets-seo-show-seo-analysis-tab').hide();
            $('.ets-seo-right-column .js-ets-seo-show-seo-analysis-tab').hide();

        } else {
            $('.ets-seo-right-column .js-ets-seo-show-seo-analysis-tab').show();
        }
        $(this).closest('.tab-content').find('.tab-pane:not(.translation-field)').removeClass('show');
        $(this).closest('.tab-content').find('.tab-pane:not(.translation-field)').removeClass('active');
        $(tabActive).addClass('show active');
    });

    //Click product tab
    $(document).on('click', 'a.nav-link', function () {
        var tabActive = $(this).attr('href');
        if (tabActive == '#step_ets_seo_analysis') {
            $('#step_ets_seo_analysis .js-ets-seo-show-seo-analysis-tab').hide();
        }
    });

    if (ETS_SEO_CONTROLLER == 'AdminProducts' && ETS_SEO_ENABLE_AUTO_ANALYSIS) {
        //Action for upload, delete or update alt for product image
        $(document).bind("ajaxSuccess", function (evt, xhr, settings) {
            if (xhr.readyState == 4 && xhr.status == 200 && xhr.responseJSON) {
                if (!ETS_SEO_PRODUCT_IMAGE) {
                    ETS_SEO_PRODUCT_IMAGE = {};
                }
                var update = false;
                var res = xhr.responseJSON;
                if (res.legend && res.id) {
                    if (!res.url_delete) { //Update data image
                        Object.keys(res.legend).forEach(function (key) {
                            if (ETS_SEO_PRODUCT_IMAGE[key]) {
                                for (var i = 0; i < ETS_SEO_PRODUCT_IMAGE[key].length; i++) {
                                    if (ETS_SEO_PRODUCT_IMAGE[key][i].id_image == res.id) {
                                        ETS_SEO_PRODUCT_IMAGE[key][i].legend = res.legend[key];
                                    }
                                }
                            }

                        });
                        update = true;
                    }

                } else {
                    if (settings.url && settings.url.indexOf('/catalog/products/image/upload/') !== -1) { // Upload image

                        var idImage = $('.dz-preview').last().attr('data-id');
                        var altImage = $('.dz-preview').last().find('img').attr('alt');
                        var exists = false;
                        Object.keys(ETS_SEO_PRODUCT_IMAGE).forEach(function (key) {
                            if (typeof ETS_SEO_PRODUCT_IMAGE[key] !== 'undefined') {
                                for (var i = 0; i < ETS_SEO_PRODUCT_IMAGE[key].length; i++) {
                                    if (ETS_SEO_PRODUCT_IMAGE[key][i].id_image == idImage) {
                                        exists = true;
                                        break;
                                    }
                                }
                                if (!exists) {
                                    ETS_SEO_PRODUCT_IMAGE[key].push({
                                        cover: null,
                                        id_image: idImage,
                                        legend: altImage,
                                        position: 1
                                    });
                                }

                            }
                        });
                        if (!exists) {
                            update = true;

                        }

                    } else if (settings.url && settings.url.indexOf('/products/image/delete/') !== -1) //Delete image
                    {
                        var lastOfUrl = settings.url.split('/').pop();
                        var idImage = lastOfUrl.split('?')[0];
                        Object.keys(ETS_SEO_PRODUCT_IMAGE).forEach(function (key) {
                            if (typeof ETS_SEO_PRODUCT_IMAGE[key] !== 'undefined') {
                                for (var i = 0; i < ETS_SEO_PRODUCT_IMAGE[key].length; i++) {
                                    if (ETS_SEO_PRODUCT_IMAGE[key][i].id_image == idImage) {
                                        ETS_SEO_PRODUCT_IMAGE[key].splice(i, 1);
                                    }
                                }
                            }
                        });
                        update = true;
                    }

                }
                if (update) {
                    Object.keys(ETS_SEO_LANGUAGES).forEach(function (key) {
                        if ($('#form_step1_description_short_' + ETS_SEO_LANGUAGES[key]).length) {
                            etsSEO.analysisContent(
                                ETS_SEO_LANGUAGES[key],
                                $('#form_step1_description_short_' + ETS_SEO_LANGUAGES[key]).val() +
                                $('#form_step1_description_' + ETS_SEO_LANGUAGES[key]).val()
                            );
                        }
                    });
                }

            }
        });
    }

    //Analysis after init
    $(window).on('load', function () {
        etsSEO.changePlaceholderMeta();

        setTimeout(function () {
            etsSEO.updateSnippet();
        }, 500);

        if (etsSEO.activeControllers.indexOf(ETS_SEO_CONTROLLER) !== -1) {
            etsSEO.initSeoScore();
            var etsSeoRunAnalysis = false
            Object.keys(ETS_SEO_LANGUAGES).forEach(function (key) {
                var idLang=ETS_SEO_LANGUAGES[key];
                if ((!ETS_SEO_SCORE_DATA || !ETS_SEO_SCORE_DATA[idLang]) || !ETS_SEO_SCORE_DATA[idLang].score_analysis || !ETS_SEO_SCORE_DATA[idLang].content_analysis) {
                    etsSeoRunAnalysis = true;
                    return false;
                }
            });

            setTimeout(function () {
                var etsSeoPrefixInput = etsSEO.prefixInput();
                Object.keys(ETS_SEO_LANGUAGES).forEach(function (key) {
                    if ($(etsSeoPrefixInput.meta_title + ETS_SEO_LANGUAGES[key]).length) {
                        etsSEO.initTabSeo(ETS_SEO_LANGUAGES[key], etsSeoRunAnalysis);
                    }
                });

                if(!ETS_SEO_ENABLE_AUTO_ANALYSIS || ETS_SEO_SCORE_DATA){
                    $('#ets_seo_score_data').val(JSON.stringify({seo_score:etsSEO.seo_score, readability_score: etsSEO.readability_score}));
                    etsSEO.showSuccessMessageAnalysis(true);
                }

            }, 300);

            if (ETS_SEO_CONTROLLER == 'AdminProducts') {
                $('#step_ets_seo_analysis .js-ets-seo-show-seo-analysis-tab').hide();
            }
        }

        if ($('.js-ets-seo-tagify').length) {
            $('.js-ets-seo-tagify').each(function () {
                var id = $(this).attr('id');
                var id_lang = $(this).attr('data-idlang');
                etsSEO.keyPhraseGlobal[id_lang] = $('input[name="ets_seo_key_phrase[' + id_lang + ']"]').val();
                var etsTagify = new Tagify(document.querySelector('#' + id), {
                    templates: {
                        tag: function (v, tagData) {
                            return '<tag contenteditable="false" spellcheck="false" class="tagify__tag ' + (tagData.class ? tagData.class : '') + '" ' + this.getAttributes(tagData) + '><x title="" class="tagify__tag__removeBtn"></x><div><span class="tagify__tag-text">' + v + '</span></div></tag>';
                        }
                    }
                });
                etsTagify.on('add', function (e) {
                    var key_phrase = $('input[name="ets_seo_key_phrase[' + ETS_SEO_LANG_ID_ACTIVE + ']"]').val();
                    if (e.detail.data.value == key_phrase) {
                        etsTagify.removeTag(e.detail.data.value);
                        showErrorMessage(ETS_SEO_MESSAGE.minor_keyphrase_same_focus_keyphrase);
                    }
                    if(ETS_SEO_ENABLE_AUTO_ANALYSIS)
                        etsSEO.analysisMinorKeyphrase();
                });
                etsTagify.on('remove', function (e) {
                    if(ETS_SEO_ENABLE_AUTO_ANALYSIS)
                        etsSEO.analysisMinorKeyphrase();
                });
                etsTagify.on('edit', function (e) {
                    var key_phrase = $('input[name="ets_seo_key_phrase[' + ETS_SEO_LANG_ID_ACTIVE + ']"]').val();
                    if (e.detail.data.value == key_phrase) {
                        etsTagify.removeTag(e.detail.data.value);
                        showErrorMessage(ETS_SEO_MESSAGE.minor_keyphrase_same_focus_keyphrase);
                    }
                    if(ETS_SEO_ENABLE_AUTO_ANALYSIS)
                        etsSEO.analysisMinorKeyphrase();
                });
            })

        }
    });

    $(document).on('click', '.js-ets-seo-btn-control-analysis:not(.loading)', function (event) {
        var $this = $(this);
        $this.addClass('loading');
        etsSEO.showMessageAnalysis = false;
        var startTime = new Date().getTime()/1000;
        var prefix = etsSEO.prefixInput();
        $('#ets_seo_score_data').val('');
        $('.js-ets-seo-preview-analysis .processing').addClass('excuting');
        $('.js-ets-seo-preview-analysis .sub-title').html(ETS_SEO_MESSAGE.analyzing);
        setTimeout(function () {
            Object.keys(ETS_SEO_LANGUAGES).forEach(function (key) {
                var id_lang = ETS_SEO_LANGUAGES[key];
                if ($(prefix.meta_title + id_lang).length) {
                    var content = '';
                    if (prefix.short_desc && $(prefix.short_desc + id_lang).length) {
                        content += $(prefix.short_desc + id_lang).val();
                    }
                    if (prefix.content && prefix.short_desc != prefix.content && $(prefix.content + id_lang).length) {
                        content += $(prefix.content + id_lang).val();
                    }
                    etsSEO.analysisContent(id_lang, content);
                }
            });
            var endTime = new Date().getTime()/1000;
            var timeout = 3-( endTime-startTime);
            if(timeout < 0){
                timeout = 0;
            }
            setTimeout(function () {
                etsSEO.showSuccessMessageAnalysis(true);
                showSuccessMessage(ETS_SEO_MESSAGE['analysis_success']);
                $this.removeClass('loading');
            }, timeout*1000);
        },500);
        return false;
    });

    $(document).on('change', '#form_step1_id_manufacturer', function () {
        Object.keys(ETS_SEO_LANGUAGES).forEach(function (key) {
            etsSEO.changePreview(ETS_SEO_LANGUAGES[key]);
        });
    });

})(jQuery);
