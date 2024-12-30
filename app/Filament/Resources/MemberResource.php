<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MemberResource\Pages;
use App\Models\Member;
use App\Models\Person;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MemberResource extends Resource
{
    protected static ?string $model = Member::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?int $navigationSort = 2;

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
                                $person = Person::where('cpf', $state)->first();
                                if ($person) {
                                    $set('person.name', $person->name);
                                    $set('person_id', $person->id);
                                }
                            }),
                        Forms\Components\TextInput::make('person.name')
                            ->required()
                            ->maxLength(255)
                            ->hidden(fn (Forms\Get $get) => !$get('person.cpf')),
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
