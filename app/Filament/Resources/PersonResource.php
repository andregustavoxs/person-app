<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PersonResource\Pages;
use App\Models\Person;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\QueryException;

class PersonResource extends Resource
{
    protected static ?string $model = Person::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'People';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('cpf')
                    ->label('CPF')
                    ->required()
                    ->maxLength(14)
                    ->mask('999.999.999-99')
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('cpf')
                    ->label('CPF')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
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
                Tables\Actions\DeleteAction::make()
                    ->before(function (Person $record) {
                        if ($record->employee()->exists()) {
                            Notification::make()
                                ->danger()
                                ->title('Delete Failed')
                                ->body('It is not possible to delete a Person with related Employees.')
                                ->send();

                            return false;
                        }

                        if ($record->member()->exists()) {
                            Notification::make()
                                ->danger()
                                ->title('Delete Failed')
                                ->body('It is not possible to delete a Person with related Members.')
                                ->send();

                            return false;
                        }
                    })
                    ->using(function (Person $record) {
                        try {
                            $record->delete();
                            return true;
                        } catch (QueryException $e) {
                            return false;
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records) {
                            foreach ($records as $record) {
                                if ($record->employee()->exists()) {
                                    Notification::make()
                                        ->danger()
                                        ->title('Bulk Delete Failed')
                                        ->body('Some selected people have Employee relationships and cannot be deleted.')
                                        ->send();

                                    return false;
                                }

                                if ($record->member()->exists()) {
                                    Notification::make()
                                        ->danger()
                                        ->title('Bulk Delete Failed')
                                        ->body('Some selected people have Member relationships and cannot be deleted.')
                                        ->send();

                                    return false;
                                }
                            }
                        })
                        ->using(function ($records) {
                            foreach ($records as $record) {
                                try {
                                    $record->delete();
                                } catch (QueryException $e) {
                                    continue;
                                }
                            }
                            return true;
                        }),
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
            'index' => Pages\ListPeople::route('/'),
            'create' => Pages\CreatePerson::route('/create'),
            'edit' => Pages\EditPerson::route('/{record}/edit'),
        ];
    }
}
