<?php

class Application_Model_DbTable_CmsSitemapPages extends Zend_Db_Table_Abstract {

    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    protected $_name = 'cms_sitemap_pages';
    // dohvata se preko statickog getera
    protected static $sitemapPagesMap;

    /**
     * 
     * @return array with keys as sitemap page ids and values as assoc. arrays with keys url and type
     * 
     */
    public static function getSitemapPagesMap() {
        //lasy loading, ne povlace se resursi dok ne zatrebaju
        if (!self::$sitemapPagesMap) {

            $sitemapPagesMap = array();

            $cmsSitemapPagesDbTable = new self();
            // same as
            //         $cmsSitemapPagesDbTable = new Application_Model_DbTable_CmsSitemapPages()::

            $sitemapPages = $cmsSitemapPagesDbTable->search(array(
                'orders' => array(
                    'parent_id' => 'ASC',
                    'order_number' => 'ASC'
                )
            ));

            foreach ($sitemapPages as $sitemapPage) {
                $type = $sitemapPage['type'];
                $url = $sitemapPage['url_slug'];

                if (isset($sitemapPagesMap[$sitemapPage['parent_id']])) {

                    $url = $sitemapPagesMap[$sitemapPage['parent_id']]['url'] . '/' . $url;
                }

                $sitemapPagesMap[$sitemapPage['id']] = array(
                    'url' => $url,
                    'type' => $type
                );
            }

            self::$sitemapPagesMap = $sitemapPagesMap;
        }

        return self::$sitemapPagesMap;
    }

    /**
     * 
     * @param int $id
     * @return null|array Associative array with keys as cms_sitemap_pages table columns or NULL if not found
     */
    public function getSitemapPageById($id) {
        $select = $this->select();
        $select->where('id =?', $id);
        $row = $this->fetchRow($select);
        if ($row instanceof Zend_Db_Table_Row) {
            return $row->toArray();
        } else {
            //row not found
            return null;
        }
    }

    public function updateSitemapPage($id, $sitemapPage) {
        //izbegavamo da se promeni id usera, brise se iz niza ukoliko je setovan
        if (isset($sitemapPage['id'])) {
            unset($sitemapPage['id']);
        }
        $this->update($sitemapPage, 'id = ' . $id);
    }

    /**
     * 
     * @param array $sitemapPage
     * @return int The new ID of new sitemapPage (autoincrement)
     */
    public function insertSitemapPage($sitemapPage) {
        $select = $this->select();
        //Sort rows by order_number DESC and fets=ch row from the top
        //with biggest order number
        $select->where('parent_id = ?', $sitemapPage['parent_id'])
                ->order('order_number DESC');
        $sitemapPageWithBiggestOrderNumber = $this->fetchRow($select);
        if ($sitemapPageWithBiggestOrderNumber instanceof Zend_Db_Table_Row) {
            $sitemapPage['order_number'] = $sitemapPageWithBiggestOrderNumber['order_number'] + 1;
        } else {
            //table was empty, we are inserting first sitemapPage
            $sitemapPage['order_number'] = 1;
        }
        $id = $this->insert($sitemapPage);
        return $id;
    }

    /**
     * 
     * @param int $id ID of sitemapPage to delete
     */
    public function deleteSitemapPage($id) {
        //sitemapPage to delete
        $sitemapPage = $this->getSitemapPageById($id);
//        $this->update(array(
//            'order_number' => new Zend_Db_Expr('order_number -1')
//                ), 'order_number > ' . $sitemapPage['order_number'] . 'AND parent_id = ' . $sitemapPage['parent_id']);
//
        $childSitemapPages = $this->search(array(
            'filters' => array(
                'parent_id' => $id //ili $sitemapPage['id']
            )
        ));
        if (!empty($childSitemapPages)) {
            //delete children pages recursively
            foreach ($childSitemapPages as $key => $childSitemapPage) {
                // print_r($childSitemapPage['id']);
                //    die();
                $this->deleteSitemapPage($childSitemapPage['id']);
            }
            $this->delete('id = ' . $id);
        } else {
            $this->delete('id = ' . $id);
        }
    }

    public function updateSitemapOfOrder($sortedIds) {
        foreach ($sortedIds as $orderNumber => $id) {
            $this->update(array(
                'order_number' => $orderNumber + 1 // +1 because it starts from 0
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
                    case 'short_title':
                    case 'url_slug':
                    case 'title':
                    case 'parent_id':
                    case 'type':
                    case 'order_number':
                    case 'status':
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
                case 'short_title':
                case 'url_slug':
                case 'title':
                case 'parent_id':
                case 'type':
                case 'order_number':
                case 'status':
                    if (is_array($value)) {
                        $select->where($field . ' IN (?)', $value);
                    } else {
                        $select->where($field . ' = ?', $value);
                    }
                    break;
                case 'title_search':
                    $select->where('title LIKE ?', '%' . $value . '%');
                    break;
                case 'short_title_search':
                    $select->where('short_title LIKE ?', '%' . $value . '%');
                    break;
                case 'description_search':
                    $select->where('description_search LIKE ?', '%' . $value . '%');
                    break;
                case 'body_search':
                    $select->where('body_search LIKE ?', '%' . $value . '%');
                    break;
                case 'id_exclude':
                    if (is_array($value)) {
                        $select->where('id NOT IN (?)', $value);
                    } else {
                        $select->where('id != ?', $value);
                    }
                    break;
            }
        }
    }

    /*     * @param The id of sitemap page
     * @ return array Sitemap page rows in path
     */

    public function getSitemapPageBreadcrumbs($id) {
        $sitemapPagesBreadcrumbs = array();
        while ($id > 0) {
            $sitemapPageInPath = $this->getSitemapPageById($id);
            if ($sitemapPageInPath) {
                $id = $sitemapPageInPath['parent_id'];
                //add current page at the beggining of breadcrumbs array
                array_unshift($sitemapPagesBreadcrumbs, $sitemapPageInPath);
            } else {
                $id = 0;
            }
        }
        return $sitemapPagesBreadcrumbs;
    }

    /**
     * 
     * @param nt $id ID of member to enable
     */
    public function disableSitemap($id) {
        $this->update(array(
            'status' => self::STATUS_DISABLED
                ), 'id = ' . $id);
    }

    /**
     * 
     * @param nt $id ID of sitemapPage to enable
     */
    public function enableSitemapPage($id) {
        $this->update(array(
            'status' => self::STATUS_ENABLED
                ), 'id = ' . $id);
    }

}
