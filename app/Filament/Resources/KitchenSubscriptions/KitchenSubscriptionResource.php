<?php

namespace App\Filament\Resources\KitchenSubscriptions;

use App\Filament\Resources\KitchenSubscriptions\Pages\CreateKitchenSubscription;
use App\Filament\Resources\KitchenSubscriptions\Pages\EditKitchenSubscription;
use App\Filament\Resources\KitchenSubscriptions\Pages\ListKitchenSubscriptions;
use App\Filament\Resources\KitchenSubscriptions\Schemas\KitchenSubscriptionForm;
use App\Filament\Resources\KitchenSubscriptions\Tables\KitchenSubscriptionsTable;
use App\Models\KitchenSubscription;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class KitchenSubscriptionResource extends Resource
{
    public static function canDelete(Model $record): bool
    {
        return false;
    }

    protected static ?string $model = KitchenSubscription::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-check';

    protected static ?string $recordTitleAttribute = 'KitchenSubscription';

    protected static UnitEnum|string|null $navigationGroup = 'إدارة الاشتراكات و الدفعات';

    protected static ?string $navigationLabel = 'الاشتراكات';

    protected static ?string $modelLabel = 'الاشتراكات';

    protected static ?string $pluralModelLabel = 'الاشتراكات';

    public static function form(Schema $schema): Schema
    {
        return KitchenSubscriptionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KitchenSubscriptionsTable::configure($table);
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
            'index' => ListKitchenSubscriptions::route('/'),
            'create' => CreateKitchenSubscription::route('/create'),
            'edit' => EditKitchenSubscription::route('/{record}/edit'),
        ];
    }
}
