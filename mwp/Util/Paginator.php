<?php

namespace Util;

class Paginator {

    private $lastPage;
    private $actualPage;
    private $link;

    public function __construct($recordCount, $recordsOnPage, $actualPage, $link) {
        if($recordCount <= $recordsOnPage)
            $this->lastPage = 0;
        else 
            $this->lastPage = ceil(($recordCount - 1) / $recordsOnPage);
        $this->actualPage = $actualPage;
        $this->link = $link;
    }

    public function printPagelinks() {
        if($this->lastPage < 1)
            return;
        $start = $this->actualPage - 3;
        if($start < 0)
            $start = 0;
        $end = $this->actualPage + 3;
        if($end > $this->lastPage)
            $end = $this->lastPage;

        echo '<p class="pagelinks">Oldalak: ' ;
        for($c = $start; $c < $end; )
            echo '<a href="', $this->link, '.', $c, '">', ++$c, '</a>';
        echo '</p>';
    }

}

?>