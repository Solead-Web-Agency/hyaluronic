<?php
/**
 * 2017-2022 liewebs - Prestashop module developers and website designers.
 *
 * NOTICE OF LICENSE
 *  @author    liewebs <info@liewebs.com>
 *  @copyright 2017-2022 www.liewebs.com - Liewebs
 *  @license see file:license_es.html and license_en.html
 * 	@module Advanced VAT Manager
 */
 
if (!defined('_PS_VERSION_')) {
    exit;
};

class AdvancedVatManagerOC extends Module
{    
    public static function backupOverrideFolder($module)
    {
        if (is_dir(_PS_ROOT_DIR_.'/override')) {
            //Create a zip with backup file
            $zip = new ZipArchive;
            $source = realpath(_PS_ROOT_DIR_.'/override');
            $destination = _PS_MODULE_DIR_.'/'.$module.'/backup/override_(Date_'.date("Y_m_d").'_Hour_'.date("Hi").').zip';
    
            if (!extension_loaded('zip')) {
                return false;
            }
            
            // Initialize archive object
            $zip = new ZipArchive();
            $zip->open($destination, ZipArchive::CREATE | ZipArchive::OVERWRITE);
            
            // Create recursive directory iterator
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($source),
                RecursiveIteratorIterator::LEAVES_ONLY
            );
            
            foreach ($files as $name => $file)
            {
                // Skip directories (they would be added automatically)
                if (!$file->isDir())
                {
                    // Get real and relative path for current file
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen($source) + 1);
            
                    // Add current file to archive
                    $zip->addFile($filePath, $relativePath);
                }
            }
            
