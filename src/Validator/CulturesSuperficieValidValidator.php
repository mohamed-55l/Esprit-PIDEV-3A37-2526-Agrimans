<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use App\Entity\Parcelle;

class CulturesSuperficieValidValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof CulturesSuperficieValid) {
            throw new UnexpectedTypeException($constraint, CulturesSuperficieValid::class);
        }

        if (!$value instanceof Parcelle) {
            return;
        }

        $superficieParcelle = $value->getSuperficie();
        $totalSuperficieCultures = 0;

        // Note: Dans une vraie implémentation, on pourrait avoir une propriété superficie dans Culture
        // Pour l'exemple, on suppose que les cultures n'ont pas de superficie individuelle
        // On peut calculer basé sur le nombre de cultures ou autre logique métier

        // Exemple simplifié : si plus de 5 cultures, considérer comme dépassement
        $nombreCultures = count($value->getCultures());
        if ($nombreCultures > 5 && $superficieParcelle < 10) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ total }}', $nombreCultures)
                ->setParameter('{{ parcelle }}', $superficieParcelle)
                ->addViolation();
        }
    }
}