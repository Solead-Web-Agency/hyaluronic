<?php

class exportProduct
{
  private $_context;
  private $_idShop;
  private $_idLang;
  private $_currency;
  private $_shopGroupId;
  private $_format;
  private $_model;
  private $_PHPExcel;
  private $_alphabet;
  private $_head;
  private $_separate;
  private $_more_settings;
  private $_name_file;
  private $_imageType;
  private $_productsCount;
  private $_limit;
  private $_limitN = 100;
  private $_productIdCount = false;
  private $_connID;
  private $_distinctAttributes = array();
  private $_maxImages = 0;
  private $_distinctFeatures = array();
  private $_categoryTreesCount = 0;
  private $_parentCategories = array();
  private $_xml_head = array();
  private $edited_xml_names = array();
  private $_insertValues = '';

  public function __construct($idShop, $idLang, $format, $separate, $more_settings, $name_file)
  {
    include_once(dirname(__FILE__) . '/../../config/config.inc.php');
    include_once(dirname(__FILE__) . '/../../init.php');

    if (!class_exists('PHPExcel')) {
      include_once(_PS_MODULE_DIR_ . 'exportproducts/libraries/PHPExcel_1.7.9/Classes/PHPExcel.php');
      include_once(_PS_MODULE_DIR_ . 'exportproducts/libraries/PHPExcel_1.7.9/Classes/PHPExcel/IOFactory.php');
    }

    include_once('datamodel.php');

    $this->_context = Context::getContext();
    $this->_idShop = $idShop;
    $this->_idLang = $idLang;
    $this->_currency = $more_settings['currency'];
    $this->_format = $format;
    $this->_separate = $separate;
    $this->_more_settings = $more_settings;
    $this->_name_file = $name_file;
    $this->edited_xml_names = is_array($more_settings['edited_xml_names']) ? $more_settings['edited_xml_names'] : array();
    $imageTypes = ImageType::getImagesTypes('products');
    if (isset(Context::getContext()->shop->id_shop_group)) {
      $this->_shopGroupId = Context::getContext()->shop->id_shop_group;
    } elseif (isset(Context::getContext()->shop->id_shop_group)) {
      $this->_shopGroupId = Context::getContext()->shop->id_shop_group;
    }
    foreach ($imageTypes as $type) {
      if ($type['height'] > 150) {
        $this->_imageType = $type['name'];
        break;
      }
    }
    $this->_model = new productsExportModel();
    $this->_PHPExcel = new PHPExcel();
    
    for( $i = 0; $i < 2000; $i++ ){
      $this->_alphabet[$i] = $this->columnLetter($i+1);
    }
    
  }
  
  public function columnLetter($c){

    $c = intval($c);
    if ($c <= 0) return '';

    $letter = '';

    while($c != 0){
      $p = ($c - 1) % 26;
      $c = intval(($c - $p) / 26);
      $letter = chr(65 + $p) . $letter;
    }

    return $letter;

  }

  public function exportProducts($limit = 0)
  {
    if ($this->_more_settings['feed_target'] == 'ftp') {
      $conn_id = ftp_connect($this->_more_settings['ftp_server']);
      $this->_connID = $conn_id;
      if (!$conn_id) {
        throw new Exception(Module::getInstanceByName('exportproducts')->l('Can not connect to your FTP Server!', 'export'));
      }

      $login_result = @ftp_login($conn_id, $this->_more_settings['ftp_user'], $this->_more_settings['ftp_password']);

      if (!$login_result) {
        throw new Exception(Module::getInstanceByName('exportproducts')->l('Can not Login to your FTP Server, please check access!', 'export'));
      }
    }
    $this->_limit = $limit;
    if (!$limit) {
      $this->_truncateTable();
      Configuration::updateValue('EXPORT_PRODUCTS_TIME', Date('Y.m.d_G-i-s'), false, $this->_shopGroupId, $this->_idShop);
      $this->_productsCount = $this->_model->getExportIds($this->_idShop, $this->_idLang, $this->_separate, $this->_more_settings, $limit, $this->_limitN, true);
      Configuration::updateGlobalValue('EXPORT_PRODUCTS_COUNT', $this->_productsCount);
      Configuration::updateGlobalValue('EXPORT_PRODUCTS_CURRENT_COUNT', 0);
      Configuration::updateGlobalValue('EXPORT_CATEGORY_TREE_COUNT', 0);

      if (!$this->_productsCount) {
        throw new Exception(Module::getInstanceByName('exportproducts')->l('No of matching products', 'export'));
      }
    } else {
      $this->_productsCount = Configuration::getGlobalValue('EXPORT_PRODUCTS_COUNT');
      $this->_categoryTreesCount = Configuration::getGlobalValue('EXPORT_CATEGORY_TREE_COUNT');
    }
    $productIds = $this->_model->getExportIds($this->_idShop, $this->_idLang, $this->_separate, $this->_more_settings, $this->_limit, $this->_limitN);
    $selected_fields = Tools::unserialize(Configuration::get('GOMAKOIL_FIELDS_CHECKED', '', $this->_shopGroupId, $this->_idShop));
    $selected_fields = $this->splitSpecificPriceFields($selected_fields);
    $selected_fields = $this->splitSupplierFields($selected_fields);

    Configuration::updateValue('GOMAKOIL_FIELDS_CHECKED', serialize($selected_fields), false, $this->_shopGroupId, $this->_idShop);

    if (isset($selected_fields['combinations_value'])) {
      $this->_distinctAttributes = $this->_model->getExportIds($this->_idShop, $this->_idLang, $this->_separate, $this->_more_settings, $this->_limit, $this->_limitN, false, true);
    }
    if (isset($selected_fields['images_value'])) {
      $this->_maxImages = $this->_model->getExportIds($this->_idShop, $this->_idLang, $this->_separate, $this->_more_settings, $this->_limit, $this->_limitN, false, false, true);
    }

    if ( isset($selected_fields['separated_categories']) && !$this->_limit ) {
      $allProductIds = $this->_model->getExportIds($this->_idShop, $this->_idLang, $this->_separate, $this->_more_settings, $this->_limit, $this->_limitN, false, false, false, true);

      foreach ($allProductIds as $productId) {
        $this->_parentCategories = array();
        $categories = $this->_getWsCategories($productId['id_product']);

        $currentCount = 0;
        foreach ($categories as $category) {
          if ($this->_getCategoryTree($category['id'])) {
            $currentCount++;
          }
        }
        if ($this->_categoryTreesCount < $currentCount) {
          $this->_categoryTreesCount = $currentCount;
          Configuration::updateGlobalValue('EXPORT_CATEGORY_TREE_COUNT', $this->_categoryTreesCount);
        }
      }
    }

    if (isset($selected_fields['features'])) {
      $this->_distinctFeatures = $this->_model->getExportIds($this->_idShop, $this->_idLang, $this->_separate, $this->_more_settings, $this->_limit, $this->_limitN, false, false, false, false, true);
    }

    $this->_setDataInDB($productIds);
    if ((int)$this->_productsCount > ((int)$this->_limit * (int)$this->_limitN) + (int)$this->_limitN) {
      return (int)$this->_limit + 1;
    }
    else{
      $res = $this->_saveDataInFile();

      Configuration::updateGlobalValue('EXPORT_PRODUCTS_CURRENT_COUNT', Module::getInstanceByName('exportproducts')->l('Export done'));
      $this->_updateExportRunning(true);

      return $res;
    }
  }

  private function _truncateTable()
  {
    Db::getInstance()->execute('TRUNCATE '._DB_PREFIX_.'exportproducts_data');
  }

  private function _getCategoryTree($categoryId, $level = array())
  {
    $catInfo = new Category($categoryId, $this->_idLang);
    if (in_array($categoryId, $this->_parentCategories) && !$level) {
      return false;
    }
    if ($level) {
      $this->_parentCategories[] = $categoryId;
    }

    if ($catInfo->id_parent) {
      $level[] = $catInfo->name;
      return $this->_getCategoryTree($catInfo->id_parent, $level);
    }

    return array_reverse($level);
  }