            // Zip archive will be created only after closing object
            return $zip->close();
        }
        return false;
    }
    
    /**
     * Remove override element in a module override from the override class.
     *
     * @param string $module Module name lowcase
     * @param string $method_name Name of the method
     * @param string $property Name of the property
     * @param string $constant_ Name of the constant
     * @param string $classname
     *
     * @return bool
     */
    public function removeModuleOverride($module, $classname, $method_name = null, $property = null, $constant_ = null)
    {
        $orig_path = $path = PrestaShopAutoload::getInstance()->getClassPath($classname . 'Core');
        $module_instance = Module::getInstanceByName($module);

        if ($orig_path && !$file = PrestaShopAutoload::getInstance()->getClassPath($classname)) {
            return false;
        } elseif (!$orig_path && Module::getModuleIdByName($classname)) {
            $path = 'modules'.DIRECTORY_SEPARATOR.$classname.DIRECTORY_SEPARATOR.$classname.'.php';
        }
        
        $module_override_file = $module_instance->getLocalPath() . 'override/' . $path;
        // Checks if module override file exists or end execution of the code.
        if (file_exists($module_override_file)) {
            $module_file = file($module_override_file);     
        }
        else {
            return false;
        }       

        // Check if override file is writable
        if ($orig_path) {
            $override_path = _PS_ROOT_DIR_.'/'.$file;
        } else {
            $override_path = _PS_OVERRIDE_DIR_.$path;
        }
        
        // Check if override file exists  and method in the module
        if (strpos(implode(',', $module_file), $method_name) === false) {
            return false;
        }
                
        if (!is_file($override_path) || !file_exists($override_path)) {
            return false;
        }

        if (!is_writable($override_path)) {
            return false;
        }
        
        //Checks if the original class exists
        if ($orig_path) {
            
            // Get a uniq id for the class, because you can override a class (or remove the override) twice in the same session and we need to avoid redeclaration
            do {
                $uniq = uniqid();
            } while (class_exists($classname . 'OverrideOriginal_remove', false));

            // Make a reflection of the override class and the module override class
            $override_file = file($override_path);

            eval(
                preg_replace(
                    ['#^\s*<\?(?:php)?#', '#class\s+' . $classname . '\s+extends\s+([a-z0-9_]+)(\s+implements\s+([a-z0-9_]+))?#i'],
                    [' ', 'class ' . $classname . 'OverrideOriginal_remove' . $uniq . ' extends \stdClass'],
                    implode('', $override_file)
                )
            );
            $override_class = new ReflectionClass($classname . 'OverrideOriginal_remove' . $uniq);
            
            eval(
                preg_replace(
                    ['#^\s*<\?(?:php)?#', '#class\s+' . $classname . '(\s+extends\s+([a-z0-9_]+)(\s+implements\s+([a-z0-9_]+))?)?#i'],
                    [' ', 'class ' . $classname . 'Override_remove' . $uniq . ' extends \stdClass'],
                    implode('', $module_file)
                )
            );
            $module_class = new ReflectionClass($classname . 'Override_remove' . $uniq);

            if ($method_name !== null) {
                // Remove methods from override file
                foreach ($module_class->getMethods() as $method) {
                    if ($method_name != $method->getName()) {
                        continue;    
                    }
                    if (!$override_class->hasMethod($method->getName())) {
                        continue;
                    }
    
                    $method = $override_class->getMethod($method->getName());
                    $length = $method->getEndLine() - $method->getStartLine() + 1;
    
                    $module_method = $module_class->getMethod($method->getName());
                    $module_length = $module_method->getEndLine() - $module_method->getStartLine() + 1;
    
                    $override_file_orig = $override_file;
    
                    $orig_content = preg_replace('/\s/', '', implode('', array_splice($override_file, $method->getStartLine() - 1, $length, array_pad([], $length, '#--remove--#'))));
                    $module_content = preg_replace('/\s/', '', implode('', array_splice($module_file, $module_method->getStartLine() - 1, $length, array_pad([], $length, '#--remove--#'))));
    
                    $replace = true;
                    if (preg_match('/\* module: (' . $module_instance->name  . ')/ism', $override_file[$method->getStartLine() - 5])) {
                        $override_file[$method->getStartLine() - 6] = $override_file[$method->getStartLine() - 5] = $override_file[$method->getStartLine() - 4] = $override_file[$method->getStartLine() - 3] = $override_file[$method->getStartLine() - 2] = '#--remove--#';
                        $replace = false;
                    }
    
                    if (md5($module_content) != md5($orig_content) && $replace) {
                        $override_file = $override_file_orig;
                    }
                }
            }

            if ($property !== null) {
                // Remove properties from override file
                foreach ($module_class->getProperties() as $property) {
                    if ($property != $property->getName()) {
                        continue;    
                    }
                    if (!$override_class->hasProperty($property->getName())) {
                        continue;
                    }
    
                    // Replace the declaration line by #--remove--#
                    foreach ($override_file as $line_number => &$line_content) {
                        if (preg_match('/(public|private|protected)\s+(static\s+)?(\$)?' . $property->getName() . '/i', $line_content)) {
                            if (preg_match('/\* module: (' . $module_instance->name  . ')/ism', $override_file[$line_number - 4])) {
                                $override_file[$line_number - 5] = $override_file[$line_number - 4] = $override_file[$line_number - 3] = $override_file[$line_number - 2] = $override_file[$line_number - 1] = '#--remove--#';
                            }
                            $line_content = '#--remove--#';
    
                            break;
                        }
                    }
                }
            }
            if ($constant_ !== null) {
                // Remove properties from override file
                foreach ($module_class->getConstants() as $constant => $value) {
                    if ($constant_ != $constant) {
                        continue;    
                    }
                    if (!$override_class->hasConstant($constant)) {
                        continue;
                    }
    
                    // Replace the declaration line by #--remove--#
                    foreach ($override_file as $line_number => &$line_content) {
                        if (preg_match('/(const)\s+(static\s+)?(\$)?' . $constant . '/i', $line_content)) {
                            if (preg_match('/\* module: (' . $module_instance->name  . ')/ism', $override_file[$line_number - 4])) {
                                $override_file[$line_number - 5] = $override_file[$line_number - 4] = $override_file[$line_number - 3] = $override_file[$line_number - 2] = $override_file[$line_number - 1] = '#--remove--#';
                            }
                            $line_content = '#--remove--#';
    
                            break;
                        }
                    }
                }
            }

            $count = count($override_file);
            for ($i = 0; $i < $count; ++$i) {
                if (preg_match('/(^\s*\/\/.*)/i', $override_file[$i])) {
                    $override_file[$i] = '#--remove--#';
                } elseif (preg_match('/(^\s*\/\*)/i', $override_file[$i])) {
                    if (!preg_match('/(^\s*\* module:)/i', $override_file[$i + 1])
                        && !preg_match('/(^\s*\* date:)/i', $override_file[$i + 2])
                        && !preg_match('/(^\s*\* version:)/i', $override_file[$i + 3])
                        && !preg_match('/(^\s*\*\/)/i', $override_file[$i + 4])) {
                        for (; $override_file[$i] && !preg_match('/(.*?\*\/)/i', $override_file[$i]); ++$i) {
                            $override_file[$i] = '#--remove--#';
                        }
                        $override_file[$i] = '#--remove--#';
                    }
                }
            }

            // Rewrite nice code
            $code = '';
            foreach ($override_file as $line) {
                if ($line == '#--remove--#') {
                    continue;
                }

                $code .= $line;
            }

            $to_delete = preg_match('/<\?(?:php)?\s+(?:abstract|interface)?\s*?class\s+' . $classname . '\s+extends\s+' . $classname . 'Core\s*?[{]\s*?[}]/ism', $code);
        }

        if (!isset($to_delete) || $to_delete) {
            unlink($override_path);
        } else {
            file_put_contents($override_path, $code);
        }

        // Re-generate the class index
        Tools::generateIndex();

        return true;
    }
}