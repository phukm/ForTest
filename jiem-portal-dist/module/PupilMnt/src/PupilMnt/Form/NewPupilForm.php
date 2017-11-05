<?php
namespace PupilMnt\Form;

use Zend\Form\Form;
use Zend\Form\Element;
use Dantai\Utility\DateHelper;

class NewPupilForm extends Form
{

    public function __construct($name = null)
    {
        parent::__construct('form');

        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'id',
            'type' => 'hidden'
        ));

        $this->add(array(
            'name' => 'Number',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'id' => 'Number',
                'class' => 'form-control',
                'maxlength' => 10
            )
        ));
//         $this->add(array(
//             'name' => 'pupilId',
//             'type' => 'Zend\Form\Element\Text',
//             'attributes' => array(
//                 'id' => 'pupilId',
//                 'class' => 'form-control',
//                 'maxlength' => 10
//             )
//         ));
        $this->add(array(
            'name' => 'firstNameKanji',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'id' => 'firstNameKanji',
                'class' => 'form-control',
                'placeholder' => '姓'
            )
        ));

        $this->add(array(
            'name' => 'lastNameKanji',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'id' => 'lastNameKanji',
                'class' => 'form-control',
                'placeholder' => '名'
            )
        ));

        $this->add(array(
            'name' => 'firstNameKana',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'id' => 'firstNameKana',
                'class' => 'form-control',
                'placeholder' => '姓'
            )
        ));

        $this->add(array(
            'name' => 'lastNameKana',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'id' => 'lastNameKana',
                'class' => 'form-control',
                'placeholder' => '名'
            )
        ));

        $this->add(array(
            'name' => 'birthYear',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'ddlYear',
                'value' => ''
            )
        ));

        $this->add(array(
            'name' => 'birthMonth',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'ddlMonth',
                'value' => ''
            )
        ));

        $this->add(array(
            'name' => 'birthDay',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'ddlDay',
                'value' => ''
            )
        ));

        $this->add(array(
            'name' => 'gender',
            'type' => 'Zend\Form\Element\Radio',
            'attributes' => array(
                'class' => 'padding_radio fix-width-66',
            ),
            'options' => array(
                'value_options' => array(
                    '1' => '男',
                    '0' => '女'
                ),
                'allow_empty' => false,
                'nullable' => false
            )
        ));

        $this->add(array(
            'name' => 'year',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'year',
                'class' => 'form-control',
                'value' => ''
            )
        ));
        $this->add(array(
            'name' => 'orgSchoolYear',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'orgSchoolYear',
                'value' => ''
            )
        ));
        $this->add(array(
            'name' => 'classj',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'classj',
                'value' => ''
            )
        ));
        $this->add(array(
            'name' => 'einaviId',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'id' => 'einaviId',
                'class' => 'form-control'
            ),
            'options' => array()
        ));
        $this->add(array(
            'name' => 'eikenId',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'id' => 'eikenId',
                'class' => 'form-control'
            ),
            'options' => array()
        ));
        $this->add(array(
            'name' => 'eikenPassword',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'id' => 'eikenPassword',
                'class' => 'form-control'
            ),
            'options' => array()
        ));
        $this->add(array(
            'name' => 'eikenLevel',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'eikenLevel',
                'value' => ''
            )
        ));
        $this->add(array(
            'name' => 'eikenYear',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'eikenYear',
                'value' => ''
            )
        ));
        $this->add(array(
            'name' => 'kai',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'kai',
                'value' => ''
            )
        ));
        $this->add(array(
            'name' => 'eikenRead',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'id' => 'eikenRead',
                'class' => 'form-control',
                'maxlength' => 3
            ),
            'options' => array()
        ));
        $this->add(array(
            'name' => 'eikenListen',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'id' => 'eikenListen',
                'class' => 'form-control',
                'maxlength' => 3
            ),
            'options' => array()
        ));
        $this->add(array(
            'name' => 'eikenWrite',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'id' => 'eikenWrite',
                'class' => 'form-control',
                'maxlength' => 3
            ),
            'options' => array()
        ));
        $this->add(array(
            'name' => 'eikenSpeak',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'id' => 'eikenSpeak',
                'class' => 'form-control',
                'maxlength' => 3
            ),
            'options' => array()
        ));
        $this->add(array(
            'name' => 'eikenTotal',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'id' => 'eikenTotal',
                'class' => 'form-control',
                'maxlength' => 4
            ),
            'options' => array()
        ));
        $this->add(array(
            'name' => 'ibaLevel',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'ibaLevel',
                'value' => ''
            )
        ));
        // Datepicker from Org
        $this->add(array(
            'name' => 'datetime',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'id' => 'datetimepicker',
                'class' => 'form-control input-date-1'
            ),
            'options' => array()
        ));
        $this->add(array(
            'name' => 'examDateEkien',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'id' => 'examDateEkien',
                'class' => 'form-control input-date-1'
            ),
            'options' => array()
        ));
        // End
        $this->add(array(
            'name' => 'ibaRead',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'id' => 'ibaRead',
                'class' => 'form-control',
                'maxlength' => 3
            ),
            'options' => array()
        ));
        $this->add(array(
            'name' => 'ibaListen',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'id' => 'ibaListen',
                'class' => 'form-control',
                'maxlength' => 3
            ),
            'options' => array()
        ));
        $this->add(array(
            'name' => 'ibaTotal',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'id' => 'ibaTotal',
                'placeholder' => '',
                'class' => 'form-control',
                'maxlength' => 4
            ),
            'options' => array()
        ));
        $this->add(array(
            'name' => 'resultVocabulary',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'resultVocabulary',
                'value' => ''
            )
        ));
        $this->add(array(
            'name' => 'resultGrammar',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'resultGrammar',
                'value' => ''
            )
        ));
        $this->add(array(
            'name' => 'submit',
            'type' => 'Submit',
            'attributes' => array(
                'value' => 'Go',
                'id' => 'submitButton'
            )
        ));
    }

    public function setFormEditPupil($pupil = NULL, $id = false)
    {
        $date = !empty($pupil['examDateIbar'])?$pupil['examDateIbar']->format('Y/m/d'):'';
        $dateEiken = !empty($pupil['examDateEkien'])?$pupil['examDateEkien']->format('Y/m/d'):'';
        //$pupilID =  isset($pupil['pupilID'])?trim($pupil['pupilID']):'';
        $number =  isset($pupil['number'])?trim($pupil['number']):'';
        $firstNameKanji =  isset($pupil['firstNameKanji'])?$pupil['firstNameKanji']:'';
        $lastNameKanji =  isset($pupil['lastNameKanji'])?$pupil['lastNameKanji']:'';
        $firstNameKana =  isset($pupil['firstNameKana'])?$pupil['firstNameKana']:'';
        $lastNameKana =  isset($pupil['lastNameKana'])?$pupil['lastNameKana']:'';
        $gender =  isset($pupil['gender'])?$pupil['gender']:1;
        $einaviId =  isset($pupil['einaviId'])?$pupil['einaviId']:'';
        $eikenId =  isset($pupil['eikenId'])?$pupil['eikenId']:'';
        $eikenPassword =  isset($pupil['eikenPassword'])?$pupil['eikenPassword']:'';
        $cSEScoreReading =  isset($pupil['cSEScoreReading'])?$pupil['cSEScoreReading']:'';
        $cSEScoreListening =  isset($pupil['cSEScoreListening'])?$pupil['cSEScoreListening']:'';
        $cSEScoreWriting =  isset($pupil['cSEScoreWriting'])?$pupil['cSEScoreWriting']:'';
        $cSEScoreSpeaking =  isset($pupil['cSEScoreSpeaking'])?$pupil['cSEScoreSpeaking']:'';
        $eikenTotal = (int)$cSEScoreReading+(int)$cSEScoreListening+(int)$cSEScoreWriting+(int)$cSEScoreSpeaking;
        $listenIbar = isset($pupil['listenIbar'])?$pupil['listenIbar']:'';
        $readIbar = isset($pupil['readIbar'])?$pupil['readIbar']:'';
        $ibaTotal = (int)$listenIbar+(int)$readIbar;

        //$this->get('pupilId')->setValue($pupilID);
        $this->get('Number')->setValue($number);
        $this->get('firstNameKanji')->setValue($firstNameKanji);
        $this->get('lastNameKanji')->setValue($lastNameKanji);
        $this->get('firstNameKana')->setValue($firstNameKana);
        $this->get('lastNameKana')->setValue($lastNameKana);
        $this->get('gender')->setValue($gender);
        $this->get('einaviId')->setValue($einaviId);
        $this->get('eikenId')->setValue($eikenId);
        $this->get('eikenPassword')->setValue($eikenPassword);
        $this->get('eikenRead')->setValue($cSEScoreReading);
        $this->get('eikenListen')->setValue($cSEScoreListening);
        $this->get('eikenWrite')->setValue($cSEScoreWriting);
        $this->get('eikenSpeak')->setValue($cSEScoreSpeaking);
        $this->get('eikenTotal')->setValue($eikenTotal);
        $this->get('datetime')->setValue($date);
        $this->get('examDateEkien')->setValue($dateEiken);
        $this->get('ibaRead')->setValue($readIbar);
        $this->get('ibaListen')->setValue($listenIbar);
        $this->get('ibaTotal')->setValue($ibaTotal);

        return $this;
    }

    /**
     *
     * @param array $listclass
     * @param string $idCl
     * @return \PupilMnt\Form\NewPupilForm
     */
    public function setListClass(array $listclass, $idCl = '')
    {
        $this->get("classj")
            ->setValueOptions($listclass)
            ->setAttributes(array(
                'value' => $idCl,
                'selected' => true
            ));

        return $this;
    }

    /**
     *
     * @param array $yearschool
     * @param string $orgSchoolYearId
     * @return \PupilMnt\Form\NewPupilForm
     */
    public function setListSchoolYear(array $yearschool, $orgSchoolYearId = '')
    {
        $this->get("orgSchoolYear")
            ->setValueOptions($yearschool)
            ->setAttributes(array(
                'value' => $orgSchoolYearId,
                'selected' => true
            ));
        return $this;
    }

    /**
     *
     * @return \PupilMnt\Form\NewPupilForm
     */
    public function setListKai($kai = 0)
    {
        $listKai =array('',1,2,3);
        $this->get("kai")
            ->setValueOptions($listKai)
            ->setAttributes(array(
                'value' => $kai,
                'selected' => true
            ));
        return $this;
    }

    public function setListBirthDay(\DateTime $birthday = null,$add=false)
    {
        $y = !empty($birthday)? $birthday->format('Y'):'';
        $m = !empty($birthday)? $birthday->format('m'):'';
        $d = !empty($birthday)? $birthday->format('d'):'';

        $listyear = array();
        $yearTo = (int) date('Y');
        $fromYear = $yearTo - 99;
        $listyear[''] = '';
        for ($i = $yearTo; $i >= $fromYear; $i --) {
            $listyear[$i] = DateHelper::gengo($i);
        }
        $listmonth = array();
        for ($i = 1; $i < 13; $i ++) {
            if ($i == 1) {
                $listmonth[''] = '';
            }
            $listmonth[$i] = $i;
        }
        $listday = array();
        $listday[''] = '';
        for ($i = 1; $i < 32; $i ++) {
            if ($i == 1) {
                $listday[''] = '';
            }
            $listday[$i] = $i;
        }
        if($add){
            $listday = array();
            $listmonth = array();
            $listmonth[''] = '';
            $listday[''] = '';
        }
        $this->get('birthYear')
            ->setValueOptions($listyear)
            ->setAttributes(array(
                'value' => $y,
                'selected' => true
            ));
        $this->get('birthMonth')
            ->setValueOptions($listmonth)
            ->setAttributes(array(
                'value' => $m,
                'selected' => true
            ));
        $this->get('birthDay')
            ->setValueOptions($listday)
            ->setAttributes(array(
                'value' => $d,
                'selected' => true
            ));

        return $this;
    }


    /**
     *
     * @param string $yearEkr
     * @param string $year
     * @param number $yearFrom
     * @return \PupilMnt\Form\NewPupilForm
     */
    public function setListEikenYear($yearEkr = '', $year = '', $yearFrom = 2009)
    {
        $listYear = array();
        $listYear[''] = '';
        for ($i = (int) date('Y') + 2; $i > $yearFrom; $i --) {
            $listYear[$i] = $i;
        }

        $this->get('eikenYear')
            ->setValueOptions($listYear)
            ->setAttributes(array(
            'value' => $yearEkr,
            'selected' => true
        ));
//        $listYears = array();
////         $listYears[''] = '';
//         for ($i = (int) date('Y'); $i >= ((int) date('Y') -50); $i --) {
//            $listYears[$i] = $i;
//        }
        $listYears = array();
        $listYears[''] = '';
        for ($i = (int) date('Y') + 2; $i > $yearFrom; $i --) {
            $listYears[$i] = $i;
        }
        $this->get('year')
            ->setValueOptions($listYears)
            ->setAttributes(array(
                'value' => $year,
                'selected' => true
            ));

        return $this;
    }

    /**
     *
     * @param array $listeikenlevel
     * @param string $eikenLevelIdEkr
     * @param string $eikenLevelIdIbar
     * @param string $vocabularySMResult
     * @param string $grammarSMResult
     * @return \PupilMnt\Form\NewPupilForm
     */
    public function setListEikenLevel(array $listeikenlevel, $eikenLevelIdEkr = '', $eikenLevelIdIbar = '', $vocabularySMResult = '', $grammarSMResult = '')
    {
        $this->get("eikenLevel")
            ->setValueOptions($listeikenlevel)
            ->setAttributes(array(
                'value' => $eikenLevelIdEkr,
                'selected' => true
            ));

        $this->get("ibaLevel")
            ->setValueOptions($listeikenlevel)
            ->setAttributes(array(
                'value' => $eikenLevelIdIbar,
                'selected' => true
            ));
        $this->get("resultVocabulary")
            ->setValueOptions($listeikenlevel)
            ->setAttributes(array(
                'value' => $vocabularySMResult,
                'selected' => true
            ));
        $this->get("resultGrammar")
            ->setValueOptions($listeikenlevel)
            ->setAttributes(array(
                'value' => $grammarSMResult,
                'selected' => true
            ));

        return $this;
    }
    
}