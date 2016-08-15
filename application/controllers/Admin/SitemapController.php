<?php
class Admin_SitemapController extends Zend_Controller_Action {
    
    public function indexAction() {
        $request = $this->getRequest();
        $flashMessenger = $this->getHelper('FlashMessenger');
        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors')
        );
        //if no request_id parametere, than $Id will be 0
        $id = (int) $request->getParam('id', 0);
        if ($id < 0) {
            throw new Zend_Controller_Router_Exception('Invalid parent id for site map pages ', 404);
        }
        $cmsSitemapPagesDbTable = new Application_Model_DbTable_CmsSitemapPages();
        if ($id != 0) {
            $sitemapPage = $cmsSitemapPagesDbTable->getSitemapPageById($id);
            if (!$sitemapPage) {
                throw new Zend_Controller_Router_Exception('No sitemap is found for sitemap pages ', 404);
            }
        }
        $childSitemapPages = $cmsSitemapPagesDbTable->search(array(
            'filters' => array(
                'parent_id' => $id
            ),
            'orders' => array(
                'order_number' => 'ASC'
            ),
                //'limit' => 50,
                //'page' => 3
        ));
        $sitemapPageBreadcrumbs = $cmsSitemapPagesDbTable->getSitemapPageBreadcrumbs($id);
        
