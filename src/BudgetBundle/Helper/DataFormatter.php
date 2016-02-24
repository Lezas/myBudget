<?php

/**
 * Created by PhpStorm.
 * User: Vartotojas
 * Date: 2016.02.24
 * Time: 21:17
 */
namespace BudgetBundle\Helper;

use Symfony\Component\Validator\Constraints\Date;

class DataFormatter
{
    /**
     * @param $data
     * @return array
     */
    public static function groupByDay($data)
    {
        for($i = 0; $i < count($data); $i++){
            $row = $data[$i];
            if(isset($row['deleted']) != true)
                for($o = $i+1; $o < count($data); $o++){
                    $second_row = $data[$o];

                    if(isset($second_row['deleted']) != true) {
                        if ($row['dateTime']->format('Y-m-d') === $second_row['dateTime']->format('Y-m-d')) {
                            $data[$i]['money'] += $data[$o]['money'];
                            $data[$o]['deleted'] = true;
                        }
                    }
                }
        }

        $return_data = [];
        foreach($data as $key => $value){
            if(!isset($value['deleted'])){
                $return_data[]= [$data[$key]['dateTime']->format('Y-m-d'), (float)$data[$key]['money']];
            }
        }
        return $return_data;
    }
}