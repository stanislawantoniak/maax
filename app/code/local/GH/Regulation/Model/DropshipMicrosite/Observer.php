<?php

/**
 * Class GH_Regulation_Model_DropshipMicrosite_Observer
 */
class GH_Regulation_Model_DropshipMicrosite_Observer extends ZolagoOs_OmniChannelMicrosite_Model_Observer
{

    /**
     * Synchronize admin user from vendor object
     *
     * @param mixed $observer
     */
    public function udropship_vendor_save_after($observer)
    {
        $vendor = $observer->getEvent()->getVendor();

        $user = Mage::getModel('admin/user')->load($vendor->getId(), 'udropship_vendor');
        $changed = false;
        $nameChanged = false;
        $new = false;
        if (!$user->getId()) {
            $new = true;
            $user->setData(array(
                'udropship_vendor' => $vendor->getId(),
                'is_active' => 1,
            ));
        }
        if (!$new && $vendor->getVendorName() != $user->getFirstname()) {
            $nameChanged = true;
        }
//        $isActive = $vendor->getStatus()=='A' ? 1 : 0;
        if ($new
            || $vendor->getVendorName() != $user->getFirstname()
            || $vendor->getVendorAttn() != $user->getLastname()
            || $vendor->getEmail() != $user->getEmail()
//            || $isActive!=$user->getIsActive()
        ) {
            $user->addData(array(
                'firstname' => $vendor->getVendorName(),
                'lastname' => $vendor->getVendorAttn(),
                'email' => $vendor->getEmail(),
                'username' => $vendor->getEmail(),
//                'is_active' => $isActive,
            ));
            $changed = true;
        }
        if (!Mage::helper('core')->validateHash($this->_vendorPassword, $user->getPassword())) {
            $user->setNewPassword($this->_vendorPassword);
            $changed = true;
        } else {
            $user->unsPassword();
        }
        if ($changed) {
            $user->save();
        }

        if ($new) {
            $roles = Mage::getModel('admin/role')->getCollection()
                ->addFieldToFilter('role_name', 'Dropship Vendor')
                ->addFieldToFilter('parent_id', 0);
            foreach ($roles as $role) {
                $user->setRoleId($role->getRoleId())->add();
                break;
            }
        } elseif ($nameChanged) {
            $roles = Mage::getModel('admin/role')->getCollection()
                ->addFieldToFilter('user_id', $user->getId());
            foreach ($roles as $role) {
                $role->setRoleName($vendor->getVendorName())->save();
            }
        }

        if ($vendor->getRegId()) {
            if ((!Mage::helper('udropship')->isModuleActive('udmspro')
                    || Mage::getStoreConfigFlag('udropship/microsite/skip_confirmation')
                    || !$vendor->getSendConfirmationEmail()
                ) && $vendor->getStatus() != ZolagoOs_OmniChannel_Model_Source::VENDOR_STATUS_REJECTED
            ) {
                if($vendor->getStatus() != ZolagoOs_OmniChannel_Model_Source::VENDOR_STATUS_INACTIVE){
                    $vendor->setPassword($this->_vendorPassword);
                    //Email with password should not be send on REGISTRATION stage in any case
                    //Mage::helper('umicrosite')->sendVendorWelcomeEmail($vendor);
                    $vendor->setPassword('');
                }

            }
            Mage::getModel('umicrosite/registration')->load($vendor->getRegId())->delete();
        }
        if (Mage::helper('udropship')->isModuleActive('udmspro')) {
            if ($vendor->getSendConfirmationEmail()) {
                $vendor->setConfirmation(md5(uniqid()));
                $vendor->setConfirmationSent(1);
                $localeTime = Mage::getModel('core/date')->timestamp(time());
                $localeTimeF = date("Y-m-d H:i:s", $localeTime);

                $vendor->setData("regulation_confirm_request_sent_date", $localeTimeF);
                Mage::getResourceSingleton('udropship/helper')
                    ->updateModelFields(
                        $vendor,
                        array('confirmation', 'confirmation_sent', 'regulation_confirm_request_sent_date')
                    );
                Mage::helper('udmspro')->sendVendorConfirmationEmail($vendor);
            } elseif ($vendor->getSendRejectEmail()) {
                Mage::helper('udmspro')->sendVendorRejectEmail($vendor);
            }
        }
    }
}