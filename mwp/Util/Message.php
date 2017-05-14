<?php

namespace Util;

class Message {

    const ERROR = 1;
    const WARNING = 2;
    const INFO = 3;
    
    private static $cssClass = array(
        self::ERROR => 'error',
        self::WARNING => 'warning',
        self::INFO => 'info',
    );

    private $type;
    private $summary;
    private $details;

    public function __construct($type, $summary, $details = null) {
        $this->type = $type;
        $this->summary = $summary;
        $this->details = $details;
    }
    
    public function render() {
        if(empty(self::$cssClass[$this->type]) || empty($this->summary))
            return;
        $details = empty($this->details) ? '' : '<p>' . $this->details . '</p>';
        
        echo '<details class="', self::$cssClass[$this->type], '"><summary>' , $this->summary, '</summary>', $details, '</details>';
    }
    
    public function getType() {
        return $this->type;
    }

    public function getSummary() {
        return $this->summary;
    }

    public function getDetails() {
        return $this->details;
    }

}

?>