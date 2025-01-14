<?php

namespace App\Filament\Resources\MemberResource\Pages;

use App\Filament\Resources\MemberResource;
use App\Models\Person;
use Filament\Resources\Pages\CreateRecord;

class CreateMember extends CreateRecord
{
    protected static string $resource = MemberResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (!isset($data['person_id'])) {
            $person = Person::create([
                'name' => $data['person']['name'],
                'cpf' => $data['person']['cpf'],
                'email' => $data['person']['email'] ?? null,
            ]);
            $data['person_id'] = $person->id;
        }

        unset($data['person']);
        return $data;
    }
}
