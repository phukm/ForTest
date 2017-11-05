<?php

namespace OrgMnt\Controller;

use OrgMnt\Service\ServiceInterface\ImportMasterDataServiceInterface;
use Dantai\Utility\PHPExcel;
use Application\Service\ServiceInterface\DantaiServiceInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Doctrine\ORM\EntityManager;

class ImportMasterDataController extends AbstractActionController {

    /**
     *
     * @var DantaiServiceInterface
     */
    protected $dantaiService;

    /**
     *
     * @var ImportMasterDataServiceInterface
     */
    const CHUNK_SIZE = 3000;

    protected $importMasterDataService;

    public function __construct(DantaiServiceInterface $dantaiService, ImportMasterDataServiceInterface $importMasterDataService, EntityManager $entityManager) {
        $this->dantaiService = $dantaiService;
        $this->importMasterDataService = $importMasterDataService;
        $this->em = $entityManager;
    }

    protected $validation;

    public function indexAction() {
        $viewModel = new ViewModel();
        $request = $this->getRequest();
        // get roleId check role
        if (!\Dantai\PublicSession::isSysAdminRole()) {
            return $this->redirect()->toRoute('accessDenied');
        }
        // add json
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $jsMessages = array('MSG_NotSelect' => $translator->translate('ErrorBoxNotSelect'),
            'MSG_NotExist' => $translator->translate('ErrorFileExist'),
            'MSG_NotFile' => $translator->translate('TitleFileType'));

        $jsonMessage = \Dantai\Utility\JsonModelHelper::getInstance();

        $jsonMessage->setFail();
        $jsonMessage->setData($jsMessages);
        // is post
        if ($this->getRequest()->isPost()) {
            $validation = $this->importMasterDataService->validateImportMasterData(array(
                'formDataSubmited' => array(
                    'data' => $this->getRequest()->getPost()->toArray(),
                    'rules' => array(
                        'required' => array(
                            'ddbYear' => array('field' => 'ddbYear', 'label' => 'Year'),
                            'ddlKai' => array('field' => 'ddlKai', 'label' => 'Kai'),
                        )
                    )
                ),
                'fileDataSubmited' => array(
                    'data' => $this->getRequest()->getFiles()->toArray(),
                    'inputFileName' => 'fileImport',
                    'rules' => array(
                        'accepted' => '.CSV',
                        'isEmpty' => false,
                        'hasHeader' => false,
                        'numberOfColumn' => 22,
                        'numberOfRow' => 50000,
                    )
                ),
            ));

            if (!empty($validation['messages']) && is_string($validation['messages'])) {
                $validation['messages'] = array($validation['messages']);
            }
            if (!empty($validation['data'])) {
                $this->em->getConnection()->beginTransaction();
                try {
                    list($dantaiInsert, $dantaiUpdate, $accessKeyInsert, $accessKeyUpdate) = $this->importMasterDataService->splitMasterData($validation['data'], $this->getRequest()->getPost("ddbYear"), $this->getRequest()->getPost("ddlKai"));
                    if (!empty($dantaiInsert)) {
                        foreach (array_chunk($dantaiInsert, self::CHUNK_SIZE) as $row) {
                            $this->importMasterDataService->insertDantaiMasterData($row);
                        }
                    }
                    if (!empty($dantaiUpdate)) {
                        foreach (array_chunk($dantaiUpdate, self::CHUNK_SIZE) as $row) {
                            $this->importMasterDataService->updateDantaiMasterData($row);
                        }
                    }

                    if (!empty($accessKeyInsert)) {
                        foreach (array_chunk($accessKeyInsert, self::CHUNK_SIZE) as $row) {
                            $this->importMasterDataService->insertAccessKeyMasterData($row);
                        }
                    }
                    if (!empty($accessKeyUpdate)) {
                        foreach (array_chunk($accessKeyUpdate, self::CHUNK_SIZE) as $row) {
                            $this->importMasterDataService->updateAccessKeyMasterData($row);
                        }
                    }
                    $this->em->getConnection()->commit();
                } catch (Exception $ex) {
                    $this->em->getConnection()->rollback();
                    return $viewModel->setVariables(array(
                                "messageId" => $validation['messageId'],
                                "messagesValidate" => $validation['messages'],
                                "passed" => $validation['passed'],
                                "year" => $this->getRequest()->getPost("ddbYear"),
                                "kai" => $this->getRequest()->getPost("ddlKai"),
                                "jsMessages" => $jsonMessage,
                                "systemError" => true,
                    ));
                }
            }
            return $viewModel->setVariables(array(
                        "messageId" => $validation['messageId'],
                        "messagesValidate" => $validation['messages'],
                        "passed" => $validation['passed'],
                        "year" => $this->getRequest()->getPost("ddbYear"),
                        "kai" => $this->getRequest()->getPost("ddlKai"),
                        "jsMessages" => $jsonMessage
            ));
        }

        return $viewModel->setVariables(array(
                    "year" => $this->getRequest()->getPost("ddbYear"),
                    "kai" => $this->getRequest()->getPost("ddlKai"),
                    "jsMessages" => $jsonMessage
        ));
    }

}
