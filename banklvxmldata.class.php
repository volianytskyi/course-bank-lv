<?php

class BankLvXmlData
{
    private $xml;
    public function __construct(SimpleXMLElement $xml)
    {
      $this->xml = $xml;
    }


    /**
    * @return string date in 'd.m.Y' format (e.g. 01.31.1970)
    *
    */
    public function getDate()
    {
      $date = DateTime::createFromFormat('Ymd', (string)$this->xml->Date);
      return date_format($date, 'd.m.Y');
    }


    /**
    * @param string id as 'USD', 'GBP' etc
    * @return array
    *   id - string id ('USD', 'GBP' etc)
    *   rate - float rate value
    */
    public function getCurrencyDataById($id)
    {
      foreach($this->xml->Currencies->Currency as $currency)
      {
        if((string)$currency->ID == $id)
        {
          return array(
            'id' => $id,
            'rate' => floatval(str_replace(',', '.', $currency->Rate))
          );
        }
      }

      return array();
    }
}


 ?>
