<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Illuminate\Support\Facades\Hash;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\UserResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\UserResource\RelationManagers;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $label = 'Utilisateurs';

    protected static ?string $navigationGroup = 'Authentification';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                ->schema([
                    Grid::make(2)
                    ->schema([

                        TextInput::make('name')
                        ->label(strval(__('Nom complet')))
                        ->required(),
                    TextInput::make('username')
                        ->required()
                        ->unique(table: static::$model, ignorable: fn ($record) => $record)
                        ->label(strval(__('Nom d\'utilisateur'))),
                    TextInput::make('password')
                        ->same('passwordConfirmation')
                        ->hiddenOn('view')
                        ->live(debounce: 250)
                        ->password()
                        ->maxLength(255)
                        ->required(fn ($component, $get, $livewire, $model, $record, $set, $state) => $record === null)
                        ->dehydrateStateUsing(fn ($state) => ! empty($state) ? Hash::make($state) : '')
                        ->label(strval(__('Mot de passe'))),
                    TextInput::make('passwordConfirmation')
                        ->password()
                        ->dehydrated(false)
                        ->visible(fn(Get $get) => filled($get('password')))
                        ->maxLength(255)
                        ->label(strval(__('Confirmation de mot de passe'))),
                    ]),
                   
                        
                        ])
            ]);
    }

    public static function table(Table $table): Table
    {
        $columns = [
    
            'name'              => TextColumn::make('name')
                ->searchable()
                ->sortable()
                ->label(strval(__('filament-authentication::filament-authentication.field.user.name'))),
            'username'             => TextColumn::make('username')
                ->searchable()
                ->sortable()
                ->label(strval(__('Nom d\'utilisateur'))),

            // 'email_verified_at' => IconColumn::make('email_verified_at')
            // ->default(false)
            //     ->boolean()
            //     ->label(strval(__('filament-authentication::filament-authentication.field.user.verified_at'))),
            // 'roles.name'        => TextColumn::make('roles.name')->badge()
            //     ->label(strval(__('filament-authentication::filament-authentication.field.user.roles'))),
            'created_at'        => TextColumn::make('created_at')
                ->dateTime('Y-m-d H:i:s')
                ->label(strval(__('Date de création')))
                ->date("d M Y"),
        ];

        return $table
            ->columns($columns)
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
