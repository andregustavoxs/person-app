<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Models\Employee;
use App\Models\Member;
use App\Models\Person;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?int $navigationSort = 3;

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
                                    $set('person_id', null);
                                    $set('hire_date', null);
                                    $set('work_card', '');
                                    return;
                                }

                                if ($person) {
                                    $set('person.name', $person->name);
                                    $set('person_id', $person->id);
                                }
                            }),
                        Forms\Components\TextInput::make('person.name')
                            ->required()
                            ->maxLength(255),
                            // ->hidden(fn (Forms\Get $get) => !$get('person.cpf')),
                        Forms\Components\Hidden::make('person_id'),
                    ]),
                Forms\Components\DatePicker::make('hire_date')
                    ->required(),
                Forms\Components\TextInput::make('work_card')
                    ->required()
                    ->maxLength(255),
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
                Tables\Columns\TextColumn::make('hire_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('work_card')
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
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }
}
