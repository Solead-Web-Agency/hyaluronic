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

require_once(dirname(_PS_MODULE_DIR_) . '/modules/adminmobapp/services/Core.php');

class ApiSetEmployeeReset extends Core
{
    public function getData()
    {
        $email = Tools::getValue('email');
        if (empty($email) || !Validate::isEmail($email)) {
            $this->response['response'] = array(
                'status' => 'error',
                'message' => 'Valid email is required',
            );
            return $this->fetchJSONResponse();
        }

        $employee = new Employee();
        $employee->getByEmail($email);

        // Check if the employee was successfully loaded (i.e., if the ID is set)
        if (!$employee->id || $employee->id == 0 || $employee->id == null) {
            $this->response['response'] = array(
                'status' => 'error',
                'message' => 'Employee not found.',
            );
            return $this->fetchJSONResponse();
        }


        // Check if employee exists and active
        if (!Validate::isLoadedObject($employee) || !$employee->active) {
            $this->response['response'] = array(
                'status' => 'error',
                'message' => 'Employee not found or inactive.',
            );
            return $this->fetchJSONResponse();
        }

        if (!$employee->hasRecentResetPasswordToken()) {
            $employee->stampResetPasswordToken();
            $employee->update();
        }

        $login_url = Configuration::get('ADMINMOBAPP_ADMIN_LOGIN');
        // Validate the login URL
        if (empty($login_url) || !Validate::isUrl($login_url)) {
            $this->response['response'] = array(
                'status' => 'error',
                'message' => 'Invalid login URL.',
            );
            return $this->fetchJSONResponse();
        }
        
        $resetUrl = $login_url . '&id_employee=' . (int)$employee->id . '&reset_token=' . urlencode($employee->reset_password_token);

        // Prepare the email parameters
        $params = [
            '{email}' => $employee->email,
            '{lastname}' => Tools::stripslashes($employee->lastname),
            '{firstname}' => Tools::stripslashes($employee->firstname),
            '{url}' => $resetUrl,
        ];

        $employeeLanguage = new Language((int)$employee->id_lang);
        
        // Send the password reset email
        $mailSent = Mail::Send(
            (int)$employee->id_lang,
            'password_query',
            'Your new password',
            $params,
            $employee->email,
            $employee->firstname . ' ' . $employee->lastname,
            null, null, null, null, _PS_ROOT_DIR_ . '/mails/'
        );
        
        if ($mailSent) {
            $this->response['response'] = array(
                'status' => 'success',
                'message' => 'Password reset email has been sent.'
            );
            return $this->fetchJSONResponse();
        } else {
            $this->response['response'] = array(
                'status' => 'failure',
                'message' => 'Failed to send password reset email.'
            );
            return $this->fetchJSONResponse();
        }
        return $this->fetchJSONResponse();
    }
}
