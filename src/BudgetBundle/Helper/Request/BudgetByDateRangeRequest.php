<?php

namespace BudgetBundle\Helper\Request;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Created by PhpStorm.
 * User: Lezas
 * Date: 2017-01-03
 * Time: 18:14
 */
final class BudgetByDateRangeRequest
{
    private $request;

    private $dateTo = null;

    private $dateFrom = null;

    public function setRequest(RequestStack $request_stack) {

        $this->request = $request_stack->getCurrentRequest();
    }

    public function getDateFrom()
    {
        $dateFrom = $this->dateFrom;
        if ($dateFrom == null) {
            if ($this->request->query->get('date_from') != "") {
                $dateFrom = new \DateTime($this->request->query->get('date_from'));
            } else {
                $date = new \DateTime('now');

                //how to get first and last day of the month
                $dateFrom = new \DateTime(date('Y-m-01', $date->getTimestamp()));
            }
        }

        return $dateFrom;
    }

    public function getDateTo()
    {
        $dateTo = $this->dateTo;
        if ($dateTo == null) {
            if ($this->request->query->get('date_to') != "") {

                $dateTo = new \DateTime($this->request->query->get('date_to'));
            } else {
                $date = new \DateTime('now');

                $dateTo = new \DateTime(date('Y-m-t', $date->getTimestamp()));
            }
        }

        return $dateTo;
    }
}