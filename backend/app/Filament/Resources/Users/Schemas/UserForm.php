<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Modules\Identity\Enums\Role;
use App\Modules\Identity\Enums\UserStatus;
use App\Modules\Identity\Http\Requests\Concerns;
use App\Modules\Identity\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Password;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identité')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')->label('Nom complet')->required()->maxLength(120),
                        TextInput::make('email')->label('Adresse e-mail')->email()->maxLength(190)
                            ->unique(ignoreRecord: true)
                            ->dehydrateStateUsing(fn (?string $state) => Concerns::normalizeEmail($state)),
                        TextInput::make('phone')->label('Téléphone')->tel()
                            ->regex(Concerns::PHONE_REGEX)->unique(ignoreRecord: true),
                        Select::make('locale')->label('Langue')->options(['fr' => 'Français'])->default('fr')->required(),
                    ]),
                Section::make('Accès')
                    ->columns(2)
                    ->schema([
                        Select::make('roles')->label('Rôles')
                            ->relationship('roles', 'name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => Role::tryFrom($record->name)?->label() ?? $record->name)
                            ->multiple()->preload()->required(),
                        Select::make('status')->label('Statut')
                            ->options(UserStatus::class)->default(UserStatus::Active)->required()
                            // On ne peut pas se suspendre soi-même.
                            ->disabled(fn (?User $record) => $record?->is(auth()->user())),
                        TextInput::make('password')->label('Mot de passe')
                            ->password()->revealable()
                            ->rule(Password::min(10)->letters()->numbers())
                            ->required(fn (Page $livewire) => $livewire instanceof CreateRecord)
                            ->dehydrated(fn (?string $state) => filled($state))
                            ->helperText('Laisser vide pour conserver le mot de passe actuel.'),
                    ]),
            ]);
    }
}
