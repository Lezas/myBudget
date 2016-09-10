<?php

namespace BudgetBundle\Helper;

use BudgetBundle\Entity\Income;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints\Date;

class DataFormatter
{
    /**
     * @param $data
     * @return array
     */
    public static function groupByDay($data)
    {

        $arrayCol = new ArrayCollection($data);

        $retturnArray = new ArrayCollection();
        foreach ($arrayCol as $mkey=>$data) {

            if ($retturnArray->get($data->getDateTime()->format('Y-m-d')) != null) {
                $row = $retturnArray->get($data->getDateTime()->format('Y-m-d')) + (float)$data->getMoney();
                $retturnArray->set($data->getDateTime()->format('Y-m-d'), $row);
            } else {
                $retturnArray->set($data->getDateTime()->format('Y-m-d'), (float)$data->getMoney());
            }
        }

        $true = [];
        foreach ($retturnArray as $key=>$array) {
            $true[] =  [$key, $array];
        }
        
        return $true;
    }

    public static function connectData($first, $second)
    {
        $return = [];

        foreach ($first as $first_row) {

            $found = false;
            foreach ($second as $sk => $second_row){
                if($first_row[0] == $second_row[0]){
                    $return[] = [$first_row[0],$first_row[1],$second_row[1]];
                    unset($second[$sk]);
                    $found = true;
                    break;
                }
            }

            if($found == false){
                $return[] = [$first_row[0],$first_row[1],0];
            }
        }

        foreach ($second as $srow){
            $return[] = [$srow[0],0,$srow[1]];
        }
        sort($return);

        return $return;
    }
}