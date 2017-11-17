<?php

namespace BudgetBundle\Entity;

/**
 * Class DonutChart
 * @package BudgetBundle\Entity
 */
class DonutChart
{
    /** @var array */
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