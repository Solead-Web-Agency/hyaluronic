<?php

/*
Uploadify
Copyright (c) 2012 Reactive Apps, Ronnie Garcia
Released under the MIT License <http://www.opensource.org/licenses/mit-license.php>
*/

require_once (dirname(__FILE__).'/../../../../config/config.inc.php');
require_once (dirname(__FILE__).'/../../../../init.php');
ini_set('max_execution_time', '2880');
$module = Module::getInstanceByName('pqshippingcostsbasedonzipcodes');


// Set the uplaod directory
if ($_POST['location'] == 'uploads')
	$uploadDir = dirname(__FILE__).'/../../uploads/';
else
	$uploadDir = dirname(__FILE__).'/../../uploads/';

// Set the allowed file extensions
$fileTypes = array('csv'); // Allowed file extensions



if (!empty($_FILES)) 
{
	$tempFile   = $_FILES['Filedata']['tmp_name'];
	#$uploadDir  = $_SERVER['DOCUMENT_ROOT'] . $uploadDir;
	$targetFile = $uploadDir . $_FILES['Filedata']['name'];

	// Validate the filetype
	$fileParts = pathinfo($_FILES['Filedata']['name']);
	if (in_array(strtolower($fileParts['extension']), $fileTypes)) 
	{
		// Save the file
		move_uploaded_file($tempFile, $targetFile);

		$handle = fopen($targetFile, 'r');

		while (($data = fgetcsv($handle, 1000, ',')) !== false) 
		{
			$id_country = @$data[0];
			$id_zone = @$data[1];
			$filter = @$data[2];
			$zipcode_min = @$data[3];
			$zipcode_max = @$data[4];

			$module->addCondition( array('id_country' => $id_country, 
										'id_zone' => $id_zone, 
										'filter' => $filter,
										'zipcode_min' => $zipcode_min,
										'zipcode_max' => $zipcode_max,
								) );
		}


		echo $_FILES['Filedata']['name'];
	} 
	else 
	{
		// The file type wasn't allowed
		echo 'error2';
	}
}


?>