<?php

class Zolago_Common_Model_Core_Email_Template extends ZolagoOs_OmniChannel_Model_Email
{

    protected $_domAttachments = array();
    protected $_allowedExtensions = array('jpg' => 1, 'jpeg' => 1, 'png' => 1, 'apng' => 1, 'gif' => 1, 'bmp' => 1, 'svg' => 1, 'ico' => 1);


    /**
     * Process email template code
     *
     * @param   array $variables
     * @return  string
     */
    public function getProcessedTemplate(array $variables = array())
    {
        $processor = $this->getTemplateFilter();
        $processor->setUseSessionInUrl(false)
            ->setPlainTemplateMode($this->isPlain());

        if (!$this->_preprocessFlag) {
            $variables['this'] = $this;
        }

        if (isset($variables['subscriber']) && ($variables['subscriber'] instanceof Mage_Newsletter_Model_Subscriber)) {
            $processor->setStoreId($variables['subscriber']->getStoreId());
        }

        // Apply design config so that all subsequent code will run within the context of the correct store
        $this->_applyDesignConfig();

        // Populate the variables array with store, store info, logo, etc. variables
        $variables = $this->_addEmailVariables($variables, $processor->getStoreId());

        $processor
            ->setTemplateProcessor(array($this, 'getTemplateByConfigPath'))
            ->setIncludeProcessor(array($this, 'getInclude'))
            ->setVariables($variables);

        try {
            // Filter the template text so that all HTML content will be present
            $result = $processor->filter($this->getTemplateText());
            // If the {{inlinecss file=""}} directive was included in the template, grab filename to use for inlining
            //$this->setInlineCssFile($processor->getInlineCssFile());
            // Now that all HTML has been assembled, run email through CSS inlining process
            $processedResult = $this->getPreparedTemplateText($result);
        } catch (Exception $e) {
            $this->_cancelDesignConfig();
            throw $e;
        }
        $this->_cancelDesignConfig();
        return $processedResult;
    }

    /**
     * do not send email on test servers
     *
     * @param array $mail
     * @return array
     */
    protected function _allowSend($mail)
    {
        if (((string)Mage::getConfig()->getNode('global/test_server')) == 'true') {
            $out = array();
            $allowEmails = Mage::getConfig()->getNode('global/allow_emails');
            if (!empty($allowEmails)) {
                if (!is_array($mail)) {
                    $mail = array($mail);
                }
                foreach ($mail as $key => $item) {
                    if (!is_array($item)) {
                        $item = array($item);
                    }
                    foreach ($item as $itemKey => $address) {
                        if (preg_match('/' . $allowEmails . '/', $address)) {
                            $out[$key][$itemKey] = $address;
                            Mage::log(sprintf('Email allowed to send (%s)', $address), null, 'email_blocked.log');
                        } else {
                            Mage::log(sprintf('Email not allowed to send (%s)', $address), null, 'email_blocked.log');
                        }
                    }
                }
            } else {
                return array();
            }
        } else {
            $out = $mail;
        }
        return $out;
    }


    /**
     * override bcc
     */

    public function addBcc($bcc)
    {
        if ($ret = $this->_allowSend($bcc)) {
            return parent::addBcc($ret);
        }
        return $this;
    }

    /**
     * add CC to email
     *
     * @param array|string $cc email
     * @param string $name
     */

    public function addCc($cc, $name = '')
    {
        if (!is_array($cc)) {
            $cc = array($name => $cc);
        }
        if ($ret = $this->_allowSend($cc)) {
            return $this->getMail()->addCc($ret);
        }
        return $this;
    }


