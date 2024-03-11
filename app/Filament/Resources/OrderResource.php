<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Filament\Resources\OrderResource\RelationManagers\ProductOnesRelationManager;
use App\Filament\Resources\OrderResource\RelationManagers\ProductThreesRelationManager;
use App\Filament\Resources\OrderResource\RelationManagers\ProductTwosRelationManager;
use App\Filament\Resources\OrderResource\RelationManagers\UserRelationManager;
use App\Models\Order;
use App\Settings\PlatformSettings;
use Webbingbrasil\FilamentDateFilter\DateFilter;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\IconColumn;


class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationLabel = 'Verifications';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('user_id')
                    ->required(),
                Forms\Components\TextInput::make('product_type')
                    ->required()
                    ->maxLength(255),
                // Forms\Components\TextInput::make('product_id'),
                Forms\Components\TextInput::make('cost')
                    ->required(),
                Forms\Components\TextInput::make('phase')
                    ->required(),
                Forms\Components\DatePicker::make('breached_at')->visible(fn (Order $record) => $record->isBreached()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name'),
                Tables\Columns\TextColumn::make('product_type')->description(fn (Order $record) => match ($record->product_type) {
                    'ONE' =>  app(PlatformSettings::class)->product_one_title,
                    'TWO' => app(PlatformSettings::class)->product_two_title,
                    'THREE' => app(PlatformSettings::class)->product_three_title,
                })->searchable()->label("Product"),
                // Tables\Columns\TextColumn::make('product_id'),
                Tables\Columns\TextColumn::make('cost'),
                Tables\Columns\TextColumn::make('phase'),
                Tables\Columns\TextColumn::make('breached_at')
                    ->date()->icon(fn (Order $record) => $record->isBreached() ? 'heroicon-o-x-circle' : null)->color('danger'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
                // Tables\Columns\TextColumn::make('updated_at')
                //     ->dateTime(),
                IconColumn::make('product_id')->label("Product Assigned")
                    ->boolean(fn (Order $record) => $record->product_id !== null)
                    ->falseIcon('heroicon-o-x-circle')
            ])
            ->filters([
                Filter::make('breached_at')->label("Hide breached orders")->query(
                    fn (Builder $query): Builder => $query->whereNull('breached_at')
                ),


                SelectFilter::make('product_type')->label("Challenge Type")->options([
                    'ONE' =>  app(PlatformSettings::class)->product_one_title,
                    'TWO' => app(PlatformSettings::class)->product_two_title,
                    'THREE' => app(PlatformSettings::class)->product_three_title,
                ])->multiple(),

                // FilalemtDa

                DateFilter::make('created_at')
                    ->label(__('Created At'))
                    ->minDate(Carbon::today()->subMonth(1))
                    ->maxDate(Carbon::today()->addMonth(2))
                    ->timeZone('Africa/Lagos')
                    ->range()
                    ->fromLabel(__('From'))
                    ->untilLabel(__('Until'))
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                // Tables\Actions\Action::make('Assign account'),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('breach')
                        ->label('Mark As Breached')->requiresConfirmation()->action(
                            function (Order $record) {
                                $record->markAsBreached();
                                Notification::make()->title('Order marked as breached')->success()->send();
                            }
                        )->visible(function (Order $record) {
                            return !$record->isBreached();
                        }),
                    Tables\Actions\Action::make('promote')
                        ->label('Promote Account')->requiresConfirmation()->action(
                            function (Order $record) {
                                $record->promote();
                                // } else {
                                // }
                            }
                        )->visible(function (Order $record) {
                            return !$record->isBreached() && $record->phase <= 2;
                        }),
                ])
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            UserRelationManager::class,
            ProductOnesRelationManager::class,
            ProductTwosRelationManager::class,
            ProductThreesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            // 'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->latest();
    }
}
