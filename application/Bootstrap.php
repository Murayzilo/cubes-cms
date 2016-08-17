<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {
    //bitno je da pocinje _init
    protected function _initRouter() {
        //ensure if database is configured
        $this->bootstrap('db'); 
        //ogranicvanje broja stranica
        $sitemapPageTypes = array(
            'StaticPage' => array(
                'title' => 'Static Page',
                'subtypes' => array(
                    // 0 means unlimited number
                    'StaticPage' => 0
                )
            ),
            'AboutUsPage' => array(
                'title' => 'About Us Page',
                'subtypes' => array( // pod ovom stranicom ne mozemo dodati bilo sta prazan niz array()
                )
            ),
            'ServicesPage' => array(
                'title' => 'Services Page',
                'subtypes' => array(

                )
            ),
            'ContactPage' => array(
                 'title' => 'Contact Page',
                'subtypes' => array(
                )
            )
        );
        
        // tipovi stranica u kojima definisemo sta sve moze da se nadje u rutu sajta i koliko puta
        $rootSitemapPageTypes = array(
            'StaticPage' => 0, // neogranicen broj strana; parent_id = 0
            'AboutUsPage' => 1,
            'ServicesPage' => 1,
            'ContactPage' => 1
        );
        // popunjava se u bootstrap fajlu
        // registar vrednosti koje koristimo kroz aplikaciju
        Zend_Registry::set('sitemapPageTypes', $sitemapPageTypes);
        Zend_Registry::set('rootSitemapPageTypes', $rootSitemapPageTypes);

    
        //ruter dobijamo iz Zend_Controller_Front on poziva sve ostale controllere
        $router = Zend_Controller_Front::getInstance()->getRouter();
        // i ima metodu
        $router instanceof Zend_Controller_Router_Rewrite;
        
//        //svaka ruta mora da stoji pod kljucem
//        $router->addRoute('about-us-route', new Zend_Controller_Router_Route_Static(
//                'about-us', 
//            array(
//            'controller' => 'aboutus',
//            'action' => 'index'
//                )
//                
//                //poslednja dodata ruta ima najveci prioritet
//        ))->addRoute('member-route', new Zend_Controller_Router_Route(
//                //posto id pocinje sa dve tacke tu se menja
//                //id naziv parametra koji hvatamo iz URL-a
//                'about-us/member/:id/:member_slug', 
//            array(
//            'controller' => 'aboutus',
//            'action' => 'member',
//            'member_slug' => ''
//                )
        $router->addRoute('contact-us-route', new Zend_Controller_Router_Route_Static(
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
        
        $sitemapPagesMap = Application_Model_DbTable_CmsSitemapPages::getSitemapPagesMap();
        //print_r($sitemapPagesMap);die();
        foreach ($sitemapPagesMap as $sitemapPageId => $sitemapPageMap) {
            if ($sitemapPageMap['type'] == 'StaticPage') {

            $router->addRoute('static-page-route-' . $sitemapPageId, new Zend_Controller_Router_Route_Static(
                $sitemapPageMap['url'], 
            array(
            'controller' => 'staticpage',
            'action' => 'index',
            'sitemap_page_id' => $sitemapPageId
                )
            ));
        }
        
        if ($sitemapPageMap['type'] == 'AboutUsPage') {

            $router->addRoute('static-page-route-' . $sitemapPageId, new Zend_Controller_Router_Route_Static(
                $sitemapPageMap['url'], 
            array(
            'controller' => 'aboutus',
            'action' => 'index',
            'sitemap_page_id' => $sitemapPageId
                )
            ));
                $router->addRoute('member-route', new Zend_Controller_Router_Route(
                //posto id pocinje sa dve tacke tu se menja
                //id naziv parametra koji hvatamo iz URL-a
                $sitemapPageMap['url'] . '/member/:id/:member_slug', 
            array(
            'controller' => 'aboutus',
            'action' => 'member',
            'member_slug' => ''
                )
            ));
        }
        if ($sitemapPageMap['type'] == 'ContactPage') {

            $router->addRoute('static-page-route-' . $sitemapPageId, new Zend_Controller_Router_Route_Static(
                $sitemapPageMap['url'], 
            array(
            'controller' => 'contact',
            'action' => 'index',
            'sitemap_page_id' => $sitemapPageId
                )
            ));
        }
        
        if ($sitemapPageMap['type'] == 'ServicesPage') {

            $router->addRoute('static-page-route-' . $sitemapPageId, new Zend_Controller_Router_Route_Static(
                $sitemapPageMap['url'], 
            array(
            'controller' => 'services',
            'action' => 'index',
            'sitemap_page_id' => $sitemapPageId
                )
            ));
        }
        
    }
  }

}
