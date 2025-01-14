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
                'email' => $this->record->person->email,
            ];
        }

        return $data;
    }

    protected function afterSave(): void
    {
        // Update the person's name and email if they have changed
        $person = $this->record->person;
        $changed = false;

        if ($this->data['person']['name'] !== $person->name) {
            $person->name = $this->data['person']['name'];
            $changed = true;
        }

        if (($this->data['person']['email'] ?? null) !== $person->email) {
            $person->email = $this->data['person']['email'];
            $changed = true;
        }

        if ($changed) {
            $person->save();

            Notification::make()
                ->success()
                ->title('Person updated')
                ->body('The person\'s information has been updated successfully.')
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
