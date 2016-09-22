<?php

class Application_Form_Admin_IndexSlideAdd extends Zend_Form {

    public function init() {
        // title
        $title = new Zend_Form_Element_Text('title');
        //$title->addFilter(new Zend_Filter_StringTrim);
        //$title->addValidator(new Zend_Validate_StringLenght(array('min'=> 3, 'max'=> 255)));
        //
        $title->addFilter('StringTrim')
                ->addValidator('StringLength', false, array('min' => 3, 'max' => 255))
                ->setRequired(true);
        $this->addElement($title);
        // description
        $description = new Zend_Form_Element_Textarea('description');
        $description->addFilter('StringTrim')
                ->setRequired(false);
        $this->addElement($description);
        // link_type
        $linkType = new Zend_Form_Element_Select('link_type');
        $linkType->addMultiOption('Nolink', "No link is diplayed in slide")
                ->addMultiOption('SitemapPage', "Link to sitemap page")
                ->addMultiOption('InternalLink', "Link to internal url")
                ->addMultiOption('ExternalLink', "Link to external url")
                ->setRequired(true);
        $this->addElement($linkType);

        //link_label
        $linkLabel = new Zend_Form_Element_Text('link_label');
        $linkLabel->setRequired(false);
        $this->addElement($linkLabel);

        //sitemap_page_id
        $sitemapPageId = new Zend_Form_Element_Text('sitemap_page_id');
        $sitemapPageId->setRequired(false);
        $this->addElement($sitemapPageId);

        //internal_link_url
        $internalLinkUrl = new Zend_Form_Element_Text('internal_link_url');
        $internalLinkUrl->setRequired(false);
        $this->addElement($internalLinkUrl);

        //external_link_url
        $externalLinkUrl = new Zend_Form_Element_Text('external_link_url');
        $externalLinkUrl->setRequired(false);
        $this->addElement($externalLinkUrl);

        //indexSlide Photography
        $indexSlidePhoto = new Zend_Form_Element_File('index_slide_photo');

        $indexSlidePhoto->addValidator('Count', true, 1)
                ->addValidator('MimeType', true, array('image/jpeg', 'image/gif', 'image/png'))// true prekida izvrsenje naredbe, a false ne prekida (prilikom validacije) NA NIVOU ELEMENTA
                ->addValidator('ImageSize', false, array(
                    'minwidth' => 600,
                    'minheight' => 400,
                    'maxwidth' => 2000,
                    'maxheight' => 2000
                ))
                ->addValidator('Size', false, array(
                    'max' => '10MB'
                ))
                // disable move file to destionation when calling method getValues
                ->setValueDisabled(true)
                ->setRequired(false);

        $this->addElement($indexSlidePhoto);
    }

}
