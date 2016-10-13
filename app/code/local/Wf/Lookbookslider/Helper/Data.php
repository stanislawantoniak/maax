<?php

/**
 * Class Wf_Lookbookslider_Helper_Data
 */
class Wf_Lookbookslider_Helper_Data extends Altima_Lookbookslider_Helper_Data {


    function checkEntry($domain, $ser) {
        return true;
        if ($this->isEnterpr()) {
            $key = sha1(base64_decode('bG9va2Jvb2tzbGlkZXJfZW50ZXJwcmlzZQ=='));
        } else {
            $key = sha1(base64_decode('YWx0aW1hbG9va2Jvb2tzbGlkZXI='));
        }

        $domain = str_replace('www.', '', $domain);
        $www_domain = 'www.' . $domain;

        if (sha1($key . $domain) == $ser || sha1($key . $www_domain) == $ser) {
            return true;
        }

        return false;
    }

    function checkEntryDev($domain, $ser) {
        return true;
        $key = sha1(base64_decode('YWx0aW1hbG9va2Jvb2tzbGlkZXJfZGV2'));

        $domain = str_replace('www.', '', $domain);
        $www_domain = 'www.' . $domain;
        if (sha1($key . $domain) == $ser || sha1($key . $www_domain) == $ser) {
            return true;
        }

        return false;
    }

}