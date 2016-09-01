<?php
/**
 * 2016 HiPay
 *
 * NOTICE OF LICENSE
 *
 *
 * @author    HiPay <support.wallet@hipay.com>
 * @copyright 2016 HiPay
 * @license   https://github.com/hipay/hipay-wallet-sdk-prestashop/blob/master/LICENSE.md
 *
 */

define('MAX_SIZE', 300000);  // max weight
define('WIDTH_MAX', 400);    // max width
define('HEIGHT_MAX', 400);   // max height

class AdminHiPayPaymentbuttonController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();

        if (!$this->module->active) {
            $this->sendErrorRequest('Invalid request.');
        }
    }

    public function ajaxProcessImageButtons()
    {
        $tabExt = array('jpg','gif','png','jpeg');
        $infosImg = array();
        // Variables
        $extension = '';
        $message = '';
        $nomImage = '';
        $return = [];

        if( $_FILES != null ) {
            $image = $_FILES['file'];
            // control if not empty
            if (!empty($image['name'])) {
                // get image extension
                $extension = pathinfo($image['name'], PATHINFO_EXTENSION);
                // check if extension is valid
                if (in_array(strtolower($extension), $tabExt)) {
                    // get resolution image
                    $infosImg = getimagesize($image['tmp_name']);
                    // check image type
                    if ($infosImg[2] >= 1 && $infosImg[2] <= 14) {
                        // check resolution and weight
                        if (($infosImg[0] <= WIDTH_MAX) && ($infosImg[1] <= HEIGHT_MAX) && (filesize($image['tmp_name']) <= MAX_SIZE)) {
                            // if error
                            if (isset($image['error'])
                                && UPLOAD_ERR_OK === $image['error']
                            ) {
                                // rename image
                                $nomImage = md5(uniqid()) . '.' . $extension;
                                // if ok execute upload
                                if (move_uploaded_file($image['tmp_name'], _PS_MODULE_DIR_.'hipay/views/img/payment_buttons/' . $nomImage)) {
                                    $return = [
                                        'status' => true,
                                        'image' => $nomImage,
                                    ];
                                } else {
                                    $message = $this->module->l('Upload has failed, try again.');
                                    $return = [
                                        'status' => false,
                                        'message' => $message,
                                    ];
                                }
                            } else {
                                $message = $this->module->l('Internal error - Upload has failed, try again.');
                                $return = [
                                    'status' => false,
                                    'message' => $message,
                                ];
                            }
                        } else {
                            $message = $this->module->l('The dimensions or the size of the picture are not correct.');
                            $return = [
                                'status' => false,
                                'message' => $message,
                            ];
                        }
                    } else {
                        $message = $this->module->l('Error on the type of the picture.');
                        $return = [
                            'status' => false,
                            'message' => $message,
                        ];
                    }
                } else {
                    $message = $this->module->l('Error on the extension of the picture.');
                    $return = [
                        'status' => false,
                        'message' => $message,
                    ];
                }
            } else {
                $message = $this->module->l('Sorry, no payment button selected !');
                $return = [
                    'status' => false,
                    'message' => $message,
                ];
            }
        }else {
            $message = $this->module->l('Sorry, no payment button selected !');
            $return = [
                'status' => false,
                'message' => $message,
            ];
        }
        die(Tools::jsonEncode($return));
    }
}