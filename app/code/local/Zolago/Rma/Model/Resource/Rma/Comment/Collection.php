<?php

class Zolago_Rma_Model_Resource_Rma_Comment_Collection extends ZolagoOs_Rma_Model_Mysql4_Rma_Comment_Collection
{
    public function setCreatedAtOrder($direction='desc')
    {
        $this->setOrder('created_at', $direction);
        $this->setOrder('entity_id', $direction);
        return $this;
    }
}
