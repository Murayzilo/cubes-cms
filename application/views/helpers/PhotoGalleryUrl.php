<?php
class Zend_View_Helper_PhotoGalleryUrl extends Zend_View_Helper_Abstract
{
    protected $urlSlugFilter;
	
	protected function getUrlSlugFilter() {
		
		/*** Lazy Loading ***/
		
		if (!$this->urlSlugFilter) {
			$this->urlSlugFilter = new Application_Model_Filter_UrlSlug();
		}
		
		return $this->urlSlugFilter;
	}
    public function photoGalleryUrl($photoGallery){
        $slug = new Application_Model_Filter_UrlSlug();
       
        return $this->view->url(array(
            'id'=>$photoGallery['id'],
            'photo_gallery_slug'=>$slug->filter($photoGallery['title'])
            
        ), 'photo-gallery-route', true);
       
    }
  
}