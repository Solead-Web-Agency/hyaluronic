<?php
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
 * @author ETS-Soft <etssoft.jsc@gmail.com>
 * @copyright  2007-2021 ETS-Soft
 * @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of ETS-Soft
 */

if (!defined('_PS_VERSION_') || !defined('_ETS_SEO_MODULE_')) {
    exit;
}

class Ets_Seo_Define
{
    public $context;
    public $module;
    public static $instance = null;

    public function __construct($module = null)
    {
        if (!(is_object($module)) || !$module) {
            $module = Module::getInstanceByName(_ETS_SEO_MODULE_);
        }
        $this->module = $module;
        $context = Context::getContext();
        $this->context = $context;
    }

    /**
     * l
     *
     * @param  mixed $string
     *
     * @return string
     */
    public function l($string)
    {
        return Translate::getModuleTranslation(_ETS_SEO_MODULE_, $string, pathinfo(__FILE__, PATHINFO_FILENAME));
    }

    /**
     * display
     *
     * @param  mixed $template
     *
     * @return string
     */
    public function display($template)
    {
        if (!$this->module)
            return;
        return $this->module->display($this->module->getLocalPath(), $template);
    }

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new Ets_Seo_Define();
        }
        return self::$instance;
    }

    /**
     * seo_analysis_rules
     *
     * @return array
     */
    public function seo_analysis_rules($controller = null, $is_cms_category = false)
    {
        $pageTitleLabel = $this->l('Page title');
        switch ($controller) {
            case 'AdminProducts':
                $pageTitleLabel = $this->l('Product name');
                break;
            case 'AdminCategories':
                $pageTitleLabel = $this->l('Category name');
                break;
            case 'AdminCmsContent':
                if ($is_cms_category) {
                    $pageTitleLabel = $this->l('CMS category title');
                } else {
                    $pageTitleLabel = $this->l('CMS title');
                }
                break;
            case 'AdminMeta':
                $pageTitleLabel = $this->l('Page title');
                break;
            case 'AdminManufacturers':
                $pageTitleLabel = $this->l('Brand (Manufacturer) name');
                break;
            case 'AdminSuppliers':
                $pageTitleLabel = $this->l('Supplier name');
                break;
        }
        return array(
            'outbound_link' => array(
                'error' => array(
                    'text' => $this->l('[link_support]: No outbound links appear in this page. [link_doc]!'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Outbound links'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/outbound-links-check/'
                        ),
                        '[link_doc]' => array(
                            'text' => $this->l('Add some'),
                            'type' => 'link',
                            'key' => 'doc',
                            'link' => 'https://yoast.com/academy/all-around-seo-training/outbound-links/'
                        ),
                    )
                ),
                'all_nofollowed' => array(
                    'text' => $this->l('[link_support]: All outbound links on this page are nofollowed. [link_doc]!'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Outbound links'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/outbound-links-check/'
                        ),
                        '[link_doc]' => array(
                            'text' => $this->l('Add some normal links'),
                            'type' => 'link',
                            'key' => 'doc',
                            'link' => 'https://yoast.com/academy/all-around-seo-training/outbound-links/'
                        ),
                    )
                ),
                'both'=> array(
                    'text' => $this->l('[link_support]: There are both nofollowed and normal outbound links on this page. Good job!'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Outbound links'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/outbound-links-check/'
                        ),
                    )
                ),
                'success' => array(
                    'text' => $this->l('[link_support]: Good job!'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Outbound links'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/outbound-links-check/'
                        ),
                    )
                )
            ),
            'internal_link' => array(
                'error' => array(
                    'text' => $this->l('[link_support]: No internal links appear in this page. [link_doc]!'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Internal links'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/internal-links-check/'
                        ),
                        '[link_doc]' => array(
                            'text' => $this->l('Make sure to add some'),
                            'type' => 'link',
                            'key' => 'doc',
                            'link' => 'https://yoast.com/academy/site-structure-training/internal-links/'
                        ),
                    )
                ),
                'all_nofollowed' => array(
                    'text' => $this->l('[link_support]: The internal links in this page are all nofollowed. [link_doc]!'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Internal links'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/internal-links-check/'
                        ),
                        '[link_doc]' => array(
                            'text' => $this->l('Add some good internal links'),
                            'type' => 'link',
                            'key' => 'doc',
                            'link' => 'https://yoast.com/academy/site-structure-training/internal-links/'
                        ),
                    )
                ),
                'both' => array(
                    'text' => $this->l('[link_support]: There are both nofollowed and normal internal links on this page. Good job!'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Internal links'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/internal-links-check/'
                        ),
                    )
                ),
                'success' => array(
                    'text' => $this->l('[link_support]: You have enough internal links. Good job!'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Internal links'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/internal-links-check/'
                        ),

                    ),
                )
            ),
            'keyphrase_length' => array(
                'error' => array(
                    'text' => $this->l('[link_support]: No focus keyphrase was set for this page. [link_doc].'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Focus keyphrase length'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/keyphrase-length-check/'
                        ),
                        '[link_doc]' => array(
                            'text' => $this->l('Set a keyphrase in order to calculate your SEO score'),
                            'type' => 'link',
                            'key' => 'doc',
                            'link' => 'https://yoast.com/academy/keyword-research-training/keyphrase-length/'
                        ),
                    )
                ),
                'too_long' => array(
                    'text' => $this->l('[link_support]: The focus keyphrase is [count_length] words long. That\'s more than the recommended maximum of 4 words. [link_doc].'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Focus keyphrase length'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/keyphrase-length-check/'
                        ),

                        '[link_doc]' => array(
                            'text' => $this->l('Make it shorter'),
                            'type' => 'link',
                            'key' => 'doc',
                            'link' => 'https://yoast.com/academy/keyword-research-training/keyphrase-length/'
                        ),
                        '[count_length]' => array(
                            'type' => 'number',
                            'number' => ''
                        ),
                    )
                ),
                'success' => array(
                    'text' => $this->l('[link_support]: Good job!'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Focus keyphrase length'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/keyphrase-length-check/'
                        ),

                    )
                )
            ),
            'keyphrase_in_subheading'=> array(
                'too_little' => array(
                    'text' => $this->l('[link_support]: [link_doc]!'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Focus keyphrase in subheading'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/keyphrase-in-subheading-check'
                        ),
                        '[link_doc]' => array(
                            'text' => $this->l('Use more keyphrases or synonyms in your higher-level subheadings'),
                            'type' => 'link',
                            'key' => 'doc',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/keyphrase-in-subheading-check'
                        ),
                    )
                ),
                'too_much' => array(
                    'text' => $this->l('[link_support]: More than 75% of your higher-level subheadings reflect the topic of your copy. That\'s too much. [link_doc]!'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Focus keyphrase in subheading'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/keyphrase-in-subheading-check'
                        ),
                        '[link_doc]' => array(
                            'text' => $this->l(' Don\'t over-optimize'),
                            'type' => 'link',
                            'key' => 'doc',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/keyphrase-in-subheading-check'
                        ),
                    )
                ),
                'good' => array(
                    'text' => $this->l('[link_support]: [count] of your higher-level subheading(s) reflects the topic of your copy. Good job!'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Focus keyphrase in subheading'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/keyphrase-in-subheading-check/'
                        ),
                        '[count]' => array(
                            'type' => 'number',
                            'number' => '0',
                        ),
                    )
                ),
                'success' => array(
                    'text' => $this->l('[link_support]:  Your higher-level subheading reflects the topic of your copy. Good job!'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Focus keyphrase in subheading'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/keyphrase-in-subheading-check/'
                        ),
                    )
                ),
            ),
            'keyphrase_in_title' => array(
                'error' => array(
                    'text' => $this->l('[link_support]: Not all the words from your keyphrase "[keyphrase]" appear in the meta title. [link_doc].'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Focus keyphrase in meta title'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/keyphrase-in-title-check/'
                        ),
                        '[link_doc]' => array(
                            'text' => $this->l('Try to use the exact match of your focus keyphrase in the meta title'),
                            'type' => 'link',
                            'key' => 'doc',
                            'link' => 'https://yoast.com/academy/all-around-seo-training/keyphrase-in-title/'
                        ),
                        '[keyphrase]' => array(
                            'type' => 'string',
                            'string' => ''
                        ),
                    )
                ),
                'warning' => array(
                    'text' => $this->l('[link_support]: The exact match of the focus keyphrase appears in the meta title, but not at the beginning. [link_doc].'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Focus keyphrase in meta title'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/keyphrase-in-title-check/'
                        ),
                        '[link_doc]' => array(
                            'text' => $this->l('Try to move it to the beginning'),
                            'type' => 'link',
                            'key' => 'doc',
                            'link' => 'https://yoast.com/academy/all-around-seo-training/keyphrase-in-title/'
                        ),
                    )
                ),
                'success' => array(
                    'text' => $this->l('[link_support]: The exact match of the focus keyphrase appears at the beginning of the meta title. Good job!'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Focus keyphrase in meta title'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/keyphrase-in-title-check/'
                        ),

                    )
                )
            ),
            'keyphrase_in_page_title' => array(
                'error' => array(
                    'text' => $this->l('[link_support]: Not all the words from your focus keyphrase "[keyphrase]" appear in the ') . $pageTitleLabel . '. [link_doc].',
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Focus keyphrase in ') . $pageTitleLabel,
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/keyphrase-in-title-check/'
                        ),
                        '[link_doc]' => array(
                            'text' => $this->l('Try to use the exact match of your focus keyphrase in ') . $pageTitleLabel,
                            'type' => 'link',
                            'key' => 'doc',
                            'link' => 'https://yoast.com/academy/all-around-seo-training/keyphrase-in-title/'
                        ),
                        '[keyphrase]' => array(
                            'type' => 'string',
                            'string' => ''
                        ),
                    )
                ),

                'warning' => array(
                    'text' => $this->l('[link_support]: The exact match of the focus keyphrase appears in the title, but not at the beginning. [link_doc].'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Focus keyphrase in ') . $pageTitleLabel,
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/keyphrase-in-title-check/'
                        ),
                        '[link_doc]' => array(
                            'text' => $this->l('Try to move it to the beginning'),
                            'type' => 'link',
                            'key' => 'doc',
                            'link' => 'https://yoast.com/academy/all-around-seo-training/keyphrase-in-title/'
                        ),
                    )
                ),
                'success' => array(
                    'text' => $this->l('[link_support]: The exact match of the focus keyphrase appears at the beginning of the title. Good job!'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Focus keyphrase in ') . $pageTitleLabel,
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/keyphrase-in-title-check/'
                        ),

                    )
                )
            ),
            'page_title_length' => array(
                'too_long' => array(
                    'text' => '[link_support]: The ' . $pageTitleLabel . $this->l(' is too long. That\'s over 65 characters. [link_doc]!'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $pageTitleLabel . $this->l(' length'),
                            'type' => 'link',
                            'key' => '',
                            'link' => '#'
                        ),
                        '[link_doc]' => array(
                            'text' => $this->l('Fix that'),
                            'type' => 'link',
                            'key' => 'doc',
                            'link' => '#'
                        ),
                    )
                ),
                'empty' => array(
                    'text' => '[link_support]: The ' . $pageTitleLabel . $this->l(' is empty. [link_doc]!'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $pageTitleLabel . $this->l(' length'),
                            'type' => 'link',
                            'key' => '',
                            'link' => '#'
                        ),
                        '[link_doc]' => array(
                            'text' => $this->l('Add some'),
                            'type' => 'link',
                            'key' => 'doc',
                            'link' => '#'
                        ),
                    )
                ),
                'success' => array(
                    'text' => '[link_support]: ' . $this->l(' Good job!'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $pageTitleLabel . $this->l(' length'),
                            'type' => 'link',
                            'key' => '',
                            'link' => '#'
                        ),
                    )
                ),
            ),
            'keyphrase_in_intro' => array(
                'error' => array(
                    'text' => $this->l('[link_support]: Your focus keyphrase or its synonyms do not appear in the first paragraph. [link_doc].'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Focus keyphrase in introduction'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/keyphrase-in-introduction-check/'
                        ),
                        '[link_doc]' => array(
                            'text' => $this->l('Make sure the topic is clear immediately'),
                            'type' => 'link',
                            'key' => 'doc',
                            'link' => 'https://yoast.com/academy/seo-copywriting-training/keyphrase-in-introduction/'
                        ),
                    )
                ),
                'success' => array(
                    'text' => $this->l('[link_support]: Well done!'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Focus keyphrase in introduction'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/keyphrase-in-introduction-check/'
                        ),
                    )
                ),
            ),
            'keyphrase_density' => array(
                'error' => array(
                    'text' => $this->l('[link_support]: The focus keyphrase was found [count_word] time. That\'s less than the recommended minimum of [recommended_keyphrase_length] times for a text of this length. [link_doc]!'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Focus keyphrase density'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/keyphrase-density-check/'
                        ),
                        '[link_doc]' => array(
                            'text' => $this->l('Focus on your keyphrase'),
                            'type' => 'link',
                            'key' => 'doc',
                            'link' => 'https://yoast.com/academy/seo-copywriting-training/keyphrase-density/'
                        ),
                        '[count_word]' => array(
                            'type' => 'number',
                            'number' => '0'
                        ),
                        '[recommended_keyphrase_length]' => array(
                            'type' => 'number',
                            'number' => '2'
                        )
                    )
                ),
                'more_than' => array(
                    'text' => $this->l('[link_support]: The focus keyphrase was found [count_word] time. That\'s more than the recommended maximum of [recommended_keyphrase_length] times for a text of this length. [link_doc]!'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Focus keyphrase density'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/keyphrase-density-check/'
                        ),
                        '[link_doc]' => array(
                            'text' => $this->l('Focus on your keyphrase'),
                            'type' => 'link',
                            'key' => 'doc',
                            'link' => 'https://yoast.com/academy/seo-copywriting-training/keyphrase-density/'
                        ),
                        '[count_word]' => array(
                            'type' => 'number',
                            'number' => '0'
                        ),
                        '[recommended_keyphrase_length]' => array(
                            'type' => 'number',
                            'number' => '2'
                        )
                    )
                ),
                'success' => array(
                    'text' => $this->l('[link_support]:  The focus keyphrase was found [count_word] times. This is great!'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Focus keyphrase density'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/keyphrase-density-check/'
                        ),
                        '[count_word]' => array(
                            'type' => 'number',
                            'number' => '0'
                        )
                    )
                ),
            ),
            'keyphrase_density_individual' => array(
                'error' => array(
                    'text' => $this->l('[link_support]: Each single word of the focus keyphrase ("[keyphrase_individual]") should appear at least [recommended_keyphrase_length] time(s) in the content (appearing on focus keyphrase does not count) . [link_doc]!'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Individual words of focus keyphrase'),
                            'type' => 'link',
                            'key' => '',
                            'link' => '#'
                        ),
                        '[link_doc]' => array(
                            'text' => $this->l('Add some'),
                            'type' => 'link',
                            'key' => 'doc',
                            'link' => '#'
                        ),
                        '[keyphrase_individual]' => array(
                            'type' => 'string',
                            'string' => ''
                        ),
                        '[recommended_keyphrase_length]' => array(
                            'type' => 'number',
                            'number' => '2'
                        )
                    )
                ),
                'success' => array(
                    'text' => $this->l('[link_support]: Great job!'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Individual words of focus keyphrase'),
                            'type' => 'link',
                            'key' => '',
                            'link' => '#'
                        ),
                    )
                )
            ),

            'image_alt_attribute' => array(
                'error' => array(
                    'text' => $this->l('[link_support]: No images appear on this page. [link_doc]!'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Image alt attributes'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/image-alt-attributes-check/'
                        ),
                        '[link_doc]' => array(
                            'text' => $this->l('Add some'),
                            'type' => 'link',
                            'key' => 'doc',
                            'link' => 'https://yoast.com/academy/all-around-seo-training/image-alt-attributes/'
                        ),
                    )
                ),
                'no_alt' => array(
                    'text' => $this->l('[link_support]: Images on this page do not have alt attributes that reflect the topic of your text. [link_doc]!'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Image alt attributes'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/image-alt-attributes-check/'
                        ),
                        '[link_doc]' => array(
                            'text' => $this->l('Add your focus keyphrase or synonyms to the alt tags of relevant images'),
                            'type' => 'link',
                            'key' => 'doc',
                            'link' => 'https://yoast.com/academy/all-around-seo-training/image-alt-attributes/'
                        ),
                    )
                ),
                'alt_no_keyphrase' => array(
                    'text' => $this->l('[link_support]: Image alt attributes are missing or do contain the focus keyphrase. [link_doc]!'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Image alt attributes'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/image-alt-attributes-check/'
                        ),
                        '[link_doc]' => array(
                            'text' => $this->l('Add your focus keyphrase or synonyms to the alt tags of relevant images'),
                            'type' => 'link',
                            'key' => 'doc',
                            'link' => 'https://yoast.com/academy/all-around-seo-training/image-alt-attributes/'
                        ),
                    )
                ),
                'success' => array(
                    'text' => $this->l('[link_support]: Good job!'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Image alt attributes'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/image-alt-attributes-check/'
                        ),
                    )
                )
            ),
            'single_h1'=> array(
                'error' => array(
                    'text' => $this->l('[link_support]: H1s should only be used as [page_title]. Find all H1s in your text that aren\'t your [page_title] and change them to a lower heading level!'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Single title'),
                            'type' => 'link',
                            'key' => '',
                            'link' => '#'
                        ),
                        '[page_title]' => array(
                            'type' => 'string',
                            'string' => $pageTitleLabel,
                        ),
                    )
                ),
            ),
            'text_length' => array(
                'error' => array(
                    'text' => $this->l('[link_support]: The text contains [text_length] words. This is far below the recommended minimum of [min_length] words. [link_doc]!'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Text length'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/text-length-check/'
                        ),
                        '[link_doc]' => array(
                            'text' => $this->l('Add more content'),
                            'type' => 'link',
                            'key' => 'doc',
                            'link' => 'https://yoast.com/academy/seo-copywriting-training/text-length'
                        ),
                        '[text_length]' => array(
                            'type' => 'number',
                            'number' => '0'
                        ),
                        '[min_length]' => array(
                            'type' => 'number',
                            'number' => '0'
                        ),
                    )
                ),
                'success' => array(
                    'text' => $this->l('[link_support]: The text contains [text_length] words. Good job'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Text length'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/text-length-check/'
                        ),
                        '[text_length]' => array(
                            'type' => 'number',
                            'number' => '0'
                        ),
                    )
                )
            ),

        );
    }

    /**
     * seo_analysis_rules_meta
     *
     * @return array
     */
    public function seo_analysis_rules_meta()
    {
        return array(
            'meta_description_length' => array(
                'error' => array(
                    'text' => $this->l('[link_support]: No meta description has been specified. Search engines will display copy from the page instead. [link_doc]!'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Meta description length'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/meta-description-length-check/'
                        ),
                        '[link_doc]' => array(
                            'text' => $this->l('Make sure to write one'),
                            'type' => 'link',
                            'key' => 'doc',
                            'link' => 'https://yoast.com/academy/all-around-seo-training/meta-description-length/'
                        ),
                    )
                ),
                'warning' => array(
                    'text' => $this->l('[link_support]: The meta description is too short (under 120 characters). Up to 156 characters are available. [link_doc]!'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Meta description length'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/meta-description-length-check/'
                        ),
                        '[link_doc]' => array(
                            'text' => $this->l('Use the space'),
                            'type' => 'link',
                            'key' => 'doc',
                            'link' => 'https://yoast.com/academy/all-around-seo-training/meta-description-length/'
                        ),
                    )
                ),
                'over_limited' => array(
                    'text' => $this->l('[link_support]: The meta description is over 156 characters. To ensure the entire description will be visible, [link_doc]'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Meta description length'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/meta-description-length-check/'
                        ),
                        '[link_doc]' => array(
                            'text' => $this->l('you should reduce the length'),
                            'type' => 'link',
                            'key' => 'doc',
                            'link' => 'https://yoast.com/academy/all-around-seo-training/meta-description-length/'
                        ),
                    )
                ),
                'success' => array(
                    'text' => $this->l('[link_support]: Well done!'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Meta description length'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/meta-description-length-check/'
                        ),

                    )
                ),
            ),
            'seo_title_width' => array(
                'error' => array(
                    'text' => $this->l('[link_support]: [link_doc]!'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Meta title length'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/seo-title-width-check/'
                        ),
                        '[link_doc]' => array(
                            'text' => $this->l('Please create a meta title'),
                            'type' => 'link',
                            'key' => 'doc',
                            'link' => 'https://yoast.com/academy/all-around-seo-training/seo-title-width/'
                        ),
                    )
                ),
                'too_long' => array(
                    'text' => $this->l('[link_support]: The meta title is wider than the viewable limit. [link_doc].'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Meta title length'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/seo-title-width-check/'
                        ),
                        '[link_doc]' => array(
                            'text' => $this->l('Try to make it shorter'),
                            'type' => 'link',
                            'key' => 'doc',
                            'link' => 'https://yoast.com/academy/all-around-seo-training/seo-title-width/'
                        ),
                    )
                ),
                'warning' => array(
                    'text' => $this->l('[link_support]: The meta title is too short. [link_doc].'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Meta title length'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/seo-title-width-check/'
                        ),
                        '[link_doc]' => array(
                            'text' => $this->l('Use the space to add focus keyphrase variations or create compelling call-to-action copy'),
                            'type' => 'link',
                            'key' => 'doc',
                            'link' => 'https://yoast.com/academy/all-around-seo-training/seo-title-width/'
                        ),
                    )
                ),
                'success' => array(
                    'text' => $this->l('[link_support]: Good job!'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Meta title length'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/seo-title-width-check/'
                        ),

                    )
                ),
            ),
            'keyphrase_in_meta_desc' => array(
                'error' => array(
                    'text' => $this->l('[link_support]: The meta description has been specified, but it does not contain the focus keyphrase. [link_doc]!'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Focus keyphrase in meta description'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/keyphrase-in-meta-description-check/'
                        ),
                        '[link_doc]' => array(
                            'text' => $this->l('Fix that'),
                            'type' => 'link',
                            'key' => 'doc',
                            'link' => 'https://yoast.com/academy/all-around-seo-training/keyphrase-in-meta-description/'
                        ),
                    )
                ),
                'more_than' => array(
                    'text' => $this->l('[link_support]: The meta description contains the focus keyphrase [number] times, which is over the advised maximum of 2 times. [link_doc]!'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Focus keyphrase in meta description'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/keyphrase-in-meta-description-check/'
                        ),
                        '[link_doc]' => array(
                            'text' => $this->l('Limit that'),
                            'type' => 'link',
                            'key' => 'doc',
                            'link' => 'https://yoast.com/academy/all-around-seo-training/keyphrase-in-meta-description/'
                        ),
                        '[number]' => array(
                            'type' => 'number',
                            'number' => '0'
                        ),
                    )
                ),
                'success' => array(
                    'text' => $this->l('[link_support]: Focus keyphrase or synonym appear in the meta description. Well done!'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Focus keyphrase in meta description'),
                            'type' => 'link',
                            'key' => 'doc',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/keyphrase-in-meta-description-check/'
                        ),

                    )
                ),
            ),

            'keyphrase_in_slug' => array(
                'warning' => array(
                    'text' => $this->l('[link_support]: (Part of) your focus keyphrase does not appear in the friendly URL. [link_doc]!'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Focus keyphrase in friendly URL'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/keyphrase-in-slug-check/'
                        ),
                        '[link_doc]' => array(
                            'text' => $this->l('Change that'),
                            'type' => 'link',
                            'key' => 'doc',
                            'link' => 'https://yoast.com/academy/all-around-seo-training/keyphrase-in-slug/'
                        ),
                    )
                ),
                'good' => array(
                    'text' => $this->l('[link_support]: more than half of your focus keyphrase appears in the friendly URL. That\'s great!!'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Focus keyphrase in friendly URL'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/keyphrase-in-slug-check/'
                        ),
                    )
                ),
                'success' => array(
                    'text' => $this->l('[link_support]: Great work!'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Focus keyphrase in friendly URL'),
                            'type' => 'link',
                            'key' => 'doc',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/keyphrase-in-slug-check/'
                        ),
                    )
                ),
            ),
            'minor_keyphrase_length' => array(

                'too_long' => array(
                    'text' => $this->l('[link_support]: The related keyphrase "[minor_keyphrase]" is [count_length] words long. That\'s more than the recommended maximum of 4 words. [link_doc].'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Related keyphrase length'),
                            'type' => 'link',
                            'key' => '',
                            'link' => '#'
                        ),

                        '[link_doc]' => array(
                            'text' => $this->l('Make it shorter'),
                            'type' => 'link',
                            'key' => 'doc',
                            'link' => '#'
                        ),
                        '[count_length]' => array(
                            'type' => 'number',
                            'number' => ''
                        ),
                        '[minor_keyphrase]' => array(
                            'type' => 'string',
                            'string' => ''
                        ),
                    )
                ),
                'success' => array(
                    'text' => $this->l('[link_support]: Good job!'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Related keyphrase length'),
                            'type' => 'link',
                            'key' => '',
                            'link' => '#'
                        ),

                    )
                )
            ),
            'minor_keyphrase_in_content' => array(
                'error' => array(
                    'text' => $this->l('[link_support]: The content does not contain the related keyphrases: "[minor_keyphrase]". [link_doc]'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Related keyphrase density'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://www.seozoom.co.uk/minor-keywords-how-to-optimize-a-text-with-search-intent-and-correlated-ones/'
                        ),
                        '[link_doc]' => array(
                            'text' => $this->l('Fix that'),
                            'type' => 'link',
                            'key' => 'doc',
                            'string' => 'https://www.seozoom.co.uk/minor-keywords-how-to-optimize-a-text-with-search-intent-and-correlated-ones/'
                        ),
                        '[minor_keyphrase]' => array(
                            'type' => 'string',
                            'string' => ''
                        )
                    )
                ),
                'over_limited' => array(
                    'text' => $this->l('[link_support]: The related keyphrase "[minor_keyphrase]" was found [count_word] time. That\'s more than the recommended maximum of [recommended_minor_keyphrase_length] times for a text of this length. [link_doc]!'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Related keyphrase density'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://www.seozoom.co.uk/minor-keywords-how-to-optimize-a-text-with-search-intent-and-correlated-ones/'
                        ),
                        '[link_doc]' => array(
                            'text' => $this->l('Fix that'),
                            'type' => 'link',
                            'key' => 'doc',
                            'link' => 'https://www.seozoom.co.uk/minor-keywords-how-to-optimize-a-text-with-search-intent-and-correlated-ones/'
                        ),
                        '[minor_keyphrase]' => array(
                            'type' => 'string',
                            'string' => ''
                        ),
                        '[count_word]' => array(
                            'type' => 'number',
                            'number' => '0'
                        ),
                        '[recommended_minor_keyphrase_length]' => array(
                            'type' => 'number',
                            'number' => '2'
                        ),
                    )
                ),
                'less_than' => array(
                    'text' => $this->l('[link_support]: The related keyphrase "[minor_keyphrase]" was found [count_word] time. That\'s less than the recommended minimum of [recommended_minor_keyphrase_length] times for a text of this length. [link_doc]!'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Related keyphrase density'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://www.seozoom.co.uk/minor-keywords-how-to-optimize-a-text-with-search-intent-and-correlated-ones/'
                        ),
                        '[link_doc]' => array(
                            'text' => $this->l('Add some'),
                            'type' => 'link',
                            'key' => 'doc',
                            'link' => 'https://www.seozoom.co.uk/minor-keywords-how-to-optimize-a-text-with-search-intent-and-correlated-ones/'
                        ),
                        '[minor_keyphrase]' => array(
                            'type' => 'string',
                            'string' => ''
                        ),
                        '[count_word]' => array(
                            'type' => 'number',
                            'number' => '0'
                        ),
                        '[recommended_minor_keyphrase_length]' => array(
                            'type' => 'number',
                            'number' => '2'
                        ),
                    )
                ),

                'success' => array(
                    'text' => $this->l('[link_support]: Great work!'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Related keyphrases density'),
                            'type' => 'link',
                            'key' => 'doc',
                            'link' => 'https://www.seozoom.co.uk/minor-keywords-how-to-optimize-a-text-with-search-intent-and-correlated-ones/'
                        ),
                    )
                ),
            ),
            'minor_keyphrase_in_content_individual' => array(
                'error' => array(
                    'text' => $this->l('[link_support]:  Individual word "[keyphrase_individual]" of the related keyphrases should appear at least [recommended_keyphrase_length] times. [link_doc]!'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Individual words of related keyphrase'),
                            'type' => 'link',
                            'key' => 'individual',
                            'link' => '#'
                        ),
                        '[link_doc]' => array(
                            'text' => $this->l('Add some'),
                            'type' => 'link',
                            'key' => 'doc',
                            'link' => '#'
                        ),
                        '[keyphrase_individual]' => array(
                            'type' => 'string',
                            'string' => ''
                        ),
                        '[recommended_keyphrase_length]' => array(
                            'type' => 'number',
                            'number' => '2'
                        )
                    )
                ),
                'success' => array(
                    'text' => $this->l('[link_support]:  Great job!'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Individual words of related keyphrase'),
                            'type' => 'link',
                            'key' => 'individual',
                            'link' => '#'
                        ),
                    )
                )
            ),
            'minor_keyphrase_in_title' => array(
                'error' => array(
                    'text' => $this->l('[link_support]: The meta title does not contain the related keyphrase: "[minor_keyphrase]". [link_doc]'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Related keyphrases in meta title'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://www.seozoom.co.uk/minor-keywords-how-to-optimize-a-text-with-search-intent-and-correlated-ones/'
                        ),
                        '[link_doc]' => array(
                            'text' => $this->l('Fix that'),
                            'type' => 'link',
                            'key' => 'doc',
                            'string' => 'https://www.seozoom.co.uk/minor-keywords-how-to-optimize-a-text-with-search-intent-and-correlated-ones/'
                        ),
                        '[minor_keyphrase]' => array(
                            'type' => 'string',
                            'string' => ''
                        )
                    )
                ),
                'over_limited' => array(
                    'text' => $this->l('[link_support]: The related keyphrase "[minor_keyphrase]" was found [count_word] time. That\'s more than the recommended minimum of 2 times for a text of this length. [link_doc]!'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Related keyphrases in meta title'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://www.seozoom.co.uk/minor-keywords-how-to-optimize-a-text-with-search-intent-and-correlated-ones/'
                        ),
                        '[link_doc]' => array(
                            'text' => $this->l('Fix that'),
                            'type' => 'link',
                            'key' => 'doc',
                            'string' => 'https://www.seozoom.co.uk/minor-keywords-how-to-optimize-a-text-with-search-intent-and-correlated-ones/'
                        ),
                        '[count_word]' => array(
                            'type' => 'number',
                            'number' => '0'
                        ),
                        '[minor_keyphrase]' => array(
                            'type' => 'string',
                            'string' => ''
                        )
                    )
                ),

                'success' => array(
                    'text' => $this->l('[link_support]: Great work!'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Related keyphrase in meta title'),
                            'type' => 'link',
                            'key' => 'doc',
                            'link' => 'https://www.seozoom.co.uk/minor-keywords-how-to-optimize-a-text-with-search-intent-and-correlated-ones/'
                        ),
                    )
                ),
            ),
            'minor_keyphrase_in_page_title' => array(
                'error' => array(
                    'text' => $this->l('[link_support]: The title does not contain the related keyphrase: "[minor_keyphrase]". [link_doc]'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Related keyphrases in title (name)'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://www.seozoom.co.uk/minor-keywords-how-to-optimize-a-text-with-search-intent-and-correlated-ones/'
                        ),
                        '[link_doc]' => array(
                            'text' => $this->l('Fix that'),
                            'type' => 'link',
                            'key' => 'doc',
                            'string' => 'https://www.seozoom.co.uk/minor-keywords-how-to-optimize-a-text-with-search-intent-and-correlated-ones/'
                        ),
                        '[minor_keyphrase]' => array(
                            'type' => 'string',
                            'string' => ''
                        )
                    )
                ),
                'over_limited' => array(
                    'text' => $this->l('[link_support]: The related keyphrase "[minor_keyphrase]" was found [count_word] time(s). That\'s more than the recommended minimum of 2 times for a text of this length. [link_doc]!'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Related keyphrases in title (name)'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://www.seozoom.co.uk/minor-keywords-how-to-optimize-a-text-with-search-intent-and-correlated-ones/'
                        ),
                        '[link_doc]' => array(
                            'text' => $this->l('Fix that'),
                            'type' => 'link',
                            'key' => 'doc',
                            'string' => 'https://www.seozoom.co.uk/minor-keywords-how-to-optimize-a-text-with-search-intent-and-correlated-ones/'
                        ),
                        '[count_word]' => array(
                            'type' => 'number',
                            'number' => '0'
                        ),
                        '[minor_keyphrase]' => array(
                            'type' => 'string',
                            'string' => ''
                        )
                    )
                ),

                'success' => array(
                    'text' => $this->l('[link_support]: Great work!'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Related keyphrase in title (name)'),
                            'type' => 'link',
                            'key' => 'doc',
                            'link' => 'https://www.seozoom.co.uk/minor-keywords-how-to-optimize-a-text-with-search-intent-and-correlated-ones/'
                        ),
                    )
                ),
            ),
            'minor_keyphrase_in_desc' => array(
                'error' => array(
                    'text' => $this->l('[link_support]: The meta description does not contain the related keyphrase: "[minor_keyphrase]". [link_doc]'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Related keyphrase in meta description'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://www.seozoom.co.uk/minor-keywords-how-to-optimize-a-text-with-search-intent-and-correlated-ones/'
                        ),
                        '[link_doc]' => array(
                            'text' => $this->l('Fix that'),
                            'type' => 'link',
                            'key' => '',
                            'string' => 'https://www.seozoom.co.uk/minor-keywords-how-to-optimize-a-text-with-search-intent-and-correlated-ones/'
                        ),
                        '[minor_keyphrase]' => array(
                            'type' => 'string',
                            'string' => ''
                        )
                    )
                ),
                'over_limited' => array(
                    'text' => $this->l('[link_support]: The related keyphrase "[minor_keyphrase]" was found [count_word] time. That\'s more than the recommended minimum of 2 times for a text of this length. [link_doc]!'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Related keyphrases in meta description'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://www.seozoom.co.uk/minor-keywords-how-to-optimize-a-text-with-search-intent-and-correlated-ones/'
                        ),
                        '[link_doc]' => array(
                            'text' => $this->l('Fix that'),
                            'type' => 'link',
                            'key' => '',
                            'string' => 'https://www.seozoom.co.uk/minor-keywords-how-to-optimize-a-text-with-search-intent-and-correlated-ones/'
                        ),
                        '[minor_keyphrase]' => array(
                            'type' => 'string',
                            'string' => ''
                        ),
                        '[count_word]' => array(
                            'type' => 'number',
                            'number' => '0'
                        ),
                    )
                ),
                'success' => array(
                    'text' => $this->l('[link_support]: Great work!'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Related keyphrase in meta description'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://www.seozoom.co.uk/minor-keywords-how-to-optimize-a-text-with-search-intent-and-correlated-ones/'
                        ),
                    )
                ),
            ),
            'minor_keyphrase_acceptance' => array(
                'success' => array(
                    'text' => $this->l('[link_support]: Great work!'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Related keyphrase in title or meta title'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://www.seozoom.co.uk/minor-keywords-how-to-optimize-a-text-with-search-intent-and-correlated-ones/'
                        ),
                    )
                )
            )
        );
    }

    /**
     * readability_rules
     *
     * @return array
     */
    public function readability_rules()
    {
        return array(
            'not_enough_content' => array(
                'error' => array(
                    'text' => $this->l('[link_support]: [link_doc].'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Not enough content'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/not-enough-content-check/'
                        ),
                        '[link_doc]' => array(
                            'text' => $this->l('Please add some content to enable a good analysis'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/not-enough-content-check/'
                        ),
                    )
                ),
            ),
            'sentence_length' => array(
                'error' => array(
                    'text' => $this->l('[link_support]: [number]% of the sentences contain more than 20 words, which is more than the recommended maximum of 25%. [link_doc]!'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Sentence length'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/sentence-length-check/'
                        ),
                        '[link_doc]' => array(
                            'text' => $this->l('Try to shorten the sentences'),
                            'type' => 'link',
                            'key' => 'doc',
                            'link' => 'https://yoast.com/academy/seo-copywriting-training/sentence-length/'
                        ),
                        '[number]' => array(
                            'type' => 'number',
                            'number' => '0'
                        ),
                    )
                ),
                'success' => array(
                    'text' => $this->l('[link_support]: Great!'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Sentence length'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/sentence-length-check/'
                        ),
                    )
                )

            ),
            'flesch_reading_ease' => array(
                'error' => array(
                    'text' => $this->l('[link_support]: The copy scores [score] in the test, which is considered difficult to read. [link_doc].'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Flesch Reading Ease'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/flesch-reading-ease-check/'
                        ),
                        '[link_doc]' => array(
                            'text' => $this->l('Try to make shorter sentences to improve readability'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/flesch-reading-ease-check/'
                        ),
                        '[score]' => array(
                            'type' => 'number',
                            'number' => '0'
                        )
                    )
                ),
                'warning' => array(
                    'text' => $this->l('[link_support]: The copy scores [score] in the test, which is considered fairly difficult to read. [link_doc].'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Flesch Reading Ease'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/flesch-reading-ease-check/'
                        ),
                        '[link_doc]' => array(
                            'text' => $this->l('Try to make shorter sentences to improve readability'),
                            'type' => 'link',
                            'key' => 'doc',
                            'link' => 'https://yoast.com/academy/seo-copywriting-training/flesch-reading-ease/'
                        ),
                        '[score]' => array(
                            'type' => 'number',
                            'number' => '0'
                        )
                    )
                ),
                'success' => array(
                    'text' => $this->l('[link_support]: The copy scores [score] in the test, which is considered ok to read. Good job!.'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Flesch Reading Ease'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/flesch-reading-ease-check/'
                        ),
                        '[score]' => array(
                            'type' => 'number',
                            'number' => '0'
                        )
                    )
                ),
            ),
            'paragraph_length' => array(
                'error' => array(
                    'text' => $this->l('[link_support]: [number] of the paragraphs contains more than the recommended maximum of 150 words. [link_doc].'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Paragraph length'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/paragraph-length-check/'
                        ),
                        '[link_doc]' => array(
                            'text' => $this->l('Shorten your paragraphs.'),
                            'type' => 'link',
                            'key' => 'doc',
                            'link' => 'https://yoast.com/academy/seo-copywriting-training/paragraph-length/'
                        ),
                        '[number]' => array(
                            'type' => 'number',
                            'number' => '0'
                        )
                    )
                ),
                'warning' => array(
                    'text' => $this->l('[link_support]: [number] of the paragraphs contains more than the recommended maximum of 150 words. [link_doc].'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Paragraph length'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/paragraph-length-check/'
                        ),
                        '[link_doc]' => array(
                            'text' => $this->l('Shorten your paragraphs.'),
                            'type' => 'link',
                            'key' => 'doc',
                            'link' => 'https://yoast.com/academy/seo-copywriting-training/paragraph-length/'
                        ),
                        '[number]' => array(
                            'type' => 'number',
                            'number' => '0'
                        )
                    )
                ),

                'success' => array(
                    'text' => $this->l('[link_support]: None of the paragraphs are too long. Great job!.'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Paragraph length'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/paragraph-length-check/'
                        ),
                    )
                ),
            ),
            'passive_voice' => array(
                'success' => array(
                    'text' => $this->l('[link_support]: You\'re using enough active voice. That\'s great!'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Passive voice'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/passive-voice-check/'
                        ),
                    )
                ),
                'error' => array(
                    'text' => $this->l('[link_support]: [number]% of the sentences contain passive voice, which is more than the recommended maximum of 10% [link_doc].'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Passive voice'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/passive-voice-check/'
                        ),
                        '[link_doc]' => array(
                            'text' => $this->l('Try to use their active counterparts'),
                            'type' => 'link',
                            'key' => 'doc',
                            'link' => 'https://yoast.com/academy/seo-copywriting-training/passive-voice/'
                        ),
                        '[number]' => array(
                            'type' => 'number',
                            'number' => '0'
                        ),
                    )
                ),
            ),
            'consecutive_sentences' => array(
                'error' => array(
                    'text' => $this->l('[link_support]: The text contains [number] consecutive sentences starting with the same word. [link_doc]!'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Consecutive sentences'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/consecutive-sentences-check/'
                        ),
                        '[link_doc]' => array(
                            'text' => $this->l(' Try to mix things up'),
                            'type' => 'link',
                            'key' => 'doc',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/consecutive-sentences-check/'
                        ),
                        '[number]' => array(
                            'type' => 'number',
                            'number' => '0'
                        )

                    )
                ),
                'success' => array(
                    'text' => $this->l('[link_support]: There is enough variety in your sentences. That\'s great!'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Consecutive sentences'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/consecutive-sentences-check/'
                        ),

                    )
                ),
            ),
            'subheading_distribution' => array(
                'success' => array(
                    'text' => $this->l('[link_support]: You are not using any subheadings, but your text is short enough and probably doesn\'t need them'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Subheading distribution'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/subheading-distribution-check/'
                        ),

                    )
                ),
                'good' => array(
                    'text' => $this->l('[link_support]: Great job!'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Subheading distribution'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/subheading-distribution-check/'
                        ),

                    )
                ),
            ),
            'transition_words' => array(
                'error' => array(
                    'text' => $this->l('[link_support]: None of the sentences contain transition words. [link_doc].'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Transition words'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/transition-words-check/'
                        ),
                        '[link_doc]' => array(
                            'text' => $this->l('Use some'),
                            'type' => 'link',
                            'key' => 'doc',
                            'link' => 'https://yoast.com/academy/seo-copywriting-training/transition-words/'
                        ),
                    )
                ),
                'too_little' => array(
                    'text' => $this->l('[link_support]: Only [count] of the sentences contain them. This is not enough. [link_doc].'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Transition words'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/transition-words-check/'
                        ),
                        '[count]' => array(
                            'type' => 'number',
                            'number' => '0',
                        ),
                        '[link_doc]' => array(
                            'text' => $this->l('Use more transition words'),
                            'type' => 'link',
                            'key' => 'doc',
                            'link' => 'https://yoast.com/academy/seo-copywriting-training/transition-words/'
                        ),
                    )
                ),
                'little' => array(
                    'text' => $this->l('[link_support]: Only [count] of the sentences contain them. This is not enough. [link_doc].'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Transition words'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/transition-words-check/'
                        ),
                        '[count]' => array(
                            'type' => 'number',
                            'number' => '0',
                        ),
                        '[link_doc]' => array(
                            'text' => $this->l('Use more transition words'),
                            'type' => 'link',
                            'key' => 'doc',
                            'link' => 'https://yoast.com/academy/seo-copywriting-training/transition-words/'
                        ),
                    )
                ),
                'success' => array(
                    'text' => $this->l('[link_support]: Well done!'),
                    'short_code' => array(
                        '[link_support]' => array(
                            'text' => $this->l('Transition words'),
                            'type' => 'link',
                            'key' => '',
                            'link' => 'https://yoast.com/wordpress/plugins/seo/transition-words-check/'
                        ),
                    )
                ),
            )
        );
    }

    /**
     * analysis_types
     *
     * @return array
     */
    public function analysis_types()
    {
        //Should make key the same "type" of each item
        return array(
            'error' => array(
                'title' => $this->l('Problems'),
                'type' => 'error'
            ),
            'waring' => array(
                'title' => $this->l('Implements'),
                'type' => 'warning'
            ),
            'success' => array(
                'title' => $this->l('Good results'),
                'type' => 'success'
            ),
        );
    }


    /**
     * key_phrase_input
     *
     * @param  string $type : cms, product
     * @param  int $id
     * @param  object $context
     *
     * @return array
     */
    public function key_phrase_input($type, $id = null, $context = null)
    {
        $seo_data = array();
        if ($type == 'product' && $id) {
            $seo_data = EtsSeoProduct::getSeoProduct($id, $context);
        } elseif ($type == 'cms' && $id) {
            $seo_data = EtsSeoCms::getSeoCms($id, $context);
        } elseif ($type == 'meta' && $id) {
            $seo_data = EtsSeoMeta::getSeoMeta($id, $context);
        } elseif ($type == 'category' && $id) {
            $seo_data = EtsSeoCategory::getSeoCategory($id, $context);
        } elseif ($type == 'cms_category' && $id) {
            $seo_data = EtsSeoCmsCategory::getSeoCmsCategory($id, $context);
        } elseif ($type == 'manufacturer' && $id) {
            $seo_data = EtsSeoManufacturer::getSeoManufacturer($id, $context);
        } elseif ($type == 'supplier' && $id) {
            $seo_data = EtsSeoSupplier::getSeoSupplier($id, $context);
        }
        $key_phrase = array();
        $minor_key_phrase = array();
        $social_input = array();
        if ($seo_data) {
            foreach ($seo_data as $item) {
                $key_phrase[$item['id_lang']] = $item['key_phrase'];
                $minor_key_phrase[$item['id_lang']] = $item['minor_key_phrase'];
                $social_input['social_title'][$item['id_lang']] = $item['social_title'];
                $social_input['social_desc'][$item['id_lang']] = $item['social_desc'];
                $social_input['social_img'][$item['id_lang']] = $item['social_img'];

            }
        }

        return array(
            'focus_keyphrase' => array(
                'label' => $this->l('Focus keyphrase (keyword)'),
                'name' => 'key_phrase',
                'id' => 'ets_seo_focus_keyphrase',
                'value' => $key_phrase,
            ),
            'minor_keyphrase' => array(
                'label' => $this->l('Related keyphrases (keywords)'),
                'name' => 'minor_keyphrase',
                'id' => 'ets_seo_minor_keyphrase',
                'value' => $minor_key_phrase
            ),
            'social_title' => array(
                'label' => $this->l('Social title'),
                'name' => 'social_title',
                'id' => 'ets_seo_social_title',
                'value' => isset($social_input['social_title']) ? $social_input['social_title'] : array()
            ),
            'social_desc' => array(
                'label' => $this->l('Social description'),
                'name' => 'social_desc',
                'id' => 'ets_seo_social_desc',
                'value' => isset($social_input['social_desc']) ? $social_input['social_desc'] : array()
            ),
            'social_img' => array(
                'label' => $this->l('Social image'),
                'name' => 'social_img',
                'id' => 'ets_seo_social_img',
                'value' => isset($social_input['social_img']) ? $social_input['social_img'] : array(),
            ),
        );
    }


    /**
     * seo_advanced
     *
     * @param  string $type : product, cms, meta
     * @param  int $id
     * @param  object $context
     *
     * @return array
     */
    public function seo_advanced($type, $id, $context)
    {

        $seo_data = null;
        $config_allow_search = '';
        $indexLabel = $this->l('Allow search engines to show this Post in search results?');
        $followLabel = $this->l('Should search engines follow links on this Product?');
        $defaultIndexLabel = $this->l('Use default behavior');
        switch ($type) {
            case 'product':
                $indexLabel = $this->l('Allow search engines to show this Product page in search results?');
                $followLabel = $this->l('Should search engines follow links on this Product?');
                $defaultIndexLabel = $this->l('Use default behavior ');
                $config_allow_search = (int)Configuration::get('ETS_SEO_PROD_SHOW_IN_SEARCH_RESULT');
                if ((int)$id)
                    $seo_data = EtsSeoProduct::getSeoProduct($id, $context);
                break;
            case 'cms':
                $indexLabel = $this->l('Allow search engines to show this CMS page in search results?');
                $followLabel = $this->l('Should search engines follow links on this CMS?');
                $defaultIndexLabel = $this->l('Use default behavior');
                $config_allow_search = (int)Configuration::get('ETS_SEO_CMS_SHOW_IN_SEARCH_RESULT');
                if ((int)$id)
                    $seo_data = EtsSeoCms::getSeoCms($id, $context);
                break;
            case 'meta':
                $indexLabel = $this->l('Allow search engines to show this Meta page in search results?');
                $followLabel = $this->l('Should search engines follow links on this Meta?');
                $defaultIndexLabel = $this->l('Use default behavior');
                $config_allow_search = (int)Configuration::get('ETS_SEO_META_SHOW_IN_SEARCH_RESULT');
                if ((int)$id)
                    $seo_data = EtsSeoMeta::getSeoMeta($id, $context);
                break;
            case 'category':
                $indexLabel = $this->l('Allow search engines to show this Category page in search results?');
                $followLabel = $this->l('Should search engines follow links on this Category?');
                $defaultIndexLabel = $this->l('Use default behavior');
                $config_allow_search = (int)Configuration::get('ETS_SEO_CATEGORY_SHOW_IN_SEARCH_RESULT');
                if ((int)$id)
                    $seo_data = EtsSeoCategory::getSeoCategory($id, $context);
                break;
            case 'cms_category':
                $indexLabel = $this->l('Allow search engines to show this CMS category page in search results?');
                $followLabel = $this->l('Should search engines follow links on this CMS category?');
                $defaultIndexLabel = $this->l('Use default behavior');
                $config_allow_search = (int)Configuration::get('ETS_SEO_CMS_CATE_SHOW_IN_SEARCH_RESULT');
                if ((int)$id)
                    $seo_data = EtsSeoCmsCategory::getSeoCmsCategory($id, $context);
                break;
            case 'manufacturer':
                $indexLabel = $this->l('Allow search engines to show this Brand page in search results?');
                $followLabel = $this->l('Should search engines follow links on this Brand?');
                $defaultIndexLabel = $this->l('Use default behavior');
                $config_allow_search = (int)Configuration::get('ETS_SEO_MANUFACTURER_SHOW_IN_SEARCH_RESULT');
                if ((int)$id)
                    $seo_data = EtsSeoManufacturer::getSeoManufacturer($id, $context);
                break;
            case 'supplier':
                $indexLabel = $this->l('Allow search engines to show this Supplier page in search results?');
                $followLabel = $this->l('Should search engines follow links on this Supplier?');
                $defaultIndexLabel = $this->l('Use default behavior');
                $config_allow_search = (int)Configuration::get('ETS_SEO_SUPPLIER_SHOW_IN_SEARCH_RESULT');
                if ((int)$id)
                    $seo_data = EtsSeoSupplier::getSeoSupplier($id, $context);
                break;
        }
        $data = array(
            'allow_search' => array(),
            'allow_flw_link' => array(),
            'meta_robots_adv' => array(),
            'canonical_url' => array(),
        );
        if ($seo_data) {
            foreach ($seo_data as $seo) {
                foreach ($data as $key => $value) {
                    if (is_array($value)) {
                        $data[$key][$seo['id_lang']] = $seo[$key];
                    } elseif ($key == 'meta_robots_adv') {
                        $data[$key][$seo['id_lang']] = '';
                    }

                }
            }
        }
        $indexOptions = array(
            array(
                'label' => $defaultIndexLabel,
                'value' => 2,
                'default_option' => true,
                'suffix_label' => $config_allow_search ? $this->l('(Yes)') : $this->l('(No)')
            ),
            array(
                'label' => $this->l('Yes'),
                'value' => 1,
            ),
            array(
                'label' => $this->l('No'),
                'value' => 0,
            ),
        );
        if ($type == 'meta') {
            unset($indexOptions[0]);
        }
        return array(
            'allow_search' => array(
                'label' => $indexLabel,
                'id' => 'ets_seo_allow_search_engine_show_post',
                'type' => 'select',
                'config_value' => $config_allow_search,
                'selected' => $data['allow_search'],
                'link_default' => $this->context->link->getAdminLink('AdminEtsSeoSearchAppearanceContentType', true),
                //'default_selected' => $config_allow_search,
                'options' => $indexOptions
            ),
            'allow_flw_link' => array(
                'label' => $followLabel,
                'id' => 'ets_seo_allow_search_engine_follow_links',
                'type' => 'radio',
                'checked' => $data['allow_flw_link'],
                'options' => array(
                    array(
                        'label' => $this->l('Yes'),
                        'value' => 1,
                        'id' => 'ets_seo_allow_search_engine_follow_links_yes'
                    ),
                    array(
                        'label' => $this->l('No'),
                        'value' => 0,
                        'id' => 'ets_seo_allow_search_engine_follow_links_no'
                    ),
                )
            ),
            'meta_robots_adv' => array(
                'label' => $this->l('Meta robots advanced'),
                'id' => 'ets_seo_meta_robots_advanced',
                'type' => 'select2',
                'options' => array(
                    array(
                        'label' => $this->l('Site-wide default'),
                        'value' => '',
                    ),
                    array(
                        'label' => $this->l('None'),
                        'value' => 'none',
                    ),
                    array(
                        'label' => $this->l('No Image index'),
                        'value' => 'noimageindex',
                    ),
                    array(
                        'label' => $this->l('No Archive'),
                        'value' => 'noarchive',
                    ),
                    array(
                        'label' => $this->l('No Snippet'),
                        'value' => 'nosnippet',
                    ),
                ),
                'selected' => $data['meta_robots_adv'],
                'multiple' => true,
                'desc' => $this->l('Advanced meta robots settings for this page.')
            ),
            // 'bcb_title' => array(
            //     'label' => $this->l('Breadcrumbs Title'),
            //     'id' => 'ets_seo_breadcrumbs_title',
            //     'type' => 'input_text',
            //     'value' => $data['bcb_title'],
            //     'desc' => $this->l('Title to use for this page in breadcrumb paths.')
            // ),
            'canonical_url' => array(
                'label' => $this->l('Canonical URL'),
                'id' => 'ets_seo_canonical_url',
                'type' => 'input_text',
                'value' => $data['canonical_url'],
                'desc' => $this->l('The canonical URL that this page should point to. Leave empty to default to current page link. Cross domain canonical (Opens in a new browser tab) supported too..')
            ),
        );
    }

    public function transition_words()
    {
        return array(
            'en' => array(
                'illustration' => 'thus, for example, for instance, namely, to illustrate, in other words, in particular, specifically, such as',
                'contrast' => 'on the contrary, most importantly, contrarily, notwithstanding, but, however, nevertheless, in spite of, in contrast, yet, on one hand, on the other hand, rather, or, nor, conversely, at the same time, while this may be true',
                'addition' => 'and, in addition to, furthermore, moreover, besides, than, too, also, both-and, another, equally important, first, second, etc., again, further, last, finally, not only-but also, as well as, in the second place, next, likewise, similarly, in fact, as a result, consequently, in the same way, for example, for instance, however, thus, therefore, otherwise',
                'time' => 'after, afterward, before, then, once, next, last, at last, at length, first, second, etc., at first, formerly, rarely, usually, another, finally, soon, meanwhile, at the same time, for a minute, hour, day, etc., during the morning, day, week, etc., most important, later, ordinarily, to begin with, afterwards, generally, in order to, subsequently, previously, in the meantime, immediately, eventually, concurrently, simultaneously',
                'space' => 'at the left, at the right, in the center, on the side, along the edge, on top, below, beneath, under, around, above, over, straight ahead, at the top, at the bottom, surrounding, opposite, at the rear, at the front, in front of, beside, behind, next to, nearby, in the distance, beyond, in the forefront, in the foreground, within sight, out of sight, across, under, nearer, adjacent, in the background',
                'concession' => 'although, at any rate, at least, still, thought, even though, granted that, while it may be true, in spite of, of course',
                'similarity_or_comparison' => 'similarly, likewise, in like fashion, in like manner, analogous to',
                'emphasis' => 'above all, indeed, truly, of course, certainly, surely, in fact, really, in truth, again, besides, also, furthermore, in addition',
                'details' => 'specifically, especially, in particular, to explain, to list, to enumerate, in detail, namely, including',
                'examples' => 'for example, for instance, to illustrate, thus, in other words, as an illustration, in particular',
                'consequence_or_result' => 'so that, with the result that, thus, consequently, hence, accordingly, for this reason, therefore, so, because, since, due to, as a result, in other words, then',
                'summary' => 'therefore, finally, consequently, thus, in short, in conclusion, in brief, as a result, accordingly',
                'suggestion' => 'for this purpose, to this end, with this in mind, with this purpose in mind, therefore.',
            )
        );
    }


    /**
     * @return array
     * @throws PrestaShopException
     */
    public function get_menus()
    {
        return array(
            'AdminEtsSeoGeneralDashboard' => array(
                'title' => $this->l('Dashboard'),
                'origin' => 'Dashboard',
                'controller' => 'AdminEtsSeoGeneralDashboard',
                'link' => $this->context->link->getAdminLink('AdminEtsSeoGeneralDashboard', true),
                'icon' => 'code',
                'menu_icon' => 'menu-icon-dashboard',
                'has_sub' => true
            ),
            'AdminEtsSeoUrlAndRemoveId' => array(
                'title' => $this->l('SEO URLs'),
                'origin' => 'SEO URLs',
                'controller' => 'AdminEtsSeoUrlAndRemoveId',
                'link' => $this->context->link->getAdminLink('AdminEtsSeoUrlAndRemoveId', true),
                'icon' => 'code',
                'menu_icon' => 'menu-icon-seo-urls',
                'has_sub' => true
            ),
            'AdminEtsSeoDuplicateUrl' => array(
                'title' => $this->l('Check duplicate URLs'),
                'origin' => 'Check duplicate URLs',
                'controller' => 'AdminEtsSeoDuplicateUrl',
                'link' => $this->context->link->getAdminLink('AdminEtsSeoDuplicateUrl', true),
                'icon' => 'code',
                'menu_icon' => 'menu-icon-duplicate-url',
                'parent_controller' => 'AdminEtsSeoUrlAndRemoveId'
            ),
            'AdminEtsSeoUrlRedirect' => array(
                'title' => $this->l('URL redirects'),
                'origin' => 'URL redirects',
                'controller' => 'AdminEtsSeoUrlRedirect',
                'link' => $this->context->link->getAdminLink('AdminEtsSeoUrlRedirect', true),
                'icon' => 'code',
                'menu_icon' => 'menu-icon-redirect',
                'parent_controller' => 'AdminEtsSeoUrlAndRemoveId'
            ),
            'AdminEtsSeoSearchAppearanceSitemap' => array(
                'title' => $this->l('Sitemap'),
                'origin' => 'Sitemap',
                'controller' => 'AdminEtsSeoSearchAppearanceSitemap',
                'link' => $this->context->link->getAdminLink('AdminEtsSeoSearchAppearanceSitemap', true),
                'icon' => 'code',
                'menu_icon' => 'menu-icon-sitemap',
                'has_sub' => true
            ),
            'AdminEtsSeoSearchAppearanceRSS' => array(
                'title' => $this->l('RSS'),
                'origin' => 'RSS',
                'controller' => 'AdminEtsSeoSearchAppearanceRSS',
                'link' => $this->context->link->getAdminLink('AdminEtsSeoSearchAppearanceRSS', true),
                'icon' => 'code',
                'menu_icon' => 'menu-icon-rss',
                'has_sub' => true
            ),
            'AdminEtsSeoFileEditor' => array(
                'title' => $this->l('Robots.txt'),
                'origin' => 'Robots.txt',
                'controller' => 'AdminEtsSeoFileEditor',
                'link' => $this->context->link->getAdminLink('AdminEtsSeoFileEditor', true),
                'icon' => 'code',
                'menu_icon' => 'menu-icon-editor',
                'has_sub' => true
            ),
            'AdminEtsSeoRatingSnippet' => array(
                'title' => $this->l('Rating / snippet'),
                'origin' => 'Rating / snippet',
                'controller' => 'AdminEtsSeoRatingSnippet',
                'link' => $this->context->link->getAdminLink('AdminEtsSeoRatingSnippet', true),
                'icon' => 'star',
                'menu_icon' => 'menu-icon-rating-snippet',
                'has_sub' => true
            ),

            'AdminEtsSeoRating' => array(
                'title' => $this->l('Ratings'),
                'origin' => 'Ratings',
                'controller' => 'AdminEtsSeoRating',
                'link' => $this->context->link->getAdminLink('AdminEtsSeoRating', true),
                'icon' => 'star',
                'menu_icon' => 'menu-icon-rating',
                'parent_controller' => 'AdminEtsSeoRatingSnippet'
            ),
            'AdminEtsSeoBreadcrumb' => array(
                'title' => $this->l('Breadcrumbs'),
                'origin' => 'Breadcrumbs',
                'controller' => 'AdminEtsSeoBreadcrumb',
                'link' => $this->context->link->getAdminLink('AdminEtsSeoBreadcrumb', true),
                'icon' => 'code',
                'menu_icon' => 'menu-icon-breadcrumb',
                'parent_controller' => 'AdminEtsSeoRatingSnippet'
            ),
            'AdminEtsSeoAuthority' => array(
                'title' => $this->l('Authority'),
                'origin' => 'Authority',
                'controller' => 'AdminEtsSeoAuthority',
                'link' => $this->context->link->getAdminLink('AdminEtsSeoAuthority', true),
                'icon' => 'code',
                'menu_icon' => 'menu-icon-authority',
                'parent_controller' => 'AdminEtsSeoRatingSnippet'
            ),
            'AdminEtsSeoSearchAppearanceContentType' => array(
                'title' => $this->l('Meta templates'),
                'origin' => 'Meta templates',
                'controller' => 'AdminEtsSeoSearchAppearanceContentType',
                'link' => $this->context->link->getAdminLink('AdminEtsSeoSearchAppearanceContentType', true),
                'icon' => 'code',
                'menu_icon' => 'menu-icon-meta-template',
                'has_sub' => true
            ),
            'AdminEtsSeoSocial' => array(
                'title' => $this->l('Socials'),
                'origin' => 'Socials',
                'controller' => 'AdminEtsSeoSocial',
                'link' => $this->context->link->getAdminLink('AdminEtsSeoSocial', true),
                'icon' => 'code',
                'menu_icon' => 'menu-icon-social',
                'has_sub' => true
            ),

            /*'AdminEtsSeoGeneralTool'=> array(
                'title' => $this->l('Webmaster tools'),
                'controller' => 'AdminEtsSeoGeneralTool',
                'link' => $this->context->link->getAdminLink('AdminEtsSeoGeneralTool', true),
                'icon' => 'code',
                'menu_icon' => 'menu-icon-tools',
                'has_sub' => true
            ),*/
            'AdminEtsSeoTraffic' => array(
                'title' => $this->l('Traffic'),
                'origin' => 'Traffic',
                'controller' => 'AdminEtsSeoTraffic',
                'link' => $this->context->link->getAdminLink('AdminEtsSeoTraffic', true),
                'icon' => 'code',
                'menu_icon' => 'menu-icon-traffic',
                'has_sub' => true
            ),
            'AdminEtsSeoSettings' => array(
                'title' => $this->l('Settings'),
                'origin' => 'Settings',
                'controller' => 'AdminEtsSeoSettings',
                'link' => $this->context->link->getAdminLink('AdminEtsSeoSettings', true),
                'icon' => 'code',
                'menu_icon' => 'menu-icon-general-setting',
                'has_sub' => true,
            ),
            'AdminEtsSeoSearchAppearanceGeneral' => array(
                'title' => $this->l('General'),
                'origin' => 'General',
                'controller' => 'AdminEtsSeoSearchAppearanceGeneral',
                'link' => $this->context->link->getAdminLink('AdminEtsSeoSearchAppearanceGeneral', true),
                'icon' => 'code',
                'menu_icon' => 'menu-icon-setting',
                'parent_controller' => 'AdminEtsSeoSettings',
            ),
            'AdminEtsSeoImportExport' => array(
                'title' => $this->l('Backup'),
                'origin' => 'Backup',
                'controller' => 'AdminEtsSeoImportExport',
                'link' => $this->context->link->getAdminLink('AdminEtsSeoImportExport', true),
                'icon' => 'code',
                'menu_icon' => 'menu-icon-import-export',
                'parent_controller' => 'AdminEtsSeoSettings',
            ),

            'AdminEtsSeoSocialAccount' => array(
                'title' => $this->l('Social profiles'),
                'origin' => 'Social profiles',
                'controller' => 'AdminEtsSeoSocialAccount',
                'link' => $this->context->link->getAdminLink('AdminEtsSeoSocialAccount', true),
                'icon' => 'code',
                'menu_icon' => 'menu-icon-account',
                'parent_controller' => 'AdminEtsSeoSocial'
            ),
            'AdminEtsSeoSocialFacebook' => array(
                'title' => $this->l('Facebook'),
                'origin' => 'Facebook',
                'controller' => 'AdminEtsSeoSocialFacebook',
                'link' => $this->context->link->getAdminLink('AdminEtsSeoSocialFacebook', true),
                'icon' => 'code',
                'menu_icon' => 'menu-icon-facebook',
                'parent_controller' => 'AdminEtsSeoSocial'
            ),
            'AdminEtsSeoSocialTwitter' => array(
                'title' => $this->l('Twitter'),
                'origin' => 'Twitter',
                'controller' => 'AdminEtsSeoSocialTwitter',
                'link' => $this->context->link->getAdminLink('AdminEtsSeoSocialTwitter', true),
                'icon' => 'code',
                'menu_icon' => 'menu-icon-twitter',
                'parent_controller' => 'AdminEtsSeoSocial'
            ),
            'AdminEtsSeoSocialPinterest' => array(
                'title' => $this->l('Pinterest'),
                'origin' => 'Pinterest',
                'controller' => 'AdminEtsSeoSocialPinterest',
                'link' => $this->context->link->getAdminLink('AdminEtsSeoSocialPinterest', true),
                'icon' => 'code',
                'menu_icon' => 'menu-icon-pinterest',
                'parent_controller' => 'AdminEtsSeoSocial'
            ),

        );
    }

    /**
     * traffic_seo_tabs
     *
     * @return array
     */
    public function traffic_seo_tabs()
    {
        return ['AdminMeta', 'AdminSearchEngines', 'AdminReferrers'];
    }


    public function fields_config()
    {
        return array(
            'general_featured' => array(
                'ETS_SEO_ENABLE_ANALISYS' => array(
                    'title' => $this->l('SEO analysis'),
                    'hint' => $this->l('Hint'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'bool',
                    'default' => 1,
                    'no_multishop_checkbox' => true,

                ),
                'ETS_SEO_ENABLE_READABILITY' => array(
                    'title' => $this->l('Readability analysis'),
                    'hint' => $this->l('Readability analysis'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'bool',
                    'default' => 1,
                    'no_multishop_checkbox' => true,

                ),

            ),
            'ps_extra' => array(

                'ETS_SEO_ENABLE_REMOVE_ID_IN_URL' => array(
                    'title' => $this->l('Remove ID in URL'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'bool',
                    'default' => 0,
                    //'no_multishop_checkbox' => true,
                ),
                'ETS_SEO_ENABLE_REMOVE_LANG_CODE_IN_URL' => array(
                    'title' => $this->l('Remove ISO code in URL for default language'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'bool',
                    'default' => 0,
                    //'no_multishop_checkbox' => true,
                ),
                'ETS_SEO_ENABLE_REMOVE_ATTR_ALIAS' => array(
                    'title' => $this->l('Remove attribute alias in URL'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'bool',
                    'default' => 0,
                    //'no_multishop_checkbox' => true,
                ),
                'ETS_SEO_ENABLE_REDRECT_NOTFOUND' => array(
                    'title' => $this->l('Redirect to new url'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'bool',
                    'default' => 1,
                    //'no_multishop_checkbox' => true,
                ),
                'ETS_SEO_REDIRECT_STATUS_CODE' => array(
                    'title' => $this->l('Redirect type'),
                    'validation' => 'isInt',
                    'type' => 'select',
                    'identifier' => 'value',
                    'list' => array(
                        array(
                            'name' => $this->l('302 Moved Temporarily (recommended while setting up your store)'),
                            'value' => '302'
                        ),
                        array(
                            'name' => $this->l('301 Moved Permanently (recommended once you have gone live)'),
                            'value' => '301'
                        )
                    ),
                    'default' => '302',
                    //'no_multishop_checkbox' => true,
                ),
            ),
            'general_tool' => array(
                'ETS_SEO_GOOGLE_VERIFY_CODE' => array(
                    'title' => $this->l('Google verification code'),
                    'validation' => 'isString',
                    'type' => 'text',
                    'desc' => $this->l('Get your Google verification code in') . ' ' . $this->getLinkDesc('Google Search Console', 'https://www.google.com/webmasters/verification/verification'),
                    'no_multishop_checkbox' => true,
                ),
                'ETS_SEO_BING_VERIFY_CODE' => array(
                    'title' => $this->l('Bing verification code'),
                    'validation' => 'isString',
                    'type' => 'text',
                    'desc' => $this->l('Get your Bing verification code in') . ' ' . $this->getLinkDesc('Bing Webmaster Tools', 'https://www.bing.com/toolbox/webmaster/#/Dashboard/?url=' . $this->context->shop->getBaseURL(true, true)),
                    'no_multishop_checkbox' => true,
                ),
                'ETS_SEO_BAIDU_VERIFY_CODE' => array(
                    'title' => $this->l('Baidu verification code'),
                    'validation' => 'isString',
                    'type' => 'text',
                    'desc' => $this->l('Get your Baidu verification code in ') . ' ' . $this->getLinkDesc('Baidu Webmaster Tools', 'https://ziyuan.baidu.com/site/siteadd'),
                    'no_multishop_checkbox' => true,
                ),
                'ETS_SEO_YANDEX_VERIFY_CODE' => array(
                    'title' => $this->l('Yandex verification code'),
                    'validation' => 'isString',
                    'type' => 'text',
                    'desc' => $this->l('Get your Yandex verification code in') . ' ' . $this->getLinkDesc('Yandex Webmaster Tools', 'https://webmaster.yandex.com/sites/add/'),
                    'no_multishop_checkbox' => true,
                ),
                'ETS_SEO_VERIFIED_BY_USING_OTHER_METHODS' => array(
                    'title' => '',
                    'validation' => 'isInt',
                    'type' => 'checkbox',
                    'choices' => array(
                        1 => $this->l('I have verified my website using other verification methods')
                    ),
                    'no_multishop_checkbox' => true,
                ),


            ),
            'search_general_separator' => array(
                'ETS_SEO_TITLE_SEPARATOR' => array(
                    'title' => $this->l('Title separator'),
                    'type' => 'radio',
                    'validation' => 'isCleanHtml',
                    'choices' => array(
                        '-' => '-',
                        '–' => '–',
                        ':' => ':',
                        '·' => '·',
                        '•' => '•',
                        '*' => '*',
                        '⋆' => '⋆',
                        '|' => '|',
                        '~' => '~',
                        '«' => '«',
                        '»' => '»',
                        '&lt;' => '<',
                        '&gt;' => '>',
                    ),
                    'default' => '|',
                    'no_multishop_checkbox' => true,
                ),
                'ETS_SEO_ENABLE_AUTO_ANALYSIS' => array(
                    'title' => $this->l('Enable auto analysis'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'bool',
                    'default' => 1,
                    'no_multishop_checkbox' => true,
                ),
            ),
            'translation_setting' => array(
                'ETS_SEO_ENABLE_NEW_TRANS' => array(
                    'title' => $this->l('Use new translation system'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'bool',
                    'default' => 1,
                    'no_multishop_checkbox' => true,
                ),
            ),
            'search_general' => array(
                'ETS_SEO_SITE_OF_PERSON_OR_COMP' => array(
                    'title' => $this->l('Choose whether the site represents an organization or a person'),
                    'validation' => 'isString',
                    'type' => 'select',
                    'value' => 'COMPANY',
                    'default' => 'COMPANY',
                    'identifier' => 'value',
                    'list' => array(
                        array(
                            'name' => $this->l('Organization'),
                            'value' => 'COMPANY'
                        ),
                        array(
                            'name' => $this->l('Person'),
                            'value' => 'PERSON'
                        )
                    ),
                    'no_multishop_checkbox' => true,
                ),
                'ETS_SEO_SITE_ORIG_NAME' => array(
                    'title' => $this->l('Organization name'),
                    'validation' => 'isString',
                    'type' => 'text',
                    'default' => (string)Configuration::get('PS_SHOP_NAME'),
                    'no_multishop_checkbox' => true,
                ),
                'ETS_SEO_SITE_ORIG_LOGO' => array(
                    'title' => $this->l('Organization logo'),
                    'type' => 'file',
                    'name' => 'ETS_SEO_SITE_ORIG_LOGO',
                    'desc' => sprintf($this->l('Accepted formats: jpg, jpeg, png, gif. Limit: %sMb'), Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE')),
                    'no_multishop_checkbox' => true,
                ),
                'ETS_SEO_SITE_PERSON_NAME' => array(
                    'title' => $this->l('Name'),
                    'validation' => 'isString',
                    'type' => 'text',
                    'no_multishop_checkbox' => true,
                ),
                'ETS_SEO_SITE_PERSON_AVATAR' => array(
                    'title' => $this->l('Person logo / avatar'),
                    'type' => 'file',
                    'name' => 'ETS_SEO_SITE_PERSON_AVATAR',
                    'desc' => sprintf($this->l('Accepted formats: jpg, jpeg, png, gif. Limit: %sMb'), Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE')),
                    'no_multishop_checkbox' => true,
                ),

            ),

            'search_content_type_product' => array(
                'ETS_SEO_PROD_FORCE_USE_META_TEMPLATE' => array(
                    'title' => $this->l('Force to use meta template for product pages'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'bool',
                    'default' => 0,
                    'no_multishop_checkbox' => true,
                ),
                'ETS_SEO_PROD_SHOW_IN_SEARCH_RESULT' => array(
                    'title' => $this->l('Show Products in search results?'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'bool',
                    'default' => 1,
                    'no_multishop_checkbox' => true,
                ),

                'ETS_SEO_PROD_META_TILE' => array(
                    'title' => $this->l('Meta title'),
                    'validation' => 'isString',
                    'type' => 'textLang',
                    'default' => '',
                    'no_multishop_checkbox' => true,
                    'placeholder' => $this->l('Leave blank to use product name'),
                ),
                'ETS_SEO_PROD_META_DESC' => array(
                    'title' => $this->l('Meta description'),
                    'validation' => 'isString',
                    'type' => 'textareaLang',
                    'default' => '',
                    'no_multishop_checkbox' => true,
                    'placeholder' => $this->l('Leave blank to use product short description'),
                ),

            ),
            'search_content_type_cms' => array(
                'ETS_SEO_CMS_FORCE_USE_META_TEMPLATE' => array(
                    'title' => $this->l('Force to use meta template for cms pages'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'bool',
                    'default' => 0,
                    'no_multishop_checkbox' => true,
                ),
                'ETS_SEO_CMS_SHOW_IN_SEARCH_RESULT' => array(
                    'title' => $this->l('Show CMS pages in search results?'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'bool',
                    'default' => 1,
                    'no_multishop_checkbox' => true,
                ),

                'ETS_SEO_CMS_META_TILE' => array(
                    'title' => $this->l('Meta title'),
                    'validation' => 'isString',
                    'type' => 'textLang',
                    'default' => '',
                    'no_multishop_checkbox' => true,
                    'placeholder' => $this->l('Leave blank to use page title'),
                ),
                'ETS_SEO_CMS_META_DESC' => array(
                    'title' => $this->l('Meta description'),
                    'validation' => 'isString',
                    'type' => 'textareaLang',
                    'default' => '',
                    'no_multishop_checkbox' => true,
                    'placeholder' => $this->l('Leave blank to use page content'),
                ),
            ),
            'search_content_type_cms_cate' => array(
                'ETS_SEO_CMS_CATE_FORCE_USE_META_TEMPLATE' => array(
                    'title' => $this->l('Force to use meta template for CMS category pages'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'bool',
                    'default' => 0,
                    'no_multishop_checkbox' => true,
                ),
                'ETS_SEO_CMS_CATE_SHOW_IN_SEARCH_RESULT' => array(
                    'title' => $this->l('Show CMS category pages in search results?'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'bool',
                    'default' => 1,
                    'no_multishop_checkbox' => true,
                ),

                'ETS_SEO_CMS_CATE_META_TILE' => array(
                    'title' => $this->l('Meta title'),
                    'validation' => 'isString',
                    'type' => 'textLang',
                    'default' => '',
                    'no_multishop_checkbox' => true,
                    'placeholder' => $this->l('Leave blank to use CMS category name'),
                ),
                'ETS_SEO_CMS_CATE_META_DESC' => array(
                    'title' => $this->l('Meta description'),
                    'validation' => 'isString',
                    'type' => 'textareaLang',
                    'default' => '',
                    'no_multishop_checkbox' => true,
                    'placeholder' => $this->l('Leave blank to use CMS category description'),
                ),
            ),
            'search_content_type_category' => array(
                'ETS_SEO_CATEGORY_FORCE_USE_META_TEMPLATE' => array(
                    'title' => $this->l('Force to use meta template for category pages'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'bool',
                    'default' => 0,
                    'no_multishop_checkbox' => true,
                ),
                'ETS_SEO_CATEGORY_SHOW_IN_SEARCH_RESULT' => array(
                    'title' => $this->l('Show product category pages in search results?'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'bool',
                    'default' => 1,
                    'no_multishop_checkbox' => true,
                ),

                'ETS_SEO_CATEGORY_META_TILE' => array(
                    'title' => $this->l('Meta title'),
                    'validation' => 'isString',
                    'type' => 'textLang',
                    'default' => '',
                    'no_multishop_checkbox' => true,
                    'placeholder' => $this->l('Leave blank to use category name'),

                ),
                'ETS_SEO_CATEGORY_META_DESC' => array(
                    'title' => $this->l('Meta description'),
                    'validation' => 'isString',
                    'type' => 'textareaLang',
                    'default' => '',
                    'no_multishop_checkbox' => true,
                    'placeholder' => $this->l('Leave blank to use category description'),
                ),
            ),
            'search_content_type_manufacturer' => array(
                'ETS_SEO_MANUFACTURER_FORCE_USE_META_TEMPLATE' => array(
                    'title' => $this->l('Force to use meta template for brand (manufacturer) pages'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'bool',
                    'default' => 0,
                    'no_multishop_checkbox' => true,
                ),
                'ETS_SEO_MANUFACTURER_SHOW_IN_SEARCH_RESULT' => array(
                    'title' => $this->l('Show brand (manufacturer) pages in search results?'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'bool',
                    'default' => 1,
                    'no_multishop_checkbox' => true,
                ),
                'ETS_SEO_MANUFACTURER_META_TITLE' => array(
                    'title' => $this->l('Meta title'),
                    'validation' => 'isString',
                    'type' => 'textLang',
                    'default' => '',
                    'no_multishop_checkbox' => true,
                    'placeholder' => $this->l('Leave blank to use brand name'),
                ),
                'ETS_SEO_MANUFACTURER_META_DESC' => array(
                    'title' => $this->l('Meta description'),
                    'validation' => 'isString',
                    'type' => 'textareaLang',
                    'default' => '',
                    'no_multishop_checkbox' => true,
                    'placeholder' => $this->l('Leave blank to use brand short description (if brand short description is empty, long description will be used)'),
                ),
            ),
            'search_content_type_supplier' => array(
                'ETS_SEO_SUPPLIER_FORCE_USE_META_TEMPLATE' => array(
                    'title' => $this->l('Force to use meta template for supplier pages'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'bool',
                    'default' => 0,
                    'no_multishop_checkbox' => true,
                ),
                'ETS_SEO_SUPPLIER_SHOW_IN_SEARCH_RESULT' => array(
                    'title' => $this->l('Show supplier pages in search results?'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'bool',
                    'default' => 1,
                    'no_multishop_checkbox' => true,
                ),
                'ETS_SEO_SUPPLIER_META_TILE' => array(
                    'title' => $this->l('Meta title'),
                    'validation' => 'isString',
                    'type' => 'textLang',
                    'default' => '',
                    'no_multishop_checkbox' => true,
                    'placeholder' => $this->l('Leave blank to use supplier name'),
                ),
                'ETS_SEO_SUPPLIER_META_DESC' => array(
                    'title' => $this->l('Meta description'),
                    'validation' => 'isString',
                    'type' => 'textareaLang',
                    'default' => '',
                    'no_multishop_checkbox' => true,
                    'placeholder' => $this->l('Leave blank to use supplier description'),
                ),
            ),

            'breadcrumb_general' => array(
                'ETS_SEO_BREADCRUMB_ENABLED' => array(
                    'title' => $this->l('Enable breadcrumbs'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'bool',
                    'default' => 1,
                    'no_multishop_checkbox' => true,
                ),
                'ETS_SEO_BREADCRUMB_ANCHOR_TEXT_HOME' => array(
                    'title' => $this->l('Anchor text for the Homepage'),
                    'validation' => 'isString',
                    'type' => 'textLang',
                    'default' => 'Home',
                    'no_multishop_checkbox' => true,
                ),
                'ETS_SEO_BREADCRUMB_PREFIX_SEARCH' => array(
                    'title' => $this->l('Prefix for Search Page breadcrumbs'),
                    'validation' => 'isString',
                    'type' => 'textLang',
                    'default' => 'Search result for',
                    'no_multishop_checkbox' => true,
                ),
                'ETS_SEO_BREADCRUMB_404_PAGE' => array(
                    'title' => $this->l('Breadcrumb for 404 Page'),
                    'validation' => 'isString',
                    'type' => 'textLang',
                    'default' => 'Error 404: Page not found',
                    'no_multishop_checkbox' => true,
                ),
            ),
            'breadcrumb_types' => array(
                'ETS_SEO_BREADCRUMB_PRODUCT' => array(
                    'title' => $this->l('Middle node to product pages'),
                    'validation' => 'isString',
                    'type' => 'select',
                    'identifier' => 'value',
                    'list' => array(
                        array(
                            'name' => $this->l('None'),
                            'value' => ''
                        ),
                        array(
                            'name' => $this->l('Product category'),
                            'value' => 'category'
                        ),
                    ),
                    'default' => 'category',
                    'no_multishop_checkbox' => true,
                ),
                'ETS_SEO_BREADCRUMB_CMS' => array(
                    'title' => $this->l('Middle node to CMS pages'),
                    'validation' => 'isString',
                    'type' => 'select',
                    'identifier' => 'value',
                    'list' => array(
                        array(
                            'name' => $this->l('None'),
                            'value' => ''
                        ),
                        array(
                            'name' => $this->l('CMS category'),
                            'value' => 'category'
                        ),
                    ),
                    'default' => 'category',
                    'no_multishop_checkbox' => true,
                ),
            ),
            'rating' => array(
                'ETS_SEO_RATING_PAGES' => array(
                    'title' => $this->l('Enable forced ratings for: '),
                    'validation' => 'isString',
                    'type' => 'text',
                    'default' => 'product,cms,meta,category,cms_category,manufacturer,supplier',
                    'no_multishop_checkbox' => true,
                ),

            ),
            'rss_setting' => array(

                'ETS_SEO_RSS_ENABLE' => array(
                    'title' => $this->l('Enable RSS feed'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'bool',
                    'default' => 1,
                    'required' => true,
                    'no_multishop_checkbox' => true,
                ),
                'ETS_SEO_RSS_OPTION' => array(
                    'title' => $this->l('Pages to include in RSS'),
                    'validation' => 'isString',
                    'type' => 'text',
                    'default' => 'product_category,cms_category,all_products,new_products,special_products,popular_products',
                    'no_multishop_checkbox' => true,
                ),
                'ETS_SEO_RSS_CONTENT_BEFORE' => array(
                    'title' => $this->l('Content to put before each item in the feed'),
                    'validation' => 'isString',
                    'type' => 'textareaLang',
                    'rows' => 5,
                    'no_multishop_checkbox' => true,
                ),
                'ETS_SEO_RSS_CONTENT_AFTER' => array(
                    'title' => $this->l('Content to put after each item in the feed'),
                    'validation' => 'isString',
                    'type' => 'textareaLang',
                    'rows' => 5,
                    'no_multishop_checkbox' => true,
                ),
                'ETS_SEO_RSS_LINK' => array(
                    'title' => $this->l('RSS link(s)'),
                    'type' => 'text',
                    'no_multishop_checkbox' => true,
                ),
                'ETS_SEO_RSS_POST_LIMIT' => array(
                    'title' => $this->l('Item limit (the number of latest added items to display)'),
                    'validation' => 'isString',
                    'type' => 'text',
                    'default' => '100',
                    'desc' => $this->l('Leave blank to display all items (not recommended for large catalog)'),
                    'no_multishop_checkbox' => true,
                ),
            ),
            'sitemap_setting' => array(
                'ETS_SEO_ENABLE_XML_SITEMAP' => array(
                    'title' => $this->l('Enable sitemaps'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'bool',
                    'default' => 1,
                    'no_multishop_checkbox' => true,

                ),
                'ETS_SEO_SITEMAP_PRIMARY' => array(
                    'title' => $this->l('Primary sitemap'),
                    'type' => 'text',
                    'no_multishop_checkbox' => true,
                ),
                'ETS_SEO_SITEMAP_LANG' => array(
                    'title' => $this->l('Sitemap by languages'),
                    'type' => 'text',
                    'no_multishop_checkbox' => true,
                ),
                'ETS_SEO_SITEMAP_PRIORITY' => array(
                    'title' => $this->l('Priority / Change frequency'),
                    'validation' => 'isUnsignedFloat',
                    'type' => 'text',
                    'default' => 0.5,
                    'no_multishop_checkbox' => true,
                ),
                'ETS_SEO_SITEMAP_OPTION' => array(
                    'title' => $this->l('Pages to include in sitemap'),
                    'type' => 'text',
                    'required' => 1,
                    'default' => 'product,category,cms,cms_category,manufacturer,supplier,meta',
                    'no_multishop_checkbox' => true,
                ),
                'ETS_SEO_PROD_SITEMAP_LIMIT' => array(
                    'title' => $this->l('Number product per page in sitemap pagination'),
                    'validation' => 'isString',
                    'type' => 'text',
                    'default' => '1000',
                    'no_multishop_checkbox' => true,
                    'desc' => $this->l('Leave blank to include all products in one sitemap (not recommended for large catalog)'),
                ),
            ),
            'sitemap_value' => array(
                'ETS_SEO_SITEMAP_PRIORITY_PRODUCT' => array(
                    'title' => '',
                    'type' => 'text',
                    'default' => 0.9,
                    'no_multishop_checkbox' => true,
                ),
                'ETS_SEO_SITEMAP_PRIORITY_CATEGORY' => array(
                    'title' => '',
                    'type' => 'text',
                    'default' => 0.8,
                    'no_multishop_checkbox' => true,
                ),
                'ETS_SEO_SITEMAP_PRIORITY_CMS' => array(
                    'title' => '',
                    'type' => 'text',
                    'default' => 0.1,
                    'no_multishop_checkbox' => true,
                ),
                'ETS_SEO_SITEMAP_PRIORITY_CMS_CATEGORY' => array(
                    'title' => '',
                    'type' => 'text',
                    'default' => 0.1,
                    'no_multishop_checkbox' => true,
                ),
                'ETS_SEO_SITEMAP_PRIORITY_META' => array(
                    'title' => '',
                    'type' => 'text',
                    'default' => 0.1,
                    'no_multishop_checkbox' => true,
                ),
                'ETS_SEO_SITEMAP_PRIORITY_SUPPLIER' => array(
                    'title' => '',
                    'type' => 'text',
                    'default' => 0.1,
                    'no_multishop_checkbox' => true,
                ),
                'ETS_SEO_SITEMAP_PRIORITY_MANUFACTURER' => array(
                    'title' => '',
                    'type' => 'text',
                    'default' => 0.1,
                    'no_multishop_checkbox' => true,
                ),
            ),
            'sitemap_freq' => array(
                'ETS_SEO_SITEMAP_FREQ_PRODUCT' => array(
                    'title' => '',
                    'type' => 'text',
                    'default' => 'weekly',
                    'no_multishop_checkbox' => true,
                ),
                'ETS_SEO_SITEMAP_FREQ_CATEGORY' => array(
                    'title' => '',
                    'type' => 'text',
                    'default' => 'weekly',
                    'no_multishop_checkbox' => true,
                ),
                'ETS_SEO_SITEMAP_FREQ_CMS' => array(
                    'title' => '',
                    'type' => 'text',
                    'default' => 'weekly',
                    'no_multishop_checkbox' => true,
                ),
                'ETS_SEO_SITEMAP_FREQ_CMS_CATEGORY' => array(
                    'title' => '',
                    'type' => 'text',
                    'default' => 'weekly',
                    'no_multishop_checkbox' => true,
                ),
                'ETS_SEO_SITEMAP_FREQ_META' => array(
                    'title' => '',
                    'type' => 'text',
                    'default' => 'weekly',
                    'no_multishop_checkbox' => true,
                ),
                'ETS_SEO_SITEMAP_FREQ_SUPPLIER' => array(
                    'title' => '',
                    'type' => 'text',
                    'default' => 'weekly',
                    'no_multishop_checkbox' => true,
                ),
                'ETS_SEO_SITEMAP_FREQ_MANUFACTURER' => array(
                    'title' => '',
                    'type' => 'text',
                    'default' => 'weekly',
                    'no_multishop_checkbox' => true,
                ),
            ),
            'social_account' => array(
                'ETS_SEO_URL_FACEBOOK' => array(
                    'title' => $this->l('Facebook page URL'),
                    'validation' => 'isString',
                    'type' => 'text',
                    'no_multishop_checkbox' => true,
                ),
                'ETS_SEO_URL_TWITTER' => array(
                    'title' => $this->l('Twitter Username'),
                    'validation' => 'isString',
                    'type' => 'text',
                    'no_multishop_checkbox' => true,
                ),
                'ETS_SEO_URL_INSTA' => array(
                    'title' => $this->l('Instagram URL'),
                    'validation' => 'isString',
                    'type' => 'text',
                    'no_multishop_checkbox' => true,
                ),
                'ETS_SEO_URL_LINKEDIN' => array(
                    'title' => $this->l('LinkedIn URL'),
                    'validation' => 'isString',
                    'type' => 'text',
                    'no_multishop_checkbox' => true,
                ),
                'ETS_SEO_URL_MYSPACE' => array(
                    'title' => $this->l('Myspace URL'),
                    'validation' => 'isString',
                    'type' => 'text',
                    'no_multishop_checkbox' => true,
                ),
                'ETS_SEO_URL_PINTEREST' => array(
                    'title' => $this->l('Pinterest URL'),
                    'validation' => 'isString',
                    'type' => 'text',
                    'no_multishop_checkbox' => true,
                ),
                'ETS_SEO_URL_YOUTUBE' => array(
                    'title' => $this->l('YouTube URL'),
                    'validation' => 'isString',
                    'type' => 'text',
                    'no_multishop_checkbox' => true,
                ),
                'ETS_SEO_URL_WIKI' => array(
                    'title' => $this->l('Wikipedia URL'),
                    'validation' => 'isString',
                    'type' => 'text',
                    'no_multishop_checkbox' => true,
                ),
            ),
            'facebook_setting' => array(
                'ETS_SEO_FACEBOOK_ENABLE_OG' => array(
                    'title' => $this->l('Add Open Graph meta data'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'bool',
                    'default' => 1,
                    'desc' => $this->l('Enable this feature if you want Facebook and other social media to display a preview with images and a text excerpt when a link to your site is shared.'),
                    'no_multishop_checkbox' => true,
                ),
                'ETS_SEO_FACEBOOK_DEFULT_IMG_URL' => array(
                    'title' => $this->l('Image URL'),
                    'type' => 'file',
                    'name' => 'ETS_SEO_FACEBOOK_DEFULT_IMG_URL',
                    'no_multishop_checkbox' => true,
                    'desc' => $this->l('This image is used if the post/page being shared does not contain any images.').' '.sprintf($this->l('Accepted formats: jpg, jpeg, png, gif. Limit: %sMb'), Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE'))
                ),
            ),

            'twitter_setting' => array(
                'ETS_SEO_TWITTER_ENABLE_CARD_META' => array(
                    'title' => $this->l('Add Twitter card meta data'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'bool',
                    'default' => 1,
                    'desc' => $this->l('Enable this feature if you want Twitter to display a preview with images and a text excerpt when a link to your site is shared.'),
                    'no_multishop_checkbox' => true,
                ),
                'ETS_SEO_TWITTER_DEFAULT_CARD_TYPE' => array(
                    'title' => $this->l('The default card type to use'),
                    'validation' => 'isString',
                    'type' => 'select',
                    'identifier' => 'value',
                    'default' => 'summary_large_image',
                    'list' => array(
                        array(
                            'name' => $this->l('Summary'),
                            'value' => 'summary'
                        ),
                        array(
                            'name' => $this->l('Summary with large image'),
                            'value' => 'summary_large_image'
                        ),
                    ),
                    'no_multishop_checkbox' => true,
                ),
            ),
            'pinterest_setting' => array(
                'ETS_SEO_PINTEREST_CONFIRM' => array(
                    'title' => $this->l('Pinterest confirmation'),
                    'validation' => 'isString',
                    'type' => 'text',
                    'no_multishop_checkbox' => true,
                ),
            ),
            'search_console' => array(
                'ETS_SEO_GOOGLE_AUTH_CODE' => array(
                    'title' => $this->l('Enter your Google Authorization Code and press the Authenticate button.'),
                    'validation' => 'isString',
                    'type' => 'text',
                    'no_multishop_checkbox' => true,
                )
            ),
            'robot_txt' => array(
                'ETS_SEO_ROBOT_TXT' => array(
                    'title' => $this->l('Edit the content of your robots.txt'),
                    'validation' => 'isString',
                    'type' => 'textarea',
                    'rows' => 20,
                    'cols' => 20,
                    'no_multishop_checkbox' => true
                )
            ),
            'url_redirect_setting' => array(
                'ETS_SEO_ENABLE_URL_REDIRECT' => array(
                    'title' => $this->l('Enabled'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'bool',
                    'default' => 0,
                    'no_multishop_checkbox' => true,
                ),
            )
        );
    }

    public function rating_pages()
    {
        return array(
            array(
                'title' => $this->l('Product page'),
                'value' => 'product',
                'desc' => $this->l('Do not recommended if "Customer comments" module is installed')
            ),
            array(
                'title' => $this->l('Product category page'),
                'value' => 'category',
            ),
            array(
                'title' => $this->l('CMS page'),
                'value' => 'cms'
            ),
            array(
                'title' => $this->l('CMS category page'),
                'value' => 'cms_category',
            ),
            array(
                'title' => $this->l('Brand (Manufacturer) page'),
                'value' => 'manufacturer',
            ),
            array(
                'title' => $this->l('Supplier page'),
                'value' => 'supplier',
            ),
            array(
                'title' => $this->l('Other pages'),
                'value' => 'meta'
            ),
        );
    }

    public function installDb()
    {
        $tbl_seo_product = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "ets_seo_product` (
            `id_ets_seo_product` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `id_product` INT(10) UNSIGNED NOT NULL,
            `id_shop` INT(11) NOT NULL,
            `id_lang` INT(11) NOT NULL,
            `key_phrase` VARCHAR(191) DEFAULT NULL,
            `minor_key_phrase` VARCHAR(191) DEFAULT NULL,
            `allow_search` INT(1) UNSIGNED DEFAULT 2,
            `allow_flw_link` INT(1) UNSIGNED DEFAULT 1,
            `meta_robots_adv` VARCHAR(191) DEFAULT NULL,
            `meta_keywords` VARCHAR(191) DEFAULT NULL,
            `canonical_url` VARCHAR(191) DEFAULT NULL,
            `seo_score` INT(3) UNSIGNED  DEFAULT NULL,
            `readability_score` INT(3) UNSIGNED DEFAULT NULL,
            `score_analysis` TEXT DEFAULT NULL,
            `content_analysis` TEXT DEFAULT NULL,
            `social_title` VARCHAR(191) DEFAULT NULL,
            `social_desc` TEXT DEFAULT NULL,
            `social_img` VARCHAR(191) DEFAULT NULL,
            PRIMARY KEY (`id_ets_seo_product`),
            UNIQUE KEY `ets_seo_psl` (id_product, id_shop, id_lang)
        ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

        $tbl_seo_category = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "ets_seo_category` (
            `id_ets_seo_category` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `id_category` INT(10) UNSIGNED NOT NULL,
            `id_shop` INT(11) NOT NULL,
            `id_lang` INT(11) NOT NULL,
            `key_phrase` VARCHAR(191) DEFAULT NULL,
            `minor_key_phrase` VARCHAR(191) DEFAULT NULL,
            `allow_search` INT(1) UNSIGNED DEFAULT 2,
            `allow_flw_link` INT(1) UNSIGNED DEFAULT 1,
            `meta_robots_adv` VARCHAR(191) DEFAULT NULL,
            `meta_keywords` VARCHAR(191) DEFAULT NULL,
            `canonical_url` VARCHAR(191) DEFAULT NULL,
            `seo_score` INT(3) UNSIGNED  DEFAULT NULL,
            `readability_score` INT(3) UNSIGNED DEFAULT NULL,
            `score_analysis` TEXT DEFAULT NULL,
            `content_analysis` TEXT DEFAULT NULL,
            `social_title` VARCHAR(191) DEFAULT NULL,
            `social_desc` TEXT DEFAULT NULL,
            `social_img` VARCHAR(191) DEFAULT NULL,
            PRIMARY KEY (`id_ets_seo_category`),
            UNIQUE KEY `ets_seo_csl` (id_category, id_shop, id_lang)
        ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

        $tbl_seo_cms = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "ets_seo_cms` (
            `id_ets_seo_cms` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `id_cms` INT(10) UNSIGNED NOT NULL,
            `id_shop` INT(11) NOT NULL,
            `id_lang` INT(11) NOT NULL,
            `key_phrase` VARCHAR(191) DEFAULT NULL,
            `minor_key_phrase` VARCHAR(191) DEFAULT NULL,
            `allow_search` INT(1) UNSIGNED DEFAULT 2,
            `allow_flw_link` INT(1) UNSIGNED DEFAULT 1,
            `meta_robots_adv` VARCHAR(191) DEFAULT NULL,
            `meta_keywords` VARCHAR(191) DEFAULT NULL,
            `canonical_url` VARCHAR(191) DEFAULT NULL,
            `seo_score` INT(3) UNSIGNED DEFAULT NULL,
            `readability_score` INT(3) UNSIGNED DEFAULT NULL,
            `score_analysis` TEXT DEFAULT NULL,
            `content_analysis` TEXT DEFAULT NULL,
            `social_title` VARCHAR(191) DEFAULT NULL,
            `social_desc` TEXT DEFAULT NULL,
            `social_img` VARCHAR(191) DEFAULT NULL,
            PRIMARY KEY (`id_ets_seo_cms`),
            UNIQUE KEY `ets_seo_csl` (id_cms, id_shop, id_lang)
        ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

        $tbl_seo_cms_category = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "ets_seo_cms_category` (
            `id_ets_seo_cms_category` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `id_cms_category` INT(10) UNSIGNED NOT NULL,
            `id_shop` INT(11) NOT NULL,
            `id_lang` INT(11) NOT NULL,
            `key_phrase` VARCHAR(191) DEFAULT NULL,
            `minor_key_phrase` VARCHAR(191) DEFAULT NULL,
            `allow_search` INT(1) UNSIGNED DEFAULT 2,
            `allow_flw_link` INT(1) UNSIGNED DEFAULT 1,
            `meta_robots_adv` VARCHAR(191) DEFAULT NULL,
            `meta_keywords` VARCHAR(191) DEFAULT NULL,
            `canonical_url` VARCHAR(191) DEFAULT NULL,
            `seo_score` INT(3) UNSIGNED DEFAULT NULL,
            `readability_score` INT(3) UNSIGNED DEFAULT NULL,
            `score_analysis` TEXT DEFAULT NULL,
            `content_analysis` TEXT DEFAULT NULL,
            `social_title` VARCHAR(191) DEFAULT NULL,
            `social_desc` TEXT DEFAULT NULL,
            `social_img` VARCHAR(191) DEFAULT NULL,
            PRIMARY KEY (`id_ets_seo_cms_category`),
            UNIQUE KEY `ets_seo_ccsl` (id_cms_category, id_shop, id_lang)
        ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

        $tbl_seo_meta = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "ets_seo_meta` (
            `id_ets_seo_meta` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `id_meta` INT(10) UNSIGNED NOT NULL,
            `id_shop` INT(11) NOT NULL,
            `id_lang` INT(11) NOT NULL,
            `key_phrase` VARCHAR(191) DEFAULT NULL,
            `minor_key_phrase` VARCHAR(191) DEFAULT NULL,
            `allow_search` INT(1) UNSIGNED DEFAULT 2,
            `allow_flw_link` INT(1) UNSIGNED DEFAULT 1,
            `meta_robots_adv` VARCHAR(191) DEFAULT NULL,
            `meta_keywords` VARCHAR(191) DEFAULT NULL,
            `canonical_url` VARCHAR(191) DEFAULT NULL,
            `seo_score` INT(3) UNSIGNED DEFAULT NULL,
            `readability_score` INT(3) UNSIGNED DEFAULT NULL,
            `score_analysis` TEXT DEFAULT NULL,
            `content_analysis` TEXT DEFAULT NULL,
            `social_title` VARCHAR(191) DEFAULT NULL,
            `social_desc` TEXT DEFAULT NULL,
            `social_img` VARCHAR(191) DEFAULT NULL,
            PRIMARY KEY (`id_ets_seo_meta`),
            UNIQUE KEY `ets_seo_msl` (id_meta, id_shop, id_lang)
        ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

        $tbl_seo_supplier = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "ets_seo_supplier` (
            `id_ets_seo_supplier` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `id_supplier` INT(10) UNSIGNED NOT NULL,
            `id_shop` INT(11) NOT NULL,
            `id_lang` INT(11) NOT NULL,
            `key_phrase` VARCHAR(191) DEFAULT NULL,
            `minor_key_phrase` VARCHAR(191) DEFAULT NULL,
            `allow_search` INT(1) UNSIGNED DEFAULT 2,
            `allow_flw_link` INT(1) UNSIGNED DEFAULT 1,
            `meta_robots_adv` VARCHAR(191) DEFAULT NULL,
            `meta_keywords` VARCHAR(191) DEFAULT NULL,
            `canonical_url` VARCHAR(191) DEFAULT NULL,
            `seo_score` INT(3) UNSIGNED DEFAULT NULL,
            `readability_score` INT(3) UNSIGNED DEFAULT NULL,
            `score_analysis` TEXT DEFAULT NULL,
            `content_analysis` TEXT DEFAULT NULL,
            `social_title` VARCHAR(191) DEFAULT NULL,
            `social_desc` TEXT DEFAULT NULL,
            `social_img` VARCHAR(191) DEFAULT NULL,
            PRIMARY KEY (`id_ets_seo_supplier`),
            UNIQUE KEY `ets_seo_ssl` (id_supplier, id_shop, id_lang)
        ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

        $tbl_seo_manufacturer = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "ets_seo_manufacturer` (
            `id_ets_seo_manufacturer` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `id_manufacturer` INT(10) UNSIGNED NOT NULL,
            `id_shop` INT(11) NOT NULL,
            `id_lang` INT(11) NOT NULL,
            `key_phrase` VARCHAR(191) DEFAULT NULL,
            `minor_key_phrase` VARCHAR(191) DEFAULT NULL,
            `allow_search` INT(1) UNSIGNED DEFAULT 2,
            `allow_flw_link` INT(1) UNSIGNED DEFAULT 1,
            `meta_robots_adv` VARCHAR(191) DEFAULT NULL,
            `meta_keywords` VARCHAR(191) DEFAULT NULL,
            `canonical_url` VARCHAR(191) DEFAULT NULL,
            `seo_score` INT(3) UNSIGNED DEFAULT NULL,
            `readability_score` INT(3) UNSIGNED DEFAULT NULL,
            `score_analysis` TEXT DEFAULT NULL,
            `content_analysis` TEXT DEFAULT NULL,
            `social_title` VARCHAR(191) DEFAULT NULL,
            `social_desc` TEXT DEFAULT NULL,
            `social_img` VARCHAR(191) DEFAULT NULL,
            PRIMARY KEY (`id_ets_seo_manufacturer`),
            UNIQUE KEY `ets_seo_msl` (id_manufacturer, id_shop, id_lang)
        ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

        $tbl_seo_url_redirect = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "ets_seo_redirect` (
            `id_ets_seo_redirect` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(191) DEFAULT NULL,
            `url` VARCHAR(191) NOT NULL,
            `target` VARCHAR(191) NOT NULL,
            `type` ENUM('301', '302', '303', '404') DEFAULT NULL,
            `active` INT(1) DEFAULT 1,
            `id_shop` INT(11) NOT NULL,
            PRIMARY KEY (`id_ets_seo_redirect`),
            UNIQUE KEY `ets_seo_url` (url),
            INDEX (`active`,`id_shop`)
        ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

        $tbl_rating = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "ets_seo_rating` (
            `id_ets_seo_rating` INT(11) unsigned NOT NULL AUTO_INCREMENT,
            `page_type` VARCHAR(191) NOT NULL,
            `id_page` INT(10) unsigned NOT NULL,
            `enable` INT(1) NOT NULL,
            `average_rating` DOUBLE(4,2) NOT NULL,
            `best_rating` INT(1) DEFAULT NULL,
            `worst_rating` INT(1) DEFAULT NULL,
            `rating_count` INT(10) NOT NULL,
            `id_shop` INT(11) NOT NULL,
            PRIMARY KEY (`id_ets_seo_rating`),
            INDEX (`id_shop`,`enable`),
            UNIQUE KEY `ets_seo_type_id` (page_type, id_page)
        ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

        return Db::getInstance()->execute($tbl_seo_product)
            && Db::getInstance()->execute($tbl_seo_category)
            && Db::getInstance()->execute($tbl_seo_cms)
            && Db::getInstance()->execute($tbl_seo_cms_category)
            && Db::getInstance()->execute($tbl_seo_meta)
            && Db::getInstance()->execute($tbl_seo_manufacturer)
            && Db::getInstance()->execute($tbl_seo_supplier)
            && Db::getInstance()->execute($tbl_seo_url_redirect)
            && Db::getInstance()->execute($tbl_rating)
            && $this->createIndexColumnsData();
    }

    public function uninstallDb()
    {
        return Db::getInstance()->execute("DROP TABLE IF EXISTS `" . _DB_PREFIX_ . "ets_seo_product`")
            && Db::getInstance()->execute("DROP TABLE IF EXISTS `" . _DB_PREFIX_ . "ets_seo_category`")
            && Db::getInstance()->execute("DROP TABLE IF EXISTS `" . _DB_PREFIX_ . "ets_seo_cms`")
            && Db::getInstance()->execute("DROP TABLE IF EXISTS `" . _DB_PREFIX_ . "ets_seo_cms_category`")
            && Db::getInstance()->execute("DROP TABLE IF EXISTS `" . _DB_PREFIX_ . "ets_seo_meta`")
            && Db::getInstance()->execute("DROP TABLE IF EXISTS `" . _DB_PREFIX_ . "ets_seo_manufacturer`")
            && Db::getInstance()->execute("DROP TABLE IF EXISTS `" . _DB_PREFIX_ . "ets_seo_supplier`")
            && Db::getInstance()->execute("DROP TABLE IF EXISTS `" . _DB_PREFIX_ . "ets_seo_redirect`")
            && Db::getInstance()->execute("DROP TABLE IF EXISTS `" . _DB_PREFIX_ . "ets_seo_rating`")
            && $this->dropIndexColumnsData();
    }

    public function createIndexColumnsData()
    {
        return Db::getInstance()->execute("CREATE INDEX ets_seo_rr ON " . _DB_PREFIX_ . "product_lang(link_rewrite)")
            && Db::getInstance()->execute("CREATE INDEX ets_seo_rr ON " . _DB_PREFIX_ . "meta_lang(url_rewrite)")
            && Db::getInstance()->execute("CREATE INDEX ets_seo_rr ON " . _DB_PREFIX_ . "category_lang(link_rewrite)")
            && Db::getInstance()->execute("CREATE INDEX ets_seo_rr ON " . _DB_PREFIX_ . "cms_lang(link_rewrite)")
            && Db::getInstance()->execute("CREATE INDEX ets_seo_rr ON " . _DB_PREFIX_ . "cms_category_lang(link_rewrite)");
    }

    public function dropIndexColumnsData()
    {
        if (Db::getInstance()->executeS("SHOW KEYS FROM  `" . _DB_PREFIX_ . "product_lang` WHERE Key_name='ets_seo_rr'")) {
            Db::getInstance()->execute("DROP INDEX ets_seo_rr ON " . _DB_PREFIX_ . "product_lang");
        }
        if (Db::getInstance()->executeS("SHOW KEYS FROM  `" . _DB_PREFIX_ . "meta_lang` WHERE Key_name='ets_seo_rr'")) {
            Db::getInstance()->execute("DROP INDEX ets_seo_rr ON " . _DB_PREFIX_ . "meta_lang");
        }
        if (Db::getInstance()->executeS("SHOW KEYS FROM  `" . _DB_PREFIX_ . "category_lang` WHERE Key_name='ets_seo_rr'")) {
            Db::getInstance()->execute("DROP INDEX ets_seo_rr ON " . _DB_PREFIX_ . "category_lang");
        }

        if (Db::getInstance()->executeS("SHOW KEYS FROM  `" . _DB_PREFIX_ . "cms_lang` WHERE Key_name='ets_seo_rr'")) {
            Db::getInstance()->execute("DROP INDEX ets_seo_rr ON " . _DB_PREFIX_ . "cms_lang");
        }
        if (Db::getInstance()->executeS("SHOW KEYS FROM  `" . _DB_PREFIX_ . "cms_category_lang` WHERE Key_name='ets_seo_rr'")) {
            Db::getInstance()->execute("DROP INDEX ets_seo_rr ON " . _DB_PREFIX_ . "cms_category_lang");
        }
        return true;
    }

    /**
     * get_meta_codes
     *
     * @param  string $post_title
     *
     * @return array
     */
    public function get_meta_codes($type = null, $params = array())
    {

        $post_title = isset($params['post_title']) ? $params['post_title'] : '';
        if (isset($params['description'])) {
            $params['description'] = strip_tags($params['description']);
        }
        $items = array();
        switch ($type) {
            case 'product':
                $items = array(
                    '%product-name%' => array(
                        'title' => $this->l('Product name'),
                        'code' => '%product-name%',
                        'type' => 'title',
                        'value' => $post_title
                    ),
                    '%price%' => array(
                        'title' => $this->l('Price'),
                        'code' => '%price%',
                        'type' => 'price',
                        'value' => isset($params['price']) ? $params['price'] : '',
                    ),
                    '%discount-price%' => array(
                        'title' => $this->l('Discount price'),
                        'code' => '%discount-price%',
                        'type' => 'discount_price',
                        'value' => isset($params['discount_price']) ? $params['discount_price'] : '',
                    ),
                    '%brand%' => array(
                        'title' => $this->l('Brand'),
                        'code' => '%brand%',
                        'type' => 'brand',
                        'value' => isset($params['brand']) ? $params['brand'] : '',
                    ),
                    '%category%' => array(
                        'title' => $this->l('Product category'),
                        'code' => '%category%',
                        'type' => 'category',
                        'value' => isset($params['category']) ? $params['category'] : '',
                    ),
                );
                if (!isset($params['is_title']) || !$params['is_title']) {
                    $items['%summary%'] = array(
                        'title' => $this->l('Summary'),
                        'code' => '%summary%',
                        'type' => 'desc',
                        'value' => isset($params['description']) ? $params['description'] : '',
                    );
                }
                break;
            case 'category':
                if (!isset($params['is_title']) || !$params['is_title']) {
                    $items = array(
                        '%category-name%' => array(
                            'title' => $this->l('Category name'),
                            'code' => '%category-name%',
                            'type' => 'title',
                            'value' => $post_title
                        ),
                        '%description%' => array(
                            'title' => $this->l('Description'),
                            'code' => '%description%',
                            'type' => 'desc',
                            'value' => isset($params['description']) ? $params['description'] : '',
                        ),
                    );
                } else {
                    $items = array(
                        '%category-name%' => array(
                            'title' => $this->l('Category name'),
                            'code' => '%category-name%',
                            'type' => 'title',
                            'value' => $post_title
                        ),
                    );
                }

                break;
            case 'cms':
                $items = array(
                    '%cms-title%' => array(
                        'title' => $this->l('Title'),
                        'code' => '%cms-title%',
                        'type' => 'title',
                        'value' => $post_title
                    ),
                    '%cms-category%' => array(
                        'title' => $this->l('CMS category'),
                        'code' => '%cms-category%',
                        'type' => 'category',
                        'value' => isset($params['category']) ? $params['category'] : '',
                    ),

                );
                break;
            case 'cms_category':
                $items = array(
                    '%cms-category-title%' => array(
                        'title' => $this->l('Title'),
                        'code' => '%cms-category-title%',
                        'type' => 'title',
                        'value' => $post_title
                    ),
                );
                if (!isset($params['is_title']) || !$params['is_title']) {
                    $items['%description%'] = array(
                        'title' => $this->l('Description'),
                        'code' => '%description%',
                        'type' => 'desc',
                        'value' => isset($params['description']) ? $params['description'] : '',
                    );
                }
                break;
            case 'meta':
                $items = array(
                    '%title%' => array(
                        'title' => $this->l('Title'),
                        'code' => '%title%',
                        'type' => 'title',
                        'value' => $post_title
                    ),
                );
                break;
            case 'manufacturer':
                if (!isset($params['is_title']) || !$params['is_title']) {
                    $items = array(
                        '%brand-name%' => array(
                            'title' => $this->l('Brand  (manufacturer) name'),
                            'code' => '%brand-name%',
                            'type' => 'title',
                            'value' => $post_title
                        ),
                        '%short-description%' => array(
                            'title' => $this->l('Short description'),
                            'code' => '%short-description%',
                            'type' => 'desc',
                            'value' => isset($params['description']) ? $params['description'] : '',
                        ),
                        '%description%' => array(
                            'title' => $this->l('Description'),
                            'code' => '%description%',
                            'type' => 'desc2',
                            'value' => isset($params['description2']) ? $params['description2'] : '',
                        ),
                    );
                } else {
                    $items = array(
                        '%brand-name%' => array(
                            'title' => $this->l('Brand  (manufacturer) name'),
                            'code' => '%brand-name%',
                            'type' => 'title',
                            'value' => $post_title
                        ),
                    );
                }
                break;
            case 'supplier':
                if (!isset($params['is_title']) || !$params['is_title']) {
                    $items = array(
                        '%supplier-name%' => array(
                            'title' => $this->l('Supplier name'),
                            'code' => '%supplier-name%',
                            'type' => 'title',
                            'value' => $post_title
                        ),
                        '%description%' => array(
                            'title' => $this->l('Description'),
                            'code' => '%description%',
                            'type' => 'desc',
                            'value' => isset($params['description']) ? $params['description'] : '',
                        ),
                    );
                } else {
                    $items = array(
                        '%supplier-name%' => array(
                            'title' => $this->l('Supplier name'),
                            'code' => '%supplier-name%',
                            'type' => 'title',
                            'value' => $post_title
                        ),
                    );
                }
                break;
        }
        $short_codes = array(
            '%shop-name%' => array(
                'title' => $this->l('Shop name'),
                'code' => '%shop-name%',
                'value' => Configuration::get('PS_SHOP_NAME'),
            ),
            '%separator%' => array(
                'title' => $this->l('Separator'),
                'code' => '%separator%',
                'value' => html_entity_decode((string)Configuration::get('ETS_SEO_TITLE_SEPARATOR')),
            ),
        );
        if (($type == 'meta' || !$type) && (isset($params['is_title']) && $params['is_title'])) {
            //unset($short_codes['%name%']);
            return array();
        }
        $short_codes = array_merge($short_codes, $items);
        return $short_codes;
    }

    public function list_separators()
    {
        return array(
            'dash' => '-',
            'en_dash' => '–',
            'colon' => ':',
            'middle_dot' => '·',
            'bullet' => '•',
            'star' => '*',
            'big_star' => '⋆',
            'vbar' => '|',
            'tilde' => '~',
            'left_angle' => '«',
            'right_angle' => '»',
            'less_than' => '&lt;',
            'greater_than' => '&gt;',
        );
    }

    public function url_rules()
    {
        return array(
            'category_rule' => array(
                'rule' => $this->getConfigRule('category_rule', '{id}-{rewrite}'),
                'new_rule' => $this->getConfigRule('category_rule', '{rewrite}', true),
                'desc_rule' => $this->l('Keywords: id* , rewrite , meta_keywords , meta_title'),
                'desc_new_rule' => $this->l('Keywords: id , rewrite* , meta_keywords'),
            ),
            'supplier_rule' => array(
                'rule' => $this->getConfigRule('supplier_rule', 'supplier/{id}-{rewrite}'),
                'new_rule' => $this->getConfigRule('supplier_rule', 'supplier/{rewrite}', true),
                'desc_rule' => $this->l('Keywords: id* , rewrite , meta_keywords , meta_title'),
                'desc_new_rule' => $this->l('Keywords: id , rewrite* , meta_keywords '),
            ),
            'manufacturer_rule' => array(
                'rule' => $this->getConfigRule('manufacturer_rule', 'brand/{id}-{rewrite}'),
                'new_rule' => $this->getConfigRule('manufacturer_rule', 'brand/{rewrite}', true),
                'desc_rule' => $this->l('Keywords: id* , rewrite , meta_keywords , meta_title'),
                'desc_new_rule' => $this->l('Keywords: id , rewrite* , meta_keywords'),
            ),
            'cms_rule' => array(
                'rule' => $this->getConfigRule('cms_rule', 'content/{id}-{rewrite}'),
                'new_rule' => $this->getConfigRule('cms_rule', 'content/{rewrite}', true),
                'desc_rule' => $this->l('Keywords: id* , rewrite , meta_keywords , meta_title'),
                'desc_new_rule' => $this->l('Keywords: id , rewrite* , meta_keywords'),
            ),
            'cms_category_rule' => array(
                'rule' => $this->getConfigRule('cms_category_rule', 'content/category/{id}-{rewrite}'),
                'new_rule' => $this->getConfigRule('cms_category_rule', 'content/category/{rewrite}', true),
                'desc_rule' => $this->l('Keywords: id* , rewrite , meta_keywords , meta_title'),
                'desc_new_rule' => $this->l('Keywords: id , rewrite* , meta_keywords'),
            ),
            'module' => array(
                'rule' => $this->getConfigRule('module', 'module/{module}{/:controller}'),
                'new_rule' => $this->getConfigRule('module', 'module/{module}{/:controller}', true),
                'desc_rule' => $this->l('Keywords: module* , controller*'),
                'desc_new_rule' => $this->l('Keywords: module* , controller*'),
            ),
            'product_rule' => array(
                'rule' => $this->getConfigRule('product_rule', '{category:/}{id}{-:id_product_attribute}-{rewrite}{-:ean13}.html'),
                'new_rule' => $this->getConfigRule('product_rule', '{category}/{rewrite}', true),
                'desc_rule' => $this->l('Keywords: id* , id_product_attribute* , rewrite* , ean13 , category , categories , reference , meta_keywords , meta_title , manufacturer , supplier , price , tags'),
                'desc_new_rule' => $this->l('Keywords: id , id_product_attribute , rewrite* , ean13 , category, categories , reference , meta_keywords , manufacturer , supplier , price , tags'),
            ),
            /* Must be after the product and category rules in order to avoid conflict */
            'layered_rule' => array(
                'rule' => $this->getConfigRule('layered_rule', '{id}-{rewrite}{/:selected_filters}'),
                'new_rule' => $this->getConfigRule('layered_rule', '{rewrite}/filter/{selected_filters}', true),
                'desc_rule' => $this->l('Keywords: id* , selected_filters* , rewrite , meta_keywords , meta_title'),
                'desc_new_rule' => $this->l('Keywords: id , selected_filters* , rewrite* , meta_keywords'),
            ),
        );
    }

    public function seo_url_schema_configs()
    {
        return array(
            'category_rule' => array(
                'root_name' => 'ETS_SEO_ROOT_URL_CATEGORY_RULE',
                'old_name' => 'ETS_SEO_OLD_URL_CATEGORY_RULE',
                'name' => 'ETS_SEO_URL_CATEGORY_RULE',
                'no_id' => 'ETS_SEO_URL_NOID_CATEGORY_RULE',
                'default' => '{id}-{rewrite}'
            ),
            'supplier_rule' => array(
                'root_name' => 'ETS_SEO_ROOT_URL_SUPPLIER_RULE',
                'old_name' => 'ETS_SEO_OLD_URL_SUPPLIER_RULE',
                'name' => 'ETS_SEO_URL_SUPPLIER_RULE',
                'no_id' => 'ETS_SEO_URL_NOID_SUPPLIER_RULE',
                'default' => 'supplier/{id}-{rewrite}'
            ),
            'manufacturer_rule' => array(
                'root_name' => 'ETS_SEO_ROOT_URL_MANUF_RULE',
                'old_name' => 'ETS_SEO_OLD_URL_MANUF_RULE',
                'name' => 'ETS_SEO_URL_MANUF_RULE',
                'no_id' => 'ETS_SEO_URL_NOID_MANUF_RULE',
                'default' => 'brand/{id}-{rewrite}'
            ),
            'cms_rule' => array(
                'root_name' => 'ETS_SEO_ROOT_URL_CMS_RULE',
                'old_name' => 'ETS_SEO_OLD_URL_CMS_RULE',
                'name' => 'ETS_SEO_URL_CMS_RULE',
                'no_id' => 'ETS_SEO_URL_NOID_CMS_RULE',
                'default' => 'content/{id}-{rewrite}'
            ),
            'cms_category_rule' => array(
                'root_name' => 'ETS_SEO_ROOT_URL_CMS_CATEGORY_RULE',
                'old_name' => 'ETS_SEO_OLD_URL_CMS_CATEGORY_RULE',
                'name' => 'ETS_SEO_URL_CMS_CATEGORY_RULE',
                'no_id' => 'ETS_SEO_URL_NOID_CMS_CATEGORY_RULE',
                'default' => 'content/category/{id}-{rewrite}'
            ),
            'module' => array(
                'root_name' => 'ETS_SEO_ROOT_URL_MODULE_RULE',
                'old_name' => 'ETS_SEO_OLD_URL_MODULE_RULE',
                'name' => 'ETS_SEO_URL_MODULE_RULE',
                'no_id' => 'ETS_SEO_URL_NOID_MODULE_RULE',
                'default' => 'module/{module}{/:controller}'
            ),
            'product_rule' => array(
                'root_name' => 'ETS_SEO_ROOT_URL_PRODUCT_RULE',
                'old_name' => 'ETS_SEO_OLD_URL_PRODUCT_RULE',
                'name' => 'ETS_SEO_URL_PRODUCT_RULE',
                'no_id' => 'ETS_SEO_URL_NOID_PRODUCT_RULE',
                'default' => '{category:/}{id}{-:id_product_attribute}-{rewrite}{-:ean13}.html'
            ),
            'layered_rule' => array(
                'root_name' => 'ETS_SEO_ROOT_URL_LAYERED_RULE',
                'old_name' => 'ETS_SEO_OLD_URL_LAYERED_RULE',
                'name' => 'ETS_SEO_URL_LAYERED_RULE',
                'no_id' => 'ETS_SEO_URL_NOID_LAYERED_RULE',
                'default' => '{id}-{rewrite}{/:selected_filters}'
            ),
        );
    }

    public function rewrite_noid_rules()
    {
        return array(
            'category_rule' => array(
                'number_param' => 1,
                'allow' => array('rewrite'),
                'keywords' => array('rewrite', 'id', 'meta_keywords', 'meta_title'),
            ),
            'supplier_rule' => array(
                'number_param' => 2,
                'allow' => array('rewrite'),
                'keywords' => array('rewrite', 'id', 'meta_keywords', 'meta_title'),
            ),
            'manufacturer_rule' => array(
                'number_param' => 2,
                'allow' => array('rewrite'),
                'keywords' => array('rewrite', 'id', 'meta_keywords', 'meta_title'),
            ),
            'cms_rule' => array(
                'number_param' => 2,
                'allow' => array('rewrite'),
                'keywords' => array('rewrite', 'id', 'meta_keywords', 'meta_title'),
            ),
            'cms_category_rule' => array(
                'number_param' => 3,
                'allow' => array('rewrite'),
                'keywords' => array('rewrite', 'id', 'meta_keywords', 'meta_title'),
            ),
            'module' => array(
                'number_param' => 1,
                'allow' => array('module', 'controller'),
                'keywords' => array('module', 'controller'),
            ),
            'product_rule' => array(
                'number_param' => 2,
                'allow' => array('category', 'rewrite'),
                'keywords' => array('id', 'category', 'rewrite', 'id_product_attribute',
                    'categories', 'reference', 'meta_keywords', 'meta_title', 'manufacturer', 'supplier', 'price', 'tags'),
            ),

            'layered_rule' => array(
                'number_param' => 3,
                'allow' => array('rewrite', 'selected_filters'),
                'keywords' => array('rewrite', 'selected_filters', 'meta_keywords', 'meta_title')
            ),
        );
    }

    public function getConfigRule($rule, $default = null, $no_id = false)
    {

        $config = $this->seo_url_schema_configs();
        if ($no_id) {
            return ($data = Configuration::get($config[$rule]['no_id'])) && (($rule !== 'module' && !preg_match('/\{id\}/', $data)) || $rule == 'module') ? Configuration::get($config[$rule]['no_id']) : $default;
        }

        if (($nearest = Configuration::get($config[$rule]['name'])) && (($rule !== 'module' && preg_match('/\{id\}/', $nearest)) || $rule == 'module')) {
            return $nearest;
        } elseif (($old = Configuration::get($config[$rule]['old_name'])) && (($rule !== 'module' && preg_match('/\{id\}/', $old)) || $rule == 'module')) {
            return $old;
        } elseif (($root = Configuration::get($config[$rule]['root_name'])) && (($rule !== 'module' && preg_match('/\{id\}/', $root)) || $rule == 'module')) {
            return $root;
        }
        return $default;
    }

    public function translateMessages()
    {
        return array(
            'avg_rating_required' => $this->l('The average rating is required.'),
            'avg_rating_decimal' => $this->l('The Average rating must be a decimal.'),
            'avg_rating_invalid' => $this->l('The average rating is invalid.'),
            'best_rating_integer' => $this->l('The Best rating must be an integer.'),
            'best_rating_invalid' => $this->l('The Best rating is invalid.'),
            'best_rating_greater_than_avg' => $this->l('The Best rating must be greater than or equal to the average rating.'),
            'worst_rating_integer' => $this->l('The Worst rating must be an integer.'),
            'worst_rating_invalid' => $this->l('The Worst rating is invalid.'),
            'worst_rating_less_than_avg' => $this->l('The Worst rating must be less than or equal to the average rating.'),
            'rating_count_required' => $this->l('The rating count is required.'),
            'rating_count_integer' => $this->l('The rating count must be an integer.'),
        );
    }

    public function listControllerAction()
    {
        return array(
            'AdminCmsContent',
            'AdminMeta',
            'AdminCategories',
            'AdminCmsCategories',
            'AdminManufacturers',
            'AdminSuppliers',
            'AdminProducts',
            'AdminReferrers',
            'AdminSearchEngines'
        );
    }

    public function getLinkDesc($title, $link)
    {
        $this->context->smarty->assign(array(
            'desc_title' => $title,
            'desc_link' => $link
        ));
        return $this->display('components/link_desc.tpl');
    }

    public function getPlaceholderPage($controller, $isCmsCate = false)
    {
        $title = '';
        $desc = '';
        switch ($controller) {
            case 'AdminProducts':
                $title = $this->l('To have a different title from the product name, enter it here.');
                $desc = $this->l('To have a different description than your product summary in search results pages, write it here.');
                break;
            case 'AdminCategories':
                $title = $this->l('To have a different title from the category name, enter it here.');
                $desc = $this->l('To have a different description than your category description in search results pages, write it here.');
                break;
            case 'AdminCmsContent':
                if ($isCmsCate) {
                    $title = $this->l('To have a different title from the CMS category name, enter it here.');
                    $desc = $this->l('To have a different description than your CMS category description in search results pages, write it here.');
                } else {
                    $title = $this->l('To have a different title from the CMS title, enter it here.');
                    $desc = $this->l('To have a different description than your CMS description in search results pages, write it here.');
                }
                break;
            case 'AdminManufacturers':
                $title = $this->l('To have a different title from the brand name, enter it here.');
                $desc = $this->l('To have a different description than your brand short description in search results pages, write it here.');
                break;
            case 'AdminSuppliers':
                $title = $this->l('To have a different title from the supplier name, enter it here.');
                $desc = $this->l('To have a different description than your supplier description in search results pages, write it here.');
                break;
        }

        return array(
            'title' => $title,
            'desc' => $desc
        );
    }
    public static function getTextLang($text, $lang,$file_name='')
    {
        $moduleName = 'ets_seo';
        $text2 = preg_replace("/\\\*'/", "\'", $text);
        if(is_array($lang))
            $iso_code = $lang['iso_code'];
        elseif(is_object($lang))
            $iso_code = $lang->iso_code;
        else
        {
            $language = new Language($lang);
            $iso_code = $language->iso_code;
        }
        $modulePath = rtrim(_PS_MODULE_DIR_, '/').'/'.$moduleName;
        $fileTransDir = $modulePath.'/translations/'.$iso_code.'.'.'php';
        if(!@file_exists($fileTransDir)){
            return '';
        }
        $fileContent = Tools::file_get_contents($fileTransDir);
        $strMd5 = md5($text2);
        $keyMd5 = '<{' . $moduleName . '}prestashop>' .($file_name ? Tools::strtolower($file_name) : $moduleName). '_' . $strMd5;
        preg_match('/(\$_MODULE\[\'' . preg_quote($keyMd5) . '\'\]\s*=\s*\')(.*)(\';)/', $fileContent, $matches);
        if($matches && isset($matches[2])){
            return  str_replace("\'", "'", $matches[2]);
        }
        return '';
    }
}