<?php

/**
 * Dantai Portal (http://dantai.com.jp/)
 *
 * @link      https://fhn-svn.fsoft.com.vn/svn/FSU1.GNC.JIEM-Portal/trunk/Development/SourceCode for the source repository
 * @copyright Copyright (c) 2015 FPT-Software. (http://www.fpt-software.com)
 * 
 * @author minhbn1<minhbn1@fsoft.com.vn>
 */

namespace ConsoleInvitation\Controller;

use ConsoleInvitation\Service\ServiceInterface\ConfigDateServiceInterface;
use Dantai\Utility\DateHelper;
use Dantai\Utility\ValidateHelper;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Zend\Mvc\Controller\AbstractActionController;

class ConsoleConfigDateController extends AbstractActionController {
    use ContainerAwareTrait;

    protected $dantaiService;
    protected $configDateService;

    public function getDantaiService()
    {
        if (!$this->dantaiService) {
            $this->dantaiService = $this->getServiceLocator()->get('Application\Service\DantaiServiceInterface');
        }

        return $this->dantaiService;
    }

    /**
     * @return ConfigDateServiceInterface
     */
    public function getConfigDateService(){
        if (!$this->configDateService) {
            $this->configDateService = $this->getServiceLocator()->get('ConsoleInvitation\Service\ConfigDateService');
        }

        return $this->configDateService;
    }

    public function getEntityManager()
    {
        return $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
    }

    public function importConfigDateAction()
    {
        $fileName = $this->params()->fromRoute('fileName');
        if (empty($fileName)) {
            echo "Error: Please input file name" . PHP_EOL;

            return false;
        }
        $startDate = date(DateHelper::DATETIME_FORMAT_DEFAULT);
        echo "[" . $startDate . "] Start import Config Date..." . PHP_EOL;
        $result = $this->getConfigDateService()->importConfigDate($fileName);
        $endDate = date(DateHelper::DATETIME_FORMAT_DEFAULT);
        if (!$result) {
            echo "[" . $endDate . "] Fail import Config Date!" . PHP_EOL;
        } else {
            echo "[" . $endDate . "] Done import Config Date!" . PHP_EOL;
        }
    }

    public function exportConfigDateAction()
    {
        $year = $this->params()->fromRoute('year');
        $kai = $this->params()->fromRoute('kai');
        // validate numeric: year, kai
        if ((isset($year) && !ValidateHelper::isYear($year))
            || (isset($kai) && !ValidateHelper::isNumeric($kai))
        ) {
            echo "Error: year and kai must be integer and greater than 0, year must be YYYY format" . PHP_EOL;

            return false;
        }
        $startDate = date(DateHelper::DATETIME_FORMAT_DEFAULT);
        echo "[" . $startDate . "] Start export Config Date..." . PHP_EOL;
        $result = $this->getConfigDateService()->exportConfigDate($year, $kai);
        $endDate = date(DateHelper::DATETIME_FORMAT_DEFAULT);
        if (!$result) {
            echo "[" . $endDate . "] Fail export Config Date!" . PHP_EOL;
        }else {
            echo "[" . $endDate . "] Done export Config Date!" . PHP_EOL;
        }
    }

}
