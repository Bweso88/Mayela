<?php

namespace App\Modules\Identity\Enums;

enum Role: string
{
    case Student = 'student';
    case Parent = 'parent';
    case SuperAdmin = 'super_admin';
    case PedagogicalAdmin = 'pedagogical_admin';
    case Contributor = 'contributor';
    case Moderator = 'moderator';

    public function label(): string
    {
        return match ($this) {
            self::Student => 'Élève',
            self::Parent => 'Parent / tuteur',
            self::SuperAdmin => 'Administrateur général',
            self::PedagogicalAdmin => 'Administrateur pédagogique',
            self::Contributor => 'Enseignant / contributeur',
            self::Moderator => 'Modérateur',
        };
    }

    /** Rôles ayant accès au back-office web. */
    public static function staff(): array
    {
        return [self::SuperAdmin, self::PedagogicalAdmin, self::Contributor, self::Moderator];
    }

    /** Rôles utilisant l'application mobile (API). */
    public static function appUsers(): array
    {
        return [self::Student, self::Parent];
    }

    public static function values(array $roles): array
    {
        return array_map(fn (self $role) => $role->value, $roles);
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $r) => [$r->value => $r->label()])->all();
    }
}