  private function _getWsCategories($productId)
  {
    $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
      'SELECT cp.`id_category` AS id
			FROM `' . _DB_PREFIX_ . 'category_product` cp
			LEFT JOIN `' . _DB_PREFIX_ . 'category` c ON (c.id_category = cp.id_category)
			' . Shop::addSqlAssociation('category', 'c') . '
			WHERE cp.`id_product` = ' . (int)$productId . '
      ORDER BY c.level_depth DESC'
    );
    return $result;
  }

  private function _setDataInDB( $productIds )
  {
    foreach ($productIds as $prodId) {
      $productId = $prodId['id_product'];

      if ($this->_separate) {
        $productAttributeId = $prodId['id_product_attribute'];
        $product = $this->_getProductById($productId, $productAttributeId);
      } else {
        $product = $this->_getProductById($productId, false);
      }

      $currentExported = Configuration::getGlobalValue('EXPORT_PRODUCTS_CURRENT_COUNT');
      Configuration::updateGlobalValue('EXPORT_PRODUCTS_CURRENT_COUNT', ((int)$currentExported + 1));

      $this->_addDataToDb($product, ((int)$currentExported + 1));
    }
    $this->_addDataToDbQuery();
  }

  private function _getRowsNumber()
  {
    $sql = "
      SELECT `row`
      FROM " . _DB_PREFIX_ . "exportproducts_data
      GROUP BY `row`
    ";

    $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
    return $res;
  }

  private function _getMaxRow()
  {
    $sql = "
      SELECT MAX(`row`) as max_row
      FROM " . _DB_PREFIX_ . "exportproducts_data
    ";

    $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
    return (int)$res[0]['max_row'];
  }

  private function _addDataToDb( $product, $row )
  {
    $this->_updateExportRunning();
//    $row = $this->_getMaxRow();
//    $row++;

    foreach( $product as $field => $value ){
      $data = array(
        'row'   => $row,
        'field' => pSQL($field),
        'value' => pSQL($value, true)
      );

      if( Tools::getValue('id_task') ){
        $data['id_task'] = Tools::getValue('id_task');
      }
      else{
        $data['id_task'] = 0;
      }

//      Db::getInstance(_PS_USE_SQL_SLAVE_)->insert('exportproducts_data', $data);
      $this->_insertValues .= '("'.$data['row'].'","'.$data['field'].'","'.$data['value'].'","'.$data['id_task'].'"),';
    }
    $this->_addDataToDbQuery();

    $limit = 50;
    if( $row % $limit == 0 ){
      $this->_addDataToDbQuery();
    }
  }

  private function _addDataToDbQuery()
  {
    if( $this->_insertValues ){
      $this->_insertValues = rtrim($this->_insertValues, ',');

      $sql = "
      INSERT INTO "._DB_PREFIX_."exportproducts_data
        (`row`,`field`,`value`,`id_task`)
      VALUES
      $this->_insertValues
      ;
    ";

      Db::getInstance()->execute($sql);
      $this->_insertValues = '';
    }

  }

  private function _getDataForSave( $row, $remove = false )
  {
    $product = array();

    $sql = "
      SELECT * 
      FROM " . _DB_PREFIX_ . "exportproducts_data
      WHERE `row` = '".(int)$row."'
    ";

    $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

    if( $res ){
      foreach( $res as $data ){
        $product[$data['field']] = $data['value'];
      }

      if( $remove ){
        Db::getInstance(_PS_USE_SQL_SLAVE_)->delete('exportproducts_data', '`row`='.$row);
      }

      return $product;
    }

    return false;
  }

  private function _updateExportRunning( $stop = false )
  {
    if( $stop ){
      Configuration::updateGlobalValue('GOMAKOIL_PRODUCTS_EXPORT_RUNNING', false);
    }
    else{
      Configuration::updateGlobalValue('GOMAKOIL_PRODUCTS_EXPORT_RUNNING', time());
    }
    Module::getInstanceByName('exportproducts')->updateProgress();
  }

  private function _saveDataInFile()
  {
    Configuration::updateGlobalValue('EXPORT_PRODUCTS_CURRENT_COUNT', Module::getInstanceByName('exportproducts')->l('Saving exported file...') );

    $date = Configuration::get('EXPORT_PRODUCTS_TIME', '', $this->_shopGroupId, $this->_idShop);
    $name_file = 'export_products_' . $date . '.' . $this->_format;
    if ($this->_name_file) {
      $name_file = $this->_name_file . '.' . $this->_format;
    }

    $more = $this->_more_settings;
    if ($more['display_headers']) {
      $line = 2;
    } else {
      $line = 1;
    }
    $rows = $this->_getRowsNumber();

    if ($this->_format == 'xml') {
      $write_fd = fopen('files/' . $name_file, 'w');
      fwrite($write_fd, '<?xml version="1.0" encoding="UTF-8"?>' . "\r\n<" . Module::getInstanceByName('exportproducts')->l('products', 'export') . ">\r\n");
    } else {
      $write_fd = false;
    }

    foreach( $rows as $row ){
      $product = $this->_getDataForSave($row['row'], true);
      $this->_saveProduct($product, $line, $write_fd);
      $line++;
    }

    if ((int)$this->_productsCount <= ((int)$this->_limit * (int)$this->_limitN) + (int)$this->_limitN) {
      if ($this->_format == 'xml') {
        if (@$write_fd !== false) {
          fwrite($write_fd, '</' . Module::getInstanceByName('exportproducts')->l('products', 'export') . '>' . "\r\n");
          fclose($write_fd);

          if ($this->_more_settings['feed_target'] == 'ftp') {
            $path = '';
            if ($this->_more_settings['ftp_folder_path']) {
              $path = $this->_more_settings['ftp_folder_path'] . '/';
              $path = str_replace('//', '/', $path);
            }

            $ftpUpload = ftp_put($this->_connID, $path . $name_file, 'files/' . $name_file, FTP_ASCII);
            if (!$ftpUpload) {
              throw new Exception(Module::getInstanceByName('exportproducts')->l('Can not upload export file to your FTP Server, please check ftp folder path and folder permissions!', 'export'));
            }
          }
        }
      } else {
        $this->_setStyle($line);
      }
    }

    $fileName = $this->_saveFile($name_file);
    return $fileName;
  }

  private function _saveProduct( $product, $line, $write_fd )
  {
    $this->_updateExportRunning();
    $this->_createHead();


    $this->_setProductInFile($product, $line, $write_fd);
  }

  private function _setStyle($line)
  {
    $i = $line;
    $j = count($this->_head);
    $more = $this->_more_settings;

    if ($more['display_headers']) {
      $style_hprice = array(
        'alignment' => array(
          'horizontal' => PHPExcel_STYLE_ALIGNMENT::HORIZONTAL_CENTER,
        ),
        'fill' => array(
          'type' => PHPExcel_STYLE_FILL::FILL_SOLID,
          'color' => array(
            'rgb' => 'CFCFCF'
          )
        ),
        'font' => array(
          'bold' => true,
          'italic' => true,
          'name' => 'Times New Roman',
          'size' => 13
        ),
      );
      $this->_PHPExcel->getActiveSheet()->getStyle('A1:' . $this->_alphabet[$j - 1] . '1')->applyFromArray($style_hprice);
    } else {
      $style_hprice = array(
        'alignment' => array(
          'horizontal' => PHPExcel_STYLE_ALIGNMENT::HORIZONTAL_LEFT,
          'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        ),
        'fill' => array(
          'type' => PHPExcel_STYLE_FILL::FILL_SOLID,
          'color' => array(
            'rgb' => 'F2F2F5'
          )
        ),
      );
      $this->_PHPExcel->getActiveSheet()->getStyle('A1:' . $this->_alphabet[$j - 1] . '1')->applyFromArray($style_hprice);
    }

    $style_wrap = array(
      //рамки
      'borders' => array(
        //внешняя рамка
        'outline' => array(
          'style' => PHPExcel_Style_Border::BORDER_THICK
        ),
        //внутренняя
        'allborders' => array(
          'style' => PHPExcel_Style_Border::BORDER_THIN,
          'color' => array(
            'rgb' => '696969'
          )
        )
      )
    );
    $this->_PHPExcel->getActiveSheet()->getStyle('A1:' . $this->_alphabet[$j - 1] . ($i - 1))->applyFromArray($style_wrap);

    $style_price = array(
      'alignment' => array(
        'horizontal' => PHPExcel_STYLE_ALIGNMENT::HORIZONTAL_LEFT,
        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
      )
    );
    $this->_PHPExcel->getActiveSheet()->getStyle('A2:' . $this->_alphabet[$j - 1] . ($i - 1))->applyFromArray($style_price);

    $style_background1 = array(
      //заполнение цветом
      'fill' => array(
        'type' => PHPExcel_STYLE_FILL::FILL_SOLID,
        'color' => array(
          'rgb' => 'F2F2F5'
        )
      ),
    );
    $this->_PHPExcel->getActiveSheet()->getStyle('A2:' . $this->_alphabet[$j - 1] . ($i - 1))->applyFromArray($style_background1);
  }

  private function _getImageObject($mime, $image)
  {
    switch (Tools::strtolower($mime['mime'])) {
      case 'image/png':
        $img_r = imagecreatefrompng($image);
        break;
      case 'image/jpeg':
        $img_r = imagecreatefromjpeg($image);
        break;
      case 'image/gif':
        $img_r = imagecreatefromgif($image);
        break;
      default:
        $img_r = imagecreatefrompng($image);;
    }

    return $img_r;
  }

  private function _setProductInFile($product, $line, $write_fd)
  {
    $i = 0;
    if ($this->_format == 'xml') {
      fwrite($write_fd, "\t" . '<' . Module::getInstanceByName('exportproducts')->l('product', 'export') . '>' . "\r\n");
    }

    foreach ($this->_head as $field => $name) {
      if ($this->_format == 'xlsx' || $this->_format == 'csv') {
        if ($field == 'image_cover') {
          if (($mime = @getimagesize($product[$field]))) {
            $gdImage = $this->_getImageObject($mime, $product[$field]);
            $objDrawing = new PHPExcel_Worksheet_MemoryDrawing();
            $objDrawing->setImageResource($gdImage);
            $objDrawing->setRenderingFunction(PHPExcel_Worksheet_MemoryDrawing::RENDERING_JPEG);
            $objDrawing->setMimeType(PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_DEFAULT);
            $objDrawing->setHeight(150);
            $objDrawing->setOffsetX(6);
            $objDrawing->setOffsetY(6);
            $objDrawing->setCoordinates($this->_alphabet[$i] . $line);
            $objDrawing->setWorksheet($this->_PHPExcel->getActiveSheet());
            $this->_PHPExcel->getActiveSheet()->getRowDimension($line)->setRowHeight(121);
            $this->_PHPExcel->getActiveSheet()->getColumnDimension($this->_alphabet[$i])->setWidth(23);
          }
        } else {
          $this->_PHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($this->_alphabet[$i] . $line, isset($product[$field]) ? $product[$field] : '', PHPExcel_Cell_DataType::TYPE_STRING);
        }
      } else {
        if (preg_match('/^extra_field_\d+$/', $field) || strpos($field, 'Attribute_') !== false) {
          $fieldName = $name;
        } else {
          $fieldName = $this->_getXmlHead($field);
        }
        $fieldName = str_replace(':', '', $fieldName);
        $fieldName = str_replace(' ', '_', $fieldName);
        $fieldName = str_replace("'", '', $fieldName);
        $fieldName = str_replace('"', '', $fieldName);

        if (strpos($field, 'images_value_') !== false) {
          if (isset($product[$field]) && $product[$field]) {
            fwrite($write_fd, "\t\t" . '<' . str_replace('images_value_', 'images_', $fieldName) . '>');
          }
        } else {
          fwrite($write_fd, "\t\t" . '<' . $fieldName . '>');
        }

        $is_valid_value = isset($product[$field]) && $product[$field] !== null && $product[$field] !== false && $product[$field] !== '';

        if ($is_valid_value) {
          fwrite($write_fd, '<![CDATA[');
          fwrite($write_fd, isset($product[$field]) ? $product[$field] : '');
          fwrite($write_fd, ']]>');
        }

        if (strpos($field, 'images_value_') !== false) {
          if (isset($product[$field]) && $product[$field]) {
            fwrite($write_fd, '</' . str_replace('images_value_', 'images_', $fieldName) . '>' . "\r\n");
          }
        } else {
          fwrite($write_fd, '</' . $fieldName . '>' . "\r\n");
        }


      }
      $i++;
    }
    if ($this->_format == 'xml') {
      fwrite($write_fd, "\t" . '</' . Module::getInstanceByName('exportproducts')->l('product', 'export') . '>' . "\r\n");
    }
  }

  private function _saveFile($name_file)
  {
    $more = $this->_more_settings;

    $delimiter = $more['delimiter_val'];
    $seperatop = $more['seperatop_val'];

    if (isset($more['settings']) && $more['settings'] && isset($more['automatic']) && $more['automatic']) {
      $not_exported = $more['not_exported'];
      $id_setting = $more['settings'];
    } else {
      $not_exported = false;
      $id_setting = false;
    }
    if ($delimiter == 'space') {
      $delimiter = ' ';
    }
    if ($delimiter == 'tab') {
      $delimiter = "\t";
    }

    if ($seperatop == 3) {
      $sep = ' ';
    } elseif ($seperatop == 2) {
      $sep = "'";
    } else {
      $sep = '"';
    }
    if ($this->_format == 'xlsx') {

      $objWriter = PHPExcel_IOFactory::createWriter($this->_PHPExcel, 'Excel2007');
      $objWriter->save('files/' . $name_file);
      if ($this->_more_settings['feed_target'] == 'ftp') {
        $path = '';
        if ($this->_more_settings['ftp_folder_path']) {
          $path = $this->_more_settings['ftp_folder_path'] . '/';
          $path = str_replace('//', '/', $path);
        }

        $ftpUpload = ftp_put($this->_connID, $path . $name_file, 'files/' . $name_file, FTP_ASCII);
        if (!$ftpUpload) {
          throw new Exception(Module::getInstanceByName('exportproducts')->l('Can not upload export file to your FTP Server, please check ftp folder path and folder permissions!', 'export'));
        }
      }

    } elseif ($this->_format == 'csv') {
      $objWriter = PHPExcel_IOFactory::createWriter($this->_PHPExcel, 'CSV');
      $objWriter->setDelimiter($delimiter);
      $objWriter->setEnclosure($sep);
      $objWriter->setUseBOM(true);
      $objWriter->save('files/' . $name_file);
      if ($this->_more_settings['feed_target'] == 'ftp') {
        $path = '';
        if ($this->_more_settings['ftp_folder_path']) {
          $path = $this->_more_settings['ftp_folder_path'] . '/';
          $path = str_replace('//', '/', $path);
        }

        $ftpUpload = ftp_put($this->_connID, $path . $name_file, 'files/' . $name_file, FTP_ASCII);
        if (!$ftpUpload) {
          throw new Exception(Module::getInstanceByName('exportproducts')->l('Can not upload export file to your FTP Server, please check ftp folder path and folder permissions!', 'export'));
        }
      }
    }

    if (isset($more['automatic']) && $more['automatic'] && $not_exported) {
      $productIds = $this->_model->getExportIds($this->_idShop, $this->_idLang, $this->_separate, $this->_more_settings, 0, 100000);
      $this->setInDbExportedProducts($id_setting, $productIds);
    }

    return Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/exportproducts/files/' . $name_file;
  }

  public function setInDbExportedProducts($id_setting, $productIds)
  {

    $ids = array();

    if ($id_setting && $productIds) {
      foreach ($productIds as $id_product) {
        Db::getInstance()->insert('exported_products', array('id_product' => (int)$id_product['id_product'], 'id_setting' => (int)$id_setting));
      }
    }
  }

  private function _createHead()
  {
    $this->_head = $this->_getHeadFields();
    if( $this->_format == 'xml' ){
      return true;
    }
    $this->_PHPExcel->getProperties()->setCreator("PHP")
      ->setLastModifiedBy("Admin")
      ->setTitle("Office 2007 XLSX")
      ->setSubject("Office 2007 XLSX")
      ->setDescription(" Office 2007 XLSX, PHPExcel.")
      ->setKeywords("office 2007 openxml php")
      ->setCategory("File");
    $this->_PHPExcel->getActiveSheet()->setTitle('Export');

    $i = 0;
    foreach ($this->_head as $field => $name) {
      $more = $this->_more_settings;
      if ($more['display_headers']) {
        $this->_PHPExcel->setActiveSheetIndex(0)
          ->setCellValue($this->_alphabet[$i] . '1', $name);
      }

      if ($field == "product_link") {
        $this->_PHPExcel->getActiveSheet()->getColumnDimension($this->_alphabet[$i])->setWidth(80);
      } elseif ($field == "images") {
        $this->_PHPExcel->getActiveSheet()->getColumnDimension($this->_alphabet[$i])->setWidth(80);
      } elseif ($field == "name") {
        $this->_PHPExcel->getActiveSheet()->getColumnDimension($this->_alphabet[$i])->setWidth(80);
      } elseif ($field == "description") {
        $this->_PHPExcel->getActiveSheet()->getColumnDimension($this->_alphabet[$i])->setWidth(80);
      } elseif ($field == "description_short") {
        $this->_PHPExcel->getActiveSheet()->getColumnDimension($this->_alphabet[$i])->setWidth(80);
      } else {
        $this->_PHPExcel->getActiveSheet()->getColumnDimension($this->_alphabet[$i])->setWidth(30);
      }
      $i++;
    }
    $this->_PHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(25);
  }

  private function splitSupplierFields($selected_fields)
  {
    $products_for_export_ids = productsExportModel::getProductForExportIdsAsString($this->_more_settings, $this->_idShop, $this->_idLang);
    $all_suppliers_ids = Db::getInstance()->executeS("SELECT DISTINCT(id_supplier) FROM " . _DB_PREFIX_ . "product_supplier
                                                          WHERE id_product IN (".$products_for_export_ids.")");

    foreach ($selected_fields as $field_for_export_key => $field_for_export_name) {
      if (preg_match('/^separate_supplier_\w+$/', $field_for_export_key)) {
        unset($selected_fields[$field_for_export_key]);
        foreach ($all_suppliers_ids as $supplier_count => $supplier_id_wrapper) {
          $supplier_id = $supplier_id_wrapper['id_supplier'];
          $selected_fields[$field_for_export_key . '_' . $supplier_id] = $field_for_export_name . '_' . ($supplier_count + 1);
        }
      }
    }

    return $selected_fields;
  }

  private function _getHeadFields()
  {
    $selected_fields = Tools::unserialize(Configuration::get('GOMAKOIL_FIELDS_CHECKED', '', $this->_shopGroupId, $this->_idShop));

    if (isset($selected_fields['features'])) {
      unset($selected_fields['features']);
      foreach ($this->_distinctFeatures as $feature) {
        $featureInfo = Feature::getFeature($this->_idLang, $feature['id_feature']);
        $selected_fields["FEATURE_" . $featureInfo['name']] = "FEATURE_" . $featureInfo['name'];
      }
    }

    if (isset($selected_fields['combinations_value'])) {
      unset($selected_fields['combinations_value']);
      if ($this->_distinctAttributes) {
        foreach ($this->_distinctAttributes as $attribute) {
          if ($attribute['id_attribute_group']) {
            $attrName = new AttributeGroup($attribute['id_attribute_group'], $this->_idLang, $this->_idShop);
            $attrName = $attrName->name;
            $selected_fields["Attribute_" . $attribute['id_attribute_group']] = "Attribute_" . str_replace(' ', '_', $attrName);
          }
        }
      }
    }

    if (isset($selected_fields['separated_categories'])) {
      unset($selected_fields['separated_categories']);
      if ($this->_categoryTreesCount) {
        for ($i = 1; $i <= $this->_categoryTreesCount; $i++) {
          $selected_fields['category_tree_' . $i] = 'Category Tree' . $i;
        }
      }
    }

    if (isset($selected_fields['images_value'])) {
      unset($selected_fields['images_value']);
      if ($this->_maxImages) {

        for ($x = 0; $x++ < $this->_maxImages;) {
          $selected_fields['images_value_' . $x] = "Product Image " . $x;
        }

      }
    }

    return $selected_fields;
  }

  private function _getProductById($productId, $productAttributeId)
  {
    $selected_fields = Tools::unserialize(Configuration::get('GOMAKOIL_FIELDS_CHECKED', '', $this->_shopGroupId, $this->_idShop));
    $product = new Product($productId, false, $this->_idLang, $this->_idShop);

    if ($this->_separate) {
      $productInfo = $this->getProductInfoSeparate($selected_fields, $productId, $productAttributeId, $product);
    } else {
      $productInfo = $this->getProductInfo($selected_fields, $productId, false, $product);
    }

    return $productInfo;
  }

  public function getProductInfoSeparate($selected_fields, $productId, $id_product_attribute, $product)
  {
    $productInfo = array();
    $combination = new Combination($id_product_attribute);
    $more_settings = $this->_more_settings;
    $round_value = $more_settings['round_value'];
    $decoration_price = $more_settings['decoration_price'];
    $separator_decimal_points = $more_settings['separator_decimal_points'];
    $address = null;
    if (is_object(Context::getContext()->cart) && Context::getContext()->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')} != null) {
      $address = Context::getContext()->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')};
    }

    $product->tax_rate = $product->getTaxesRate(new Address($address));
    $product->base_price = $product->price;
    $product->unit_price = ($product->unit_price_ratio != 0 ? $product->price / $product->unit_price_ratio : 0);

    $product_is_pack = Pack::isPack((int)$productId);

    foreach ($selected_fields as $field => $value) {
      if ($field == "id_product") {
        $productInfo[$field] = $productId;
      } elseif ($field == "id_product_attribute") {
        $productInfo[$field] = $id_product_attribute;
      } else if (strpos($field, 'extra_field_') !== false) {
        $extra_field_value = '';
        if (isset($more_settings['extra_fields'][$field]['value'])) {
          $extra_field_value = $more_settings['extra_fields'][$field]['value'];
        }

        $productInfo[$field] = $extra_field_value;
      } elseif ($field == "name_with_combination") {
          if ($combination) {
              $productInfo[$field] = productsExportModel::getProductName($product->id, $combination->id, $this->_idLang);
          } else {
              $productInfo[$field] = productsExportModel::getProductName($product->id, null, $this->_idLang);
          }
      } elseif ($field == "isbn") {
          $productInfo[$field] = $this->getIsbnForExport($product);
      } elseif (strpos($field, 'Attribute_') !== false) {
        $needAttribute = explode('_', $field);
        $needAttribute = $needAttribute[1];
        foreach ($this->_getAttributesName($id_product_attribute, $this->_idLang) as $attrValues) {
          if ($needAttribute != $attrValues['id_attribute_group']) {
            continue;
          }
          if (!isset($productInfo['Attribute_' . $attrValues['id_attribute_group']])) {
            $productInfo['Attribute_' . $attrValues['id_attribute_group']] = '';
          }
          $productInfo['Attribute_' . $attrValues['id_attribute_group']] = $attrValues['name'];
        }
      } elseif ($field == "combinations_value") {
        foreach ($this->_getAttributesName($id_product_attribute, $this->_idLang) as $attrValues) {
          if (!isset($productInfo['Attribute_' . $attrValues['id_attribute_group']])) {
            $productInfo['Attribute_' . $attrValues['id_attribute_group']] = '';
          }
          $productInfo['Attribute_' . $attrValues['id_attribute_group']] = $attrValues['name'];
        }
      } elseif ($field == "category_default_name") {
        $productInfo[$field] = "";
        $catName = CategoryCore::getCategoryInformations(array($product->id_category_default), $this->_idLang);
        if (isset($catName[$product->id_category_default])) {
          $productInfo[$field] = $catName[$product->id_category_default]['name'];
        }
      } elseif ($field == "separated_categories") {
        $categories = $this->_getWsCategories($product->id);
        $this->_parentCategories = array();

        $currentCount = 0;
        foreach ($categories as $category) {
          $catTree = '';
          if (($tree = $this->_getCategoryTree($category['id']))) {
            $currentCount++;
            foreach ($tree as $cat) {
              $catTree .= $cat . '->';
            }
            $catTree = rtrim($catTree, '->');
            $productInfo['category_tree_' . $currentCount] = $catTree;
          }
        }
      } elseif ($field == "images_value") {
        $link = new Link(null, 'http://');
        $images = $product->getCombinationImages(Context::getContext()->language->id);
        if (isset($images[$id_product_attribute]) && $images[$id_product_attribute]) {
          foreach ($images[$id_product_attribute] as $key => $image) {
            $productInfo['images_value_' . ($key + 1)] = "";
            $img_link = $link->getImageLink($product->link_rewrite, $image['id_image']);
            if ($img_link) {
              $productInfo['images_value_' . ($key + 1)] = $this->getImageLinkWithProperShopProtocol($img_link);
            }
          }
        } else {
          foreach ($product->getWsImages() as $key => $image) {
            $productInfo['images_value_' . ($key + 1)] = "";
            $img_link = $link->getImageLink($product->link_rewrite, $image['id']);
            if ($img_link) {
              $productInfo['images_value_' . ($key + 1)] = $this->getImageLinkWithProperShopProtocol($img_link);
            }
          }
        }
      } elseif ($field == "categories_ids") {
        $productInfo[$field] = "";
        foreach ($product->getWsCategories() as $category) {
          $productInfo[$field] .= $category['id'] . ",";
        }
        $productInfo[$field] = rtrim($productInfo[$field], ",");
      } 
       elseif ($field == "delivery_in_stock") {
        if( $product->additional_delivery_times == 2 ){
          $productInfo[$field] = $product->delivery_in_stock;
        }
        elseif( $product->additional_delivery_times == 1 ){
          $productInfo[$field] = Configuration::get('PS_LABEL_DELIVERY_TIME_AVAILABLE', $this->_idLang);
        }
        else{
          $productInfo[$field] = '';
        }
      }
      elseif ($field == "delivery_out_stock") {
        if( $product->additional_delivery_times == 2 ){
          $productInfo[$field] = $product->delivery_out_stock;
        }
        elseif( $product->additional_delivery_times == 1 ){
          $productInfo[$field] = Configuration::get('PS_LABEL_DELIVERY_TIME_OOSBOA', $this->_idLang);
        }
        else{
          $productInfo[$field] = '';
        }
      }
      elseif ($field == "categories_names") {
        $productInfo[$field] = "";
        foreach ($product->getWsCategories() as $category) {
          $cat_obj = new Category($category['id'], $this->_idLang, $this->_idShop);
          $productInfo[$field] .= $cat_obj->name . ",";
        }
        $productInfo[$field] = rtrim($productInfo[$field], ",");
      } elseif (preg_match('/^separate_supplier_(\w+)_(\d+)$/', $field, $supplier_matches)) {
        if (count($supplier_matches) != 3) {
          continue;
        }

        $supplier_property = $supplier_matches[1];
        $supplier_id       = $supplier_matches[2];
        $product_supplier_property = $this->getSupplierProperty($supplier_property, $supplier_id, $productId, $id_product_attribute);

        $productInfo[$field] = $product_supplier_property ?: '';
      } elseif ($field == 'suppliers_ids') {
        $product_supplier = $this->_model->getProductSuppliersID($productId);
        if ($product_supplier) {
          $productInfo[$field] = $product_supplier[0]['suppliers_ids'];
        }
      } elseif ($field == 'suppliers_name') {
        $product_supplier = $this->_model->getProductSuppliersID($productId);
        if ($product_supplier) {
          $productInfo[$field] = $product_supplier[0]['suppliers_name'];
        }
      } elseif ($field == 'quantity') {
        $productInfo[$field] = $product->getQuantity($productId, $id_product_attribute);
      } elseif ($field == 'total_quantity') {
        $productInfo[$field] = $product->getQuantity($productId, 0);
      } elseif ($field == 'out_of_stock') {
        $productInfo[$field] = StockAvailable::outOfStock($productId);
      } elseif ($field == 'depends_on_stock') {
        $productInfo[$field] = StockAvailable::dependsOnStock($productId);
      } elseif ($field == 'manufacturer_name') {
        $productInfo[$field] = Manufacturer::getNameById((int)$product->id_manufacturer);
      } elseif ($field == 'supplier_name') {
        $productInfo[$field] = Supplier::getNameById((int)$product->id_supplier);
      } elseif ($field == 'new') {
        $productInfo[$field] = $product->isNew();
      } elseif ($field == 'supplier_reference') {
        $sReference = ProductSupplier::getProductSupplierReference($productId, $id_product_attribute, $product->id_supplier);
        if (!$sReference) {
          $sReference = '';
        }
        $productInfo[$field] = $sReference;
      } elseif ($field == 'supplier_price') {
        $sPrice = ProductSupplier::getProductSupplierPrice($productId, $id_product_attribute, $product->id_supplier);
        if (!$sPrice) {
          $sPrice = '';
        } else {
          $sPrice = $this->getFormattedPrice($sPrice, $this->getPriceFormattingConfig());
        }

        $productInfo[$field] = $sPrice;
      } elseif ($field == 'supplier_price_currency') {
        $sPriceCurrency = ProductSupplier::getProductSupplierPrice($productId, $id_product_attribute, $product->id_supplier, true);
        if (isset($sPriceCurrency['id_currency'])) {
          $tmpCurrency = new Currency($sPriceCurrency['id_currency']);
          $sPriceCurrency['id_currency'] = $tmpCurrency->iso_code;
          $productInfo[$field] = $sPriceCurrency['id_currency'];
        } else {
          $productInfo[$field] = '';
        }
      } elseif ($field == "base_price" || $field == "ecotax" || $field == "additional_shipping_cost" || $field == "unit_price") {
        $productInfo[$field] = $this->getFormattedPrice($product->$field, $this->getPriceFormattingConfig());
      } elseif ($field == "base_price_with_tax") {
        $taxPrice = $product->base_price;
        if ($product->tax_rate) {
          $taxPrice = $taxPrice + ($taxPrice * ($product->tax_rate / 100));
        }

        $productInfo[$field] = $this->getFormattedPrice($taxPrice, $this->getPriceFormattingConfig());
      } elseif ($field == "wholesale_price") {
        $productInfo[$field] = $this->getFormattedPrice($product->$field, $this->getPriceFormattingConfig());
      } elseif ($field == "price") {
        $taxPrice = $product->getPrice(false, $id_product_attribute, $round_value);
        $productInfo[$field] = $this->getFormattedPrice($taxPrice, $this->getPriceFormattingConfig());
      } elseif ($field == "final_price_with_tax") {
        $taxPrice = $product->getPrice(true, $id_product_attribute, $round_value);
        $productInfo[$field] = $this->getFormattedPrice($taxPrice, $this->getPriceFormattingConfig());
      }
      elseif ($field == "combination_final_price_pre_tax") {
        $defaultCombination = $product->getDefaultIdProductAttribute();
        $taxPrice = $product->getPrice(false, $defaultCombination, $round_value);
        $productInfo[$field] = $this->getFormattedPrice($taxPrice, $this->getPriceFormattingConfig());
      }
      elseif ($field == "combination_final_price_with_tax") {
        $defaultCombination = $product->getDefaultIdProductAttribute();
        $taxPrice = $product->getPrice(true, $defaultCombination, $round_value);
        $productInfo[$field] = $this->getFormattedPrice($taxPrice, $this->getPriceFormattingConfig());
      }
      elseif ($field == "combinations_name") {
        if ($combination->id) {
          $productInfo[$field] = str_replace($product->name . " : ", '', productsExportModel::getProductName($product->id, $combination->id));
        } else {
          $productInfo[$field] = '';
        }

      } elseif ($field == "combinations_price") {
        $productInfo[$field] = $this->getFormattedPrice($combination->price, $this->getPriceFormattingConfig());
      } elseif ($field == "combinations_price_with_tax") {
        $taxPrice = $combination->price;
        $tmpPrice = ($taxPrice + ($taxPrice * ($product->tax_rate / 100)));
        $productInfo[$field] = $this->getFormattedPrice($tmpPrice, $this->getPriceFormattingConfig());
      } elseif ($field == "combinations_wholesale_price") {
        $productInfo[$field] = $this->getFormattedPrice($combination->wholesale_price, $this->getPriceFormattingConfig());
      } elseif ($field == "combinations_unit_price_impact") {
        $productInfo[$field] = $this->getFormattedPrice($combination->unit_price_impact, $this->getPriceFormattingConfig());
      }
      elseif ($field == "minimal_quantity") {
        if ($id_product_attribute) {
          $productInfo[$field] = $combination->minimal_quantity;
        } else {
          $productInfo[$field] = $product->minimal_quantity;
        }
      }
	  elseif ($field == "location") {
        if ($id_product_attribute) {
          if( method_exists( new StockAvailable(), 'getLocation' ) ){
            $productInfo[$field] = StockAvailable::getLocation($product->id, $combination->id, $this->_idShop);
          }
          else{
            $productInfo[$field] = $combination->location;
          }
        } else {
          if( method_exists( new StockAvailable(), 'getLocation' ) ){
            $productInfo[$field] = StockAvailable::getLocation($product->id, 0, $this->_idShop);
          }
          else{
            $productInfo[$field] = $product->location;
          }
        }
      }
      elseif ($field == "low_stock_threshold") {
        if ($id_product_attribute) {
          $productInfo[$field] = $combination->low_stock_threshold;
        } else {
          $productInfo[$field] = $product->low_stock_threshold;
        }
      }
      elseif ($field == "low_stock_alert") {
        if ($id_product_attribute) {
          $productInfo[$field] = $combination->low_stock_alert;
        } else {
          $productInfo[$field] = $product->low_stock_alert;
        }
      }
      elseif ($field == "available_date") {
        if ($id_product_attribute) {
          $productInfo[$field] = $combination->available_date;
        } else {
          $productInfo[$field] = $product->available_date;
        }
      }
      elseif (preg_match('/id_specific_price_\d+/', $field)) {
        $productInfo[$field] = $this->getSpecificPriceAttribute('id_specific_price', $productId, $field, $id_product_attribute);
      } elseif (preg_match('/specific_price_\d+/', $field)) {
        $tmpPrice = $this->getSpecificPriceAttribute('price', $productId, $field, $id_product_attribute);

        if ($tmpPrice > 0) {
          $productInfo[$field] = $this->getFormattedPrice($tmpPrice, $this->getPriceFormattingConfig());
        }
      } elseif (preg_match('/^specific_price_from_quantity_\d+$/', $field)) {
        $productInfo[$field] = $this->getSpecificPriceAttribute('from_quantity', $productId, $field, $id_product_attribute);
      } elseif (preg_match('/^specific_price_reduction_\d+$/', $field)) {
        $tmpPrice = $this->getSpecificPriceAttribute('reduction', $productId, $field, $id_product_attribute);

        if ($tmpPrice > 0) {
          $productInfo[$field] = $this->getFormattedPrice($tmpPrice, $this->getPriceFormattingConfig(), false);
        }
      } elseif (preg_match('/^specific_price_reduction_type_\d+$/', $field)) {
        $productInfo[$field] = $this->getSpecificPriceAttribute('reduction_type', $productId, $field, $id_product_attribute);
      } elseif (preg_match('/^specific_price_from_\d+$/', $field)) {
        $productInfo[$field] = $this->getSpecificPriceAttribute('from', $productId, $field, $id_product_attribute);
      } elseif (preg_match('/^specific_price_to_\d+$/', $field)) {
        $productInfo[$field] = $this->getSpecificPriceAttribute('to', $productId, $field, $id_product_attribute);
      } elseif (preg_match('/^specific_price_id_group_\d+$/', $field)) {
        $productInfo[$field] = $this->getSpecificPriceAttribute('id_group', $productId, $field, $id_product_attribute);
      } elseif ($field == "combinations_reference") {
        $productInfo[$field] = $combination->reference;
      } elseif ($field == "combinations_location") {
        $productInfo[$field] = $combination->location;
      } elseif ($field == "combinations_weight") {
        $tmpPrice = Tools::ps_round($combination->weight, $round_value);
        $productInfo[$field] = number_format($tmpPrice, $round_value, '.', '');
      } elseif ($field == "combinations_ecotax") {
        $productInfo[$field] = $this->getFormattedPrice($combination->ecotax, $this->getPriceFormattingConfig());
      } elseif ($field == "combinations_ean13") {
        $productInfo[$field] = $combination->ean13;
      } elseif ($field == "combinations_upc") {
        $productInfo[$field] = $combination->upc;
      } elseif ($field == "combinations_isbn") {
        $productInfo[$field] = $this->getCombinationIsbnForExport($combination);
      } elseif ($field == "tags") {
        $productInfo[$field] = $product->getTags($this->_idLang);
      } elseif ($field == "id_attachments") {
        $productInfo[$field] = "";
        foreach ($product->getAttachments($this->_idLang) as $attachments) {
          $productInfo[$field] .= $attachments['id_attachment'] . ",";
        }
        $productInfo[$field] = rtrim($productInfo[$field], ",");
      } elseif ($field == "attachments_name") {
        $productInfo[$field] = "";
        foreach ($product->getAttachments($this->_idLang) as $attachments) {
          $productInfo[$field] .= $attachments['name'] . ",";
        }
        $productInfo[$field] = rtrim($productInfo[$field], ",");
      } elseif ($field == "attachments_description") {
        $productInfo[$field] = "";
        foreach ($product->getAttachments($this->_idLang) as $attachments) {
          $productInfo[$field] .= $attachments['description'] . ",";
        }
        $productInfo[$field] = rtrim($productInfo[$field], ",");
      } elseif ($field == "attachments_file") {
        $productInfo[$field] = "";
        $link = new Link(null, 'http://');
        foreach ($product->getAttachments($this->_idLang) as $attachments) {
          $productInfo[$field] .= $link->getPageLink('attachment', true, NULL, "id_attachment=" . $attachments['id_attachment']) . ",";
        }
        $productInfo[$field] = rtrim($productInfo[$field], ",");
      } elseif ($field == "id_carriers") {
        $productInfo[$field] = "";
        foreach ($product->getCarriers() as $carriers) {
          $productInfo[$field] .= $carriers['id_carrier'] . ",";
        }
        $productInfo[$field] = rtrim($productInfo[$field], ",");
      } elseif ($field == "id_product_accessories") {
        $productInfo[$field] = "";
        foreach ($product->getWsAccessories() as $accessories) {
          $productInfo[$field] .= $accessories['id'] . ",";
        }
        $productInfo[$field] = rtrim($productInfo[$field], ",");
      } elseif ($field == "image_caption") {
        $productInfo[$field] = "";
        foreach ($product->getWsImages() as $image) {
          $img = new Image($image['id'], $this->_idLang);
          $productInfo[$field] .= $img->legend . ",";
        }
        $productInfo[$field] = rtrim($productInfo[$field], ",");
      } elseif ($field == "images") {
        $productInfo[$field] = "";
        $link = new Link(null, 'http://');
        $combImages = $this->getCombinationImageById($combination->id, $this->_idLang, false);
        if (!$combImages) {
          $combImages = $product->getWsImages();
        }
        foreach ($combImages as $image) {
          $img_link = $link->getImageLink($product->link_rewrite, $image['id']);
          $productInfo[$field] .= $this->getImageLinkWithProperShopProtocol($img_link) . ",";
        }
        $productInfo[$field] = rtrim($productInfo[$field], ",");
      } elseif ($field == "image_cover") {
        $cover = $product->getCover($product->id);
        $images = $this->getCombinationImageById($id_product_attribute, $this->_idLang);
        if (!$cover && !$images) {
          $productInfo[$field] = false;
        } else {
          if ($images['id_image']) {
            $url_cover = _PS_ROOT_DIR_.'/img/p/'.Image::getImgFolderStatic($images['id_image']).$images['id_image'].'-'.$this->_imageType.'.jpg';
          } else {
            $url_cover = _PS_ROOT_DIR_.'/img/p/'.Image::getImgFolderStatic($cover['id_image']).$cover['id_image'].'-'.$this->_imageType.'.jpg';
          }
          $productInfo[$field] = $url_cover;
        }
      }
      elseif ($field == "cover_image_url") {
        $cover = $product->getCover($product->id);
        $images = $this->getCombinationImageById($id_product_attribute, $this->_idLang);
        if (!$cover && !$images) {
          $productInfo[$field] = false;
        } else {
          $link = new Link(null, 'http://');

          if ($images['id_image']) {
            $img_link = $link->getImageLink($product->link_rewrite, $images['id_image'], $this->_imageType);
            $url_cover = $this->getImageLinkWithProperShopProtocol($img_link);
          } else {
            $img_link = $link->getImageLink($product->link_rewrite, $cover['id_image'], $this->_imageType);
            $url_cover = $this->getImageLinkWithProperShopProtocol($img_link);
          }
          $productInfo[$field] = $url_cover;
        }
      }
      elseif (strpos($field, 'FEATURE_') !== false) {
        $needFeature = explode('_', $field);
        $needFeature = $needFeature[1];
        if (Module::getInstanceByName('pm_multiplefeatures')) {
          $features = Module::getInstanceByName('pm_multiplefeatures')->getFrontFeatures($productId);
        } else {
          $features = $product->getFrontFeatures($this->_idLang);
        }

        foreach ($features as $feature) {
          if ($needFeature == $feature['name']) {
            if (!isset($productInfo["FEATURE_" . $feature['name']])) {
              $productInfo["FEATURE_" . $feature['name']] = $feature['value'];
            } else {
              $productInfo["FEATURE_" . $feature['name']] = $productInfo["FEATURE_" . $feature['name']] . ',' . $feature['value'];
            }
          }
        }
      } elseif ($field == "features") {
        $productInfo[$field] = "";
        if (Module::getInstanceByName('pm_multiplefeatures')) {
          $features = Module::getInstanceByName('pm_multiplefeatures')->getFrontFeatures($productId);
        } else {
          $features = $product->getFrontFeatures($this->_idLang);
        }

        foreach ($features as $feature) {
          if (!isset($productInfo["FEATURE_" . $feature['name']])) {
            $productInfo["FEATURE_" . $feature['name']] = $feature['value'];
          } else {
            $productInfo["FEATURE_" . $feature['name']] = $productInfo["FEATURE_" . $feature['name']] . ',' . $feature['value'];
          }
        }
      } elseif ($field == "product_link") {
        $productInfo[$field] = "";
        $link = new Link();
        $productInfo[$field] = $link->getProductLink($productId, null, null, null, $this->_idLang, $this->_idShop, $combination->id);
      } elseif ($field == "description" || $field == "description_short") {
        $mora_settings = $this->_more_settings;
        if ($mora_settings['strip_tags']) {
          $productInfo[$field] = strip_tags($product->$field);
        } else {
          $productInfo[$field] = $product->$field;
        }
      } elseif ($field == "width" || $field == "height" || $field == "depth" || $field == "weight") {
        $tmpPrice = Tools::ps_round($product->$field, $round_value);
        $productInfo[$field] = number_format($tmpPrice, $round_value, '.', '');
      } else if (in_array($field, $this->getListOfPackItemsFields())) {
        $pack_items_property_values = '';

        if ($product_is_pack && $pack_items_property = $this->getPackItemsFieldProperty($field)) {
          $pack_items_property_values = $this->getPackItemsPropertyValuesForExport($productId, $pack_items_property);
        }

        $productInfo[$field] = $pack_items_property_values;
      } else if (preg_match('/^customization_field_.+$/', $field)) {
          $field_param = explode('_', $field);
          $field_param = end($field_param);
          $productInfo[$field] = $this->getCustomizationFieldsParameterValues($productId, $field_param, $this->_idLang);
      } else {
        $productInfo[$field] = $product->$field;
      }
    }

    return $productInfo;
  }

  private function _getAttributesName($combinationId, $id_lang)
  {
    return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT al.*, a.id_attribute_group
			FROM ' . _DB_PREFIX_ . 'product_attribute_combination pac
			JOIN ' . _DB_PREFIX_ . 'attribute_lang al ON (pac.id_attribute = al.id_attribute AND al.id_lang=' . (int)$id_lang . ')
			LEFT JOIN ' . _DB_PREFIX_ . 'attribute a ON (a.id_attribute = al.id_attribute)
			WHERE pac.id_product_attribute=' . (int)$combinationId . '
    ');
  }

  public function getProductInfo($selected_fields, $productId, $id_product_attribute, $product)
  {
    $combinations = array();
    $productInfo = array();

    $more_settings = $this->_more_settings;
    $round_value = $more_settings['round_value'];
    $decoration_price = $more_settings['decoration_price'];
    $separator_decimal_points = $more_settings['separator_decimal_points'];

    $address = null;
    if (is_object(Context::getContext()->cart) && Context::getContext()->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')} != null) {
      $address = Context::getContext()->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')};
    }

    $product->tax_rate = $product->getTaxesRate(new Address($address));
    $product->base_price = $product->price;
    $product->unit_price = ($product->unit_price_ratio != 0 ? $product->price / $product->unit_price_ratio : 0);

    $product_is_pack = Pack::isPack((int)$productId);

    foreach ($product->getWsCombinations() as $attribute) {
      $combination = new Combination($attribute['id']);
      $combinations[$attribute['id']] = $combination;
    }

    foreach ($selected_fields as $field => $value) {
      if ($field == "id_product") {
        $productInfo[$field] = $productId;
      } elseif ($field == "id_product_attribute") {
        $productInfo[$field] = "";
        foreach ($combinations as $key => $attribute) {
          $productInfo[$field] .= $key . ",";
        }
        $productInfo[$field] = rtrim($productInfo[$field], ",");
      } else if (strpos($field, 'extra_field_') !== false) {
        $extra_field_value = '';
        if (isset($more_settings['extra_fields'][$field]['value'])) {
          $extra_field_value = $more_settings['extra_fields'][$field]['value'];
        }

        $productInfo[$field] = $extra_field_value;
      } elseif ($field == "isbn") {
          $productInfo[$field] = $this->getIsbnForExport($product);
      } elseif ($field == "category_default_name") {
        $productInfo[$field] = "";
        $catName = CategoryCore::getCategoryInformations(array($product->id_category_default), $this->_idLang);
        if (isset($catName[$product->id_category_default])) {
          $productInfo[$field] = $catName[$product->id_category_default]['name'];
        }
      } elseif (strpos($field, 'Attribute_') !== false) {
        $productInfo[$field] = "";
        $existsAttr = array();
        $needAttribute = explode('_', $field);
        $needAttribute = $needAttribute[1];
        foreach ($combinations as $key => $attribute) {
          foreach ($this->_getAttributesName($key, $this->_idLang) as $attrValues) {
            if ($needAttribute != $attrValues['id_attribute_group']) {
              continue;
            }
            if (!isset($productInfo['Attribute_' . $attrValues['id_attribute_group']])) {
              $productInfo['Attribute_' . $attrValues['id_attribute_group']] = '';
            }
            if (!isset($existsAttr[$attrValues['id_attribute_group'] . $attrValues['name']])) {
              $productInfo['Attribute_' . $attrValues['id_attribute_group']] .= "," . $attrValues['name'];
              $productInfo['Attribute_' . $attrValues['id_attribute_group']] = ltrim($productInfo['Attribute_' . $attrValues['id_attribute_group']], ",");
              $existsAttr[$attrValues['id_attribute_group'] . $attrValues['name']] = true;
            }
          }

        }
      } elseif ($field == "combinations_value") {
        $productInfo[$field] = "";
        $existsAttr = array();
        foreach ($combinations as $key => $attribute) {
          foreach ($this->_getAttributesName($key, $this->_idLang) as $attrValues) {
            if (!isset($productInfo['Attribute_' . $attrValues['id_attribute_group']])) {
              $productInfo['Attribute_' . $attrValues['id_attribute_group']] = '';
            }
            if (!isset($existsAttr[$attrValues['id_attribute_group'] . $attrValues['name']])) {
              $productInfo['Attribute_' . $attrValues['id_attribute_group']] .= "," . $attrValues['name'];
              $productInfo['Attribute_' . $attrValues['id_attribute_group']] = ltrim($productInfo['Attribute_' . $attrValues['id_attribute_group']], ",");
              $existsAttr[$attrValues['id_attribute_group'] . $attrValues['name']] = true;
            }
          }

        }
      } elseif ($field == "separated_categories") {
        $categories = $this->_getWsCategories($product->id);
        $this->_parentCategories = array();

        $currentCount = 0;
        foreach ($categories as $category) {
          $catTree = '';
          if (($tree = $this->_getCategoryTree($category['id']))) {
            $currentCount++;
            foreach ($tree as $cat) {
              $catTree .= $cat . '->';
            }
            $catTree = rtrim($catTree, '->');
            $productInfo['category_tree_' . $currentCount] = $catTree;
          }
        }
      } elseif ($field == "images_value") {
        $link = new Link(null, 'http://');
        foreach ($product->getWsImages() as $key => $image) {
          $productInfo['images_value_' . ($key + 1)] = "";
          $img_link = $link->getImageLink($product->link_rewrite, $image['id']);
          if ($img_link) {
            $productInfo['images_value_' . ($key + 1)] = $this->getImageLinkWithProperShopProtocol($img_link);
          }
        }
      } elseif ($field == "categories_ids") {
        $productInfo[$field] = "";
        foreach ($product->getWsCategories() as $category) {
          $productInfo[$field] .= $category['id'] . ",";
        }
        $productInfo[$field] = rtrim($productInfo[$field], ",");
      } 
      elseif ($field == "delivery_in_stock") {
        if( $product->additional_delivery_times == 2 ){
          $productInfo[$field] = $product->delivery_in_stock;
        }
        elseif( $product->additional_delivery_times == 1 ){
          $productInfo[$field] = Configuration::get('PS_LABEL_DELIVERY_TIME_AVAILABLE', $this->_idLang);
        }
        else{
          $productInfo[$field] = '';
        }
      }
      elseif ($field == "delivery_out_stock") {
        if( $product->additional_delivery_times == 2 ){
          $productInfo[$field] = $product->delivery_out_stock;
        }
        elseif( $product->additional_delivery_times == 1 ){
          $productInfo[$field] = Configuration::get('PS_LABEL_DELIVERY_TIME_OOSBOA', $this->_idLang);
        }
        else{
          $productInfo[$field] = '';
        }
      }
      elseif ($field == "categories_names") {
        $productInfo[$field] = "";
        foreach ($product->getWsCategories() as $category) {
          $cat_obj = new Category($category['id'], $this->_idLang, $this->_idShop);
          $productInfo[$field] .= $cat_obj->name . ",";
        }
        $productInfo[$field] = rtrim($productInfo[$field], ",");
      } elseif (preg_match('/^separate_supplier_(\w+)_(\d+)$/', $field, $supplier_matches)) {
        if (count($supplier_matches) != 3) {
          continue;
        }

        $supplier_property = $supplier_matches[1];
        $supplier_id       = $supplier_matches[2];
        $supplier_ready_for_export = '';

        if ($combinations) {
          foreach ($combinations as $combination) {
            $product_supplier_property = $this->getSupplierProperty($supplier_property, $supplier_id, $productId, $combination->id);
            $supplier_ready_for_export .= $product_supplier_property ? (',' . $product_supplier_property) : '';
          }
        } else {
          $product_supplier_property = $this->getSupplierProperty($supplier_property, $supplier_id, $productId, 0);
          $supplier_ready_for_export = $product_supplier_property ?: '';
        }

        $productInfo[$field] = trim($supplier_ready_for_export, ', ');
      } elseif ($field == 'suppliers_ids') {
        $product_supplier = $this->_model->getProductSuppliersID($productId);
        if ($product_supplier) {
          $productInfo[$field] = $product_supplier[0]['suppliers_ids'];
        }
      } elseif ($field == 'suppliers_name') {
        $product_supplier = $this->_model->getProductSuppliersID($productId);
        if ($product_supplier) {
          $productInfo[$field] = $product_supplier[0]['suppliers_name'];
        }
      } elseif ($field == 'quantity') {
        if ($combinations) {
          $productInfo[$field] = "";
          foreach ($combinations as $key => $combination) {
            $productInfo[$field] .= $product->getQuantity($productId, $key) . ",";
          }
          $productInfo[$field] = rtrim($productInfo[$field], ",");
        } else {
          $productInfo[$field] = $product->getQuantity($productId, 0);
        }
      } elseif ($field == 'total_quantity') {
        $productInfo[$field] = $product->getQuantity($productId, 0);
      } elseif ($field == 'out_of_stock') {
        $productInfo[$field] = StockAvailable::outOfStock($productId);
      } elseif ($field == 'depends_on_stock') {
        $productInfo[$field] = StockAvailable::dependsOnStock($productId);
      } elseif ($field == 'manufacturer_name') {
        $productInfo[$field] = Manufacturer::getNameById((int)$product->id_manufacturer);
      } elseif ($field == 'supplier_name') {
        $productInfo[$field] = Supplier::getNameById((int)$product->id_supplier);
      } elseif ($field == 'new') {
        $productInfo[$field] = $product->isNew();
      } elseif ($field == 'supplier_reference') {
        $sReference = '';
        if ($combinations) {
          foreach ($combinations as $combination) {
            $sReference .= ProductSupplier::getProductSupplierReference($productId, $combination->id, $product->id_supplier) . ",";
          }
          $sReference = rtrim($sReference, ",");
        } else {
          $sReference = ProductSupplier::getProductSupplierReference($productId, 0, $product->id_supplier);
          if (!$sReference) {
            $sReference = '';
          }
        }
        $productInfo[$field] = $sReference;
      } elseif ($field == 'supplier_price') {
        $sPrice = '';
        if ($combinations) {
          foreach ($combinations as $combination) {
            $tmpPrice = ProductSupplier::getProductSupplierPrice($productId, $combination->id, $product->id_supplier);
            $sPrice .= $this->getFormattedPrice($tmpPrice, $this->getPriceFormattingConfig()) . ",";
          }

          $sPrice = rtrim($sPrice, ",");
        } else {
          $sPrice = ProductSupplier::getProductSupplierPrice($productId, 0, $product->id_supplier);
          if (!$sPrice) {
            $sPrice = '';
          } else {
            $sPrice = Tools::ps_round($sPrice, $round_value);
            $sPrice = number_format($sPrice, $round_value, $separator_decimal_points, '');
            $sPrice = str_replace('[PRICE]', $sPrice, $decoration_price);
          }
        }

        $productInfo[$field] = $sPrice;
      } elseif ($field == 'supplier_price_currency') {
        if ($combinations) {
          $productInfo[$field] = "";
          foreach ($combinations as $combination) {
            $sPriceCurrency = ProductSupplier::getProductSupplierPrice($productId, $combination->id, $product->id_supplier, true);
            if (isset($sPriceCurrency['id_currency']) && $sPriceCurrency['id_currency']) {
              $tmpCurrency = new Currency($sPriceCurrency['id_currency']);
              $sPriceCurrency['id_currency'] = $tmpCurrency->iso_code;
              $productInfo[$field] .= $sPriceCurrency['id_currency'] . ",";
            }
          }

          $productInfo[$field] = rtrim($productInfo[$field], ",");
        } else {
          $sPriceCurrency = ProductSupplier::getProductSupplierPrice($productId, 0, $product->id_supplier, true);
          if (isset($sPriceCurrency['id_currency'])) {
            $tmpCurrency = new Currency($sPriceCurrency['id_currency']);
            $sPriceCurrency['id_currency'] = $tmpCurrency->iso_code;
            $productInfo[$field] = $sPriceCurrency['id_currency'];
          } else {
            $productInfo[$field] = '';
          }
        }
      } elseif ($field == "base_price" || $field == "ecotax" || $field == "additional_shipping_cost" || $field == "unit_price") {
        $productInfo[$field] = $this->getFormattedPrice($product->$field, $this->getPriceFormattingConfig());
      } elseif ($field == "base_price_with_tax") {
        $taxPrice = $product->base_price;
        if ($product->tax_rate) {
          $taxPrice = $taxPrice + ($taxPrice * ($product->tax_rate / 100));
        }

        $productInfo[$field] = $this->getFormattedPrice($taxPrice, $this->getPriceFormattingConfig());
      } elseif ($field == "wholesale_price") {
        $productInfo[$field] = $this->getFormattedPrice($product->$field, $this->getPriceFormattingConfig());
      } elseif ($field == "price") {
        if ($combinations) {
          $productInfo[$field] = "";
          foreach ($combinations as $combination) {
            $price = $product->getPrice(false, $combination->id, $round_value) . ",";
            $productInfo[$field] .= $this->getFormattedPrice($price, $this->getPriceFormattingConfig()) . ",";
          }
          $productInfo[$field] = rtrim($productInfo[$field], ",");
        } else {
          $taxPrice = $product->getPrice(false, 0, $round_value);
          $productInfo[$field] = $this->getFormattedPrice($taxPrice, $this->getPriceFormattingConfig());
        }
      } elseif ($field == "final_price_with_tax") {
        if ($combinations) {
          $productInfo[$field] = "";
          foreach ($combinations as $combination) {
            $price = $product->getPrice(true, $combination->id, $round_value) . ",";
            $productInfo[$field] .= $this->getFormattedPrice($price, $this->getPriceFormattingConfig()) . ",";
          }

          $productInfo[$field] = rtrim($productInfo[$field], ",");
        } else {
          $taxPrice = $product->getPrice(true, 0, $round_value);
          $productInfo[$field] = $this->getFormattedPrice($taxPrice, $this->getPriceFormattingConfig());
        }
      }
      elseif ($field == "combination_final_price_pre_tax") {
        $defaultCombination = $product->getDefaultIdProductAttribute();
        $taxPrice = $product->getPrice(false, $defaultCombination, $round_value);
        $productInfo[$field] = $this->getFormattedPrice($taxPrice, $this->getPriceFormattingConfig());
      }
      elseif ($field == "combination_final_price_with_tax") {
        $defaultCombination = $product->getDefaultIdProductAttribute();
        $taxPrice = $product->getPrice(true, $defaultCombination, $round_value);
        $productInfo[$field] = $this->getFormattedPrice($taxPrice, $this->getPriceFormattingConfig());
      }
      elseif ($field == "combinations_name") {
        $productInfo[$field] = "";
        foreach ($combinations as $combination) {
          $productInfo[$field] .= str_replace($product->name . " : ", '', productsExportModel::getProductName($product->id, $combination->id, $this->_idLang)) . ",";
        }
        $productInfo[$field] = rtrim($productInfo[$field], ",");
      } elseif ($field == "name_with_combination") {
		  if (!empty($combinations)) {
              $productInfo[$field] = "";
              foreach ($combinations as $combination) {
                  $productInfo[$field] .= productsExportModel::getProductName($product->id, $combination->id, $this->_idLang) . ",";
              }
          } else {
              $productInfo[$field] = productsExportModel::getProductName($product->id, null, $this->_idLang) . ",";
          }

        $productInfo[$field] = rtrim($productInfo[$field], ",");
      } elseif ($field == "combinations_price") {
        $productInfo[$field] = "";

        foreach ($combinations as $combination) {
          $productInfo[$field] .= $this->getFormattedPrice($combination->price, $this->getPriceFormattingConfig()) . ",";
        }

        $productInfo[$field] = rtrim($productInfo[$field], ",");
      } elseif ($field == "combinations_price_with_tax") {
        $productInfo[$field] = "";
        foreach ($combinations as $combination) {
          $taxPrice = $combination->price;
          $price = ($taxPrice + ($taxPrice * ($product->tax_rate / 100)));
          $productInfo[$field] .= $this->getFormattedPrice($price, $this->getPriceFormattingConfig()) . ",";
        }
        $productInfo[$field] = rtrim($productInfo[$field], ",");
      } elseif (preg_match('/id_specific_price_\d+/', $field)) {
        $productInfo[$field] = $this->getSpecificPriceAttribute('id_specific_price', $productId, $field);
      } elseif (preg_match('/specific_price_\d+/', $field)) {
        $tmpPrice = $this->getSpecificPriceAttribute('price', $productId, $field);

        if ($tmpPrice > 0) {
          $productInfo[$field] = $this->getFormattedPrice($tmpPrice, $this->getPriceFormattingConfig());
        }
      } elseif (preg_match('/^specific_price_from_quantity_\d+$/', $field)) {
        $productInfo[$field] = $this->getSpecificPriceAttribute('from_quantity', $productId, $field);
      } elseif (preg_match('/^specific_price_reduction_\d+$/', $field)) {
        $tmpPrice = $this->getSpecificPriceAttribute('reduction', $productId, $field);

        if ($tmpPrice > 0) {
          $productInfo[$field] = $this->getFormattedPrice($tmpPrice, $this->getPriceFormattingConfig(), false);
        }
      } elseif (preg_match('/^specific_price_reduction_type_\d+$/', $field)) {
        $productInfo[$field] = $this->getSpecificPriceAttribute('reduction_type', $productId, $field);
      } elseif (preg_match('/^specific_price_from_\d+$/', $field)) {
        $productInfo[$field] = $this->getSpecificPriceAttribute('from', $productId, $field);
      } elseif (preg_match('/^specific_price_to_\d+$/', $field)) {
        $productInfo[$field] = $this->getSpecificPriceAttribute('to', $productId, $field);
      } elseif (preg_match('/^specific_price_id_group_\d+$/', $field)) {
        $productInfo[$field] = $this->getSpecificPriceAttribute('id_group', $productId, $field);
      } elseif ($field == "combinations_wholesale_price") {
        $productInfo[$field] = "";
        foreach ($combinations as $combination) {
          $productInfo[$field] .= $this->getFormattedPrice($combination->wholesale_price, $this->getPriceFormattingConfig()) . ",";
        }
        $productInfo[$field] = rtrim($productInfo[$field], ",");
      } elseif ($field == "combinations_unit_price_impact") {
        $productInfo[$field] = "";

        foreach ($combinations as $combination) {
          $productInfo[$field] .= $this->getFormattedPrice($combination->unit_price_impact, $this->getPriceFormattingConfig()) . ",";
        }

        $productInfo[$field] = rtrim($productInfo[$field], ",");
      }
      elseif ($field == "minimal_quantity") {
        if ($combinations) {
          $productInfo[$field] = "";
          foreach ($combinations as $combination) {
            $productInfo[$field] .= $combination->minimal_quantity . ",";
          }
          $productInfo[$field] = rtrim($productInfo[$field], ",");
        } else {
          $productInfo[$field] = $product->minimal_quantity;
        }
      }
      elseif ($field == "location") {
        if ($combinations) {
          $productInfo[$field] = "";
          foreach ($combinations as $combination) {
            if( method_exists( new StockAvailable(), 'getLocation' ) ){
              $productInfo[$field] .= StockAvailable::getLocation($product->id, $combination->id, $this->_idShop) . ",";
            }
            else{
              $productInfo[$field] .= $combination->location;
            }
          }
          $productInfo[$field] = rtrim($productInfo[$field], ",");
        } else {
          if( method_exists( new StockAvailable(), 'getLocation' ) ){
            $productInfo[$field] = StockAvailable::getLocation($product->id, 0, $this->_idShop);
          }
          else{
            $productInfo[$field] = $product->location;
          }
        }
      }
      elseif ($field == "low_stock_threshold") {
        if ($combinations) {
          $productInfo[$field] = "";
          foreach ($combinations as $combination) {
            $productInfo[$field] .= $combination->low_stock_threshold . ",";
          }
          $productInfo[$field] = rtrim($productInfo[$field], ",");
        } else {
          $productInfo[$field] = $product->low_stock_threshold;
        }
      }
      elseif ($field == "low_stock_alert") {
        if ($combinations) {
          $productInfo[$field] = "";
          foreach ($combinations as $combination) {
            $productInfo[$field] .= $combination->low_stock_alert . ",";
          }
          $productInfo[$field] = rtrim($productInfo[$field], ",");
        } else {
          $productInfo[$field] = $product->low_stock_alert;
        }
      }
      elseif ($field == "available_date") {
        if ($combinations) {
          $productInfo[$field] = "";
          foreach ($combinations as $combination) {
            $productInfo[$field] .= $combination->available_date . ",";
          }
          $productInfo[$field] = rtrim($productInfo[$field], ",");
        } else {
          $productInfo[$field] = $product->available_date;
        }
      }
      elseif ($field == "combinations_reference") {
        $productInfo[$field] = "";
        foreach ($combinations as $combination) {
          $productInfo[$field] .= $combination->reference . ",";
        }
        $productInfo[$field] = rtrim($productInfo[$field], ",");
      } elseif ($field == "combinations_location") {
        $productInfo[$field] = "";
        foreach ($combinations as $combination) {
          $productInfo[$field] .= $combination->location . ",";
        }
        $productInfo[$field] = rtrim($productInfo[$field], ",");
      } elseif ($field == "combinations_weight") {
        $productInfo[$field] = "";
        foreach ($combinations as $combination) {
          $tmpPrice = Tools::ps_round($combination->weight, $round_value);
          $productInfo[$field] .= number_format($tmpPrice, $round_value, '.', '') . ",";
        }
        $productInfo[$field] = rtrim($productInfo[$field], ",");
      } elseif ($field == "combinations_ecotax") {
        $productInfo[$field] = "";
        foreach ($combinations as $combination) {
          $productInfo[$field] .= $this->getFormattedPrice($combination->ecotax, $this->getPriceFormattingConfig()) . ",";
        }

        $productInfo[$field] = rtrim($productInfo[$field], ",");
      } elseif ($field == "combinations_ean13") {
        $productInfo[$field] = "";
        foreach ($combinations as $combination) {
          $productInfo[$field] .= $combination->ean13 . ",";
        }
        $productInfo[$field] = rtrim($productInfo[$field], ",");
      } elseif ($field == "combinations_upc") {
        $productInfo[$field] = "";
        foreach ($combinations as $combination) {
          $productInfo[$field] .= $combination->upc . ",";
        }
        $productInfo[$field] = rtrim($productInfo[$field], ",");
      } elseif ($field == "combinations_isbn") {
        $productInfo[$field] = $this->getCombinationsIsbnForExportInOneField($combinations);
      } elseif ($field == "tags") {
        $productInfo[$field] = $product->getTags($this->_idLang);
      } elseif ($field == "id_attachments") {
        $productInfo[$field] = "";
        foreach ($product->getAttachments($this->_idLang) as $attachments) {
          $productInfo[$field] .= $attachments['id_attachment'] . ",";
        }
        $productInfo[$field] = rtrim($productInfo[$field], ",");
      } elseif ($field == "attachments_name") {
        $productInfo[$field] = "";
        foreach ($product->getAttachments($this->_idLang) as $attachments) {
          $productInfo[$field] .= $attachments['name'] . ",";
        }
        $productInfo[$field] = rtrim($productInfo[$field], ",");
      } elseif ($field == "attachments_description") {
        $productInfo[$field] = "";
        foreach ($product->getAttachments($this->_idLang) as $attachments) {
          $productInfo[$field] .= $attachments['description'] . ",";
        }
        $productInfo[$field] = rtrim($productInfo[$field], ",");
      } elseif ($field == "attachments_file") {
        $productInfo[$field] = "";
        $link = new Link(null, 'http://');
        foreach ($product->getAttachments($this->_idLang) as $attachments) {
          $productInfo[$field] .= $link->getPageLink('attachment', true, NULL, "id_attachment=" . $attachments['id_attachment']) . ",";
        }
        $productInfo[$field] = rtrim($productInfo[$field], ",");
      } elseif ($field == "id_carriers") {
        $productInfo[$field] = "";
        foreach ($product->getCarriers() as $carriers) {
          $productInfo[$field] .= $carriers['id_carrier'] . ",";
        }
        $productInfo[$field] = rtrim($productInfo[$field], ",");
      } elseif ($field == "id_product_accessories") {
        $productInfo[$field] = "";
        foreach ($product->getWsAccessories() as $accessories) {
          $productInfo[$field] .= $accessories['id'] . ",";
        }
        $productInfo[$field] = rtrim($productInfo[$field], ",");
      } elseif ($field == "image_caption") {
        $productInfo[$field] = "";
        foreach ($product->getWsImages() as $image) {
          $img = new Image($image['id'], $this->_idLang);
          $productInfo[$field] .= $img->legend . ",";
        }
        $productInfo[$field] = rtrim($productInfo[$field], ",");
      } elseif ($field == "images") {
        $productInfo[$field] = "";
        $link = new Link(null, 'http://');
        foreach ($product->getWsImages() as $image) {
          $img_link = $link->getImageLink($product->link_rewrite, $image['id']);
          $productInfo[$field] .= $this->getImageLinkWithProperShopProtocol($img_link) . ",";
        }
        $productInfo[$field] = rtrim($productInfo[$field], ",");
      } elseif ($field == "image_cover") {
        $cover = $product->getCover($product->id);
        if (!$cover) {
          $productInfo[$field] = false;
        } else {
          $url_cover = _PS_ROOT_DIR_.'/img/p/'.Image::getImgFolderStatic($cover['id_image']).$cover['id_image'].'-'.$this->_imageType.'.jpg';
          $productInfo[$field] = $url_cover;
        }
      }
      elseif ($field == "cover_image_url") {
        $cover = $product->getCover($product->id);
        if (!$cover) {
          $productInfo[$field] = false;
        } else {
          $link = new Link(null, 'http://');
          $img_link = $link->getImageLink($product->link_rewrite, $cover['id_image'], $this->_imageType);
          $url_cover = $this->getImageLinkWithProperShopProtocol($img_link);
          $productInfo[$field] = $url_cover;
        }
      }
      elseif (strpos($field, 'FEATURE_') !== false) {
        $needFeature = explode('_', $field);
        $needFeature = $needFeature[1];
        if (Module::getInstanceByName('pm_multiplefeatures')) {
          $features = Module::getInstanceByName('pm_multiplefeatures')->getFrontFeatures($productId);
        } else {
          $features = $product->getFrontFeatures($this->_idLang);
        }

        foreach ($features as $feature) {
          if ($needFeature == $feature['name']) {
            if (!isset($productInfo["FEATURE_" . $feature['name']])) {
              $productInfo["FEATURE_" . $feature['name']] = $feature['value'];
            } else {
              $productInfo["FEATURE_" . $feature['name']] = $productInfo["FEATURE_" . $feature['name']] . ',' . $feature['value'];
            }
          }
        }
      } elseif ($field == "features") {
        $productInfo[$field] = "";
        if (Module::getInstanceByName('pm_multiplefeatures')) {
          $features = Module::getInstanceByName('pm_multiplefeatures')->getFrontFeatures($productId);
        } else {
          $features = $product->getFrontFeatures($this->_idLang);
        }

        foreach ($features as $feature) {
          if (!isset($productInfo["FEATURE_" . $feature['name']])) {
            $productInfo["FEATURE_" . $feature['name']] = $feature['value'];
          } else {
            $productInfo["FEATURE_" . $feature['name']] = $productInfo["FEATURE_" . $feature['name']] . ',' . $feature['value'];
          }
        }
      } elseif ($field == "product_link") {
        $productInfo[$field] = "";
        $link = new Link(null, 'http://');
        $productInfo[$field] = $link->getProductLink($productId);
      } elseif ($field == "description" || $field == "description_short") {
        $mora_settings = $this->_more_settings;
        if ($mora_settings['strip_tags']) {
          $productInfo[$field] = strip_tags($product->$field);
        } else {
          $productInfo[$field] = $product->$field;
        }
      } elseif ($field == "width" || $field == "height" || $field == "depth" || $field == "weight") {
        $tmpPrice = Tools::ps_round($product->$field, $round_value);
        $productInfo[$field] = number_format($tmpPrice, $round_value, '.', '');
      } else if (in_array($field, $this->getListOfPackItemsFields())) {
        $pack_items_property_values = '';

        if ($product_is_pack && $pack_items_property = $this->getPackItemsFieldProperty($field)) {
          $pack_items_property_values = $this->getPackItemsPropertyValuesForExport($productId, $pack_items_property);
        }

        $productInfo[$field] = $pack_items_property_values;
      } else if (preg_match('/^customization_field_.+$/', $field)) {
        $field_param = explode('_', $field);
        $field_param = end($field_param);
        $productInfo[$field] = $this->getCustomizationFieldsParameterValues($productId, $field_param, $this->_idLang);
      } else {
        if (property_exists($product, $field)) {
          $productInfo[$field] = $product->$field;
        }
      }
    }
    return $productInfo;
  }

  public static function getCombinationImageById($id_product_attribute, $id_lang, $cover = true)
  {
    if (!Combination::isFeatureActive() || !$id_product_attribute) {
      return false;
    }

    $result = Db::getInstance()->executeS('
			SELECT pai.`id_image`,pai.`id_image` as id, pai.`id_product_attribute`, il.`legend`
			FROM `' . _DB_PREFIX_ . 'product_attribute_image` pai
			LEFT JOIN `' . _DB_PREFIX_ . 'image_lang` il ON (il.`id_image` = pai.`id_image`)
			LEFT JOIN `' . _DB_PREFIX_ . 'image` i ON (i.`id_image` = pai.`id_image`)
			WHERE pai.`id_product_attribute` = ' . (int)$id_product_attribute . ' AND il.`id_lang` = ' . (int)$id_lang . ' ORDER by i.`position`'
    );

    if (!$result) {
      return false;
    }

    if ($cover) {
      return $result[0];
    } else {
      return $result;
    }
  }

  private function _getXmlHead($fieldName)
  {
    if (!$this->_xml_head) {
      $allFields = array_merge(Module::getInstanceByName('exportproducts')->_exportTabInformation, Module::getInstanceByName('exportproducts')->_exportTabPrices, Module::getInstanceByName('exportproducts')->_exportTabSeo, Module::getInstanceByName('exportproducts')->_exportTabAssociations, Module::getInstanceByName('exportproducts')->_exportTabShipping, Module::getInstanceByName('exportproducts')->_exportTabCombinations, Module::getInstanceByName('exportproducts')->_exportTabQuantities, Module::getInstanceByName('exportproducts')->_exportTabImages, Module::getInstanceByName('exportproducts')->_exportTabFeatures, Module::getInstanceByName('exportproducts')->_exportTabCustomization, Module::getInstanceByName('exportproducts')->_exportTabAttachments, Module::getInstanceByName('exportproducts')->_exportTabSuppliers);
      foreach ($allFields as $field) {

        if (isset($field['xml_head']) && array_key_exists($field['xml_head'], $this->edited_xml_names)) {
            $this->_xml_head[$field['val']] = $this->edited_xml_names[$field['xml_head']];
        } else {
            $this->_xml_head[$field['val']] = isset($field['xml_head']) ? $field['xml_head'] : '';
        }
      }
    }

    if (isset($this->_xml_head[$fieldName]) && $this->_xml_head[$fieldName]) {
      return $this->_xml_head[$fieldName];
    } else {
      return $fieldName;
    }
  }

  private function splitSpecificPriceFields($selected_fields)
  {
    $specific_price_fields = array(
      'id_specific_price' => '',
      'specific_price' => '',
      'specific_price_reduction' => '',
      'specific_price_reduction_type' => '',
      'specific_price_from' => '',
      'specific_price_to' => '',
      'specific_price_from_quantity' => '',
      'specific_price_id_group' => '',
    );

    $specific_price_selected_fields = array_intersect_key($selected_fields, $specific_price_fields);
    $specific_price_num_of_cols = $this->_model->getExportIds(
      $this->_idShop,
      $this->_idLang,
      $this->_separate,
      $this->_more_settings,
      $this->_limit,
      1,
      $count = false,
      $separateAttribute = false,
      $count_images = false,
      $separatedCategories = false,
      $features = false,
      true
    );

    if (!empty($specific_price_selected_fields)) {
      foreach ($specific_price_selected_fields as $key => $value) {
        unset($selected_fields[$key]);

        for ($i = 1; $i <= $specific_price_num_of_cols; $i++) {
          $selected_fields[$key . '_' . $i] = $value . '_' . $i;
        }
      }
    }

    return $selected_fields;
  }

  private function getSpecificPriceAttribute($specific_price_attr_name, $product_id, $field, $product_attribute_id = null)
  {
    $specific_price_field_number = explode('_', $field);
    $specific_price_field_number = end($specific_price_field_number);
    $specific_prices = SpecificPrice::getByProductId($product_id);

    if ($this->_separate) {
      if ($specific_prices[$specific_price_field_number - 1]['id_product_attribute'] != 0 && $specific_prices[$specific_price_field_number - 1]['id_product_attribute'] != $product_attribute_id) {
        return '';
      }
    }

    if (isset($specific_prices[$specific_price_field_number - 1][$specific_price_attr_name])) {
      $res = $specific_prices[$specific_price_field_number - 1][$specific_price_attr_name];
      if( $specific_price_attr_name == 'reduction' ){
        if( $specific_prices[$specific_price_field_number - 1]['reduction_type'] == 'percentage' ){
          return (float)$res*100;
        }
      }
      return $res;
    }
  }

  private function getFormattedPrice($price_value, $formatting_config, $convert_to_currency = true)
  {
    $tmpPrice = Tools::ps_round($price_value, $formatting_config['round_value']);

    if ($convert_to_currency === true) {
      $tmpPrice = Tools::convertPrice($tmpPrice, $formatting_config['currency'], true);
    }

    $tmpPrice = number_format($tmpPrice, $formatting_config['round_value'], $formatting_config['separator_decimal_points'], '');
    $tmpPrice = str_replace('[PRICE]', $tmpPrice, $formatting_config['decoration_price']);

    return $tmpPrice;
  }

  private function getPriceFormattingConfig()
  {
    $config = array('currency' => $this->_more_settings['currency'],
                    'round_value' => $this->_more_settings['round_value'],
                    'decoration_price' => $this->_more_settings['decoration_price'],
                    'separator_decimal_points' => $this->_more_settings['separator_decimal_points']
    );

    return $config;
  }

  private function getImageLinkWithProperShopProtocol($image_link)
  {
      return str_replace('http://', Tools::getShopProtocol(), $image_link);
  }

  private function getPackItemsPropertyValuesForExport($id_product, $property_name)
  {
    $pack_items_property_values = '';
    $pack_items = Pack::getItems($id_product, $this->_idLang);

    if (!empty($pack_items) && property_exists('Product', $property_name)) {
      foreach ($pack_items as $pack_item) {
        $pack_items_property_values .= ($pack_item->$property_name) . ',';
      }
    }

    return trim($pack_items_property_values, ', ');
  }

  private function getListOfPackItemsFields()
  {
    return array(
      'pack_items_id',
      'pack_items_id_pack_product_attribute',
      'pack_items_reference',
      'pack_items_ean13',
      'pack_items_upc',
      'pack_items_name'
    );
  }

  private function getPackItemsFieldProperty($full_field_name) {
    preg_match('/^pack_items_(.*)$/', $full_field_name, $matches);

    if (empty($matches)) {
      return false;
    }

    return $matches[count($matches) - 1];
  }

  private function getCustomizationFieldsParameterValues($product_id, $customization_field_parameter, $id_lang)
  {
    $customization_fields = $this->getCustomizationFieldsByProductId($product_id, $id_lang);
    $customization_fields_value = '';

    if (!empty($customization_fields)) {
        foreach ($customization_fields as $customization_field) {
            $customization_fields_value .= ',' . $customization_field[$customization_field_parameter];
        }
    }

    return trim($customization_fields_value, ',');
  }

  private function getCustomizationFieldsByProductId($product_id, $id_lang)
  {
    $query = 'SELECT * 
        FROM `' . _DB_PREFIX_ . 'customization_field` cf
        LEFT JOIN `' . _DB_PREFIX_ .'customization_field_lang` as cfl
        ON cf.`id_customization_field` = cfl.`id_customization_field`
        WHERE cf.`id_product` = "'.(int)$product_id.'" 
        AND cfl.`id_lang` = "'.(int)$id_lang.'"';

    return Db::getInstance()->executeS($query);
  }

  private function getIsbnForExport(Product $product)
  {
    if (!property_exists('Product', 'isbn')) {
      return '';
    }

    return $product->isbn;
  }

  private function getCombinationsIsbnForExportInOneField(array $combinations)
  {
    $isbn = '';
    if (!property_exists('Combination', 'isbn') || empty($combinations)) {
        return $isbn;
    }

    foreach ($combinations as $combination) {
        $isbn .= ',' . $combination->isbn;
    }

    return trim($isbn, ', ');
  }

  private function getCombinationIsbnForExport(Combination $combination)
  {
    if (!property_exists('Combination', 'isbn')) {
        return '';
    }

    return $combination->isbn;
  }

  private function getSupplierProperty($supplier_property, $supplier_id, $product_id, $combination_id)
  {
    $product_supplier = $this->getProductSupplierData($supplier_id, $product_id, $combination_id);

    switch ($supplier_property) {
      case 'reference':
        $supplier_property = 'product_supplier_reference';
        break;
      case 'price':
        $supplier_property = 'product_supplier_price_te';
        break;
      case 'currency':
        $supplier_property = 'id_currency';
        break;
      case 'id':
        $supplier_property = 'id_supplier';
        break;
      default:
        break;
    }

    return isset($product_supplier[$supplier_property]) ? $product_supplier[$supplier_property] : false;
  }

  private function getProductSupplierData($id_supplier, $id_product, $id_product_attribute = 0)
  {
    $result = Db::getInstance()->executeS(
      "SELECT s.id_supplier, 
                  s.name, 
                  ps.product_supplier_reference, 
                  ps.product_supplier_price_te, 
                  ps.id_currency
            FROM " . _DB_PREFIX_ . "product_supplier as ps
            LEFT JOIN " . _DB_PREFIX_ . "supplier as s
            ON ps.id_supplier = s.id_supplier
            WHERE ps.id_supplier = " . (int)$id_supplier . "
            AND ps.id_product = " . (int)$id_product . "
            AND ps.id_product_attribute = " . (int)$id_product_attribute
    );

    return !empty($result) ? $result[0] : false;
  }
}
