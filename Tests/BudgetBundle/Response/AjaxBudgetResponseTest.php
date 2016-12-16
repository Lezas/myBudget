<?php

namespace Tests\BudgetBundle\Response;

use BudgetBundle\Response\AjaxBudgetResponse;

/**
 * Class AjaxBudgetResponseTest
 * @package Tests\BudgetBundle\Response
 */
class AjaxBudgetResponseTest extends \PHPUnit_Framework_TestCase
{
    public function testAjaxBudgetResponse()
    {
        $response = new AjaxBudgetResponse();

        $return = $response->getResponse();

        $this->assertEquals(null, $return['form']);
        $this->assertFalse($return['valid']);
        $this->assertFalse($return['success']);

        $response->setDataToInvalid();
        $response->setResponseToFailure();

        $return = $response->getResponse();

        $this->assertEquals(null, $return['form']);
        $this->assertFalse($return['valid']);
        $this->assertFalse($return['success']);

        $response->setDataToValid();
        $response->setResponseToSuccessful();
        $response->setRenderedForm('form');

        $return = $response->getResponse();

        $this->assertEquals('form', $return['form']);
        $this->assertTrue($return['valid']);
        $this->assertTrue($return['success']);
    }
}