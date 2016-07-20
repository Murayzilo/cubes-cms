<?php

class Admin_MembersController extends Zend_Controller_Action {

    public function indexAction() {

        $flashMessenger = $this->getHelper('FlashMessenger');

        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors')
        );



//prikaz svih member-a

        $cmsMembersDbTable = new Application_Model_DbTable_CmsMembers();

        // $select je objekat klase Zend_Db_Select
        $select = $cmsMembersDbTable->select();

        $select->order('order_number');


        //debug za db select - vraca se sql upit
        //die($select->assemble());

        $members = $cmsMembersDbTable->fetchAll($select);

        $this->view->members = $members; //prosledjivanje rezultata
        $this->view->systemMessages = $systemMessages;
    }

    public function addAction() {

        $request = $this->getRequest();
        $flashMessenger = $this->getHelper('FlashMessenger');

        $form = new Application_Form_Admin_MemberAdd();

//default form data
        $form->populate(array(
        ));

        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors'),
        );

        if ($request->isPost() && $request->getPost('task') === 'save') {

            try {

                //check form is valid
                if (!$form->isValid($request->getPost())) {
                    throw new Application_Model_Exception_InvalidInput('Invalid data was sent for new member.');
                }

                //get form data
                $formData = $form->getValues();

                //remove key member_photo  form data because there is no column member_photo in cms_members 
                unset($formData['member_photo']);

                //Insertujemo novi zapis u tabelu
                $cmsMembersTable = new Application_Model_DbTable_CmsMembers();

                // insert member returns ID of the new member
                $memberId = $cmsMembersTable->insertMember($formData);


                if ($form->getElement('member_photo')->isUploaded()) {

                    // photo is uploaded 
                    $fileInfos = $form->getElement('member_photo')->getFileInfo('member_photo');
                    $fileInfo = $fileInfos['member_photo']; // ILI NA OVAJ NACIN $fileInfos =$_FILES['member_photo']

                    try {
                        // open uploaded photo in temporary directory
                        $memberPhoto = Intervention\Image\ImageManagerStatic::make($fileInfo['tmp_name']);

                        $memberPhoto->fit(150, 150);

                        //snimanje slike , mesto gde ce se slika sacuvati
                        $memberPhoto->save(PUBLIC_PATH . '/uploads/members/' . $memberId . '.jpg');
                    } catch (Exception $ex) {

                        //set system message
                        $flashMessenger->addMessage('Member has been saved but error occured during image processing', 'errors');
                        // $flashMessenger->addMessage('Or maybe somethign is wrong', 'errors');
                        //redirect to same or another page
                        $redirector = $this->getHelper('Redirector');
                        $redirector->setExit(true)
                                ->gotoRoute(array(
                                    'controller' => 'admin_members',
                                    'action' => 'edit',
                                    'id' => $memberId,
                                        ), 'default', true);
                    }



                    //print_r($fileInfo);
                    //die();
                }


                //die(print_r($formData, true));
                //do actual task
                //save to database etc
                //
                //set system message
                $flashMessenger->addMessage('Member has been saved', 'success');
                // $flashMessenger->addMessage('Or maybe somethign is wrong', 'errors');
                //redirect to same or another page
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_members',
                            'action' => 'index',
                                ), 'default', true);
            } catch (Application_Model_Exception_InvalidInput $ex) {
                $systemMessages['errors'][] = $ex->getMessage();
            }
        }

        $this->view->systemMessages = $systemMessages;
        $this->view->form = $form;
    }

    public function editAction() {

        $request = $this->getRequest();

        $id = (int) $request->getParam('id');

        if ($id <= 0) {
            //prekida se izvrsavanje i prikazuje se "page not found"
            throw new Zend_Controller_Router_Exception('Invalid member id:' . $id, 404);
        }

        $cmsMembersTable = new Application_Model_DbTable_CmsMembers();

        $member = $cmsMembersTable->getMemberById($id);

        if (empty($member)) {

            throw new Zend_Controller_Router_Exception('No member is found with id:' . $id, 404);
        }

        $flashMessenger = $this->getHelper('FlashMessenger');



        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors'),
        );

        $form = new Application_Form_Admin_MemberAdd();
