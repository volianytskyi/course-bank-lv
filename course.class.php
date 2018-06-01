<?php

use Stalker\Lib\Core\Mysql;

/**
 * Exchange rate
 *
 * @package stalker_portal
 * @author sergey.volyanytsky@gmail.com
 */

class Course implements \Stalker\Lib\StbApi\Course
{
    public $db;
    public $cache_table;
    public $content_url;
    public $codes;

    public function __construct(){
        $this->db = Mysql::getInstance();
        $this->cache_table = "course_cache";
        $this->content_url = 'https://www.bank.lv/vk/ecb.xml';
        $this->codes = array('USD', 'GBP', 'RUB');
    }

    public function getData(){
        return $this->getDataFromDBCache();
    }

    public function getDataFromURI()
    {

        $result = array();
        $content = file_get_contents($this->content_url);

        $xml_obj = NULL;
        $parsed_data = new BankLvXmlData(simplexml_load_string($content));

        if ($content && ($parsed_data))
        {
          $date = $parsed_data->getDate();

          $result['title'] = _('Exchange rate on').' '. $date;
          $result['on_date'] = $date;
          $result['data'] = array();

          $new_data = array();
          foreach($this->codes as $code)
          {
            $new_data[] = $parsed_data->getCurrencyDataById($code);
          }

          $idx = 0;

          $old_data = $this->getDataFromDBCache();

          if (!array_key_exists('updated', $old_data) || $result['on_date'] != $old_data['updated'])
          {
            foreach ($new_data as $valute)
            {
              $result['data'][$idx] = array();
              $result['data'][$idx]['code'] = $valute['id'];
              $result['data'][$idx]['currency'] = $valute['id'];
              $result['data'][$idx]['value'] = $valute['rate'];

              $result['data'][$idx]['diff'] = 0;
              $result['data'][$idx]['trend'] = 0;

              if (is_array($old_data) && array_key_exists('data', $old_data) && array_key_exists($idx, $old_data['data']))
              {
                $result['data'][$idx]['diff'] = round(($result['data'][$idx]['value'] - $old_data['data'][$idx]['value']), 4);

                if ($result['data'][$idx]['diff'] > 0)
                {
                  $result['data'][$idx]['trend'] = 1;
                }
                else if ($result['data'][$idx]['diff'] < 0)
                {
                  $result['data'][$idx]['trend'] = -1;
                }
              }
            $idx++;
          }

          $this->setDataDBCache($result);
        }
        else
        {
          $result = $old_data;
        }
      }
      return $result;
    }

    private function getDataFromDBCache(){

        $content = $this->db->from($this->cache_table)->where(array('url' => $this->content_url))->get()->first('content');

        $content = unserialize(System::base64_decode($content));

        if (is_array($content)){
            return $content;
        }else{
            return array();
        }
    }

    private function setDataDBCache($arr){

        $content = System::base64_encode(serialize($arr));

        $result = $this->db->from($this->cache_table)->where(array('url' => $this->content_url))->get();
        $crc = $result->get('crc');


        if (md5($content) != $crc){

            $data = array(
                          'content' => $content,
                          'updated' => 'NOW()',
                          'url'     => $this->content_url,
                          'crc'     => md5($content)
                      );

            if ($result->count() == 1){

                $this->db->update($this->cache_table,
                                  $data, array('url' => $this->content_url));

            }else{

                $this->db->insert($this->cache_table,
                                  $data);
            }
        }
    }
}

?>
