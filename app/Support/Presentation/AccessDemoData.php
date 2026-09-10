<?php

namespace App\Support\Presentation;

class AccessDemoData
{
    public const SESSION_USERS_KEY = 'presentation.access.users';

    public const SESSION_ROLES_KEY = 'presentation.access.roles';

    /** @return list<string> */
    public static function permissionLabels(): array
    {
        return [
            'Ver cursos',
            'Editar cursos',
            'Mi aula',
            'Crear actividades',
            'Ver reportes',
            'Inscripciones',
            'Facturación',
            'Eventos',
        ];
    }

    /** @return list<array<string, mixed>> */
    public static function defaultRoles(): array
    {
        return [
            ['id' => 'role-admin', 'name' => 'Administrador', 'description' => 'Acceso total al colegio de demostración.', 'permissions' => self::permissionLabels()],
            ['id' => 'role-coord', 'name' => 'Coordinador', 'description' => 'Gestión académica y reportes.', 'permissions' => ['Ver cursos', 'Editar cursos', 'Mi aula', 'Crear actividades', 'Ver reportes', 'Eventos']],
            ['id' => 'role-teacher', 'name' => 'Miss / Docente', 'description' => 'Aula, actividades y consulta de notas.', 'permissions' => ['Ver cursos', 'Mi aula', 'Crear actividades']],
            ['id' => 'role-billing', 'name' => 'Facturación', 'description' => 'Cobros, facturas y pagos.', 'permissions' => ['Inscripciones', 'Facturación']],
        ];
    }

    /** @return list<array<string, mixed>> */
    public static function defaultUsers(): array
    {
        return [
            ['id' => 'usr-1', 'name' => 'Lucía Andrade', 'email' => 'lucia.andrade@colegio.edu', 'role' => 'Administrador', 'status' => 'active'],
            ['id' => 'usr-2', 'name' => 'Ana Pérez', 'email' => 'ana.perez@colegio.edu', 'role' => 'Miss / Docente', 'status' => 'active'],
            ['id' => 'usr-3', 'name' => 'Laura Gómez', 'email' => 'laura.gomez@colegio.edu', 'role' => 'Miss / Docente', 'status' => 'active'],
            ['id' => 'usr-4', 'name' => 'Diego Vargas', 'email' => 'diego.vargas@colegio.edu', 'role' => 'Coordinador', 'status' => 'active'],
            ['id' => 'usr-5', 'name' => 'Marta Ríos', 'email' => 'marta.rios@colegio.edu', 'role' => 'Facturación', 'status' => 'inactive'],
        ];
    }

    /** @return list<array<string, mixed>> */
    public static function roles(): array
    {
        return self::mergeSession(self::defaultRoles(), self::SESSION_ROLES_KEY);
    }

    /** @return list<array<string, mixed>> */
    public static function users(): array
    {
        return self::mergeSession(self::defaultUsers(), self::SESSION_USERS_KEY);
    }

    /** @return array<string, mixed>|null */
    public static function role(string $id): ?array
    {
        return self::find(self::roles(), $id);
    }

    /** @return array<string, mixed>|null */
    public static function user(string $id): ?array
    {
        return self::find(self::users(), $id);
    }

    /** @param array<string, mixed> $role */
    public static function addRole(array $role): void
    {
        self::push(self::SESSION_ROLES_KEY, $role);
    }

    /** @param array<string, mixed> $user */
    public static function addUser(array $user): void
    {
        self::push(self::SESSION_USERS_KEY, $user);
    }

    /** @return list<string> */
    public static function roleNames(): array
    {
        return array_column(self::roles(), 'name');
    }

    /** @param list<array<string, mixed>> $defaults */
    private static function mergeSession(array $defaults, string $key): array
    {
        $created = session($key, []);

        return array_values(array_merge($defaults, is_array($created) ? $created : []));
    }

    /** @param array<string, mixed> $item */
    private static function push(string $key, array $item): void
    {
        $created = session($key, []);
        if (! is_array($created)) {
            $created = [];
        }
        $created[] = $item;
        session([$key => $created]);
    }

    /** @param list<array<string, mixed>> $items */
    private static function find(array $items, string $id): ?array
    {
        foreach ($items as $item) {
            if ($item['id'] === $id) {
                return $item;
            }
        }

        return null;
    }
}
