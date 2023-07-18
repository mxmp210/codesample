<?php
namespace App\Validator\Constraints;

use App\Database\ORM;
use App\Entity\Customer;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueCustomerEmailValidator extends ConstraintValidator
{
    /** @var \Cycle\ORM\ORM $db **/
    private $db;

    public function __construct()
    {
        $this->db = ORM::getInstance();
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof Customer) {
            throw new UnexpectedTypeException($value, Customer::class);
        }

        if (!$constraint instanceof UniqueCustomerEmail) {
            throw new UnexpectedTypeException($constraint, UniqueCustomerEmail::class);
        }

        $entityRepository = $this->db->getRepository(Customer::class);

        /** @var Customer $searchResult */
        $searchResult = $entityRepository->findOne([
            'email' => $value->getEmail()
        ]);

        if ($searchResult !== null) {
            // Check if value and setting customer object are same - than it's update operation
            if($searchResult->getEmail() == $value->getEmail() && $searchResult->getId() == $value->getId()) {
                return;
            }

            // User is trying to claim differernt email that is occupied
            $this->context->buildViolation($constraint->message)
                ->atPath('email')
                ->setParameter('{{ email }}', $value->getEmail() )
                ->addViolation();
        }
    }
}