        $this->view->childSitemapPages = $childSitemapPages;
        $this->view->systemMessages = $systemMessages;
        $this->view->sitemapPageBreadcrumbs = $sitemapPageBreadcrumbs;
        $this->view->currentSitemapPageId = $id;
    }
    public function addAction() {
        
        $request = $this->getRequest(); //podaci iz url-a iz forme koje dobijemo
        $parentId = (int) $request->getParam('parent_id', 0);
        if ($parentId < 0) {
            throw new Zend_Controller_Router_Exception('Invalid parent id for site map pages ', 404);
        }
        
        $parentType = '';
        
        $cmsSitemapPagesDbTable = new Application_Model_DbTable_CmsSitemapPages();
        
        if ($parentId != 0) {
            //check if parent page exist
            $parentSitemapPage = $cmsSitemapPagesDbTable->getSitemapPageById($parentId);
            if (!$parentSitemapPage) {
                throw new Zend_Controller_Router_Exception('No site map page is found for id:  ' . $parentId, 404);
            }
            
            $parentType = $parentSitemapPage['type'];
        }
        $flashMessenger = $this->getHelper('FlashMessenger');
        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors'),
        );
        $form = new Application_Form_Admin_SitemapPageAdd($parentId, $parentType);
        $form->populate(array(
        ));
        if ($request->isPost() && $request->getPost('task') === 'save') {//ispitujemo da li je pokrenuta forma
            try {
                //check form is valid
                if (!$form->isValid($request->getPost())) {//sve sto je u post zahtevu prosledi formi na validaciju
                    throw new Application_Model_Exception_InvalidInput('Invalid data was sent for new sitemapPage');
                }
                //get form data//vrednosti iz forme se uzimaju preko getVaues i upisuju u niz
                //to su filtrirani i validirani podaci
                $formData = $form->getValues();
                //set parent_id for new page
                $formData['parent_id'] = $parentId;
                //remove key memebr_photo from form data because there is no column memebr_photo in cms _sitemapPages
                //unset($formData['sitemap_page_photo']);
                //insert sitemapPage returns ID of the new sitemapPage
                $sitemapPageId = $cmsSitemapPagesDbTable->insertSitemapPage($formData);
////                if ($form->getElement('sitemap_page_photo')->isUploaded()) {
//                    //photo is uploaded 
//
//                    $fileInfos = $form->getElement('sitemap_page_photo')->getFileInfo('sitemap_page_photo');
//                    $fileInfo = $fileInfos['sitemap_page_photo'];
//                    //$fileInfo = $_FILES["sitemap_page_photo"];
//
//
//                    try {
//                        //open uploaded photo in temporary directory
//                        $sitemapPagePhoto = Intervention\Image\ImageManagerStatic::make($fileInfo['tmp_name']);
//
//                        $sitemapPagePhoto->fit(150, 150);
//                        $sitemapPagePhoto->save(PUBLIC_PATH . '/uploads/sitemapPages/' . $sitemapPageId . '.jpg');
//                    } catch (Exception $ex) {
//
//                        $flashMessenger->addMessage('SitemapPage has been saved, but error occured during image processing', 'errors');
//
//                        //redirect to same or another page
//                        $redirector = $this->getHelper('Redirector');
//                        $redirector->setExit(true)
//                                ->gotoRoute(array(
//                                    'controller' => 'admin_sitemapPages',
//                                    'action' => 'edit',
//                                    'id' => $sitemapPageId
//                                        ), 'default', true);
//                    }
//                }
                // do actual task
                //save to database etc
                //set system message
                $flashMessenger->addMessage('SitemapPage has been saved', 'success');
                //redirect to same or another page
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_sitemap',
                            'action' => 'index',
                            'id' => $parentId
                                ), 'default', true);
            } catch (Application_Model_Exception_InvalidInput $ex) {
                $systemMessages['errors'][] = $ex->getMessage();
            }
        }
        $sitemapPageBreadcrumbs = $cmsSitemapPagesDbTable->getSitemapPageBreadcrumbs($parentId);
        $this->view->sitemapPageBreadcrumbs = $sitemapPageBreadcrumbs;
        $this->view->systemMessages = $systemMessages;
        $this->view->form = $form;
        $this->view->parentId = $parentId;
    }
    public function editAction() {
        $request = $this->getRequest();
        $id = (int) $request->getParam('id'); // moze biti i POst i GET
        if ($id <= 0) {
            //prekida se izvrsavanje proograma i prikazuje se page not found
            throw new Zend_Controller_Router_Exception('Invalid sitemapPage id: ' . $id, 404);
        }
        $cmsSitemapPagesTable = new Application_Model_DbTable_CmsSitemapPages();
        $sitemapPage = $cmsSitemapPagesTable->getSitemapPageById($id);
        if (empty($sitemapPage)) {
            //prekida se izvrsavanje proograma i prikazuje se page not found
            throw new Zend_Controller_Router_Exception('No sitemapPage is found with id ' . $id, 404);
        }
        
        $parentType = '';
        
        if ($sitemapPage['parent_id']!= 0)   {
            $parentSitemapPage = $cmsSitemapPagesTable->getSitemapPageById($sitemapPage['parent_id']);
            $parentType = $parentSitemapPage['type'];
        }
        
        $flashMessenger = $this->getHelper('FlashMessenger');
        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors'),
        );
        $form = new Application_Form_Admin_SitemapPageEdit($sitemapPage['id'], $sitemapPage['parent_id'], $parentType);
        //default form data//mi nemamo default vrednosti
        $form->populate($sitemapPage);
        if ($request->isPost() && $request->getPost('task') === 'update') {//ispitujemo da lije pokrenuta forma
            try {
                //check form is valid
                if (!$form->isValid($request->getPost())) {//sve sto je u post zahtevu prosledi formi na validaciju
                    throw new Application_Model_Exception_InvalidInput('Invalid data was sent for  sitemapPage');
                }
                $formData = $form->getValues();
                //unset($formData['sitemapPage_photo']);
//
//                if ($form->getElement('sitemapPage_photo')->isUploaded()) {
//                    //photo is uploaded 
//
//                    $fileInfos = $form->getElement('sitemapPage_photo')->getFileInfo('sitemapPage_photo');
//                    $fileInfo = $fileInfos['sitemapPage_photo'];
//                    //$fileInfo = $_FILES["sitemapPage_photo"];
//
//
//                    try {
//                        //open uploaded photo in temporary directory
//                        $sitemapPagePhoto = Intervention\Image\ImageManagerStatic::make($fileInfo['tmp_name']);
//
//                        $sitemapPagePhoto->fit(150, 150);
//
//                        $sitemapPagePhoto->save(PUBLIC_PATH . '/uploads/sitemapPages/' . $sitemapPage['id'] . '.jpg');
//                    } catch (Exception $ex) {
//
//                        throw new Application_Model_Exception_InvalidInput('Error occured during image processing');
//                    }
//                }
                $cmsSitemapPagesTable->updateSitemapPage($sitemapPage['id'], $formData);
                // do actual task
                //save to database etc
                //set system message
                $flashMessenger->addMessage('SitemapPage has been updated', 'success');
                //redirect to same or another page
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_sitemap',
                            'action' => 'index',
                            'id' => $sitemapPage['parent_id']
                                ), 'default', true);
            } catch (Application_Model_Exception_InvalidInput $ex) {
                $systemMessages['errors'][] = $ex->getMessage();
            }
        }
        $sitemapPageBreadcrumbs = $cmsSitemapPagesTable->getSitemapPageBreadcrumbs($sitemapPage['parent_id']);
        
        $this->view->systemMessages = $systemMessages;
        $this->view->form = $form;
        $this->view->sitemapPageBreadcrumbs = $sitemapPageBreadcrumbs;
        $this->view->sitemapPage = $sitemapPage;
    }
    public function disableAction() {
        $request = $this->getRequest();
        if (!$request->isPost() || $request->getPost('task') != 'disable') {
            //request is not post, 
            //or task is not delete
            //redirecting to index page
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_sitemap',
                        'action' => 'index',
                        'id' => $sitemapPage['parent_id']
                            ), 'default', true);
        }
        $flashMessenger = $this->getHelper('FlashMessenger');
        try {
            //read $_POST
            $id = (int) $request->getPost('id');
            if ($id <= 0) {
                throw new Application_Model_Exception_InvalidInput('Invalid sitemap id: ' . $id, 'errors');
            }
            $cmsSitemapPagesTable = new Application_Model_DbTable_CmsSitemapPages();
            $sitemapPage = $cmsSitemapPagesTable->getSitemapPageById($id);
            if (empty($sitemapPage)) {
                throw new Application_Model_Exception_InvalidInput('No sitemap is found with id: ' . $id, 'errors');
            }
            $cmsSitemapPagesTable->disableSitemap($id);
            $flashMessenger->addMessage('Site: ' . $sitemapPage['short_title'] . 'has been disabled', 'success');
            //redirect on another page
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_sitemap',
                        'action' => 'index',
                        'id' => $sitemapPage['parent_id']
                            ), 'default', true);
        } catch (Application_Model_Exception_InvalidInput $ex) {
            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            //redirect on another page
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_sitemap',
                        'action' => 'index',
                        'id' => $sitemapPage['parent_id']
                            ), 'default', true);
        }
    }
    public function enableAction() {
        $request = $this->getRequest();
        if (!$request->isPost() || $request->getPost('task') != 'enable') {
            //request is not post, 
            //or task is not delete
            //redirecting to index page
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_sitemap',
                        'action' => 'index',
                        'id' => $sitemapPage['parent_id']
                            ), 'default', true);
        }
        $flashMessenger = $this->getHelper('FlashMessenger');
        try {
            //read $_POST
            $id = (int) $request->getPost('id');
            if ($id <= 0) {
                throw new Application_Model_Exception_InvalidInput('Invalid sitemap id: ' . $id, 'errors');
            }
            $cmsSitemapPagesTable = new Application_Model_DbTable_CmsSitemapPages();
            $sitemapPage = $cmsSitemapPagesTable->getSitemapPageById($id);
            if (empty($sitemapPage)) {
                throw new Application_Model_Exception_InvalidInput('No sitemap is found with id: ' . $id, 'errors');
            }
            $cmsSitemapPagesTable->enableSitemapPage($id);
            $flashMessenger->addMessage('Site: ' . $sitemapPage['short_title'] . ' has been enabled', 'success');
            //redirect on another page
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_sitemap',
                        'action' => 'index',
                        'id' => $sitemapPage['parent_id']
                            ), 'default', true);
        } catch (Application_Model_Exception_InvalidInput $ex) {
            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            //redirect on another page
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_sitemap',
                        'action' => 'index',
                        'id' => $sitemapPage['parent_id']
                            ), 'default', true);
        }
    }
    public function deleteAction() {
        $request = $this->getRequest();
        if (!$request->isPost() || $request->getPost('task') != 'delete') {
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_sitemap',
                        'action' => 'index',
                        'id' =>$sitemapPage['parent_id']
                            ), 'default', true);
        }
        $flashMessenger = $this->getHelper('FlashMessenger');
        try {
            //read $_POST
            $id = (int) $request->getPost('id');
            if ($id <= 0) {
                throw new Application_Model_Exception_InvalidInput('Invalid sitemap id: ' . $id, 'errors');
            }
           $cmsSitemapPagesTable = new Application_Model_DbTable_CmsSitemapPages();
            $sitemapPage = $cmsSitemapPagesTable->getSitemapPageById($id);
            if (empty($sitemapPage)) {
                throw new Application_Model_Exception_InvalidInput('No sitemap is found with id: ' . $id, 'errors');
            }
            $cmsSitemapPagesTable->deleteSitemapPage($id);
            $flashMessenger->addMessage('Site: ' . $sitemapPage['short_title'] .  ' has been deleted', 'success');
            //redirect on another page
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_sitemap',
                        'action' => 'index',
                        'id' =>$sitemapPage['parent_id']
                            ), 'default', true);
        } catch (Application_Model_Exception_InvalidInput $ex) {
            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            //redirect on another page
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_sitemap',
                        'action' => 'index',
                        'id' =>$sitemapPage['parent_id']
                            ), 'default', true);
        }
    }
    
    
    public function updateorderAction() {
        $request = $this->getRequest();
        $cmsSitemapPagesTable = new Application_Model_DbTable_CmsSitemapPages();
        //dobijamo id 
        $id = $this->getParam('id');
        $sitemapPage = $cmsSitemapPagesTable->getSitemapPageById($id);
        if (!$request->isPost() || $request->getPost('task') != 'saveOrder') {
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_sitemap',
                        'action' => 'index',
                        'id' => $sitemapPage['parent_id']
                            ), 'default', true);
        }
        $flashMessenger = $this->getHelper('FlashMessenger');
        try {
            $sortedIds = $request->getPost('sorted_ids');
            if (empty($sortedIds)) {
                throw new Application_Model_Exception_InvalidInput('Sorted ids are not sent');
            }
            $sortedIds = trim($sortedIds, ' ,');
            if (!preg_match('/^[0-9]+(,[0-9]+)*$/', $sortedIds)) {
                throw new Application_Model_Exception_InvalidInput('Invalid  sorted ids ' . $sortedIds);
            }
            $sortedIds = explode(',', $sortedIds);
            $cmsSitemapPagesTable->updateSitemapOfOrder($sortedIds);
            $flashMessenger->addMessage('Order is successfully saved', 'success');
            //redirect on another page
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_sitemap',
                        'action' => 'index',
                        'id' => $sitemapPage['parent_id']
                            ), 'default', true);
        } catch (Application_Model_Exception_InvalidInput $ex) {
            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            //redirect on another page
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_sitemap',
                        'action' => 'index',
                        'id' => $sitemapPage['parent_id']
                            ), 'default', true);
        }
    }
}