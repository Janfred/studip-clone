<?php

class DataFieldTextmarkupEntry extends DataFieldTextareaEntry
{
    public function getDisplayValue($entities = true)
    {
        if ($entities) {
            return formatReady($this->getValue());
        }

        return $this->getValue();
    }
}
