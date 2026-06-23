<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ErpUserController extends Controller
{
    /**
     * List all ERP users.
     */
    public function index()
    {
        return response()->json(User::orderBy('id', 'desc')->get());
    }

    /**
     * Store a new ERP user.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => ['required', Rule::in(['admin', 'manager', 'operator', 'seller', 'financial', 'support'])],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return response()->json($user, 201);
    }

    /**
     * Update an ERP user.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:6',
            'role' => ['required', Rule::in(['admin', 'manager', 'operator', 'seller', 'financial', 'support'])],
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return response()->json($user);
    }

    /**
     * Delete an ERP user.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Prevent self-deletion
        if ($user->id === auth()->user()->id) {
            return response()->json(['message' => 'Você não pode excluir o seu próprio usuário.'], 400);
        }

        $user->delete();

        return response()->json(['message' => 'Usuário excluído com sucesso.']);
    }

    /**
     * Return structural permissions for mapping.
     */
    public function permissions()
    {
        // Define matrix of permissions per role
        return response()->json([
            'roles' => [
                'admin' => ['crm.leads', 'crm.deals', 'crm.settings', 'financial.view', 'financial.edit', 'stock.view', 'stock.edit', 'production.view', 'production.edit', 'support.view', 'support.edit', 'users.manage'],
                'manager' => ['crm.leads', 'crm.deals', 'financial.view', 'stock.view', 'stock.edit', 'production.view', 'production.edit', 'support.view'],
                'operator' => ['stock.view', 'stock.edit', 'production.view', 'production.edit'],
                'seller' => ['crm.leads', 'crm.deals'],
                'financial' => ['financial.view', 'financial.edit'],
                'support' => ['support.view', 'support.edit'],
            ],
            'modules' => [
                'CRM' => [
                    ['key' => 'crm.leads', 'label' => 'Gerenciar Leads'],
                    ['key' => 'crm.deals', 'label' => 'Visualizar Negócios'],
                    ['key' => 'crm.settings', 'label' => 'Configurar Pipeline'],
                ],
                'Estoque' => [
                    ['key' => 'stock.view', 'label' => 'Visualizar Estoque'],
                    ['key' => 'stock.edit', 'label' => 'Ajustar Movimentações'],
                ],
                'Financeiro' => [
                    ['key' => 'financial.view', 'label' => 'Visualizar DRE / Contas'],
                    ['key' => 'financial.edit', 'label' => 'Lançar Receitas / Despesas'],
                ],
                'Produção' => [
                    ['key' => 'production.view', 'label' => 'Visualizar Produção'],
                    ['key' => 'production.edit', 'label' => 'Gerenciar BOM e Ordens'],
                ],
                'Suporte' => [
                    ['key' => 'support.view', 'label' => 'Visualizar Chamados'],
                    ['key' => 'support.edit', 'label' => 'Responder Tickets'],
                ],
                'Administrativo' => [
                    ['key' => 'users.manage', 'label' => 'Gerenciar Usuários e Permissões'],
                ]
            ]
        ]);
    }
}
