<?php

class Admin_PhotoGalleriesController extends Zend_Controller_Action {

    public function indexAction() {

        $flashMessenger = $this->getHelper('FlashMessenger');

        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors')
        );

        //prikaz svih photoGallery-a
        $cmsPhotoGalleriesDbTable = new Application_Model_DbTable_CmsPhotoGalleries();

 	$photoGalleries = $cmsPhotoGalleriesDbTable->search(array(
			//'filters' => array(
			//	'status' => Application_Model_DbTable_CmsPhotoGalleries::STATUS_ENABLED
			//),
			'orders' => array(
				'order_number' => 'ASC'
			),
			//'limit' => 4,
			//'page' => 2
		));

        $this->view->photoGalleries = $photoGalleries; //prosledjivanje rezultata
        $this->view->systemMessages = $systemMessages;
    }

    public function addAction() {

        $request = $this->getRequest();
        $flashMessenger = $this->getHelper('FlashMessenger');

        $form = new Application_Form_Admin_PhotoGalleryAdd();

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
                    throw new Application_Model_Exception_InvalidInput('Invalid data was sent for new photoGallery.');
                }

                //get form data
                $formData = $form->getValues();

                //remove key photo_gallery_leading_photo  form data because there is no column photo_gallery_leading_photo in cms_photoGalleries 
                unset($formData['photo_gallery_leading_photo']);

                //Insertujemo novi zapis u tabelu
                $cmsPhotoGalleriesTable = new Application_Model_DbTable_CmsPhotoGalleries();

                // insert photoGallery returns ID of the new photoGallery
                $photoGalleryId = $cmsPhotoGalleriesTable->insertPhotoGallery($formData);


                if ($form->getElement('photo_gallery_leading_photo')->isUploaded()) {

                    // photo is uploaded 
                    $fileInfos = $form->getElement('photo_gallery_leading_photo')->getFileInfo('photo_gallery_leading_photo');
                    $fileInfo = $fileInfos['photo_gallery_leading_photo']; // ILI NA OVAJ NACIN $fileInfos =$_FILES['photo_gallery_leading_photo']

                    try {
                        // open uploaded photo in temporary directory
                        $photoGalleryPhoto = Intervention\Image\ImageManagerStatic::make($fileInfo['tmp_name']);

                        $photoGalleryPhoto->fit(360, 270);

                        //snimanje slike , mesto gde ce se slika sacuvati
                        $photoGalleryPhoto->save(PUBLIC_PATH . '/uploads/photo-galleries/' . $photoGalleryId . '.jpg');
                    } catch (Exception $ex) {

                        //set system message
                        $flashMessenger->addMessage('Photo Gallery has been saved but error occured during image processing', 'errors');
                        // $flashMessenger->addMessage('Or maybe somethign is wrong', 'errors');
                        //redirect to same or another page
                        $redirector = $this->getHelper('Redirector');
                        $redirector->setExit(true)
                                ->gotoRoute(array(
                                    'controller' => 'admin_photogalleries',
                                    'action' => 'edit',
                                    'id' => $photoGalleryId,
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
                $flashMessenger->addMessage('Photo Gallery has been saved', 'success');
                // $flashMessenger->addMessage('Or maybe somethign is wrong', 'errors');
                //redirect to same or another page
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_photogalleries',
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
            throw new Zend_Controller_Router_Exception('Invalid Photo Gallery id:' . $id, 404);
        }

        $cmsPhotoGalleriesTable = new Application_Model_DbTable_CmsPhotoGalleries();

        $photoGallery = $cmsPhotoGalleriesTable->getPhotoGalleryById($id);

        if (empty($photoGallery)) {

            throw new Zend_Controller_Router_Exception('No Photo Gallery is found with id:' . $id, 404);
        }

        $flashMessenger = $this->getHelper('FlashMessenger');



        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors'),
        );

        $form = new Application_Form_Admin_PhotoGalleryEdit();

        ////default form data
        $form->populate($photoGallery);

        if ($request->isPost() && $request->getPost('task') === 'update') {

            try {

                //check form is valid
                if (!$form->isValid($request->getPost())) {
                    throw new Application_Model_Exception_InvalidInput('Invalid data was sent for photoGallery.');
                }

                //get form data
                $formData = $form->getValues();

                //remove key photo_gallery_leading_photo  form data because there is no column photo_gallery_leading_photo in cms_photoGalleries 
                unset($formData['photo_gallery_leading_photo']);
                //Radimo update postojeceg zapisa u tabeli

                if ($form->getElement('photo_gallery_leading_photo')->isUploaded()) {

                    // photo is uploaded 
                    $fileInfos = $form->getElement('photo_gallery_leading_photo')->getFileInfo('photo_gallery_leading_photo');
                    $fileInfo = $fileInfos['photo_gallery_leading_photo']; // ILI NA OVAJ NACIN $fileInfos =$_FILES['photo_gallery_leading_photo']

                    try {
                        // open uploaded photo in temporary directory
                        $photoGalleryPhoto = Intervention\Image\ImageManagerStatic::make($fileInfo['tmp_name']);

                        $photoGalleryPhoto->fit(360, 270);

                        //snimanje slike , mesto gde ce se slika sacuvati
                        $photoGalleryPhoto->save(PUBLIC_PATH . '/uploads/photo-galleries/' . $photoGallery['id'] . '.jpg');
                    } catch (Exception $ex) {

                        throw new Application_Model_Exception_InvalidInput('Error occured during image processing: ' . $ex->getMessage());
                    }



                    //print_r($fileInfo);
                    //die();
                }


                //$cmsPhotoGalleriesTable->update($formData, 'id = ' . $photoGallery['id']);
                $cmsPhotoGalleriesTable->updatePhotoGallery($photoGallery['id'], $formData);

                //set system message
                $flashMessenger->addMessage('PhotoGallery has been updated', 'success');
                // $flashMessenger->addMessage('Or maybe somethign is wrong', 'errors');
                //redirect to same or another page
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_photogalleries',
                            'action' => 'index',
                                ), 'default', true);
            } catch (Application_Model_Exception_InvalidInput $ex) {
                $systemMessages['errors'][] = $ex->getMessage();
            }
        }

        $cmsPhotosDbTable = new Application_Model_DbTable_CmsPhotos();
        
        $photos = $cmsPhotosDbTable->search(array(
            'filters' => array(
            'photo_gallery_id' => $photoGallery['id']
            ),
            'orders' => array(
                'order_number' => 'ASC'
            )
        ));
        
        $this->view->systemMessages = $systemMessages;
        $this->view->form = $form;
        $this->view->photoGallery = $photoGallery;
         $this->view->photos = $photos;
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
                        'controller' => 'admin_photogalleries',
                        'action' => 'index',
                            ), 'default', true);
        }

        $flashMessenger = $this->getHelper('FlashMessenger');

        try {
            //(int) sve sto nije integer pretvara se u nulu
            //read $_POST['id']
            $id = (int) $request->getPost('id');
            if ($id <= 0) {

                throw new Application_Model_Exception_InvalidInput('Invalid photoGallery id:' . $id , 'errors');
            }
            $cmsPhotoGalleriesTable = new Application_Model_DbTable_CmsPhotoGalleries();

            $photoGallery = $cmsPhotoGalleriesTable->getPhotoGalleryById($id);

            if (empty($photoGallery)) {
                throw new Application_Model_Exception_InvalidInput('No photoGallery is found with id' . $id , 'errors');
            }


            $cmsPhotoGalleriesTable->deletePhotoGallery($id);

            $flashMessenger->addMessage('PhotoGallery ' . $photoGallery['title'] . ' ' . 'has been deleted', 'success');
            //redirect to same or another page
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_photogalleries',
                        'action' => 'index',
                            ), 'default', true);
        } catch (Application_Model_Exception_InvalidInput $ex) {

            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            //redirect to same or another page
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_photogalleries',
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
                        'controller' => 'admin_photogalleries',
                        'action' => 'index',
                            ), 'default', true);
        }

        $flashMessenger = $this->getHelper('FlashMessenger');


        try {
            //read $_POST['id']
            $id = (int) $request->getPost('id');

            if ($id <= 0) {

                throw new Application_Model_Exception_InvalidInput('Invalid photoGallery id: ' . $id , 'errors');
            }

            $cmsPhotoGalleriesTable = new Application_Model_DbTable_CmsPhotoGalleries();

            $photoGallery = $cmsPhotoGalleriesTable->getPhotoGalleryById($id);

            if (empty($photoGallery)) {
                throw new Application_Model_Exception_InvalidInput('No photoGallery is found with id: ' . $id , 'errors');
            }


            $cmsPhotoGalleriesTable->enablePhotoGallery($id);

            $flashMessenger->addMessage('PhotoGallery ' . $photoGallery['first_name'] . ' ' . $photoGallery['last_name'] . ' has been enabled', 'success');
            //redirect to same or another page
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_photogalleries',
                        'action' => 'index',
                            ), 'default', true);
        } catch (Application_Model_Exception_InvalidInput $ex) {

            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            //redirect to same or another page
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_photogalleries',
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
                        'controller' => 'admin_photogalleries',
                        'action' => 'index',
                            ), 'default', true);
        }

        $flashMessenger = $this->getHelper('FlashMessenger');


        try {
            //read $_POST['id']
            $id = (int) $request->getPost('id');

            if ($id <= 0) {

                throw new Application_Model_Exception_InvalidInput('Invalid photoGallery id: ' . $id , 'errors');
            }

            $cmsPhotoGalleriesTable = new Application_Model_DbTable_CmsPhotoGalleries();

            $photoGallery = $cmsPhotoGalleriesTable->getPhotoGalleryById($id);

            if (empty($photoGallery)) {
                throw new Application_Model_Exception_InvalidInput('No photoGallery is found with id' . $id , 'errors');
            }

            $cmsPhotoGalleriesTable->disablePhotoGallery($id);

            $flashMessenger->addMessage('PhotoGallery ' . $photoGallery['first_name'] . ' ' . $photoGallery['last_name'] . ' has been disabled', 'success');
            //redirect to same or another page
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_photogalleries',
                        'action' => 'index',
                            ), 'default', true);
        } catch (Application_Model_Exception_InvalidInput $ex) {

            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            //redirect to same or another page
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_photogalleries',
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
                        'controller' => 'admin_photogalleries',
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

            $cmsPhotoGalleriesTable = new Application_Model_DbTable_CmsPhotoGalleries();

            $cmsPhotoGalleriesTable->updateOrderOfPhotoGalleries($sortedIds);

            $flashMessenger->addMessage('Order is successfully saved', 'success');

            //redirect to same or another page
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_photogalleries',
                        'action' => 'index'
                            ), 'default', true);
        } catch (Application_Model_Exception_InvalidInput $ex) {

            $flashMessenger->addMessage($ex->getMessage(), 'errors');

            //redirect to same or another page
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_photogalleries',
                        'action' => 'index'
                            ), 'default', true);
        }
    }
    
     public function dashboardAction() {
        
        $cmsPhotoGalleriesDbTable = new Application_Model_DbTable_CmsPhotoGalleries();
		
		$totalNumberOfPhotoGalleries = $cmsPhotoGalleriesDbTable->count();
		$activePhotoGalleries = $cmsPhotoGalleriesDbTable->count(array(
			'status' => Application_Model_DbTable_CmsPhotoGalleries::STATUS_ENABLED
		));
                
        $this->view->totalNumberOfPhotoGalleries = $totalNumberOfPhotoGalleries;        
        $this->view->activePhotoGalleries = $activePhotoGalleries;
        

	}

}
