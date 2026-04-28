<?php

namespace App\Filament\Resources\KitchenInvoices;

use App\Filament\Resources\KitchenInvoices\Pages\CreateKitchenInvoice;
use App\Filament\Resources\KitchenInvoices\Pages\EditKitchenInvoice;
use App\Filament\Resources\KitchenInvoices\Pages\ListKitchenInvoices;
use App\Filament\Resources\KitchenInvoices\Schemas\KitchenInvoiceForm;
use App\Filament\Resources\KitchenInvoices\Tables\KitchenInvoicesTable;
use App\Filament\Widgets\AutoInvoiceWidget;
use App\Filament\Widgets\KitchenInvoicesSummaryWidget;
use App\Models\KitchenInvoice;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class KitchenInvoiceResource extends Resource
{
    protected static ?string $model = KitchenInvoice::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $recordTitleAttribute = 'KitchenInvoice';

    protected static UnitEnum|string|null $navigationGroup = 'إدارة الاشتراكات و الدفعات';

    protected static ?string $navigationLabel = 'الفواتير';

    protected static ?string $modelLabel = 'الفواتير';

    protected static ?string $pluralModelLabel = 'الفواتير';


    public static function form(Schema $schema): Schema
    {
        return KitchenInvoiceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KitchenInvoicesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getWidgets(): array
    {
        return [
            AutoInvoiceWidget::class,
            KitchenInvoicesSummaryWidget::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListKitchenInvoices::route('/'),
            'create' => CreateKitchenInvoice::route('/create'),
            'edit' => EditKitchenInvoice::route('/{record}/edit'),
        ];
    }

    /**
     * منع حذف الفواتير المرتبطة بدفعات
     */
    public static function canDelete($record): bool
    {
        return ! $record->allocations()->exists();
    }
}

