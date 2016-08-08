<?php
class Application_Model_DbTable_CmsUsers extends Zend_Db_Table_Abstract
    {
    const DEFAULT_PASSWORD = 'cubesphp';
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    
    protected $_name = 'cms_users';//podesavanje imena tabele
    
    /**
     * @param iny $id
     * return null|array Associative array with keys as cms_users table columns or NULL if not found
     */
    public function getUserById($id){
        
        
        $select = $this->select();
        $select->where('id = ?', $id);
        
        $row =  $this->fetchRow($select);//find vraca niz objekata tj vise redova, ne samo jedan
        
        if($row instanceof Zend_Db_Table_Row){
            
            return $row->toArray();
        }else {
            //row is not found
            return NULL;
        }
    }

    
    /**
     * 
     * @param type $user
     *  @param array $user Associative array with keys at column names and values as column new values
     * @return int ID of new user
     */
    public function insertUser($user) {
        
        //set default password for new user
        $user['password'] = md5(self::DEFAULT_PASSWORD);
        
        return  $this->insert($user);

    }
    
        /**
     * @param type $id
     * @param array $user Associative array with keys at column names and values as column new values
     */
    public function updateUser($id, $user){
        if(isset($user['id'])){
            //forbid changing of user id (izbegavamo da se promeni id usera, brise se iz niza ukoliko je setovan)
            unset($user['id']);
        }
        
        
        $this->update($user, 'id = ' . $id);
        
        
    }
    /**
     * @param type int $id
     * @param string $newPassword Plain password, noy hashed
     */
    public function changeUserPassword($id, $newPassword)
    {
        //update "password" column, set md5 value of new password, for user with id = $id
        $this->update(array('password'=> md5($newPassword)), 'id = ' . $id);
    }
    
    public function resetUserPassword($id) {
        $this->update(array(
            'password' => md5(self::DEFAULT_PASSWORD),
                ), 'id = ' . $id);
    }
    
      /**
     * 
     * @param int $id Id of the user to delete
     */
    public function deleteUser($id){

        $user = $this->getUserById($id);

        
        $this->delete('id = ' . $id);
    }
    
    
    /**
     * 
     * @param int $id ID of user to disable
     */
    public function disableUser($id) {
        $this->update(array(
            'status' => self::STATUS_DISABLED
                ), 'id = ' . $id);
    }
    /**
     * 
     * @param int $id ID of user to enable
     */
    public function enableUser($id) {
        $this->update(array(
            'status' => self::STATUS_ENABLED
                ), 'id = ' . $id);
    }
    

    
    /**
     * Array $parameters is keeping searched parameters 
     * Array $parameters must be in following  format
     *      array(
     *          'filters' => array(
     *                      'status' => 1,
     *                      'id' =>array(3,8,11)
     *          ),
     *          'orders' => array(
     *              'username' => 'ASC' // key is column , if value is "ASC" then ORDER BY ASC, key is column
     *              'first_name => 'DESC', // key is column , if value is "DESC" then ORDER BY DESC
     *              ),
     *          'limit' => 50, // limit result set to 50 rows
     *           'page' => 3, // start from page 3, if no limit is set , page is ignored 
     *      )
     * @param array $parameters Associative array with keys "filters", "orders", "limit" and "page"
     */
    public function search(array $parameters = array()) {
        
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
                case 'username':
                case 'first_name':
                case 'last_name':
                case 'email':
                case 'status':
                    
                    if ($orderDirection === 'DESC') {
                        
                        $select->order($field . ' ' . 'DESC');
                    }else{
                       $select->order($field); 
                    }
                break;
            }
        }
    }
                
        if (isset($parameters['limit'])) {
            
            if (isset($parameters['page'])) {
                // page is se to limit by page
                $select->limitPage($parameters['page'], $parameters['page']);
            }else{
                // page is not set, just do regular limit
                $select->limit($parameters['limit']);
            }

        }
        // provera querija koji se izvrsava - debug
        //die($select->assemble());
        
        return $this->fetchAll($select)->toArray();
        
    }
    /**
     * 
     * @param array $filters See function search  $parameters['filters']
     * @return int Count rows that match $filters
     */
    public function count(array $filters = array()) {
        
        $select = $this->select();

        $this->processFilters($filters, $select);
        
        //reset previously set columns for result
        $select->reset('columns');
        //set one column field to fetch and it is COUNT function
        $select->from($this->_name, 'COUNT(*) as total');
        
        $row = $this->fetchRow($select);// radi selekt upita, i tom redu bice samo jedna kolona total
        
        return $row['total'];
        
        
    }
    /**
     * Fill $select object with WHERE conditions
     * @param array $filters
     * @param Zend_Db_Select $select
     */
    protected function processFilters(array $filters, Zend_Db_Select $select) { 
        // $select obect will be modified outside this function
        // object are always passed by reference
        foreach ($filters as $field => $value) {
  
            switch ($field) {
                
                case 'id':
                case 'username':
                case 'first_name':
                case 'last_name':
                case 'email':
                case 'status':

                    if (is_array($value)) {
                        $select->where($field . ' IN (?)', $value); 
                    }else {
                         $select->where($field . ' = ?', $value);
                    }
                    break;
                    
                case 'password':
                    
                    if (is_array($value)) {
                        
                        array_walk($value, function(&$element, $key) {
                            //apply md5 on each element in $value array 
                            $element = md5($element);
                            
                        });
                        $select->where($field . ' IN (?)', $value); 
                    }else {
                         $select->where($field . ' = ?', md5($value));
                    }
                    break;
                    
                case 'username_search':
                    $select->where('username LIKE ?', '%' . $value . '%');
                    break;
                
                case 'first_name_search':
                    $select->where('first_name LIKE ?', '%' . $value . '%');
                    break;
                
                case 'last_name_search':
                    $select->where('last_name LIKE ?', '%' . $value . '%');
                    break;
                
                case 'email_search':  
                    $select->where('email LIKE ?', '%' . $value . '%');
                    break;
                
                case 'id_exclude':  // pod ovim kljucem dobijamo zapise koji nemaju trazeni id
                    if (is_array($value)) {
                        $select->where('id NOT in (?)', $value);
                    }else{
                        $select->where('id != ?', $value);
                    }
                    break;
                                    
                case 'username_exclude':  
                    if (is_array($value)) {
                        $select->where('username NOT in (?)', $value);
                    }else{
                        $select->where('username != ?', $value);
                    }
                    break;
                }
                
            }
    }
    
//    /**
//     * 
//     * @param array $users
//     * @return int number of active services
//     */
//      public function activeUsers($users) {
//
//        $activeUsers = 0;
//        foreach ($users as $user) {
//            if ($user['status'] == self::STATUS_ENABLED) {
//                $activeUsers ++;
//            }
//        }
//        return $activeUsers;
//    }
//    
//    /**
//     * 
//     * @param array $users
//     * @return int total number of Users
//     */
//    public function totalUsers($users) {
//        $totalNumberOfUsers = 0;
//
//        foreach ($users as $user) {
//            $totalNumberOfUsers ++;
//        }
//
//        return $totalNumberOfUsers;
//    }

}