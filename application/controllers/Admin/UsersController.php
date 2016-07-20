<?php

class Admin_UsersController extends Zend_Controller_Action
{
    
    public function indexAction() {
        
        
        $flashMessenger = $this->getHelper('FlashMessenger');

        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors')
        );
        
        $cmsUsersDbTable = new Application_Model_DbTable_CmsUsers();
        $users = $cmsUsersDbTable->fetchAll()->toArray();
        
        $this->view->users = $users;   
        $this->view->systemMessages = $systemMessages;
        
    }
    
    public function addAction() {
        $request = $this->getRequest();
        $flashMessenger = $this->getHelper('FlashMessenger');

        $form = new Application_Form_Admin_UserAdd();

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
                    throw new Application_Model_Exception_InvalidInput('Invalid data was sent for new user.');
                }

                //get form data
                $formData = $form->getValues();

                //Insertujemo novi zapis u tabelu
                $cmsUsersTable = new Application_Model_DbTable_CmsUsers();

                // insert member returns ID of the new member
                $userId = $cmsUsersTable->insertUser($formData);

                //set system message
                $flashMessenger->addMessage('User has been saved', 'success');
                // $flashMessenger->addMessage('Or maybe somethign is wrong', 'errors');
                //redirect to same or another page
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_users',
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
            throw new Zend_Controller_Router_Exception('Invalid user id:' . $id, 404);
        }

        $loggedinUser = Zend_Auth::getInstance()->getIdentity();
        
        if ($id == $loggedinUser['id']) {
            //redirector user to edit progile page
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_profile',
                            'action' => 'edit',
                                ), 'default', true);
            
            
        }
        
        $cmsUsersTable = new Application_Model_DbTable_CmsUsers();

        $user = $cmsUsersTable->getUserById($id);

        if (empty($user)) {

            throw new Zend_Controller_Router_Exception('No user is found with id:' . $id, 404);
        }

        $flashMessenger = $this->getHelper('FlashMessenger');

        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors'),
        );

        $form = new Application_Form_Admin_UserEdit($user['id']);

        ////default form data
        $form->populate($user);

        if ($request->isPost() && $request->getPost('task') === 'update') {

            try {

                //check form is valid
                if (!$form->isValid($request->getPost())) {
                    throw new Application_Model_Exception_InvalidInput('Invalid data was sent for user.');
                }

                //get form data
                $formData = $form->getValues();

                //$cmsMembersTable->update($formData, 'id = ' . $member['id']);
                $cmsUsersTable->updateUser($user['id'], $formData);

                //set system message
                $flashMessenger->addMessage('User has been updated', 'success');
                // $flashMessenger->addMessage('Or maybe somethign is wrong', 'errors');
                //redirect to same or another page
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_users',
                            'action' => 'index',
                                ), 'default', true);
            } catch (Application_Model_Exception_InvalidInput $ex) {
                $systemMessages['errors'][] = $ex->getMessage();
            }
        }

        $this->view->systemMessages = $systemMessages;
        $this->view->form = $form;

        $this->view->member = $user;
    }
    
}
