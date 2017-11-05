<?php

namespace GoalSetting\Helper;


use Zend\View\Helper\AbstractHelper;

class PaginationHelper extends AbstractHelper
{

    private $resultsPerPage;

    private $totalResults;

    private $results;

    private $baseUrl;

    private $paging;

    private $page;

    private $previousPage;

    private $nextPage;

    private $param;

    public function __invoke($pagedResults, $page, $baseUrl, $resultsPerPage = 10, $param)
    {
        $this->resultsPerPage = $resultsPerPage;
        $this->totalResults = $pagedResults->count();
        $this->results = $pagedResults;
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
        $totalPage = ceil($this->totalResults / $this->resultsPerPage);
        // Don't show pagination if there's only one page
        if ($totalPage == 1) {
            return;
        }

        // Show back to first page if not first page
        if ($this->page != 1) {
            $this->paging = '<li><a href="' . $this->baseUrl . 'page/1' . $this->param . '"><<</a></li>';
        }

        // Show back button if page is not the first page
        if ($this->page > 1) {
            $this->paging .= '<li><a href="' . $this->baseUrl . 'page/' . $this->previousPage . $this->param . '"><</a></li>';
        }

        // If current page is lower than 3
        if ($totalPage <= 5) {
            $maxPage = $totalPage;

            for ($i = 1; $i <= $maxPage; $i ++) {
                if ($i == $this->page) {
                    $this->paging .= '<li class="active"><a href="' . $this->baseUrl . 'page/' . $i . $this->param . '">' . $i . '</a></li>';
                } else {
                    $this->paging .= '<li><a href="' . $this->baseUrl . 'page/' . $i . $this->param . '">' . $i . '</a></li>';
                }
            }
        } else
            if ($totalPage > 5) {
                if ($this->page + 2 <= $totalPage) {
                    if ($this->page < 3) {
                        for ($i = 1; $i <= 5; $i ++) {
                            if ($i == $this->page) {
                                $this->paging .= '<li class="active"><a href="' . $this->baseUrl . 'page/' . $i . $this->param . '">' . $i . '</a></li>';
                            } else {
                                $this->paging .= '<li><a href="' . $this->baseUrl . 'page/' . $i . $this->param . '">' . $i . '</a></li>';
                            }
                        }
                    } else
                        if ($this->page >= 3) {
                            for ($i = $this->page - 2; $i <= $this->page + 2; $i ++) {
                                if ($i == $this->page) {
                                    $this->paging .= '<li class="active"><a href="' . $this->baseUrl . 'page/' . $i . $this->param . '">' . $i . '</a></li>';
                                } else {
                                    $this->paging .= '<li><a href="' . $this->baseUrl . 'page/' . $i . '">' . $i . '</a></li>';
                                }
                            }
                        }
                } else {
                    for ($i = $this->page - 2; $i <= $totalPage; $i ++) {
                        if ($i == $this->page) {
                            $this->paging .= '<li class="active"><a href="' . $this->baseUrl . 'page/' . $i . $this->param . '">' . $i . '</a></li>';
                        } else {
                            $this->paging .= '<li><a href="' . $this->baseUrl . 'page/' . $i . $this->param . '">' . $i . '</a></li>';
                        }
                    }
                }
            }

        // Show next button if page is not the last page
        if ($this->nextPage <= $totalPage) {
            $this->paging .= '<li><a href="' . $this->baseUrl . 'page/' . $this->nextPage . $this->param . '">></a></li>';
        }

        // Show go to last page option if not the last page
        if ($this->page != $totalPage) {
            $this->paging .= '<li><a href="' . $this->baseUrl . 'page/' . $totalPage . $this->param . '">>></a></li>';
        }

        return $this->paging;
    }
}