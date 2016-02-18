<?php
namespace BudgetBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class DateTimeNotPastValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (new \DateTime() > $value) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('%string%', $value->format('Y-m-d h:i'))
                ->addViolation();
        }
    }
}