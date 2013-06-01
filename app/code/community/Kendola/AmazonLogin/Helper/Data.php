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

class Kendola_AmazonLogin_Helper_Data extends Mage_Core_Helper_Abstract {

    /**
     * Is the module enabled?
     *
     * @return bool
     */
    public function isEnabled() {
        return 1==Mage::getStoreConfig('amazonlogin/settings/enabled');
    }

    /**
     * Gets the Client-ID from config.
     *
     * @return string Client-ID
     */
    public function getClientId() {
        return Mage::getStoreConfig('amazonlogin/settings/clientid');
    }

    /**
     * Gets the Client Secret from config.
     *
     * @return string Client Secret
     */
    public function getClientSecret() {
        return Mage::getStoreConfig('amazonlogin/settings/clientsecret');
    }

    /**
     * Create access_token from authcode.
     *
     * THIS WILL NOT WORK ATM. Waiting for Amazon to fix this.
     *
     * @param string $authcode
     * @return string $access_token
     */
    public function getAccessTokenFromAuthCode($authcode) {
        if(!$this->isEnabled()) return false;
        // setup a request to the profile endpoint
        $c = curl_init('https://api.amazon.com/auth/o2/token');
        curl_setopt($c, CURLOPT_POST, true);
        curl_setopt($c, CURLOPT_POSTFIELDS, "grant_type=authorization_code&code={$authcode}&client_id={$this->getClientId()}&client_secret={$this->getClientSecret()}");
//        curl_setopt($c, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded;charset=UTF-8'));
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);

        // make the request
        $r = curl_exec($c);

        $status = curl_getinfo($c, CURLINFO_HTTP_CODE);
        curl_close($c);

        // decode the response
        $d = json_decode($r);
        if($status != 200 or $d->error) {
            var_dump($d);
            return false;
        }
        return $d->access_token;
    }

    /**
     * Requests Amazon User-Profile from access_token
     *
     * @param string $access_token
     * @return object $profile Amazon-User-Profile
     */
    public function getUserProfileFromAccessToken($access_token) {
        if(!$this->isEnabled()) return false;
        // setup a request to the profile endpoint
        $c = curl_init('https://api.amazon.com/user/profile');
        curl_setopt($c, CURLOPT_HTTPHEADER, array('Authorization: bearer ' . $access_token));
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);

        // make the request
        $r = curl_exec($c);
        curl_close($c);

        // decode the response
        $d = json_decode($r);
        return $d;
    }
}
