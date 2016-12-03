<?php

namespace BudgetBundle\Response;
use Symfony\Component\Form\Form;

/**
 * Class AjaxBudgetResponse
 * @package BudgetBundle\Response
 */
class AjaxBudgetResponse
{

    protected $form = null;

    protected $success = false;

    protected $isDataValid = false;

    /**
     * @param $form
     */
    public function setRenderedForm($form)
    {
        $this->form = $form;
    }

    public function setResponseToSuccessful()
    {
        $this->success = true;
    }

    public function setResponseToFailure()
    {
        $this->success = false;
    }

    public function setDataToValid()
    {
        $this->isDataValid = true;
    }

    public function setDataToInvalid()
    {
        $this->isDataValid = false;
    }

    /**
     * @return mixed
     */
    public function getResponse()
    {
        return $this->generateResponse();
    }

    /**
     * @return mixed
     */
    private function generateResponse()
    {
        $response = [];
        $response['success'] = $this->success;
        $response['valid'] = $this->isDataValid;
        $response['form'] = $this->form;

        return $response;
    }
}