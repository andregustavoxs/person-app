<?php

namespace App\Filament\Resources\MemberResource\Pages;

use App\Filament\Resources\MemberResource;
use App\Models\Person;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditMember extends EditRecord
{
    protected static string $resource = MemberResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if ($this->record->person) {
            $data['person'] = [
                'cpf' => $this->record->person->cpf,
                'name' => $this->record->person->name,
            ];
        }

        return $data;
    }

    protected function afterSave(): void
    {
        // Update the person's name if it has changed
        if ($this->data['person']['name'] !== $this->record->person->name) {
            $person = $this->record->person;
            $person->name = $this->data['person']['name'];
            $person->save();

            Notification::make()
                ->success()
                ->title('Person updated')
                ->body('The person\'s name has been updated successfully.')
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
