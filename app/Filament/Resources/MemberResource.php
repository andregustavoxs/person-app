<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MemberResource\Pages;
use App\Models\Member;
use App\Models\Person;
use App\Models\Employee;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class MemberResource extends Resource
{
    protected static ?string $model = Member::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['person']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\TextInput::make('person.cpf')
                            ->label('CPF')
                            ->required()
                            ->maxLength(14)
                            ->mask('999.999.999-99')
                            ->live(onBlur: true)
                            ->disabled(fn ($livewire) => $livewire instanceof Pages\EditMember)
                            ->dehydrated(fn ($livewire) => !($livewire instanceof Pages\EditMember))
                            ->beforeStateDehydrated(function ($state, callable $get, callable $set) {
                                $person = Person::where('cpf', $state)->first();
                                if ($person) {
                                    $set('person_id', $person->id);
                                }
                            })
                            ->afterStateHydrated(function ($state, $record, callable $set) {
                                if ($record && $record->person) {
                                    $set('person.cpf', $record->person->cpf);
                                    $set('person.name', $record->person->name);
                                }
                            })
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if (!$state) return;

                                $person = Person::where('cpf', $state)->first();
                                
                                // Check if CPF exists in Members or Employees
                                $existingMember = Member::whereHas('person', function ($query) use ($state) {
                                    $query->where('cpf', $state);
                                })->first();
                                
                                $existingEmployee = Employee::whereHas('person', function ($query) use ($state) {
                                    $query->where('cpf', $state);
                                })->first();

                                if ($existingMember || $existingEmployee) {
                                    Notification::make()
                                        ->warning()
                                        ->title('Member or Employee already registered')
                                        ->body('A person with this CPF is already registered in the system.')
                                        ->persistent()
                                        ->send();

                                    // Clear the form fields
                                    $set('person.cpf', '');
                                    $set('person.name', '');
                                    $set('person.email', '');
                                    $set('person_id', null);
                                    $set('registration_number', '');
                                    $set('member_type', null);
                                    return;
                                }

                                if ($person) {
                                    $set('person.name', $person->name);
                                    $set('person.email', $person->email);
                                    $set('person_id', $person->id);
                                }
                            }),
                        Forms\Components\TextInput::make('person.name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true),
                        Forms\Components\TextInput::make('person.email')
                            ->email()
                            ->maxLength(255),
                        Forms\Components\Hidden::make('person_id'),
                    ]),
                Forms\Components\TextInput::make('registration_number')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Forms\Components\Select::make('member_type')
                    ->required()
                    ->options([
                        'active' => 'Active',
                        'retired' => 'Retired',
                        'pensioner' => 'Pensioner',
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('person.cpf')
                    ->label('CPF')
                    ->searchable(),
                Tables\Columns\TextColumn::make('person.name')
                    ->label('Name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('person.email')
                    ->label('Email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('registration_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('member_type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMembers::route('/'),
            'create' => Pages\CreateMember::route('/create'),
            'edit' => Pages\EditMember::route('/{record}/edit'),
        ];
    }
}
