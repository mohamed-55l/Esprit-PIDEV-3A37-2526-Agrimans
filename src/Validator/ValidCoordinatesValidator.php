<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ValidCoordinatesValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof ValidCoordinates) {
            throw new UnexpectedTypeException($constraint, ValidCoordinates::class);
        }

        // Si la valeur est null, on ne valide pas (laisser NotNull gérer ça)
        if ($value === null) {
            return;
        }

        // On s'attend à ce que $value soit l'entité Parcelle
        if (!$value instanceof \App\Modules\Parcelle\Entity\Parcelle) {
            return;
        }

        $latitude = $value->getLatitude();
        $longitude = $value->getLongitude();

        // Si les deux sont null, pas de validation
        if ($latitude === null && $longitude === null) {
            return;
        }

        // Si l'un est null et pas l'autre, invalide
        if (($latitude === null && $longitude !== null) || ($latitude !== null && $longitude === null)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ latitude }}', $latitude ?? 'null')
                ->setParameter('{{ longitude }}', $longitude ?? 'null')
                ->addViolation();
            return;
        }

        // Validation des plages (déjà fait par Assert\Range, mais on peut renforcer)
        if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ latitude }}', $latitude)
                ->setParameter('{{ longitude }}', $longitude)
                ->addViolation();
            return;
        }

        // Exemple de validation métier : vérifier si c'est en Tunisie (approximatif)
        // Tunisie : latitude ~30-37°N, longitude ~7-12°E
        if ($latitude < 30 || $latitude > 37 || $longitude < 7 || $longitude > 12) {
            $this->context->buildViolation('Les coordonnées doivent être situées en Tunisie (latitude 30-37°N, longitude 7-12°E).')
                ->setParameter('{{ latitude }}', $latitude)
                ->setParameter('{{ longitude }}', $longitude)
                ->addViolation();
        }
    }
}