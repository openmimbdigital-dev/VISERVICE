<?php

namespace App\Enums;

enum DocumentType: string
{
    case CitizenshipCard = 'CC';
    case IdentityCard = 'TI';
    case BirthCertificate = 'RC';
    case ForeignerId = 'CE';
    case Passport = 'PA';
    case SpecialStayPermit = 'PEP';
    case TemporaryProtectionPermit = 'PPT';
    case TaxId = 'NIT';
    case UniquePersonalId = 'NUIP';
    case LiveBirthCertificate = 'CN';
    case MinorWithoutId = 'MS';
    case AdultWithoutId = 'AS';
    case DiplomaticCard = 'CD';

    public function label(): string
    {
        return match ($this) {
            self::CitizenshipCard => 'Cédula de ciudadanía',
            self::IdentityCard => 'Tarjeta de identidad',
            self::BirthCertificate => 'Registro civil',
            self::ForeignerId => 'Cédula de extranjería',
            self::Passport => 'Pasaporte',
            self::SpecialStayPermit => 'Permiso especial de permanencia',
            self::TemporaryProtectionPermit => 'Permiso por protección temporal',
            self::TaxId => 'NIT',
            self::UniquePersonalId => 'NUIP',
            self::LiveBirthCertificate => 'Certificado de nacido vivo',
            self::MinorWithoutId => 'Menor sin identificación',
            self::AdultWithoutId => 'Adulto sin identificación',
            self::DiplomaticCard => 'Carné diplomático',
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
