<?php
class Application_Model_DbTable_CmsPhotos extends Zend_Db_Table_Abstract {
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    protected $_name = 'cms_photos';
    /**
     * 
     * @param int $id
     * @return null|array Associative array with keys as cms_photos table columns or NULL if not found
     */
    public function getPhotoById($id) {
        $select = $this->select();
        $select->where('id =?', $id);
        $row = $this->fetchRow($select);
        if ($row instanceof Zend_Db_Table_Row) {
            return $row->toArray();
        } else {
            //row is not found
            return null;
        }
    }
    public function updatePhoto($id, $photo) {
        //izbegavamo da se promeni id usera, brise se iz niza ukoliko je setovan
        if (isset($photo['id'])) {
            unset($photo['id']);
        }
        $this->update($photo, 'id = ' . $id);
    }
    /**
     * 
     * @param array $photo Associative array with keys as column names and values as coumn new values
     * @return int The new ID of new photo (autoincrement)
     */
    public function insertPhoto($photo) {
        $select = $this->select();
        //Sort rows by order_number DESC and fets=ch row from the top
        //with biggest order number
        $select->where('photo_gallery_id = ?', $photo['photo_gallery_id'])
                ->order('order_number DESC');
        $photoWithBiggestOrderNumber = $this->fetchRow($select);
        if ($photoWithBiggestOrderNumber instanceof Zend_Db_Table_Row) {
            $photo['order_number'] = $photoWithBiggestOrderNumber['order_number'] + 1;
        } else {
            //table was empty, we are inserting first photo
            $photo['order_number'] = 1;
        }
        $id = $this->insert($photo);
        return $id;
    }
    /**
     * 
     * @param int $id ID of photo to delete
     */
    public function deletePhoto($id) {
        $photoPhotoFilePath = PUBLIC_PATH . '/uploads/photo-galleries/photos/' . $id . '.jpg';
        if (is_file($photoPhotoFilePath)) {
            unlink($photoPhotoFilePath);
        }
        //photo to delete
        $photo = $this->getPhotoById($id);
        $this->update(array(
            'order_number' => new Zend_Db_Expr('order_number -1')
                ), 'order_number > ' . $photo['order_number']. ' AND photo_gallery_id = ' . $photo['photo_gallery_id']
                );
        $this->delete('id = ' . $id);
    }
    /**
     * 
     * @param nt $id ID of photo to enable
     */
    public function disablePhoto($id) {
        $this->update(array(
            'status' => self::STATUS_DISABLED
                ), 'id = ' . $id);
    }
    /**
     * 
     * @param nt $id ID of photo to enable
     */
    public function enablePhoto($id) {
        $this->update(array(
            'status' => self::STATUS_ENABLED
                ), 'id = ' . $id);
    }
    public function updatePhotoOfOrder($sortedIds) {
        foreach ($sortedIds as $orderNumber => $id) {
            $this->update(array(
                'order_number' => $orderNumber + 1 // +1 because order_number starts from 1, not from 0
                    ), 'id = ' . $id);
        }
    }
    
   
     /**
     * Array $parameters is keeping search parameters.
     * Array $parameters  must be in following format:
     *      array(
     *          'filters' => array(
     *              'status' => 1,
     *                'id' => (1, 3, 8)
     *           ),
     *           'orders' => array(
     *                'username' => 'ASC' //key is column, asc-> ORDER BY ASC
     *                 'first_name' => 'DESC' // key is column, desc-> ORDER BY DESC
     *          ),
     *          'limit' => 50, //limit result set to 50 rows
     *          'page' => 3 // start from page 3. If no limit is set, page ignored
     * )
     * @param array $parameters Asoc array with keys "filters", "orders", "limit" and "page"
     */
    public function search(array $parameters = array()) {//ovo znaci da ne mora parametar da se prosledi jer je navedeno da je array
        $select = $this->select();
        if (isset($parameters['filters'])) {
            $filters = $parameters['filters'];
            $this->processFilters($filters, $select);
        }
        if (isset($parameters['orders'])) {
            $orders = $parameters['orders'];
            foreach ($orders as $field => $orderDirection) {
                switch ($field) {
                    case 'id': 
                    case 'title':
                    case 'description':
                    case 'status':
                    case 'order_number':
                        if ($orderDirection === 'DESC') {
                            $select->order($field . ' DESC');
                        } else {
                            $select->order($field);
                        }
                        break;
                }
            }
        }
        //ovde se ispituje i uslov za page
        if (isset($parameters['limit'])) {
            if (isset($parameters['page'])) {
                // page is set do limit by page
                $select->limitPage($parameters['page'], $parameters['limit']);
            } else {
                //[age is not set, just do regular limit 
                $select->limit($parameters['limit']);
            }
        }
            //da proverimo koji nam se query izvrsava
            //die($select->assemble());
        //ovde dobijamo niz sa upitom
        return $this->fetchAll($select)->toArray();
    }
    /**
     * 
     * @param array $filters See function search $parameters ['filters']
     * return int Count rows that match $filters
     */
    public function count(array $filters = array()) {
        $select = $this->select();
        $this->processFilters($filters, $select);
        // reset previously set columns for 
        $select->reset('columns');
        // set one column/field to fetch and it is COUNT function
        $select->from($this->_name, 'COUNT(*) as total');
        $row = $this->fetchRow($select);
        return $row['total'];
    }
    /**
     * Fill $select object with WHERE conditions
     * @param array $filters
     * @param Zend_Db_Select $select
     */
    protected function processFilters(array $filters, Zend_Db_Select $select) {
        // $select object will be modified outside this function
        // objects are always passed by reference
        foreach ($filters as $field => $value) {
            switch ($field) {
                case 'id':
                case 'title':
                case 'description':
                case 'status':
                case 'photo_gallery_id':
                    if (is_array($value)) {
                        $select->where($field . ' IN (?)', $value);
                    } else {
                        $select->where($field . ' = ?', $value);
                    }
                    break;
               
                case 'title_search':
                    $select->where('title LIKE ?', '%' . $value . '%');
                    break;
                case 'description_search':
                    $select->where('description LIKE ?', '%' . $value . '%');
                    break;
                case 'id_exclude':
                    
                    if (is_array($value)){
                        $select->where('id NOT IN (?)', $value);
                    }else {
                        $select->where('id != ?', $value);
                    }
                    break;
                    
                
            }
        }
    }
    
   
}