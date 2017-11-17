<?php

namespace BudgetBundle\Response;

/**
 * Class AjaxBudgetResponse
 */
class AjaxBudgetResponse
{
    /** @var null */
    protected $form = null;

    /** @var bool */
    protected $success = false;

    /** @var bool */
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
     * @return array
     */
    public function getResponse()
    {
        return $this->generateResponse();
    }

    /**
     * @return array
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