<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {
    //bitno je da pocinje _init
    protected function _initRouter() {
        //ruter dobijamo iz Zend_Controller_Front on poziva sve ostale controllere
        $router = Zend_Controller_Front::getInstance()->getRouter();
        // i ima metodu
        $router instanceof Zend_Controller_Router_Rewrite;
        
        //svaka ruta mora da stoji pod kljucem
        $router->addRoute('about-us-route', new Zend_Controller_Router_Route_Static(
                'about-us', 
            array(
            'controller' => 'aboutus',
            'action' => 'index'
                )
                
                //poslednja dodata ruta ima najveci prioritet
        ))->addRoute('member-route', new Zend_Controller_Router_Route(
                //posto id pocinje sa dve tacke tu se menja
                //id naziv parametra koji hvatamo iz URL-a
                'about-us/member/:id/:member_slug', 
            array(
            'controller' => 'aboutus',
            'action' => 'member',
            'member_slug' => ''
                )
        ))->addRoute('contact-us-route', new Zend_Controller_Router_Route_Static(
			'contact-us',
			array(
				'controller' => 'contact',
				'action' => 'index'
			)
                        ))->addRoute('ask-member-route', new Zend_Controller_Router_Route(
			'ask-member/:id/:member_slug',
			array(
				'controller' => 'contact',
				'action' => 'askmember',
				'member_slug' => ''
			)));
	}
}
