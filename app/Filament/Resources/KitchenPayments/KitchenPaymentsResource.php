<?php

namespace App\Filament\Resources\KitchenPayments;

use App\Filament\Resources\KitchenPayments\Pages\CreateKitchenPayments;
use App\Filament\Resources\KitchenPayments\Pages\EditKitchenPayments;
use App\Filament\Resources\KitchenPayments\Pages\ListKitchenPayments;
use App\Filament\Resources\KitchenPayments\Schemas\KitchenPaymentsForm;
use App\Filament\Resources\KitchenPayments\Tables\KitchenPaymentsTable;
use App\Models\KitchenPayment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class KitchenPaymentsResource extends Resource
{
    protected static ?string $model = KitchenPayment::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $recordTitleAttribute = 'id';

    protected static UnitEnum|string|null $navigationGroup = 'إدارة الاشتراكات و الدفعات';

    protected static ?string $navigationLabel = 'الدفعات';

    protected static ?string $modelLabel = 'دفعة';

    protected static ?string $pluralModelLabel = 'الدفعات';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return KitchenPaymentsForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KitchenPaymentsTable::configure($table);
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
            'index' => ListKitchenPayments::route('/'),
            'create' => CreateKitchenPayments::route('/create'),
            'edit' => EditKitchenPayments::route('/{record}/edit'),
        ];
    }
}
