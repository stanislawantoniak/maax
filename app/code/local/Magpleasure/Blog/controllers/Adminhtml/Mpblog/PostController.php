<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */
class Magpleasure_Blog_Adminhtml_Mpblog_PostController extends Magpleasure_Blog_Controller_Adminhtml_Filterable
{
    /**
     * Helper
     * @return Magpleasure_Blog_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('mpblog');
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        $aclRoute = 'cms/mpblog/posts';

        $this
            ->_getSession()
            ->setControlRoutePath($aclRoute)
        ;

        return Mage::getSingleton('admin/session')->isAllowed($aclRoute);
    }

    /**
     * Initialize layout prefer any action
     *
     * @return $this
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('cms/mpblog/posts')
            ->_addBreadcrumb($this->_helper()->__('Blog'), $this->_helper()->__('Blog'));
        return $this;
    }

    protected function _resetDraft()
    {
        $postId = $this->getRequest()->getParam('id');
        $user = $this->_helper()->getAdminSession()->getUser();
        if ($user) {
            $userId = $user->getId();
        }
        /** @var Magpleasure_Blog_Model_Draft $draft */
        $draft = Mage::getModel('mpblog/draft');

        if ($postId) {
            $draft->clearForPost($postId, $userId);
        } else {
            $draft->clearUnassigned($userId);
        }

