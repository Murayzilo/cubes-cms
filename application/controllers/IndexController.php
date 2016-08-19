<?php

class IndexController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {   // index slides 
        $cmsSlidesDBTable = new Application_Model_DbTable_CmsIndexSlides ();
       
       $indexSlides = $cmsSlidesDBTable->search(array(
           'filters' => array(
               'status' => Application_Model_DbTable_CmsIndexSlides::STATUS_ENABLED
           ),
           'orders' => array(
                'order_number' => 'ASC',
            )
       ));
       
        //services
        $cmsServicesDbTable = new Application_Model_DbTable_CmsServices();

        $services = $cmsServicesDbTable->search(array(
           'filters' => array(
               'status' => Application_Model_DbTable_CmsServices::STATUS_ENABLED
           ),
           'orders' => array(
                'order_number' => 'ASC',
            ),
            'limit'=> 4
       ));
       
        //photoGalleries 
      
        $photoGalleriesSitemapPages = $cmsSitemapPagesDbTable->search(array(
			'filters' => array(
				'status' => Application_Model_DbTable_CmsSitemapPages::STATUS_ENABLED,
				'type' => 'PhotoGalleriesPage'
			),
			'limit' => 1
		));
		$photoGalleriesSitemapPage = !empty($photoGalleriesSitemapPages) ? $photoGalleriesSitemapPages[0] : null;
		
		$cmsPhotoGalleriesDbTable = new Application_Model_DbTable_CmsPhotoGalleries();
		$photoGalleries = $cmsPhotoGalleriesDbTable->search(array(
			'filters' => array(
				'status' => Application_Model_DbTable_CmsServices::STATUS_ENABLED
			),
			'orders' => array(
				'order_number' => 'ASC'
			),
			'limit' => 3
		));
        
     
        // sitemapPage    
        $cmsSitemapPagesDbTable = new Application_Model_DbTable_CmsSitemapPages();
        $servicesSitemapPages = $cmsSitemapPagesDbTable->search(array(
			'filters' => array(
				'status' => Application_Model_DbTable_CmsSitemapPages::STATUS_ENABLED,
				'type' => 'ServicesPage'
			),
			'limit' => 1
		));
        
            $this->view->indexSlides = $indexSlides;
            $this->view->servicesSitemapPages = $servicesSitemapPages;
            $this->view->services = $services;
            $this->view->photoGalleriesSitemapPage = $photoGalleriesSitemapPage;
            $this->view->photoGalleries = $photoGalleries;
    }
    public function testAction()
    {
        
    }

}

