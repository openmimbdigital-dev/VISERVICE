<?php

namespace App\Actions\Business;

use App\Models\Business;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Crea al administrador de un negocio recién nacido.
 *
 * Un negocio sin usuario no le sirve a nadie: nadie puede entrar. Por eso el
 * registro público siempre crea uno, y por eso esta acción existe aparte —para
 * que crear el negocio desde el panel del superAdmin haga exactamente lo mismo
 * y no queden dos maneras distintas de nacer.
 *
 * Son cuatro cosas que tienen que pasar juntas: el usuario, su vínculo con el
 * negocio como principal, el rol de administrador y su ficha de participante.
 */
class CreateBusinessOwnerAction
{
    use AsAction;

    /**
     * @param  array{first_name: string, last_name: string, email: string, username: string, password: string, phone_number?: ?string}  $data
     */
    public function handle(Business $business, array $data, string $role = 'Administrador'): User
    {
        $user = User::create([
            'first_name'   => $data['first_name'],
            'last_name'    => $data['last_name'],
            'email'        => $data['email'],
            'username'     => $data['username'],
            'password'     => Hash::make($data['password']),
            'phone_number' => ($data['phone_number'] ?? '') ?: null,
            'status'       => true,
        ]);

        $user->attachBusiness((int) $business->id, is_primary: true);
        $user->assignRole($role);

        CreateParticipantFromUserAction::run($user, (int) $business->id);

        return $user;
    }
}
