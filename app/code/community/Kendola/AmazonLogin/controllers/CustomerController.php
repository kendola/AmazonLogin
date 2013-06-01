<?php
/**
 * Kendola Login With Amazon for Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Kendola
 * @package    Kendola_AmazonLogin
 * @author     Oliver Treck, kendola - easy eCommerce <o.treck@kendola.de>
 * @copyright  Copyright (c) 2013 kendola - easy eCommerce (http://www.kendola.de/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Kendola_AmazonLogin_CustomerController extends Mage_Core_Controller_Front_Action {

    public function LoginAction()
    {
        /**
         * @var $helper Kendola_AmazonLogin_Helper_Data
         */
        $helper = Mage::helper('amazonlogin');

        // grep authentication code
//        $code = $this->getRequest()->getParam('code');
        // get access token
//            $token = $helper->getAccessTokenFromAuthCode($code);
        $token = $this->getRequest()->getParam('token', false);
        if(!$token) {
            $this->_afterRedirect();
            return;
        }
        // get profile
        $profile = $helper->getUserProfileFromAccessToken($token);
        // create user if non existent
        if($profile && $profile->user_id) {
            $amazonuser = Mage::getModel('amazonlogin/amazonlogin')->load($profile->user_id, 'amazon_id');
            if(!$amazonuser->getId()) {
                // if not known try to get by email
                $customer = Mage::getModel('customer/customer')->setWebsiteId(Mage::app()->getWebsite()->getId())->loadByEmail($profile->email);
                if($customer->getId()) {
                    $customer_id = $customer->getId();
                } else {
                    // create customer
                    if(false===strpos($profile->name, ' ')) {
                        $len = round(strlen($profile->name) / 2);
                        $data['firstname'] = substr($profile->name, 0, $len);
                        $data['lastname'] = substr($profile->name, $len);
                    } else {
                        $list = explode(' ', $profile->name);
                        $data['lastname'] = array_pop($list);
                        $data['firstname'] = implode(' ', $list);
                    }
                    $data['email'] = $profile->email;
                    $data['user_id'] = $profile->user_id;
                    $data['postal_code'] = $profile->postal_code;

                    $customer_id = $this->_createCustomer($data);
                }

                if(false!==$customer_id) {
                    $amazonuser->setCustomerId($customer_id);
                    $amazonuser->setAmazonId($profile->user_id);
                    $amazonuser->save();
                }
            } else {
                $customer_id = $amazonuser->getCustomerId();
            }
        }
        // login user
        if(false!==$customer_id) {
            $this->_loginCustomer($customer_id);
        }
        // redirect (if new to edit profile / if in checkout
        $this->_afterRedirect();
    }

    private function _createCustomer($data)
    {
        $customer = Mage::getModel('customer/customer')
            ->setFirstname($data['firstname'])
            ->setLastname($data['lastname'])
            ->setEmail($data['email'])
            ->setPassword(md5(uniqid() . $data['user_id']))
            ->setIsActive(1)
            ->setWebsiteId(Mage::app()->getWebsite()->getId())
            ->setConfirmation(null);
        $customer->save();
        $customer->setConfirmation(null);
        $customer->save();
        $customer->sendNewAccountEmail(
            'registered',
            '',
            Mage::app()->getStore()->getId()
        );
        $id = $customer->getId();
        if(is_numeric($id) && $id>0) {
            return $id;
        }
        return false;
    }

    private function _loginCustomer($customer_id) {
        $session = Mage::getSingleton('customer/session');
        $customer = Mage::getModel('customer/customer')->load($customer_id);
        if($customer->getId()) {
            $session->setCustomerAsLoggedIn($customer);
        }
    }

    private function _afterRedirect()
    {
        $session = Mage::getSingleton('customer/session');

        if ($referer = $this->getRequest()->getParam(Mage_Customer_Helper_Data::REFERER_QUERY_PARAM_NAME)) {
            $referer = Mage::helper('core')->urlDecode($referer);
            if ((strpos($referer, Mage::app()->getStore()->getBaseUrl()) === 0)
                    || (strpos($referer, Mage::app()->getStore()->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK, true)) === 0)) {
                $session->setBeforeAuthUrl($referer);
            } else {
                $session->setBeforeAuthUrl(Mage::helper('customer')->getDashboardUrl());
            }
        } else {
            $session->setBeforeAuthUrl(Mage::helper('customer')->getDashboardUrl());
        }
        $this->_redirectUrl($session->getBeforeAuthUrl(true));
    }
}
