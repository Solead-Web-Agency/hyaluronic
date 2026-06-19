<?php

class deleteordersfree extends Module
{
    function __construct()
    {
        $this->name = 'deleteordersfree';
        $this->author = 'MyPresta.eu';
        $this->tab = 'Other';
        $this->version = '1.5.3';
        $this->module_key = '';
        $this->dir = '/modules/deleteordersfree/';
        $this->mypresta_link = 'https://mypresta.eu/modules/administration-tools/delete-orders-free.html';
        $this->bootstrap = 1;
        parent::__construct();
        $this->displayName = $this->l('Delete Orders Free');
        $this->description = $this->l('Delete Orders Free is the best module for deleting orders. This version is free. Developed by MyPresta.eu');
        $this->tab = 'Admin';
        $this->tabClassName = 'deleteorderstab';
        $this->tabParentName = 'AdminParentOrders';
        $this->checkforupdates();
    }

    public function checkforupdates($display_msg = 0, $form = 0)
    {
        // ---------- //
        // ---------- //
        // VERSION 16 //
        // ---------- //
        // ---------- //
        $this->mkey = "nlc";
        if (@file_exists('../modules/' . $this->name . '/key.php')) {
            @require_once('../modules/' . $this->name . '/key.php');
        } else {
            if (@file_exists(dirname(__FILE__) . $this->name . '/key.php')) {
                @require_once(dirname(__FILE__) . $this->name . '/key.php');
            } else {
                if (@file_exists('modules/' . $this->name . '/key.php')) {
                    @require_once('modules/' . $this->name . '/key.php');
                }
            }
        }
        if ($form == 1) {
            return '
            <div class="panel" id="fieldset_myprestaupdates" style="margin-top:20px;">
            ' . ($this->psversion() == 6 || $this->psversion() == 7 ? '<div class="panel-heading"><i class="icon-wrench"></i> ' . $this->l('MyPresta updates') . '</div>' : '') . '
			<div class="form-wrapper" style="padding:0px!important;">
            <div id="module_block_settings">
                    <fieldset id="fieldset_module_block_settings">
                         ' . ($this->psversion() == 5 ? '<legend style="">' . $this->l('MyPresta updates') . '</legend>' : '') . '
                        <form action="' . $_SERVER['REQUEST_URI'] . '" method="post">
                            <label>' . $this->l('Check updates') . '</label>
                            <div class="margin-form">' . (Tools::isSubmit('submit_settings_updates_now') ? ($this->inconsistency(0) ? '' : '') . $this->checkforupdates(1) : '') . '
                                <button style="margin: 0px; top: -3px; position: relative;" type="submit" name="submit_settings_updates_now" class="button btn btn-default" />
                                <i class="process-icon-update"></i>
                                ' . $this->l('Check now') . '
                                </button>
                            </div>
                            <label>' . $this->l('Updates notifications') . '</label>
                            <div class="margin-form">
                                <select name="mypresta_updates">
                                    <option value="-">' . $this->l('-- select --') . '</option>
                                    <option value="1" ' . ((int)(Configuration::get('mypresta_updates') == 1) ? 'selected="selected"' : '') . '>' . $this->l('Enable') . '</option>
                                    <option value="0" ' . ((int)(Configuration::get('mypresta_updates') == 0) ? 'selected="selected"' : '') . '>' . $this->l('Disable') . '</option>
                                </select>
                                <p class="clear">' . $this->l('Turn this option on if you want to check MyPresta.eu for module updates automatically. This option will display notification about new versions of this addon.') . '</p>
                            </div>
                            <label>' . $this->l('Module page') . '</label>
                            <div class="margin-form">
                                <a style="font-size:14px;" href="' . $this->mypresta_link . '" target="_blank">' . $this->displayName . '</a>
                                <p class="clear">' . $this->l('This is direct link to official addon page, where you can read about changes in the module (changelog)') . '</p>
                            </div>
                            <div class="panel-footer">
                                <button type="submit" name="submit_settings_updates"class="button btn btn-default pull-right" />
                                <i class="process-icon-save"></i>
                                ' . $this->l('Save') . '
                                </button>
                            </div>
                        </form>
                    </fieldset>
                    <style>
                    #fieldset_myprestaupdates {
                        display:block;clear:both;
                        float:inherit!important;
                    }
                    </style>
                </div>
            </div>
            </div>';
        } else {
            if (defined('_PS_ADMIN_DIR_')) {
                if (Tools::isSubmit('submit_settings_updates')) {
                    Configuration::updateValue('mypresta_updates', Tools::getValue('mypresta_updates'));
                }
                if (Configuration::get('mypresta_updates') != 0 || (bool)Configuration::get('mypresta_updates') != false) {
                    if (Configuration::get('update_' . $this->name) < (date("U") - 259200)) {
                        $actual_version = deleteordersfreeUpdate::verify($this->name, (isset($this->mkey) ? $this->mkey : 'nokey'), $this->version);
                    }
                    if (deleteordersfreeUpdate::version($this->version) < deleteordersfreeUpdate::version(Configuration::get('updatev_' . $this->name)) && Tools::getValue('ajax', 'false') == 'false') {
                        $this->context->controller->warnings[] = '<strong>' . $this->displayName . '</strong>: ' . $this->l('New version available, check http://MyPresta.eu for more informations') . ' <a href="' . $this->mypresta_link . '">' . $this->l('More details in changelog') . '</a>';
                        $this->warning = $this->context->controller->warnings[0];
                    }
                } else {
                    if (Configuration::get('update_' . $this->name) < (date("U") - 259200)) {
                        $actual_version = deleteordersfreeUpdate::verify($this->name, (isset($this->mkey) ? $this->mkey : 'nokey'), $this->version);
                    }
                }
                if ($display_msg == 1) {
                    if (deleteordersfreeUpdate::version($this->version) < deleteordersfreeUpdate::version(deleteordersfreeUpdate::verify($this->name, (isset($this->mkey) ? $this->mkey : 'nokey'), $this->version))) {
                        return "<span style='color:red; font-weight:bold; font-size:16px; margin-right:10px;'>" . $this->l('New version available!') . "</span>";
                    } else {
                        return "<span style='color:green; font-weight:bold; font-size:16px; margin-right:10px;'>" . $this->l('Module is up to date!') . "</span>";
                    }
                }
            }
        }
    }

    public function viewAccess($disable = false)
    {
        $result = true;
        return $result;
    }

    function install()
    {
        if (parent::install() == false) {
            return false;
        }
        if (!isset($id_tab)) {
            $tab = new Tab();
            $tab->class_name = $this->tabClassName;
            $tab->id_parent = Tab::getIdFromClassName($this->tabParentName);
            $tab->module = $this->name;
            $languages = Language::getLanguages();
            foreach ($languages as $language) {
                $tab->name[$language['id_lang']] = $this->displayName;
            }
            $tab->add();
        }
        return true;
    }

    public function uninstall()
    {
        if (parent::uninstall() == false) {
            return false;
        }
        $id_tab = Tab::getIdFromClassName($this->tabClassName);

        if ($id_tab) {
            $tab = new Tab($id_tab);
            $tab->delete();
        }
        return true;
    }

    public function psversion()
    {
        $version = _PS_VERSION_;
        $exp = $explode = explode(".", $version);
        return $exp[1];
    }

    public function displayAdvert($return = 0)
    {
        $ret = '<iframe src="http://mypresta.eu/content/uploads/2012/09/deleteorders_advertise.html" width="100%" height="130" border="0" style="border:none;"></iframe>';
        if ($return == 0) {
            echo $ret;
        } else {
            return $ret;
        }
    }

    public function displayFooter($return = 0)
    {
        $ret = "<div class='panel'>";
        $ret .= $this->l('proudly developed by') . " <a style=\"font-weight:bold; color:orange;\" href=\"http://mypresta.eu\" target=\"_blank\">MyPresta.eu</a><br/><br/>";
        $ret .= '<iframe src="//www.facebook.com/plugins/likebox.php?href=http%3A%2F%2Fwww.facebook.com%2Fmypresta&amp;width=200&amp;height=62&amp;show_faces=false&amp;colorscheme=light&amp;stream=false&amp;border_color&amp;header=false&amp;appId=211918662219581" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:200px; height:62px;" allowTransparency="true"></iframe>';
        $ret .= "</div>";
        if ($return == 0) {
            echo $ret;
        } else {
            return $ret;
        }
    }

    public function displayinputid($return = 0)
    {
        $verps = "";

        $ret = "
		<script>
			function deleteorderbyid(id,msg){
				var answer = confirm(msg);
					if (answer){
						document.getElementById(\"deletebyid\").submit();
					}
			}			
		</script>
		
		<fieldset class='panel'>
		<h3>" . $this->l('Fill form with correct ORDER ID and delete it') . "</h3>
			<div align=\"center\" style=\"margin-bottom:20px;\">
				<form action=\"index.php?tab=deleteorderstab$verps&token={$_GET['token']}\" method=\"post\" id=\"deletebyid\" name=\"deletebyid\">
				<strong>" . $this->l('ORDER ID') . "<br/></strong>
				<input style=\"width:200px; margin-top:5px; text-align:center;\" type=\"text\" value=\"\" name=\"idord\" id=\"idord\"><br/><br/>
				<img src=\"../modules/deleteordersfree/delete.png\" onClick=\"deleteorderbyid(document.getElementById('idord'),'" . $this->l('Are you sure you want to delete:') . " #" . "'+document.getElementById('idord').value+'" . " " . $this->l('order?') . "');\" style=\"cursor:pointer;\" ></form>
				" . $this->l('click to remove') . "
			</div>
		</fieldset>
        
        ";

        if ($return == 0) {
            echo $ret;
        } else {
            return $ret;
        }
    }

    public function deleteorderbyid($id, $return = 0)
    {
        $psversion = $this->psversion();

        if ($psversion == 7) {
            $thisorder = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('SELECT id_cart FROM ' . _DB_PREFIX_ . 'orders WHERE id_order = ' . $id);

            if (isset($thisorder[0])) {
                //deleting order_return
                $q = 'DELETE a,b FROM ' . _DB_PREFIX_ . 'order_return AS a LEFT JOIN ' . _DB_PREFIX_ . 'order_return_detail AS b ON a.id_order_return = b.id_order_return WHERE id_order="' . $id . '"';
                if (!Db::getInstance()->Execute($q)) {
                    $this->errorlog[] = $this->l("ERROR");
                }

                //deleting order_slip
                $q = 'DELETE a,b FROM ' . _DB_PREFIX_ . 'order_slip AS a LEFT JOIN ' . _DB_PREFIX_ . 'order_slip_detail AS b ON a.id_order_slip = b.id_order_slip WHERE id_order="' . $id . '"';
                if (!Db::getInstance()->Execute($q)) {
                    $this->errorlog[] = $this->l("ERROR");
                }

                $q = 'DELETE FROM ' . _DB_PREFIX_ . 'cart_product WHERE id_cart="' . $thisorder[0]['id_cart'] . '"';
                if (!Db::getInstance()->Execute($q)) {
                    $this->errorlog[] = $this->l("ERROR");
                }

                $q = 'DELETE FROM ' . _DB_PREFIX_ . 'order_history WHERE id_order="' . $id . '"';
                if (!Db::getInstance()->Execute($q)) {
                    $this->errorlog[] = $this->l("ERROR");
                }

                $q = 'DELETE FROM ' . _DB_PREFIX_ . 'order_detail_tax WHERE id_order_detail IN (SELECT id_order_detail FROM ' . _DB_PREFIX_ . 'order_detail WHERE id_order ="' . $id . '")';
                if (!Db::getInstance()->Execute($q)) {
                    $this->errorlog[] = $this->l("ERROR");
                }

                $q = 'DELETE FROM ' . _DB_PREFIX_ . 'order_detail WHERE id_order="' . $id . '"';
                if (!Db::getInstance()->Execute($q)) {
                    $this->errorlog[] = $this->l("ERROR");
                }

                $q = 'DELETE FROM ' . _DB_PREFIX_ . 'order_payment WHERE order_reference IN (SELECT reference FROM ' . _DB_PREFIX_ . 'orders WHERE id_order="' . $id . '")';
                if (!Db::getInstance()->Execute($q)) {
                    $this->errorlog[] = $this->l("ERROR");
                }

                $q = 'DELETE FROM ' . _DB_PREFIX_ . 'orders WHERE id_order="' . $id . '"';
                if (!Db::getInstance()->Execute($q)) {
                    $this->errorlog[] = $this->l("ERROR");
                }

                $q = 'DELETE FROM ' . _DB_PREFIX_ . 'order_carrier WHERE id_order="' . $id . '"';
                if (!Db::getInstance()->Execute($q)) {
                    $this->errorlog[] = $this->l("ERROR");
                }

                $q = 'DELETE FROM ' . _DB_PREFIX_ . 'order_invoice_tax WHERE id_order_invoice IN (SELECT id_order_invoice FROM ' . _DB_PREFIX_ . 'order_invoice WHERE id_order="' . $id . '")';
                if (!Db::getInstance()->Execute($q)) {
                    $this->errorlog[] = $this->l("ERROR");
                }

                $q = 'DELETE FROM ' . _DB_PREFIX_ . 'order_invoice WHERE id_order="' . $id . '"';
                if (!Db::getInstance()->Execute($q)) {
                    $this->errorlog[] = $this->l("ERROR");
                }

                $q = 'DELETE FROM ' . _DB_PREFIX_ . 'order_invoice_payment WHERE id_order="' . $id . '"';
                if (!Db::getInstance()->Execute($q)) {
                    $this->errorlog[] = $this->l("ERROR");
                }

                $q = 'DELETE FROM ' . _DB_PREFIX_ . 'order_cart_rule WHERE id_order="' . $id . '"';
                if (!Db::getInstance()->Execute($q)) {
                    $this->errorlog[] = $this->l("ERROR");
                }

                if (empty($this->errorlog)) {
                    if ($return == 1) {
                        $this->context->controller->confirmations[] = $this->l('Order deleted');
                    } else {
                        return $this->l('Order deleted');
                    }
                } else {
                    if ($return == 1) {
                        $this->context->controller->errors[] = $this->l('Something wrong');
                    } else {
                        return $this->l('Something wrong');
                    }
                }

            } else {
                if ($return == 1) {
                    $this->context->controller->errors[] = $this->l('Order with this id doesnt exists');
                } else {
                    return $this->l('Order with this id doesnt exists');
                }
            }
        }
    }

    public function getContent()
    {
        $this->context->controller->informations[] = $this->l('Delete orders form is available here: ') . '<a href="'.$this->context->link->getAdminLink('deleteorderstab').'">' . $this->l('Delete orders form') . '</a>';
        return $this->checkforupdates(0, 1);
    }

    public function inconsistency($var)
    {
        return;
    }

    public function displayForm()
    {
        return '';
    }

    public function getorders_psv3($limit = null)
    {
        global $cookie;

        return Db::getInstance()->ExecuteS('
			SELECT *, (
				SELECT `name`
				FROM `' . _DB_PREFIX_ . 'order_history` oh
				LEFT JOIN `' . _DB_PREFIX_ . 'order_state_lang` osl ON (osl.`id_order_state` = oh.`id_order_state`)
				WHERE oh.`id_order` = o.`id_order`
				AND osl.`id_lang` = ' . (int)$cookie->id_lang . '
				ORDER BY oh.`date_add` DESC
				LIMIT 1
			) AS `state_name`
			FROM `' . _DB_PREFIX_ . 'orders` o
			LEFT JOIN `' . _DB_PREFIX_ . 'customer` c ON (c.`id_customer` = o.`id_customer`)
			ORDER BY o.`date_add` DESC
			' . ((int)$limit ? 'LIMIT 0, ' . (int)$limit : ''));
    }
}

class deleteordersfreeUpdate extends deleteordersfree
{
    public static function version($version)
    {
        $version = (int)str_replace(".", "", $version);
        if (strlen($version) == 3) {
            $version = (int)$version . "0";
        }
        if (strlen($version) == 2) {
            $version = (int)$version . "00";
        }
        if (strlen($version) == 1) {
            $version = (int)$version . "000";
        }
        if (strlen($version) == 0) {
            $version = (int)$version . "0000";
        }
        return (int)$version;
    }

    public static function encrypt($string)
    {
        return base64_encode($string);
    }

    public static function verify($module, $key, $version)
    {
        if (ini_get("allow_url_fopen")) {
            if (function_exists("file_get_contents")) {
                $actual_version = @file_get_contents('http://dev.mypresta.eu/update/get.php?module=' . $module . "&version=" . self::encrypt($version) . "&lic=$key&u=" . self::encrypt(_PS_BASE_URL_ . __PS_BASE_URI__));
            }
        }
        Configuration::updateValue("update_" . $module, date("U"));
        Configuration::updateValue("updatev_" . $module, $actual_version);
        return $actual_version;
    }
}

?>