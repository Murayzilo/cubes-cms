<?php
class Application_Model_DbTable_CmsMembers extends Zend_Db_Table_Abstract {
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    protected $_name = 'cms_members';
    /**
     * @param iny $id
     * return null|array Associative array with keys as cms_members table columns or NULL if not found
     */
    public function getMemberById($id) {
        $select = $this->select();
        $select->where('id = ?', $id);
        $row = $this->fetchRow($select); //find vraca niz objekata tj vise redova, ne samo jedan
        if ($row instanceof Zend_Db_Table_Row) {
            return $row->toArray();
        } else {
            //row is not found
            return NULL;
        }
    }
    /**
     * @param type  int $id
     * @param array $member Associative array with keys at column names and values as column new values
     */
    public function updateMember($id, $member) {
        if (isset($member['id'])) {
            //forbid changing of user id
            unset($member['id']);
        }
        $this->update($member, 'id = ' . $id);
    }
    /**
     * 
     * @param array $member Associative array with keys at column names and values as column new values
     * @return int The ID of new member (autoincrement)
     */
    public function insertMember($member) {
       //fetch order number of new member
        

          //trazimo query builder
          $select = $this->select();
          //Sort rows by order_number DESCENDING and fetch one row from the top
          $select->order('order_number DESC');
            //      ->limit(1); ILI na drugi nacin
           $this->fetchRow($select);
           
           $memberWithBiggestOrderNumber = $this->fetchRow($select);
          
           if ($memberWithBiggestOrderNumber instanceof Zend_Db_Table_Row) {        // implementira interface array accessable, mozemo da pristupamo kao u nizu   
               $member['order_number'] = $memberWithBiggestOrderNumber['order_number'] + 1;
        } else {
          //table was empty, we are insertinf first member
          $member['order_number'] = 1;
        }
        
        $id = $this->insert($member);
        
        return $id;
    }
    
      public function deleteMember($id) {
              
          $member = $this->getMemberById($id);
          
          $this->update(array(
             'order_number' => new Zend_Db_Expr('order_number - 1')
          ), 
                  'order_number >'  . $member['order_number']);
           
//        $select = $this->select();
//        $select->where('id =?', $id);
//        $row = $this->fetchRow($select);
//        if ($row instanceof Zend_Db_Table_Row) {
//            $row->toArray();
//        } else {
//            return null;
//        }
//        //dobijamo order number od ID-ja koji se brise
//        $orderDeleted = $row['order_number'];
//        // print_r($orderDeleted);
//        $select = $this->select();
//        $select->where('order_number > ?', $orderDeleted);
//        //dobijamo sve kolone koje zadovoljavaju uslov
//        $rows = $this->fetchAll($select);
//        // print_r($rows);
//        if (empty($rows)) {
//            return FALSE;
//        } else {
//            $rowsData = $rows->toArray();
//        }
//
//        foreach ($rowsData as $row) {
//            $this->update(array(
//                'order_number' => $row['order_number'] - 1
//                    ), 'id= ' . $row['id']);
//        }
          
       $this->delete('id = ' . $id);

    }
    
      public function disableMember($id) {

       $this->update(array(
           'status' => self::STATUS_DISABLED
       ), 'id=' . $id);

    }
    /**
     * 
     * @param int $id ID of member to enable
     */
      public function enableMember($id) {

       $this->update(array(
           'status' => self::STATUS_ENABLED
       ), 'id= ' . $id);

    }
    
    	public function updateOrderOfMembers($sortedIds) {
		
		foreach ($sortedIds as $orderNumber => $id) {
			$this->update(array(
                            'order_number' => $orderNumber + 1 // +1  because order_number starts from 1, not from 0
			), 'id = ' . $id);
		}
	}
    
}
