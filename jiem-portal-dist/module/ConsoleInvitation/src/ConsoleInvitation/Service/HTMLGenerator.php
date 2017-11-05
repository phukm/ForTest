<?php

namespace ConsoleInvitation\Service;

class HTMLGenerator {
    
    protected $config;
    
    public function __construct(array $config)
    {
        $this->config = $config;
    }
    
    /**
     * @param string $templatePath
     * @param string $templateName
     * @param array $viewData
     * @return string html
     */
    public function render($templatePath,$templateName,$viewData = array()){
        
        $renderer = new \Zend\View\Renderer\PhpRenderer();
        $viewModel = new \Zend\View\Model\ViewModel($viewData);
        $config = $this->getConfig();
        
        $viewPluginManager = $renderer->getHelperPluginManager();
        $viewPluginManager->setInvokableClass('escape', '\Zend\View\Helper\EscapeHtml');
        $viewPluginManager->setInvokableClass('callIfCallable', '\ConsoleInvitation\View\Helper\CallIfCallable');
        $viewPluginManager->setInvokableClass('to1ByteNumber', '\ConsoleInvitation\View\Helper\To1ByteNumber');
        $viewPluginManager->setInvokableClass('convertWdayJapan', '\ConsoleInvitation\View\Helper\ConvertWdayJapan');
        $viewPluginManager->setFactory('imageToBase64',
                function ($pluginManager) use ($config) {
                    $helper = new \ConsoleInvitation\View\Helper\ImageToBase64();
                    $helper->setImagePath($config['imagePath']);
                    return $helper;
                 }
         );
        
        $renderer->resolver()->addPath($templatePath);
        $viewModel->setTemplate($templateName);
        
        return $renderer->render($viewModel);
    }
    
    /**
     * @return array config of ConsoleInvitation
     */
    public function getConfig(){
        return $this->config;
    }
    
    public function saveHtmlToFile($filePath,$html){
        return file_put_contents($filePath, $html);
    }
    
    public function convertHtmlFileToPdfFile($htmlFilePath,$pdfFilePath,$size = 'A4'){
        $returnVal = shell_exec('which wkhtmltopdf');
        $returnVal = empty($returnVal) ? false : true;
        if(!$returnVal){
            throw new \Exception('wkhtmltopdf not installed!');
        }
        
        return shell_exec('wkhtmltopdf --zoom 0.5 -s '. $size .' '.$htmlFilePath.' '.$pdfFilePath);
    }
}