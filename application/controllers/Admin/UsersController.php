<?php

class Admin_UsersController extends Zend_Controller_Action
{
    
    public function indexAction() {
        
        
        $flashMessenger = $this->getHelper('FlashMessenger');

        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors')
        );
        
//        $cmsUsersDbTable = new Application_Model_DbTable_CmsUsers();
//        
//        $loggedInUser = Zend_Auth::getInstance()->getIdentity();
//        
//        $users = $cmsUsersDbTable->search(array(
//            'filters' => array(
//                'id_exclude' => $loggedInUser['id']
//            ),
//                'orders' => array(
//                'status' => 'ASC',
//                'first_name' => 'DESC'
//            ),
////                'limit' => 3,
////                'page' => 2
//        ));
        
        $this->view->users = array(); 
        
        $this->view->systemMessages = $systemMessages;
        
    }
    
    public function addAction() {
        $request = $this->getRequest();
        $flashMessenger = $this->getHelper('FlashMessenger');

        $form = new Application_Form_Admin_UserAdd();

//default form data, nemamo default vrednosti
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

        $this->view->user = $user;
    }
    
    
    public function deleteAction() {

        $request = $this->getRequest();

        if (!$request->isPost() || $request->getPost('task') != 'delete') {

            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_users',
                        'action' => 'index'
                            ), 'default', true);
        }

        $flashMessenger = $this->getHelper('FlashMessenger');

        try {

            //read $_POST
            $id = (int) $request->getPost('id');
            if ($id <= 0) {
                throw new Application_Model_Exception_InvalidInput('Invalid user id: ' . $id, 'errors');
            }
            $cmsUsersTable = new Application_Model_DbTable_CmsUsers();
            $user = $cmsUsersTable->getUserById($id);
            if (empty($user)) {
                throw new Application_Model_Exception_InvalidInput('No user is found with id: ' . $id, 'errors');
            }

            $cmsUsersTable->deleteUser($id);
            $request instanceof Zend_Controller_Request_Http;

            //ispitivanje da li je request Ajax
            if ($request->isXmlHttpRequest()) {
                //request je Ajax
                //send response as json

                $responseJson = array(
                    'status' => 'ok',
                    'statusMessage' => 'User ' . $user['first_name'] . ' ' . $user['last_name'] . ' has been deleted.'
                );

                //send json as response by helpers
                $this->getHelper('Json')->sendJson($responseJson);
            } else {
                //request nije Ajax
                //send message over session
                //and do not redirect

                $flashMessenger->addMessage('User: ' . $user['first_name'] . ' ' . $user['last_name'] . ' has been deleted', 'success');
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_users',
                            'action' => 'index'
                                ), 'default', true);
            }
        } catch (Application_Model_Exception_InvalidInput $ex) {
            if ($request->isXmlHttpRequest()) {
                //request is ajax
                //send json as response
                $responseJson = array(
                    'status' => 'error',
                    'statusMessage' => $ex->getMessage()
                );
                //send json as response
                $this->getHelper('Json')->sendJson($responseJson);
            } else {
                //request is not ajax
                $flashMessenger->addMessage($ex->getMessage());
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_users',
                            'action' => 'index'
                                ), 'default', true);
            }
        }
    }

    public function enableAction() {
        $request = $this->getRequest();
        if (!$request->isPost() || $request->getPost('task') != 'enable') {
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_users',
                        'action' => 'index'
                            ), 'default', true);
        }
        $flashMessenger = $this->getHelper('FlashMessenger');
        try {
            //read $_POST['id']
            $id = (int) $request->getPost('id');
            
            if ($id <= 0) {
                
                throw new Application_Model_Exception_InvalidInput('Invalid user id: ' . $id, 'errors');
            }
            $loggedinUser = Zend_Auth::getInstance()->getIdentity();
            
            if ($id == $loggedinUser['id']) {
                
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_profile',
                            'action' => 'edit'
                                ), 'default', true);
            }
            $cmsUsersTable = new Application_Model_DbTable_CmsUsers();
            
            $user = $cmsUsersTable->getUserById($id);
            
            if (empty($user)) {
                throw new Application_Model_Exception_InvalidInput('No user is found with id: ' . $id, 'errors');
            }
            $cmsUsersTable->enableUser($id);
            //preko request objekta kao zahtev uzimamo sve sto je poslato na server 
            $request instanceof Zend_Controller_Request_Http;
            
            //ispitivanje da li je request Ajax
            if($request->isXmlHttpRequest()){
                //request je Ajax
                //send response as json
                
                $responseJson=array(
                    'status'=>'ok',
                    'statusMessage' => 'User: ' . $user['first_name'] . ' ' . $user['last_name'] . ' has been enabled'
                );
                
                //send json as response
                $this->getHelper('Json')->sendJson($responseJson);
                
            }else{
                //request nije Ajax
                //send message over session
                //and do not redirect
             //ukoliko zahtev nije ajax onda saljemo ovo   
            $flashMessenger->addMessage('User: ' . $user['first_name'] . ' ' . $user['last_name'] . ' has been enabled', 'success');            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_users',
                        'action' => 'index'
                            ), 'default', true);
            }
            
            
        } catch (Application_Model_Exception_InvalidInput $ex) {
            //ukoliko je uhvacena neka greska 
            //ponovo ispitujemo da li je ajax zahtev
            if($request->isXmlHttpRequest()){
                //request is ajax
                
                $responseJson = array(
                    'status'=>'error',
                    'statusMessage'=>$ex->getMessage()
                    
                );
                //send json as response
                $this->getHelper('Json')->sendJson($responseJson);
                
            }else{
                //request is not ajax
            $flashMessenger->addMessage($ex->getMessage());
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_users',
                        'action' => 'index'
                            ), 'default', true);
            }
            
        }
    }
    

    public function disableAction() {
        $request = $this->getRequest();
        if (!$request->isPost() || $request->getPost('task') != 'disable') {
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_users',
                        'action' => 'index'
                            ), 'default', true);
        }
        $flashMessenger = $this->getHelper('FlashMessenger');
        try {
            $id = (int) $request->getPost('id');
            if ($id <= 0) {
                throw new Application_Model_Exception_InvalidInput('Invalid user id: ' . $id, 'errors');
            }
            $loggedinUser = Zend_Auth::getInstance()->getIdentity();
            
            if ($id == $loggedinUser['id']) {
                //redirect user to edit profile page
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_profile',
                            'action' => 'edit'
                                ), 'default', true);
            }
            $cmsUsersTable = new Application_Model_DbTable_CmsUsers();
            
            $user = $cmsUsersTable->getUserById($id);
            
            if (empty($user)) {
                throw new Application_Model_Exception_InvalidInput('No user is found with id: ' . $id, 'errors');
            }
            $cmsUsersTable->disableUser($id);
            
           $request instanceof Zend_Controller_Request_Http;
           
            if ($request->isXmlHttpRequest()) {
                //request is ajax request
                //send response as json
                $responseJson = array(
                    'status' => 'ok',
                    'statusMessage' => 'User: ' . $user['first_name'] . ' ' . $user['last_name'] . ' has been disabled'
                );
                //send json as response
                $this->getHelper('Json')->sendJson($responseJson);//json action helper, layout is being disabled, view script rendering is being disabled
            } else {
                //request is not ajax
                //send message over session-flash message
                //and do redirect
                $flashMessenger->addMessage('User: ' . $user['first_name'] . ' ' . $user['last_name'] . ' has been disabled', 'success');
                //redirect on another page
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_users',
                            'action' => 'index'
                                ), 'default', true);
            }
        } catch (Application_Model_Exception_InvalidInput $ex) {
            if ($request->isXmlHttpRequest()) {
                //request is ajax
                $responseJson = array(
                    'status' => 'error',
                    'statusMessage' => $ex->getMessage()
                );
                //send json as response
                $this->getHelper('Json')->sendJson($responseJson);
            } else {
                $flashMessenger->addMessage($ex->getMessage());
                //redirect on another page
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_users',
                            'action' => 'index'
                                ), 'default', true);
            }
        }
    }
   

      public function resetpasswordAction() {

        $request = $this->getRequest();
        $id = (int) $request->getParam('id');
        if ($id <= 0) {

            throw new Zend_Controller_Router_Exception('Invalid user id: ' . $id, 404);
        }

        $loggedinUser = Zend_Auth::getInstance()->getIdentity();

        if ($id == $loggedinUser['id']) {
            //redirect to same or another page
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_profile',
                        'action' => 'changepassword'
                            ), 'default', true);
        }


        $cmsUsersTable = new Application_Model_DbTable_CmsUsers();

        $user = $cmsUsersTable->getUserById($id);
        if (empty($user)) {

            throw new Zend_Controller_Router_Exception('No user is found with id ' . $id, 404);
        }
        $flashMessenger = $this->getHelper('FlashMessenger');
        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors'),
        );

        if ($request->isPost() && $request->getPost('task') === 'resetpassword') {

            $cmsUsersTable->resetUserPassword($user['id']);

            $request instanceof Zend_Controller_Request_Http;

            if ($request->isXmlHttpRequest()) {
                $responseJson = array(
                    'status' => 'ok',
                    'statusMessage' => 'Users password: ' . $user['first_name'] . ' ' . $user['last_name'] . ' has been reset'
                );
                $this->getHelper('Json')->sendJson($responseJson);
            } else {
                $flashMessenger->addMessage('Password has been updated', 'success');
                //redirect to same or another page
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_users',
                            'action' => 'index'
                                ), 'default', true);
            }
        }
        $this->view->systemMessages = $systemMessages;
        $this->view->form = $form;
        $this->view->user = $user;
    }

    public function datatableAction() {
        $request = $this->getRequest();
        //dohvatamo sve parametre koji su poslati 
        $datatableParameters = $request->getParams();
        /*
          Array
          (
          [controller] => admin_users
          [action] => datatable
          [module] => default
          [draw] => 2
          [order] => Array
            (
            [0] => Array
                (
                [column] => 2
                [dir] => asc
                )
            )
            [start] => 0
            [length] => 5
            [search] => Array
                (
                [value] =>
                [regex] => false
                )
          )
         */
        $cmsUsersTable = new Application_Model_DbTable_CmsUsers();
        //defaultne vrednosti, dohvatanje logovanog korisnika
        $loggedInUser = Zend_Auth::getInstance()->getIdentity();
        $filters = array(
            'id_exclude' => $loggedInUser
        );
        $orders = array();
        $limit = 5;
        $page = 1;
        $draw = 1;
        //mora biti isti raspored kao i u view scripti (prezentacionoj logici)
        $columns = array('status', 'username', 'first_name', 'last_name', 'email', 'actions'); //actions ne postoji u bazi kao naziv kolone, enable, disable, delete..;
        
        //Process datateble parameters
        if (isset($datatableParameters['draw'])) {
            $draw = $datatableParameters['draw'];
            
            if (isset($datatableParameters['length'])) {
                //limit rows per page
                $limit = $datatableParameters['length'];
                
                if (isset($datatableParameters['start'])) {
                    $page = floor($datatableParameters['start'] / $datatableParameters['length']) + 1;
                }
            }
        }
        
        if (
                isset($datatableParameters['order']) && is_array($datatableParameters['order'])
        ) {
            foreach ($datatableParameters['order'] as $datatableOrder) {
                $columnIndex = $datatableOrder['column'];
                $orderDirection = strtoupper($datatableOrder['dir']);// u nasoj biblioteci je "ASC", a u pluginu datatables je "[dir] => asc"
                
                if (isset($columns[$columnIndex])) {
                    $orders[$columns[$columnIndex]] = $orderDirection;
                }
            }
        }
        if ( //search polje za user_name
                isset($datatableParameters['search']) && is_array($datatableParameters['search']) && isset($datatableParameters['search']['value'])
        ) {
            $filters['username_search'] = $datatableParameters['search']['value'];
        }
        
        $users = $cmsUsersTable->search(array(
            'filters' => $filters,
            'orders' => $orders,
            'limit' => $limit,
            'page' => $page
        ));
        //prikazuju  se samo 
        $usersFilteredCount = $cmsUsersTable->count($filters);
        $usersTotal = $cmsUsersTable->count();
        
        //prosledivanje parametara prezentac logici
        $this->view->users = $users; 
        $this->view->usersFilteredCount = $usersFilteredCount;
        $this->view->usersTotal = $usersTotal;
        $this->view->draw = $draw;
        $this->view->columns = $columns;
    }
    public function dashboardAction() {

        $cmsUsersDbTable = new Application_Model_DbTable_CmsUsers();

        $totalNumberOfUsers = $cmsUsersDbTable->count();
        $activeUsers = $cmsUsersDbTable->count(array(
            'status' => Application_Model_DbTable_CmsUsers::STATUS_ENABLED
        ));
        
        $this->view->totalNumberOfUsers = $totalNumberOfUsers;
        $this->view->activeUsers = $activeUsers;
        
    }
    
}
