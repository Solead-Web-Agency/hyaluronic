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
 *  @author ETS-Soft <etssoft.jsc@gmail.com>
 *  @copyright  2007-2021 ETS-Soft
 *  @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of ETS-Soft
 */

if (!defined('_PS_VERSION_'))
    exit;

class AdminEtsSeoRatingController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();

        $seoDef = Ets_Seo_Define::getInstance();
        $this->fields_options = array(
            'rating' => array(
                'title' => $this->l('Ratings'),
                'fields' => $seoDef->fields_config()['rating'],
                'icon'=> '',
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
                'description' => $this->l('Forced ratings allow you to manually specify the number of rating stars displayed on Google search result pages. Once enabled, you can enter the ratings you want when editing products, category, CMS pages, etc. You are always recommended to use real ratings from your customers to avoid spam penalties from search engines'),
            ),
        );
        if (!Module::isEnabled('ets_seo'))
        {
            $this->warnings[] = $this->l('You must enable module SEO Audit to configure its features');
        }
    }

    public function renderOptions()
    {
        $seoDef = Ets_Seo_Define::getInstance();
        $this->context->smarty->assign(array(
            'ets_seo_rating_pages' => $seoDef->rating_pages(),
            'ETS_SEO_RATING_PAGES' => explode(',', Configuration::get('ETS_SEO_RATING_PAGES'))
        ));
        return parent::renderOptions();
    }

    public function postProcess()
    {

        if(Tools::isSubmit('submitOptionsconfiguration'))
        {
            if((int)Tools::getValue('ETS_SEO_RATING_ENABLED') == 1)
            {
                $avgRating = Tools::getValue('ETS_SEO_RATING_AVG');
                $countRating = Tools::getValue('ETS_SEO_RATING_COUNT');
                $bestRating = Tools::getValue('ETS_SEO_RATING_BEST');
                $worstRating = Tools::getValue('ETS_SEO_RATING_WORST');
                if(!$avgRating)
                {
                    $this->errors[] = $this->l('The Average rating is required.');
                }
                elseif(!Validate::isUnsignedFloat($avgRating))
                {
                    $this->errors[] = $this->l('The Average rating must be a decimal.');
                }
                elseif((float)$avgRating <= 0 && (float)$avgRating > 5)
                {
                    $this->errors[] = $this->l('The Average rating is invalid.');
                }
                else{
                    if($bestRating || $bestRating == 0) {
                        if (!Validate::isUnsignedInt($bestRating)) {
                            $this->errors[] = $this->l('The Best rating must be a integer.');
                        }
                        elseif((int)$worstRating > 5)
                        {
                            $this->errors[] = $this->l('The Best rating is invalid.');
                        }
                        elseif ((int)$bestRating < (float)$avgRating)
                        {
                            $this->errors[] = $this->l('The Best rating must be greater than or equal to the average rating.');
                        }
                    }
                    if($worstRating || $worstRating == 0) {
                        if (!Validate::isUnsignedInt($worstRating)) {
                            $this->errors[] = $this->l('The Worst rating must be a integer.');
                        }
                        elseif((int)$worstRating <= 0)
                        {
                            $this->errors[] = $this->l('The Worst rating is invalid.');
                        }
                        elseif ((int)$worstRating > (float)$avgRating)
                        {
                            $this->errors[] = $this->l('The Worst rating must be less than or equal to the average rating.');
                        }
                    }
                }

                if(!$countRating){
                    $this->errors[] = $this->l('The rating count is required.');
                }
                elseif(!Validate::isUnsignedInt($countRating))
                {
                    $this->errors[] = $this->l('The rating count must be an integer.');
                }
                elseif($countRating <= 0)
                {
                    $this->errors[] = $this->l('The rating count is invalid.');
                }
            }
            $_POST['ETS_SEO_RATING_PAGES'] = ($ratingPages = Tools::getValue('ETS_SEO_RATING_PAGES')) && is_array($ratingPages) ? implode(',' , $ratingPages) : '';
        }
        return parent::postProcess();
    }

}