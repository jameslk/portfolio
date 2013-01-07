<?php

/**
 * This class allows limiting the number of elements listed on a page.
 */

Libs('template');

class Pagination {
    public $limit; //maximum number of results
    public $page; //current page
    public $total_pages; //total number of pages
    public $total_results; //total number of results
    public $page_var;
    
    /**
     * @param string $query
     * @param int $limit
     * @param string $page OPTIONAL
     */
    function __construct($total_results, $limit, $page_var = 'page') {
        $this->total_results = $total_results;
        $this->limit = $limit;
        $this->page_var = $page_var;
        
        if(isset($_REQUEST[$this->page_var]) && ((int)$_REQUEST[$this->page_var] > 0))
            $this->page = (int)$_REQUEST[$this->page_var];
        else
            $this->page = 1;
        
        /* Get total pages count */
        $this->total_pages = ceil($this->total_results/$this->limit);
        if($this->total_pages < 1)
            $this->total_pages = 1;
        
        if($this->page > $this->total_pages)
            $this->page = $this->total_pages;
    }
    
    public function LimitOffset() {
        return (($this->page-1)*$this->limit);
    }
    
    public function SQL_Limit() {
        return $this->LimitOffset().', '.$this->limit;
    }
    
    /**
     * @brief Get the remaining page count for this query.
     * @return int
     */
    public function Remaining() {
        return $this->total_pages-$this->page;
    }
    
    /**
     * @brief Determine whether this is the first page or not for this query.
     * @return bool
     */
    public function IsFirstPage() {
        if($this->page == 1)
            return true;
        else
            return false;
    }
    
    /**
     * @brief Determine whether this is the last page or not for this query.
     * @return bool
     */
    public function IsLastPage() {
        if($this->page >= $this->total_pages)
            return true;
        else
            return false;
    }
    
    /**
     * @brief Make a pager from a template.
     * @param string $url
     * @param string $template OPTIONAL
     *
     * A pager allows navigation between different pages. Example output could
     * be:
     * << Previous 1 2 .. 10 11 12 [13] 14 15 16 .. 29 30 Next >>
     */
    public function MakePager($url, $template = 'pagination/pager.tpl') {
        $tpl = new Template;
        
        $tpl->assign('url', $url);
        $tpl->assign('page', $this->page);
        $tpl->assign('total_pages', $this->total_pages);
        $tpl->assign('total_pages_before', $this->page-1);
        $tpl->assign('total_pages_after', $this->Remaining());
        $tpl->assign('is_first_page', $this->IsFirstPage());
        $tpl->assign('is_last_page', $this->IsLastPage());
        $tpl->assign('page_var', $this->page_var);
        
        return $tpl->fetch($template);
    }
}