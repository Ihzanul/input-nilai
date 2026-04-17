<?php

namespace App\Enums;

enum Agama: string
{
    case Islam    = 'Islam';
    case Kristen  = 'Kristen';
    case Katolik  = 'Katolik';
    case Hindu    = 'Hindu';
    case Budha    = 'Budha';
    case Konghucu = 'Konghucu';

    public function label(): string
    {
        return $this->value;
    }

    public static function options(): array
    {
        return array_column(self::cases(), 'value', 'value');
    }
}
