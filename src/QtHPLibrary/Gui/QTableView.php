<?php

class QTableView extends QAbstractElement {
    private $_model;
    
    public function setModel($model){
        $this->_model = $model;
    }
    
    public function show(){
        $columnCount = $this->_model->columnCount();
        echo '<table>';
        if($this->_model->hasHeaderData()){
            
            echo '<thead><tr>';
            for($i = 0; $i < $columnCount; ++$i){
                echo '<th>' . $this->_model->headerData($i) . '</th>';
            }
            echo '</tr><thead>';
        }
        echo '<tbody>';
        while($this->_model->fetch()){
            echo '<tr>';
            for($i = 0; $i < $columnCount; ++$i){
                echo '<td>' . $this->_model->data($i) . '</td>';
            }
            echo '</tr>';
        }
        echo '</tbody></table>';
    }
}

?>