        return $this;
    }

    public function indexAction()
    {
        $this->_prepareStoreFilter();

        $this->_initAction()
            ->renderLayout();
    }

    public function newAction()
    {
        $this->_prepareStoreFilter();

        $this->_forward('edit');
    }

    protected function _restoreFromDraft(Magpleasure_Blog_Model_Post $post)
    {
        # 1. Find draft
        $postId = $this->getRequest()->getParam('id');
        /** @var Mage_Admin_Model_User $user */
        $user = $this->_helper()->getAdminSession()->getUser();
        $userId = $user->getId();
        $draft = Mage::getModel('mpblog/draft');

        $keysToCheck = array(
            'full_content',
            'short_content'
        );

        if ($postId) {

            $draft->loadByPostAndUser($postId, $userId);
        } else {

            $draft->loadLatestUnassigned($userId);
        }

        if ($draft->getId()) {

            foreach ($keysToCheck as $key) {
                if ($draft->getData($key)) {

                    if ($draft->getData($key) !== $post->getData($key)) {
                        $post->setData($key, $draft->getData($key));
                    }
                }
            }

            # 2. Create message
            $draftId = $draft->getId();
            $rejectDraftUrl = $this->getUrl('*/*/rejectDraft', array(
                'id' => $draftId
            ));

            $messageText = $this->_helper()->__("Your content has been restored from the unsaved draft. You may reject it and return to the latest saved copy.");
            $link = "<a href=\"" . $rejectDraftUrl . "\">" . $this->_helper()->__("Reject") . "</a>";
            $actionText = $this->_helper()->__("%s the Draft or continue editing.", $link);

            $this->_getSession()->addNotice($messageText . " " . $actionText);
        }


        return $this;
    }

    public function rejectDraftAction()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            $draft = Mage::getModel('mpblog/draft');
            $draft->load($id);
            if ($draft->getId()) {
                $draft->delete();
            }
        }

        $this->_redirectReferer();
    }

    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        /** @var Magpleasure_Blog_Model_Post $post */
        $post = Mage::getModel('mpblog/post');
        if ($id) {
            $post->load($id);
        }

        if ($post->getId() || !$id) {
            Mage::register('current_post', $post);
            $data = Mage::getSingleton('adminhtml/session')->getPostData(true);

            if (!empty($data)) {
                $post->setData($data);
            }

            if ($post->getId() && $post->isHidden()) {
                $postClone = clone $post;

                $stores = $postClone->getStores();
                if ($stores && is_array($stores) && count($stores) && isset($stores[0])) {
                    $storeId = $stores[0];
                } else {
                    $storeId = Mage::app()->getDefaultStoreView()->getId();
                }

                $postClone->setStoreId($storeId);
                $postUrl = $postClone->getPostUrl();

                $this->_getSession()->addNotice($this->__("This Post is hidden but you can see it here - <a href='%s' target='_blank'>%s</a>.", $postUrl, $postUrl));
            }

            if ($post->getId()) {
                $post->processMediaTemplates();
                $post->processCutter();
            }

            $this->_restoreFromDraft($post);

        } else {
            $this->_getSession()->addError($this->_helper()->__('Post is not exists.'));
            $this->_redirect('*/*/index');
        }

        $this->loadLayout();

        $this->_setActiveMenu('cms/mpblog/posts');
        $this->renderLayout();
    }

    public function backAction()
    {
        $this->_resetDraft();
        $params = $this->_getCommonParams();
        $this->_redirect("*/*/index", $params);
    }

    public function resetAction()
    {
        $this->_resetDraft();
        $params = $this->_getCommonParams();
        if ($this->getRequest()->getParam('id')) {
            $params['id'] = $this->getRequest()->getParam('id');
            $this->_redirect('*/*/edit', $params);
        } else {
            $this->_redirect('*/*/new', $params);
        }
    }

    public function saveAction()
    {
        $this->_resetDraft();
        $requestPost = $this->getRequest()->getPost();

        /** @var Magpleasure_Blog_Model_Post $post */
        $post = Mage::getModel('mpblog/post');
        if ($id = $this->getRequest()->getParam('id')) {
            $post->load($id);
        }

        try {
            $post->addData($requestPost);

            # Process Timezone
            if (($post->getUserDefinePublish() || $post->getId() || $post->getId()) && $post->getPublishedAt()) {
                $datetimeFormat = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
                $publishedAt = new Zend_Date($post->getPublishedAt(), $datetimeFormat);
                $publishedAt->addSecond($this->_helper()->getTimezoneOffset());
                $post->setPublishedAt($publishedAt->toString(Varien_Date::DATETIME_INTERNAL_FORMAT));
            }

            $post->save();
            $this->_getSession()->addSuccess($this->_helper()->__("Post was successfully saved."));

            $params = $this->_getCommonParams();
            if ($this->getRequest()->getParam('back')) {
                $params['id'] = $this->getRequest()->getParam('id') ? $this->getRequest()->getParam('id') : $post->getId();
                $params['tab'] = $this->getRequest()->getParam('tab');
                $this->_redirect('*/*/edit', $params);
            } else {
                $this->_redirect('*/*/index', $params);
            }

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->setPostData($requestPost);
            $this->_getSession()->addError($this->_helper()->__("Error while saving the post. %s", $e->getMessage()));
            $this->_redirectReferer();
        }

    }

    /**
     * Delete slide
     * @param int|string $id
     * @return boolean
     */
    protected function _delete($id)
    {
        $post = Mage::getModel('mpblog/post')->load($id);
        if ($post->getId()) {
            try {
                $post->delete();
                return true;
            } catch (Exception $e) {
                return false;
            }
        }
        return false;
    }

    /**
     * Duplicate form
     * @param int|string $id
     * @return boolean
     */
    protected function _duplicate($id)
    {
        /** @var Magpleasure_Blog_Model_Post $post */
        $post = Mage::getModel('mpblog/post')->load($id);
        if ($post->getId()) {
            try {
                $newPost = $post->duplicate();
                return $newPost;
            } catch (Exception $e) {
                return false;
            }
        }
        return false;
    }

    protected function _updateStatus($id, $status)
    {
        if ($id) {
            try {
                $post = Mage::getModel('mpblog/post')->load($id);
                $post->setStatus($status);
                $post->save();
                return true;
            } catch (Exception $e) {
                return false;
            }
        }
    }

    public function massStatusAction()
    {
        $posts = $this->getRequest()->getPost('posts');
        $status = $this->getRequest()->getPost('status');
        if ($posts) {
            $success = 0;
            $error = 0;
            foreach ($posts as $postId) {
                if ($this->_updateStatus($postId, $status)) {
                    $success++;
                } else {
                    $error++;
                }
            }
            if ($success) {
                $this->_getSession()->addSuccess($this->_helper()->__("%s posts were successfully updated.", $success));
            }
            if ($error) {
                $this->_getSession()->addError($this->_helper()->__("%s posts weren't updated.", $error));
            }
        }
        $this->_redirectReferer();
    }

    public function massDeleteAction()
    {
        $posts = $this->getRequest()->getPost('posts');
        if ($posts) {
            $success = 0;
            $error = 0;
            foreach ($posts as $postId) {
                if ($this->_delete($postId)) {
                    $success++;
                } else {
                    $error++;
                }
            }
            if ($success) {
                $this->_getSession()->addSuccess($this->_helper()->__("%s posts were successfully deleted.", $success));
            }
            if ($error) {
                $this->_getSession()->addError($this->_helper()->__("%s posts weren't deleted.", $error));
            }
        }
        $this->_redirectReferer();
    }

    public function massDuplicateAction()
    {
        $posts = $this->getRequest()->getPost('posts');
        if ($posts) {
            $success = 0;
            $error = 0;
            foreach ($posts as $postId) {
                if ($this->_duplicate($postId)) {
                    $success++;
                } else {
                    $error++;
                }
            }
            if ($success) {
                $this->_getSession()->addSuccess($this->_helper()->__("%s posts were successfully duplicated.", $success));
            }
            if ($error) {
                $this->_getSession()->addError($this->_helper()->__("%s posts weren't duplicated.", $error));
            }
        }
        $this->_redirectReferer();
    }

    public function duplicateAction()
    {
        $this->_resetDraft();

        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $newPost = $this->_duplicate($id);
                $this->_getSession()->addSuccess($this->_helper()->__("Post was successfully duplicated."));

                $params = $this->_getCommonParams();
                $params['id'] = $newPost->getId();

                $this->_redirect('*/*/edit', $params);

            } catch (Exception $e) {

                $this->_getSession()->addError($this->_helper()->__("Post wasn't duplicated. %s", $e->getMessage()));
                $this->_helper()->getCommon()->getException()->logException($e);
                $this->_redirectReferer();

                return;
            }
        }
    }

    public function deleteAction()
    {
        $this->_resetDraft();

        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $this->_delete($id);
                $this->_getSession()->addSuccess($this->_helper()->__("Post was successfully deleted."));
            } catch (Exception $e) {
                $this->_getSession()->addError($this->_helper()->__("Post wasn't deleted (%s).", $e->getMessage()));
                $this->_redirectReferer();
                return;
            }
        }
        $params = $this->_getCommonParams();
        $this->_redirect('*/*/index', $params);
    }

    public function gridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function tagsAction()
    {
        if ($q = $this->getRequest()->getParam('q')) {
            /** @var Magpleasure_Blog_Model_Mysql4_Tag_Collection $collection */
            $collection = Mage::getModel('mpblog/tag')->getCollection();

            $collection
                ->addFieldToFilter('name', array('like' => "%{$q}%"));

            if ($this->isStoreFilterApplied()) {
                $collection->addWieghtData($this->getAppliedStoreId());
            }

            if ($limit = $this->getRequest()->getParam('limit')) {
                $collection->setPageSize($limit);
            }

            foreach ($collection as $tag) {
                $key = $tag->getName();
                $name = $tag->getName();
                echo "{$name}|{$key}\n";
            }
        };
    }

    public function massUpdateAttributeAction()
    {
        $posts = $this->getRequest()->getPost('posts');
        if ($posts && is_array($posts)) {

            Mage::register(
                Magpleasure_Blog_Block_Adminhtml_Attribute_Edit_Tabs_Posts::REGISTRY_POSTS,
                $posts,
                true
            );

            $this
                ->loadLayout()
                ->_setActiveMenu('cms/mpblog/post')
                ->renderLayout();

        } else {
            $this->_getSession()->addError($this->_helper()->__("Oops! There are no any posts to update."));
            $this->_redirectReferer();
        }

        return $this;
    }

    public function massUpdateAttributeGoAction()
    {
        $postIds = $this->getRequest()->getPost('post_ids');
        $data = $this->getRequest()->getPost();

        if ($postIds) {

            try {

                $attributeHelper = $this->_helper()->getAttribute();
                $result = $attributeHelper->apply($data, $postIds);

                if ($result['errors']){

                    foreach ($result['errors'] as $errorMessage){
                        $this->_getSession()->addError($errorMessage);
                    }
                }

                if ($result['skip']){

                    $this->_getSession()->addNotice(
                        $this->_helper()->__(
                            "%s posts weren't changed. There are no changes required.",
                            $result['skip']
                        )
                    );
                }

                if ($result['success']){

                    $this->_getSession()->addSuccess(
                        $this->_helper()->__(
                            "%s posts were successfully updated",
                            $result['success']
                        )
                    );
                }



                if (Mage::getSingleton('adminhtml/session')->hasAttributeUpdateData()) {
                    Mage::getSingleton('adminhtml/session')->unsetAttributeUpdateData();
                }



                $this->_redirect('*/*/index', $this->_getCommonParams());

            } catch (Exception $e) {

                Mage::getSingleton('adminhtml/session')->setAttributeUpdateData($data);
                $this->_getSession()->addError(
                    $this->_helper()->__("Something went wrong. %s", $e->getMessage())
                );
                $this->_redirect('*/*/index', $this->_getCommonParams());
            }

        } else {

            $this->_getSession()->addError(
                $this->_helper()->__("Something went wrong. There are no posts defined.")
            );
            $this->_redirect('*/*/index', $this->_getCommonParams());
        }

        return $this;
    }

    public function uploadImageAction()
    {
        $result = array();

        $imageProvider = $this->_helper()->getImagesProvider();
        $errorCodes = array(
            1 => $this->_helper()->__("The uploaded file exceeds the upload_max_filesize directive in php.ini"),
            2 => $this->_helper()->__("The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form"),
            3 => $this->_helper()->__("The uploaded file was only partially uploaded"),
            4 => $this->_helper()->__("No file was uploaded"),
            6 => $this->_helper()->__("Missing a temporary folder"),
            7 => $this->_helper()->__("Failed to write file to disk"),
            8 => $this->_helper()->__("A PHP extension stopped the file upload"),
        );

        $fileType = strtolower($_FILES['file']['type']);
        $errorCode = strtolower($_FILES['file']['error']);
        $allowedFileTypes = array(
            'image/png',
            'image/jpg',
            'image/gif',
            'image/jpeg',
            'image/pjpeg',
        );

        if ($errorCode) {
            $result['error'] = @$errorCodes[$errorCode];

        } elseif (in_array($fileType, $allowedFileTypes)) {

            $fileName = $imageProvider->getNewName($fileType);
            $movePath = $imageProvider->getFilePath($fileName);
            move_uploaded_file($_FILES['file']['tmp_name'], $movePath);
            $result['filelink'] = $imageProvider->getDestinationUrl($fileName);

        } else {
            $result['error'] = $this->_helper()->__("Files of this type are not allowed.");
        }

        $this->_ajaxResponse($result);
    }

    public function clipboardAction()
    {
        $result = array();
        $imageProvider = $this->_helper()->getImagesProvider();
        $fileName = $imageProvider->getNewName("image/jpg");

        if ($this->getRequest()->getPost('data')) {

            $movePath = $imageProvider->getFilePath($fileName);
            $data = base64_decode($this->getRequest()->getPost('data'));
            file_put_contents($movePath, $data);
            $result['filelink'] = $imageProvider->getDestinationUrl($fileName);

        } else {

            $result['error'] = $this->_helper()->__("Clipboard request is empty.");
        }

        $this->_ajaxResponse($result);
    }

    public function autosaveAction()
    {
        $result = array(
            'error' => false,
        );
        $postId = $this->getRequest()->getParam('id');
        $paramName = $this->getRequest()->getPost('name');
        $value = urldecode($this->getRequest()->getPost($paramName));

        /** @var Mage_Admin_Model_User $user */
        $user = $this->_helper()->getAdminSession()->getUser();
        if ($user->getId()) {

            $userId = $user->getId();

            if ($value) {

                try {

                    /** @var Magpleasure_Blog_Model_Draft $draft */
                    $draft = Mage::getModel('mpblog/draft');
                    if ($postId) {

                        $post = Mage::getModel('mpblog/post');
                        $post->load($postId);
                        if ($post->getId()) {

                            if ($post->getData($paramName) != $value) {

                                $draft->loadByPostAndUser($postId, $userId);
                                $draft->addData(array(
                                    'post_id' => $postId,
                                    'user_id' => $userId,
                                    $paramName => $value,
                                ))->save();
                            }
                        }

                    } else {

                        $draft->loadLatestUnassigned($userId);
                        $draft->addData(array(
                            'user_id' => $userId,
                            $paramName => $value,
                        ))->save();
                    }

                } catch (Exception $e) {
                    $result['error'] = true;
                    $result['message'] = $e->getMessage();
                }
            }

        } else {

            $result['error'] = true;
            $result['message'] = $this->_helper()->__("Session is expired.");
        }

        $this->_ajaxResponse($result);
    }

    /**
     * Prepare images for WYSIWYG editor
     *
     * @return void
     */
    public function imageListJsonAction()
    {
        $imageProvider = $this->_helper()->getImagesProvider();
        $this->_ajaxResponse($imageProvider->getImageList());
    }

    public function magentoLinksJsonAction()
    {
        $links = array(
            array(
                "name" => $this->_helper()->__("Base URL"),
                "url" => "{{store url=''}}"
            ),
            array(
                "name" => $this->_helper()->__("Contact Us"),
                "url" => "{{store url='contacts'}}"
            ),
            array(
                "name" => $this->_helper()->__("Sign In"),
                "url" => "{{store url='customer/account/login'}}"
            ),
            array(
                "name" => $this->_helper()->__("Sign Up"),
                "url" => "{{store url='customer/account/create'}}"
            ),
            array(
                "name" => $this->_helper()->__("Blog Pro"),
                "url" => $this->_helper()->_url(Mage::app()->getDefaultStoreView()->getId())->getUrl()
            ),
            array(
                "name" => $this->_helper()->__("Media URL"),
                "url" => "{{media url='image_name.jpg'}}"
            ),
            array(
                "name" => $this->_helper()->__("Custom Page"),
                "url" => "{{store url=''}}custom_page.html"
            ),
        );
        $this->_ajaxResponse($links);
    }
}