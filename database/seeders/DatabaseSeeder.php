<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Customer;
use Modules\Products\Models\Product;
use Modules\Stock\Models\StockItem;
use Modules\Stock\Models\StockLocation;
use Modules\Stock\Models\StockLot;
use Modules\Stock\Models\StockMovement;
use Modules\Financial\Models\FinancialAccount;
use Modules\Financial\Models\CostCenter;
use Modules\Financial\Models\AccountsPayable;
use Modules\Financial\Models\AccountsReceivable;
use Modules\Financial\Models\Transaction;
use Modules\Orders\Models\Order;
use Modules\Orders\Models\OrderItem;
use Modules\Orders\Models\Coupon;
use Modules\Orders\Models\ProductReview;
use Modules\Orders\Models\BlogPost;
use Modules\Orders\Models\Wishlist;
use Modules\Notifications\Models\Notification;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Criar usuário teste ERP
        $admin = User::updateOrCreate(
            ['email' => 'admin@heimdall.com'],
            [
                'name' => 'Admin Heimdall',
                'password' => bcrypt('password'),
                'role' => 'admin',
            ]
        );

        // 2. Criar Cliente do e-commerce
        $customer = Customer::updateOrCreate(
            ['email' => 'cliente@example.com'],
            [
                'name' => 'João Silva',
                'password' => bcrypt('password'),
                'phone' => '11999999999',
                'status' => 'active'
            ]
        );

        // Rodar Feature Flags
        $this->call(FeatureFlagSeeder::class);

        // 3. Cadastrar Produtos (Máximo 2)
        $p1 = Product::updateOrCreate(
            ['sku' => 'CBL-001'],
            ['name' => 'Cabo HDMI 2.1 2m', 'price' => 89.90, 'cost' => 35.00, 'stock_control' => true, 'status' => 'active']
        );
        $p2 = Product::updateOrCreate(
            ['sku' => 'TEC-012'],
            ['name' => 'Teclado Mecânico RGB', 'price' => 349.90, 'cost' => 150.00, 'stock_control' => true, 'status' => 'active']
        );

        // 4. Cadastrar Itens de Estoque e Localizações
        $loc1 = StockLocation::firstOrCreate(
            ['warehouse' => 'CD-01', 'aisle' => 'A1', 'shelf' => 'P3', 'position' => 'G1']
        );
        $loc2 = StockLocation::firstOrCreate(
            ['warehouse' => 'CD-02', 'aisle' => 'B2', 'shelf' => 'P1', 'position' => 'G2']
        );

        foreach ([$p1, $p2] as $p) {
            StockItem::updateOrCreate(
                ['product_id' => $p->id],
                ['quantity' => 50]
            );
        }

        // Criar Lotes de Estoque (Máximo 2)
        StockLot::updateOrCreate(
            ['product_id' => $p1->id, 'lot_number' => 'L2024-001'],
            ['quantity' => 8, 'expiry_date' => now()->addYear()]
        );
        StockLot::updateOrCreate(
            ['product_id' => $p2->id, 'lot_number' => 'L2024-045'],
            ['quantity' => 40, 'expiry_date' => null]
        );

        // 5. Cadastrar Contas Financeiras e Centros de Custo (Máximo 2)
        $accCash = FinancialAccount::updateOrCreate(
            ['name' => 'Caixa Interno'],
            ['type' => 'cash', 'balance' => 5000.00]
        );
        $accBank = FinancialAccount::updateOrCreate(
            ['name' => 'Banco Itaú'],
            ['type' => 'bank', 'balance' => 45000.00]
        );

        $ccSales = CostCenter::updateOrCreate(['name' => 'Vendas']);
        $ccAdmin = CostCenter::updateOrCreate(['name' => 'Administrativo']);

        // 6. Cadastrar Cupons
        $coupon = Coupon::updateOrCreate(
            ['code' => 'WELCOME10'],
            ['type' => 'percentage', 'value' => 10.00, 'min_order_value' => 50.00, 'expires_at' => now()->addMonth()]
        );

        // 7. Cadastrar Pedido Simulado
        $order = Order::create([
            'customer_id' => $customer->id,
            'total' => 389.80,
            'status' => 'paid',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $p1->id,
            'quantity' => 2,
            'price' => 89.90,
        ]);

        // Registrar Contas a Receber
        AccountsReceivable::create([
            'customer_id' => $customer->id,
            'order_id' => $order->id,
            'amount' => 389.80,
            'due_date' => now()->subDays(1),
            'status' => 'paid',
        ]);

        // Registrar Receita no Caixa/Banco
        Transaction::create([
            'financial_account_id' => $accBank->id,
            'cost_center_id' => $ccSales->id,
            'type' => 'income',
            'amount' => 389.80,
            'category' => 'Venda e-commerce',
            'reference_type' => 'order',
            'reference_id' => $order->id,
            'occurred_at' => now(),
        ]);

        // Contas a Pagar simuladas
        AccountsPayable::create([
            'supplier_id' => 1,
            'description' => 'Fornecedor de Cabo HDMI Ltda',
            'amount' => 1200.00,
            'due_date' => now()->addDays(5),
            'status' => 'pending',
        ]);

        // 8. Wishlist e Avaliações de Produtos
        Wishlist::updateOrCreate([
            'customer_id' => $customer->id,
            'product_id' => $p2->id,
        ]);

        ProductReview::create([
            'customer_id' => $customer->id,
            'product_id' => $p1->id,
            'rating' => 5,
            'comment' => 'Excelente cabo, qualidade de som e imagem impecável!',
            'approved' => true,
        ]);

        // 9. Blog posts
        BlogPost::updateOrCreate(
            ['slug' => 'como-escolher-o-melhor-cabo-hdmi'],
            [
                'title' => 'Como Escolher o Melhor Cabo HDMI 2.1 em 2026',
                'content' => 'Os cabos HDMI 2.1 oferecem suporte a taxas de atualização mais altas e resoluções fantásticas...',
                'published_at' => now(),
            ]
        );

        // 10. Notificações do sistema
        Notification::create([
            'user_id' => $admin->id,
            'title' => 'Estoque Crítico Alerta',
            'message' => 'Cabo HDMI 2.1 2m está com quantidade (8 un) abaixo do estoque mínimo definido (10 un).',
            'type' => 'warning',
            'read_at' => null,
        ]);

        // 11. CRM Seeds (Funil, 2 Leads e 2 Atividades)
        $pipeline = \Modules\CRM\Models\Pipeline::firstOrCreate(['name' => 'Funil de Vendas Padrão']);
        $stages = [
            ['name' => 'Novo Lead', 'order_position' => 1, 'color' => '#3B82F6'],
            ['name' => 'Contato Realizado', 'order_position' => 2, 'color' => '#F59E0B'],
            ['name' => 'Proposta Enviada', 'order_position' => 3, 'color' => '#8B5CF6'],
            ['name' => 'Negócio Fechado', 'order_position' => 4, 'color' => '#10B981'],
            ['name' => 'Negócio Perdido', 'order_position' => 5, 'color' => '#EF4444'],
        ];
        
        $stageModels = [];
        foreach ($stages as $stage) {
            $stageModels[] = \Modules\CRM\Models\PipelineStage::firstOrCreate(
                ['pipeline_id' => $pipeline->id, 'name' => $stage['name']],
                ['order_position' => $stage['order_position'], 'color' => $stage['color']]
            );
        }

        $lead1 = \Modules\CRM\Models\Lead::firstOrCreate(
            ['email' => 'roberto@empresa.com'],
            [
                'name' => 'Roberto Santos',
                'phone' => '11988888888',
                'source' => 'Busca Google',
                'status' => 'new',
                'pipeline_stage_id' => $stageModels[0]->id,
            ]
        );
        $lead2 = \Modules\CRM\Models\Lead::firstOrCreate(
            ['email' => 'patricia@loja.com'],
            [
                'name' => 'Patrícia Lima',
                'phone' => '11977777777',
                'source' => 'Indicação',
                'status' => 'contacted',
                'pipeline_stage_id' => $stageModels[1]->id,
            ]
        );

        \Modules\CRM\Models\Activity::firstOrCreate(
            ['lead_id' => $lead1->id, 'title' => 'Telefonar para Roberto'],
            [
                'type' => 'call',
                'description' => 'Apresentar catálogo técnico do ERP.',
                'due_at' => now()->addDays(1),
            ]
        );
        \Modules\CRM\Models\Activity::firstOrCreate(
            ['lead_id' => $lead2->id, 'title' => 'Enviar proposta com PDF comercial'],
            [
                'type' => 'email',
                'description' => 'Montar orçamento customizado de atacado.',
                'due_at' => now()->addDays(2),
            ]
        );

        // 12. HelpDesk Seeds (2 Tickets de exemplo)
        \Modules\HelpDesk\Models\Ticket::firstOrCreate(
            ['subject' => 'Dificuldade no checkout da loja'],
            [
                'customer_id' => $customer->id,
                'category' => 'financeiro',
                'status' => 'open',
                'priority' => 'high',
                'description' => 'Não estou conseguindo finalizar o pagamento usando cartão de crédito, aparece erro interno.',
            ]
        );
        \Modules\HelpDesk\Models\Ticket::firstOrCreate(
            ['subject' => 'Rastreamento do pedido #102'],
            [
                'customer_id' => $customer->id,
                'category' => 'logistica',
                'status' => 'answered',
                'priority' => 'medium',
                'description' => 'O código de rastreamento enviado ainda não consta no sistema dos Correios.',
            ]
        );
    }
}
