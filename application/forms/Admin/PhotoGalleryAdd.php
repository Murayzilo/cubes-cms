<?php

class Application_Form_Admin_PhotoGalleryAdd extends Zend_Form
{
    public function init() {
       
        $title = new Zend_Form_Element_Text('title');
        //$firstName->addFilter(new Zend_Filter_StringTrim);
        //$firstName->addValidator(new Zend_Validate_StringLenght(array('min'=> 3, 'max'=> 255)));
        //
  
        //title
        $title->addFilter('StringTrim')
                ->addValidator('StringLength', false, array('min'=> 3, 'max'=> 255))
                ->setRequired(true);
        
        $this->addElement($title);
        
        //description
         $description = new Zend_Form_Element_Textarea('description');
        $description->addFilter('StringTrim')
                ->addValidator('StringLength', false, array('min'=> 3, 'max'=> 255))
                ->setRequired(false);
        
        $this->addElement($description);

        
        //photo_gallery_leading_photo Photography
        $photoGalleryLeadingPhoto = new Zend_Form_Element_File('photo_gallery_leading_photo');
        
        $photoGalleryLeadingPhoto->addValidator('Count', true, 1)
                    ->addValidator('MimeType', true, array('image/jpeg', 'image/gif', 'image/png'))// true prekida izvrsenje naredbe, a false ne prekida (prilikom validacije) NA NIVOU ELEMENTA
                    ->addValidator('ImageSize', false, array(
                        'minwidth' => 360,
                        'minheight' => 270,
                        'maxwidth' => 2000,
                        'maxheight' => 2000
                    ))
                     ->addValidator('Size', false , array(
                         'max' => '10MB'
                         ))
                    // disable move file to destination when calling method getValues
                     ->setValueDisabled(true)
                     ->setRequired(true); 
        
        $this->addElement($photoGalleryLeadingPhoto);
    }

    
    
}
