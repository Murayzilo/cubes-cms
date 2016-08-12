<?php
class Application_Form_Admin_SitemapPageEdit extends Zend_Form
{
    
    
    protected $parentId;
    protected $sitemapPageId;
    protected $parentType;
    
    public function __construct($sitemapPageId, $parentId, $parentType, $options = null) {
        
        $this->sitemapPageId = $sitemapPageId;
        $this->parentId = $parentId;
        $this->parentType = $parentType;
        
        parent::__construct($options);
    }
        public function init(){
            
       $sitemapPageTypes = Zend_Registry::get('sitemapPageTypes'); //registar je dostupan bilo gde, a puni se u bootstrap.php
        $rootSitemapPageTypes = Zend_Registry::get('rootSitemapPageTypes'); // 

        if ($this->parentId == 0) {
            $parentSubtypes = $rootSitemapPageTypes;
        } else {
            //$this->parentType; "Static Page"
            $parentSubtypes = $sitemapPageTypes[$this->parentType]['subtypes'];
        }


        $cmsSitemapPagesDbtable = new Application_Model_DbTable_CmsSitemapPages();
        
        $parentSubtypesCount = $cmsSitemapPagesDbtable->countBYTypes(array(
            'parent_id' => $this->parentId,
            'id_exclude' => $this->sitemapPageId
        ));

        //zend_form-element-select/multiselect/multicheckbox
        $type = new Zend_Form_Element_Select('type');

        $type->addMultiOption('', '-- Select Sitemap Page Type --')
//                ->addMultiOptions(array(
//                    'StaticPage' => 'Static Page',
//                    'AboutUsPage' => 'About Us Page',
//                    'ContactPage' => 'Contact Page'
//                    
//                ))
                ->setRequired(true);
 

        foreach ($parentSubtypes as $sitemapPageType => $sitemapPageTypeMax) {

            $sitemapPageTypeProperties = $sitemapPageTypes[$sitemapPageType];

            $totalExistingSitemapPagesOfType = isset($parentSubtypesCount[$sitemapPageType]) ? $parentSubtypesCount[$sitemapPageType] : 0;

            if ($sitemapPageTypeMax == 0 || $sitemapPageTypeMax > $totalExistingSitemapPagesOfType) {
                $type->addMultiOption($sitemapPageType, $sitemapPageTypeProperties['title']);
            }
        }


        $this->addElement($type);
        
        $urlSlug = new Zend_Form_Element_Text('url_slug');
        $urlSlug->addFilter('StringTrim')
                ->addFilter(new Application_Model_Filter_UrlSlug()) // custom filter 
                ->addValidator('StringLength', false, array('min' => 2, 'max' => 255))
                ->addValidator(new Zend_Validate_Db_NoRecordExists(array(
                 'table' => 'cms_sitemap_pages',
                 'field' => 'url_slug',
                 'exclude' => 'parent_id = ' . $this->parentId . ' AND id != ' . $this->sitemapPageId
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