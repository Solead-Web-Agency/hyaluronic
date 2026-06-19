<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file.
 * You are not authorized to modify, copy or redistribute this file.
 * Permissions are reserved by FME Modules.
 *
 *  @author    FMM Modules
 *  @copyright FME Modules 2023
 *  @license   Single domain
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(dirname(_PS_MODULE_DIR_).'/modules/adminmobapp/services/Core.php');

class ApiGetEmployeeLogin extends Core
{
    public function getData()
    {
        $errors = array();

        $email = trim(Tools::getValue('email'));
        $password = trim(Tools::getValue('password'));
        $translator = $this->context->getTranslator();
        // Validate email
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format.';
        }

        // Validate password presence
        if (empty($password)) {
            $errors[] = 'Password is required.';
        }

        // Check if there were validation errors
        if (!empty($errors)) {
            $this->response['response'] = array(
                'status' => 'error',
                'message' => 'Validation errors.',
                'errors' => $errors
            );
        } else {
            try {
                // Attempt to retrieve employee by email
                $employee = new Employee();
                $validate = $employee->getByEmail($email, $password);
                $allow_ids = Configuration::get('ADMINMOBAPP_EMPLOYEES');
                $allowed_id_array = [];
                if ($allow_ids !== null && $allow_ids !== false) {
                    $allowed_id_array = explode(",", $allow_ids);
                }
                
                if ($validate && isset($validate->active) && !$validate->active) {
                    
                    $accountlabel = $translator->trans('Account not active.', [], 'Modules.adminmobapp.Front');
                    $this->response['response'] = array(
                        'status' => 'failure',
                        'message' => $accountlabel
                    );
                } elseif (!$employee->id) {
                    $accountlabel = $translator->trans('The employee does not exist, or the password provided is incorrect.', [], 'Modules.adminmobapp.Front');
                    $this->response['response'] = array(
                        'status' => 'failure',
                        'message' => $accountlabel
                    );
                }  elseif (!$validate) {
                    $accountlabel = $translator->trans('Incorrect Password.', [], 'Modules.adminmobapp.Front');
                    $this->response['response'] = array(
                        'status' => 'failure',
                        'message' => $accountlabel
                    );
                } elseif ($validate && isset($validate->active) && $validate->id) {
                    if (!in_array($validate->id, $allowed_id_array)) {
                        $accountlabel = $translator->trans('Account Disabled.', [], 'Modules.adminmobapp.Front');
                        $this->response['response'] = array(
                            'status' => 'failure',
                            'message' => $accountlabel
                        );
                    } else {
                        $accountlabel = $translator->trans('Login successful', [], 'Modules.adminmobapp.Front');
                        $this->response['response'] = array(
                            'status' => 'success',
                            'message' => $accountlabel,
                            'data' => $employee
                        );
                    }
                } else {
                    $accountlabel = $translator->trans('Login successful', [], 'Modules.adminmobapp.Front');
                    $this->response['response'] = array(
                        'status' => 'success',
                        'message' => $accountlabel,
                        'data' => $employee
                    );
                }
            } catch (Exception $e) {
                $this->response['response'] = array(
                    'status' => 'error',
                    'message' => 'Error occurred while processing request.'
                );
            }
        }

        return $this->fetchJSONResponse();
    }
}

