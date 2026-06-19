<?php

namespace Modules\Financial\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Financial\Models\AccountsReceivable;
use Modules\Financial\Models\AccountsPayable;
use Modules\Financial\Models\FinancialAccount;
use Modules\Financial\Models\CostCenter;
use Modules\Financial\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinancialController extends Controller
{
    /**
     * Get financial account listing.
     */
    public function accounts()
    {
        return response()->json(FinancialAccount::all());
    }

    /**
     * Store a new financial account.
     */
    public function storeAccount(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:cash,bank',
            'balance' => 'required|numeric',
        ]);

        $account = FinancialAccount::create($request->all());
        return response()->json($account, 201);
    }

    /**
     * Get cost centers.
     */
    public function costCenters()
    {
        return response()->json(CostCenter::all());
    }

    /**
     * Store a new cost center.
     */
    public function storeCostCenter(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $cc = CostCenter::create($request->all());
        return response()->json($cc, 201);
    }

    /**
     * List accounts receivable.
     */
    public function receivables()
    {
        $receivables = AccountsReceivable::with('customer')->orderBy('due_date', 'asc')->get();
        return response()->json($receivables);
    }

    /**
     * Store account receivable manually.
     */
    public function storeReceivable(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'amount' => 'required|numeric|min:0.01',
            'due_date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        $receivable = AccountsReceivable::create([
            'customer_id' => $request->customer_id,
            'amount' => $request->amount,
            'due_date' => $request->due_date,
            'status' => 'pending',
        ]);

        return response()->json($receivable, 201);
    }

    /**
     * Pay/receive a receivable (receive amount into an account).
     */
    public function receivePayment(Request $request, $id)
    {
        $request->validate([
            'financial_account_id' => 'required|exists:financial_accounts,id',
            'cost_center_id' => 'nullable|exists:cost_centers,id',
        ]);

        $receivable = AccountsReceivable::findOrFail($id);
        if ($receivable->status === 'paid') {
            return response()->json(['message' => 'Lançamento já recebido.'], 400);
        }

        return DB::transaction(function () use ($receivable, $request) {
            $receivable->update(['status' => 'paid']);

            // Create Transaction
            Transaction::create([
                'financial_account_id' => $request->financial_account_id,
                'cost_center_id' => $request->cost_center_id,
                'type' => 'income',
                'amount' => $receivable->amount,
                'category' => 'Recebimento de Cliente',
                'reference_type' => 'accounts_receivable',
                'reference_id' => $receivable->id,
                'occurred_at' => now(),
            ]);

            // Update Account Balance
            $account = FinancialAccount::findOrFail($request->financial_account_id);
            $account->increment('balance', $receivable->amount);

            return response()->json(['message' => 'Recebimento efetuado com sucesso.', 'receivable' => $receivable]);
        });
    }

    /**
     * List accounts payable.
     */
    public function payables()
    {
        $payables = AccountsPayable::orderBy('due_date', 'asc')->get();
        return response()->json($payables);
    }

    /**
     * Store account payable manually.
     */
    public function storePayable(Request $request)
    {
        $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'due_date' => 'required|date',
            'supplier_id' => 'nullable',
        ]);

        $payable = AccountsPayable::create([
            'description' => $request->description,
            'amount' => $request->amount,
            'due_date' => $request->due_date,
            'supplier_id' => $request->supplier_id,
            'status' => 'pending',
        ]);

        return response()->json($payable, 201);
    }

    /**
     * Pay a payable account (withdraw from financial account).
     */
    public function payBill(Request $request, $id)
    {
        $request->validate([
            'financial_account_id' => 'required|exists:financial_accounts,id',
            'cost_center_id' => 'nullable|exists:cost_centers,id',
        ]);

        $payable = AccountsPayable::findOrFail($id);
        if ($payable->status === 'paid') {
            return response()->json(['message' => 'Lançamento já pago.'], 400);
        }

        return DB::transaction(function () use ($payable, $request) {
            $payable->update(['status' => 'paid']);

            // Create Transaction
            Transaction::create([
                'financial_account_id' => $request->financial_account_id,
                'cost_center_id' => $request->cost_center_id,
                'type' => 'expense',
                'amount' => $payable->amount,
                'category' => 'Pagamento de Despesa',
                'reference_type' => 'accounts_payable',
                'reference_id' => $payable->id,
                'occurred_at' => now(),
            ]);

            // Update Account Balance
            $account = FinancialAccount::findOrFail($request->financial_account_id);
            $account->decrement('balance', $payable->amount);

            return response()->json(['message' => 'Pagamento efetuado com sucesso.', 'payable' => $payable]);
        });
    }

    /**
     * Get Cash Flow Summary.
     */
    public function cashFlow(Request $request)
    {
        $days = $request->get('days', 30);
        $startDate = Carbon::now()->subDays($days);

        $transactions = Transaction::where('occurred_at', '>=', $startDate)
            ->select(
                DB::raw('DATE(occurred_at) as date'),
                DB::raw("SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income"),
                DB::raw("SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense")
            )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        return response()->json($transactions);
    }

    /**
     * Get DRE Statement (Stubs/Dynamic mix).
     */
    public function dre(Request $request)
    {
        // Calculate dynamic values
        $receitas = Transaction::where('type', 'income')->sum('amount');
        $despesas = Transaction::where('type', 'expense')->sum('amount');

        return response()->json([
            'receita_bruta' => (float)$receitas,
            'deducoes' => (float)($receitas * 0.10), // mock 10% impostos
            'receita_liquida' => (float)($receitas * 0.90),
            'custos_produtos' => (float)($receitas * 0.40), // mock 40% CMV
            'lucro_bruto' => (float)($receitas * 0.50),
            'despesas_operacionais' => (float)$despesas,
            'resultado_liquido' => (float)(($receitas * 0.50) - $despesas),
        ]);
    }
}