//
////default form data
        $form->populate($member);

        if ($request->isPost() && $request->getPost('task') === 'update') {

            try {

                //check form is valid
                if (!$form->isValid($request->getPost())) {
                    throw new Application_Model_Exception_InvalidInput('Invalid data was sent for member.');
                }

                //get form data
                $formData = $form->getValues();

                //remove key member_photo  form data because there is no column member_photo in cms_members 
                unset($formData['member_photo']);
                //Radimo update postojeceg zapisa u tabeli

                if ($form->getElement('member_photo')->isUploaded()) {

                    // photo is uploaded 
                    $fileInfos = $form->getElement('member_photo')->getFileInfo('member_photo');
                    $fileInfo = $fileInfos['member_photo']; // ILI NA OVAJ NACIN $fileInfos =$_FILES['member_photo']

                    try {
                        // open uploaded photo in temporary directory
                        $memberPhoto = Intervention\Image\ImageManagerStatic::make($fileInfo['tmp_name']);

                        $memberPhoto->fit(150, 150);

                        //snimanje slike , mesto gde ce se slika sacuvati
                        $memberPhoto->save(PUBLIC_PATH . '/uploads/members/' . $member['id'] . '.jpg');
                    } catch (Exception $ex) {

                        throw new Application_Model_Exception_InvalidInput('Error occured during image processing: ' . $ex->getMessage());
                    }



                    //print_r($fileInfo);
                    //die();
                }


                //$cmsMembersTable->update($formData, 'id = ' . $member['id']);
                $cmsMembersTable->updateMember($member['id'], $formData);

                //set system message
                $flashMessenger->addMessage('Member has been updated', 'success');
                // $flashMessenger->addMessage('Or maybe somethign is wrong', 'errors');
                //redirect to same or another page
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_members',
                            'action' => 'index',
                                ), 'default', true);
            } catch (Application_Model_Exception_InvalidInput $ex) {
                $systemMessages['errors'][] = $ex->getMessage();
            }
        }

        $this->view->systemMessages = $systemMessages;
        $this->view->form = $form;

        $this->view->member = $member;
    }

    public function deleteAction() {

        $request = $this->getRequest();

        if (!$request->isPost() || $request->getPost('task') != 'delete') {
            //request is not post redirect to index page
            //or task is not delete
            //redirect to index page
            //redirect to same or another page
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_members',
                        'action' => 'index',
                            ), 'default', true);
        }

        $flashMessenger = $this->getHelper('FlashMessenger');


        try {
            //read $_POST['id']
            $id = (int) $request->getPost('id');

            if ($id <= 0) {

                throw new Application_Model_Exception_InvalidInput('Invalid member id:' . $id , 'errors');
            }

            $cmsMembersTable = new Application_Model_DbTable_CmsMembers();

            $member = $cmsMembersTable->getMemberById($id);

            if (empty($member)) {
                throw new Application_Model_Exception_InvalidInput('No member is found with id' . $id , 'errors');
            }


            $cmsMembersTable->deleteMember($id);

            $flashMessenger->addMessage('Member ' . $member['first_name'] . ' ' . $member['last_name'] . 'has been deleted', 'success');
            //redirect to same or another page
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_members',
                        'action' => 'index',
                            ), 'default', true);
        } catch (Application_Model_Exception_InvalidInput $ex) {

            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            //redirect to same or another page
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_members',
                        'action' => 'index',
                            ), 'default', true);
        }
    }

    public function enableAction() {

        $request = $this->getRequest();

        if (!$request->isPost() || $request->getPost('task') != 'enable') {
            //request is not post redirect to index page
            //or task is not delete
            //redirect to index page
            //redirect to same or another page
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_members',
                        'action' => 'index',
                            ), 'default', true);
        }

        $flashMessenger = $this->getHelper('FlashMessenger');


        try {
            //read $_POST['id']
            $id = (int) $request->getPost('id');

            if ($id <= 0) {

                throw new Application_Model_Exception_InvalidInput('Invalid member id:' . $id , 'errors');
            }

            $cmsMembersTable = new Application_Model_DbTable_CmsMembers();

            $member = $cmsMembersTable->getMemberById($id);

            if (empty($member)) {
                throw new Application_Model_Exception_InvalidInput('No member is found with id' . $id , 'errors');
            }


            $cmsMembersTable->enableMember($id);

            $flashMessenger->addMessage('Member ' . $member['first_name'] . ' ' . $member['last_name'] . ' has been enabled', 'success');
            //redirect to same or another page
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_members',
                        'action' => 'index',
                            ), 'default', true);
        } catch (Application_Model_Exception_InvalidInput $ex) {

            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            //redirect to same or another page
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_members',
                        'action' => 'index',
                            ), 'default', true);
        }
    }

    public function disableAction() {

        $request = $this->getRequest();

        if (!$request->isPost() || $request->getPost('task') != 'disable') {
            //request is not post redirect to index page
            //or task is not delete
            //redirect to index page
            //redirect to same or another page
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_members',
                        'action' => 'index',
                            ), 'default', true);
        }

        $flashMessenger = $this->getHelper('FlashMessenger');


        try {
            //read $_POST['id']
            $id = (int) $request->getPost('id');

            if ($id <= 0) {

                throw new Application_Model_Exception_InvalidInput('Invalid member id:' . $id , 'errors');
            }

            $cmsMembersTable = new Application_Model_DbTable_CmsMembers();

            $member = $cmsMembersTable->getMemberById($id);

            if (empty($member)) {
                throw new Application_Model_Exception_InvalidInput('No member is found with id' . $id , 'errors');
            }

            $cmsMembersTable->disableMember($id);

            $flashMessenger->addMessage('Member ' . $member['first_name'] . ' ' . $member['last_name'] . ' has been disabled', 'success');
            //redirect to same or another page
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_members',
                        'action' => 'index',
                            ), 'default', true);
        } catch (Application_Model_Exception_InvalidInput $ex) {

            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            //redirect to same or another page
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_members',
                        'action' => 'index',
                            ), 'default', true);
        }
    }

    public function updateorderAction() {

        $request = $this->getRequest();

        if (!$request->isPost() || $request->getPost('task') != 'saveOrder') {
            // request is not post
            // or task is not saveOrder
            //redirect to index page
            //redirect to same or another page
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_members',
                        'action' => 'index'
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
                throw new Application_Model_Exception_InvalidInput('Invalid sorted ids: ' . $sortedIds);
            }

            $sortedIds = explode(',', $sortedIds);

            $cmsMembersTable = new Application_Model_DbTable_CmsMembers();

            $cmsMembersTable->updateOrderOfMembers($sortedIds);

            $flashMessenger->addMessage('Order is successfully saved', 'success');

            //redirect to same or another page
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_members',
                        'action' => 'index'
                            ), 'default', true);
        } catch (Application_Model_Exception_InvalidInput $ex) {

            $flashMessenger->addMessage($ex->getMessage(), 'errors');

            //redirect to same or another page
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_members',
                        'action' => 'index'
                            ), 'default', true);
        }
    }

}
