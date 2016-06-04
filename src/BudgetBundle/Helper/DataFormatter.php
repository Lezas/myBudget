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
            $retturnArray->set($data->getDateTime()->format('Y-m-d'), (float)$data->getMoney());
            foreach ($arrayCol as $key=>$data2) {
                /** @var Income $data2 */
                /** @var Income $data */
                
                if ($data->getDateTime()->format('Y-m-d') == $data2->getDateTime()->format('Y-m-d') && $data->getId() != $data2->getId()) {

                    $row = (float)$retturnArray->get($data->getDateTime()->format('Y-m-d'));
                    $row += (float)$data2->getMoney();

                    $retturnArray->set($data->getDateTime()->format('Y-m-d'), $row);

                    $arrayCol->remove($key);
                }

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