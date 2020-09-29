<?php


namespace App\Service;

use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

trait ValidationTrait
{
    /**
     * @param FormInterface $form
     * @param ConstraintViolationListInterface $violationList
     */
    private function addErrorToForm(FormInterface $form, ConstraintViolationListInterface $violationList)
    {
        foreach ($violationList as $error) {
            $form->get($error->getPropertyPath())->addError(new FormError($error->getMessage()));
        }
    }
}
