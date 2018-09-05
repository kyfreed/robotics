<?php

include_once 'classes/init.php';

class Page {

    private $templateRoot = '../';
    private $template = 'template';
    private $head = '';
    private $title = '';
    private $bodyClass = '';
    private $afterContent = '';

    public function __construct($templateRoot = null, $template = null) {
        if (strlen($templateRoot)) {
            $this->templateRoot = $templateRoot;
        }
        if (strlen($template)) {
            $this->template = $template;
        }
        ob_start();
    }

    public function setTemplate($temp) {
        $this->template = $temp;
    }

    public function __destruct() {
        $this->send($this->head, $this->title, $this->bodyClass, ob_get_clean(), $this->afterContent);
    }

    private function send($head, $title, $bodyClass, $content, $afterContent) {
        include(dirname(dirname(__FILE__)) . '/_' . $this->template . '.php');
    }

    public function addHead($str) {
        if (strlen($this->head)) {
            $this->head .= "\n";
        }
        $this->head .= $str;
    }

    public function addTitle($str) {
        if (strlen($this->title)) {
            $this->title .= ' ';
        }
        $this->title .= $str;
    }

    public function addBodyClass($str) {
        if (strlen($this->bodyClass)) {
            $this->bodyClass .= ' ';
        }
        $this->bodyClass .= $str;
    }

    public function addAfterContent($str) {
        if (strlen($this->afterContent)) {
            $this->afterContent .= "\n";
        }
        $this->afterContent .= $str;
    }

    public function redirect($url) {
        ob_clean();
        header("Location: sample_searchresults.php");
        exit;
    }

}
