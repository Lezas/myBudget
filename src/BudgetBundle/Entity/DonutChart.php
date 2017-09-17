<?php
/**
 * Created by Lezas.
 * Date: 2017-09-16
 * Time: 12:17
 */

namespace BudgetBundle\Entity;


/**
 * Class DonutChart
 * @package BudgetBundle\Entity
 */
class DonutChart
{
    protected $data = [];

    /**
     * @param $name
     * @param $value
     */
    public function addData($name, $value)
    {
        $this->data[] = ['label' => $name,'value' => $value];
    }

    /**
     * @return array
     */
    public function generateChartData()
    {
        return $this->data;
    }

}