    /**
     * Send mail to recipient
     *
     * @param   array|string $email E-mail(s)
     * @param   array|string|null $name receiver name(s)
     * @param   array $variables template variables
     * @return  boolean
     **/
    public function send($email, $name = null, array $variables = array())
    {
        if (!$this->isValidForSend()) {
            Mage::logException(new Exception('This letter cannot be sent.')); // translation is intentionally omitted
            return false;
        }

        $emails = array_values((array)$email);
        $names = is_array($name) ? $name : (array)$name;
        $names = array_values($names);
        foreach ($emails as $key => $email) {
            if (!isset($names[$key])) {
                $names[$key] = substr($email, 0, strpos($email, '@'));
            }
        }

        $variables['email'] = reset($emails);
        $variables['name'] = reset($names);

        ini_set('SMTP', Mage::getStoreConfig('system/smtp/host'));
        ini_set('smtp_port', Mage::getStoreConfig('system/smtp/port'));

        $mail = $this->getMail();

        $setReturnPath = Mage::getStoreConfig(self::XML_PATH_SENDING_SET_RETURN_PATH);
        switch ($setReturnPath) {
            case 1:
                $returnPathEmail = $this->getSenderEmail();
                break;
            case 2:
                $returnPathEmail = Mage::getStoreConfig(self::XML_PATH_SENDING_RETURN_PATH_EMAIL);
                break;
            default:
                $returnPathEmail = null;
                break;
        }

        if ($returnPathEmail !== null) {
            $mailTransport = new Zend_Mail_Transport_Sendmail("-f" . $returnPathEmail);
            Zend_Mail::setDefaultTransport($mailTransport);
        }

        foreach ($emails as $key => $email) {
            $mail->addTo($email, '=?utf-8?B?' . base64_encode($names[$key]) . '?=');
        }

        $this->setUseAbsoluteLinks(true);
        $text = $this->getProcessedTemplate($variables, true);

        //embed images start
        $text = $this->_imagesToAttachments($text);
        if (!empty($this->_domAttachments)) {
            if (isset($variables['_ATTACHMENTS'])) {
                $variables['_ATTACHMENTS'] = array_merge((array)$variables['_ATTACHMENTS'], $this->_domAttachments);
            } else {
                $variables['_ATTACHMENTS'] = $this->_domAttachments;
            }
            //cleanup
            $this->_domAttachments = array();
        }
        //embed images end

        ////////////////////////////////////////////////////////////////////////
        // Start changes
        ////////////////////////////////////////////////////////////////////////
        if (!empty($variables['_ATTACHMENTS'])) {
            foreach ((array)$variables['_ATTACHMENTS'] as $a) {
                if (is_string($a)) {
                    $a = array('filename' => $a);
                }
                if (empty($a['content']) && (empty($a['filename']) || !is_readable($a['filename']))) {
                    Mage::throwException('Invalid attachment data: ' . print_r($a, 1));
                }
                $at = $mail->createAttachment(
                    !empty($a['content']) ? $a['content'] : file_get_contents($a['filename']),
                    !empty($a['type']) ? $a['type'] : Zend_Mime::TYPE_OCTETSTREAM,
                    !empty($a['disposition']) ? $a['disposition'] : Zend_Mime::DISPOSITION_ATTACHMENT,
                    !empty($a['encoding']) ? $a['encoding'] : Zend_Mime::ENCODING_BASE64,
                    basename($a['filename'])
                );
                /* @var $at Zend_Mime_Part */

                if (isset($a['id']) && !empty($a['id'])) {
                    $at->id = $a['id'];
                }
            }
        }
        ////////////////////////////////////////////////////////////////////////
        // End changes
        ////////////////////////////////////////////////////////////////////////

        if ($this->isPlain()) {
            $mail->setBodyText($text);
        } else {
            $mail->setBodyHTML($text);
        }

        $mail->setSubject('=?utf-8?B?' . base64_encode($this->getProcessedTemplateSubject($variables)) . '?=');
        $mail->setFrom($this->getSenderEmail(), $this->getSenderName());

        $errors = array();

        try {
            $to = $mail->getRecipients();
            $testFlag = (string)Mage::getConfig()->getNode('global/test_server');
            if ($testFlag == 'true') {
                $allowEmails = Mage::getConfig()->getNode('global/allow_emails');
                if (!empty($allowEmails)) {
                    foreach ($to as $k => $email) {
                        if (!preg_match('/' . $allowEmails . '/', $email)) {
                            unset($to[$k]);
                            $errors[] = sprintf('Email not allowed to send (%s)', $email);
                        }
                    }
                } else {
                    $to = array();
                    $errors[] = 'Not allowed recipient emails [test server]';
                }
            }
            if (count($errors)) {
                foreach ($errors as $error) {
                    Mage::log($error, null, 'email_blocked.log');
                }
            }

            if (count($to)) {
                $mail->send();
            }
            $this->_mail = null;
        } catch (Exception $e) {
            $this->_mail = null;
            Mage::logException($e);
            return false;
        }

        return true;
    }

    protected function _imagesToAttachments($html)
    {
        $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'); //preserve ążźćęłó etc
        $dom = new DOMDocument(null, 'UTF-8');
        @$dom->loadHTML($html); //suppress warnings because this gets sometimes wrongly formatted html code and warnings prevent emails from sending
        $dom->preserveWhiteSpace = false;

        foreach ($dom->getElementsByTagName('img') as $image) {
            //check if image has class 'noembed' if it has then skip to the next one
            //using array_flip + isset combo instead of in_array/array_search because it's quicker
            $classes = $image->getAttribute('class');
            $classes = $classes ? array_flip(explode(" ", $classes)) : array();
            if (!isset($classes['noembed'])) {
                $src = trim($image->getAttribute('src'));
                //skip already embeded images
                if (substr($src, 0, 4) !== "cid:") {
                    $extensionArr = explode(".", $src);
                    $extension = end($extensionArr);
                    if ($this->_checkImageExtensions($extension)) {
                        if (substr($src, 0, 7) !== "http://" && substr($src, 0, 8) !== "https://") {
                            $src = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK) . $src;
                        }
                        $key = md5($src);
                        $filename = $key . "." . $extension;
                        if (!isset($this->_domAttachments[$key])) {
                            $this->_domAttachments[$key] = array(
                                'content' => file_get_contents($src, FILE_USE_INCLUDE_PATH),
                                'type' => Zend_Mime::TYPE_OCTETSTREAM,
                                'disposition' => Zend_Mime::DISPOSITION_ATTACHMENT,
                                'encoding' => Zend_Mime::ENCODING_BASE64,
                                'filename' => $filename,
                                'id' => $filename
                            );
                        }
                        $image->setAttribute('src', 'cid:' . $filename);
                    }
                }
            }
        }

        return $dom->saveHTML();
    }

    protected function _checkImageExtensions($ext)
    {
        return isset($this->_allowedExtensions[strtolower($ext)]);
    }
}