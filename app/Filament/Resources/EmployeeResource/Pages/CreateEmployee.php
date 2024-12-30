<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Filament\Resources\EmployeeResource;
use App\Models\Person;
use Filament\Resources\Pages\CreateRecord;

class CreateEmployee extends CreateRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (!isset($data['person_id'])) {
            $person = Person::create([
                'name' => $data['person']['name'],
                'cpf' => $data['person']['cpf'],
            ]);
            $data['person_id'] = $person->id;
        }

        unset($data['person']);
        return $data;
    }
}
