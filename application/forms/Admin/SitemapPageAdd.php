<?php
class Application_Form_Admin_SitemapPageAdd extends Zend_Form
{
    
    
    protected $parentId;
    
    public function __construct($parentId, $options = null) {
        
        $this->parentId = $parentId;
        
        parent::__construct($options);
    }
        public function init(){
        
        //type
        //url_slug
        //short_title
        //description
        //body
        
        //zend_form-element-select/multiselect/multicheckbox
        $type = new Zend_Form_Element_Select('type');
            
        $type->addMultiOption('', '-- Select Sitemap Page Type --')
                ->addMultiOptions(array(
                    'StaticPage' => 'Static Page',
                    'AboutUsPage' => 'About Us Page',
                    'ContactPage' => 'Contact Page'
                    
                ))->setRequired(true);
        $this->addElement($type);
        
        
        $urlSlug = new Zend_Form_Element_Text('url_slug');
        $urlSlug->addFilter('StringTrim')
                ->addFilter(new Application_Model_Filter_UrlSlug()) //posto je ovo nas custom filter poziva se na ovaj nacin
                ->addValidator('StringLength', false, array('min' => 2, 'max' => 255))
                ->addValidator(new Zend_Validate_Db_NoRecordExists(array(
                 'table' => 'cms_sitemap_pages',
                 'field' => 'url_slug',
                 'exclude' => 'parent_id = ' . $this->parentId
                    )))
                ->setRequired(true);
        $this->addElement($urlSlug);
        $shortTitle= new Zend_Form_Element_Text('short_title');
        $shortTitle->addFilter('StringTrim')
                ->addValidator('StringLength', false, array('min' => 2, 'max' => 255))
                ->setRequired(true);
        $this->addElement($shortTitle);
        $title = new Zend_Form_Element_Text('title');
        
        $title->addFilter('StringTrim')
                ->addValidator('StringLength', false, array('min' => 2, 'max' => 500))
                ->setRequired(true);
        $this->addElement($title);
        
        
        $description= new Zend_Form_Element_Textarea('description');
        $description->addFilter('StringTrim')
                ->setRequired(false);
        $this->addElement($description);
        
        
        $body= new Zend_Form_Element_Textarea('body');
        $body->setRequired(true);
        $this->addElement($body);
    }
}
