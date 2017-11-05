<?php
namespace Eiken\Helper;

use Zend\View\Helper\AbstractHelper;

class PaginationHelper extends AbstractHelper
{

    private $numPerPage;

    private $totalResults;

    private $results;

    private $baseUrl;

    private $paging;

    private $page;

    private $previousPage;

    private $nextPage;

    private $param;

    public function __invoke($paginator, $page, $baseUrl, $numPerPage = 10, $param = '')
    {
        $this->numPerPage = $numPerPage;
        $this->totalResults = count($paginator);
        $this->baseUrl = $baseUrl . '/';
        $this->page = $page;
        $this->previousPage = $page - 1;
        $this->nextPage = $page + 1;
        $this->param = $param;
        
        return $this->generatePaging();
    }
    
    /**
     * Generate paging html
     */
    private function generatePaging()
    {
        // Get total page count
        $totalPage = ceil($this->totalResults / $this->numPerPage);
        // Don't show pagination if there's only one page
        if ($totalPage == 1) {
            return;
        }

        // Show back to first page if not first page
        if ($this->page != 1) {
            $pageUrl = $this->baseUrl . 'page/1' . $this->param;
            $this->paging .= '<li><a href="' . $pageUrl . '"><<</a></li>';
        }

        // Show back button if page is not the first page
        if ($this->page > 1) {
            $pageUrl = $this->baseUrl . 'page/' . $this->previousPage . $this->param;
            $this->paging .= '<li><a href="' . $pageUrl . '"><</a></li>';
        }

        // If current page is lower than 3
        if ($totalPage <= 5) {
            $maxPage = $totalPage;

            for ($i = 1; $i <= $maxPage; $i ++) {
                $pageUrl = $this->baseUrl . 'page/' . $i . $this->param;
                $class = ($i == $this->page) ? 'class="active"' : '';
                $this->paging .= '<li '.$class.'><a href="' . $pageUrl . '">' . $i . '</a></li>';
            }
        } else
            if ($totalPage > 5) {
                if ($this->page + 2 <= $totalPage) {
                    if ($this->page < 3) {
                        for ($i = 1; $i <= 5; $i ++) {
                            $pageUrl = $this->baseUrl . 'page/' . $i . $this->param;
                            $class = ( $i == $this->page ) ? 'class="active"' : '';
                            $this->paging .= '<li '.$class.'><a href="' . $pageUrl . '">' . $i . '</a></li>';
                        }
                    } else
                        if ($this->page >= 3) {
                            for ($i = $this->page - 2; $i <= $this->page + 2; $i ++) {
                                $pageUrl = $this->baseUrl . 'page/' . $i . $this->param;
                                $class = ( $i == $this->page ) ? 'class="active"' : '';
                                $this->paging .= '<li '.$class.'><a href="' . $pageUrl . '">' . $i . '</a></li>';
                            }
                        }
                } else {
                    for ($i = $this->page - 2; $i <= $totalPage; $i ++) {
                        $pageUrl = $this->baseUrl . 'page/' . $i . $this->param;
                        $class = ( $i == $this->page ) ? 'class="active"' : '';
                        $this->paging .= '<li '.$class.'><a href="' . $pageUrl . '">' . $i . '</a></li>';
                    }
                }
            }

        // Show next button if page is not the last page
        if ($this->nextPage <= $totalPage) {
            $pageUrl = $this->baseUrl . 'page/' . $this->nextPage . $this->param;
            $this->paging .= '<li><a href="' . $pageUrl . '">></a></li>';
        }

        // Show go to last page option if not the last page
        if ($this->page != $totalPage) {
            $pageUrl = $this->baseUrl . 'page/' . $totalPage . $this->param;
            $this->paging .= '<li><a href="' . $pageUrl . '">>></a></li>';
        }

        return $this->paging;
    }
}