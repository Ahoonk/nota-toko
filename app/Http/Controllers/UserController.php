<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('q')->toString();

        $users = User::query()
            ->with('company')
            ->latest('id')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%')
                        ->orWhere('role', 'like', '%'.$search.'%')
                        ->orWhereHas('company', function ($companyQuery) use ($search) {
                            $companyQuery->where('name', 'like', '%'.$search.'%');
                        });
                });
            })
            ->paginate(10)
            ->withQueryString();

        return view('users.index', [
            'users' => $users,
        ]);
    }

    public function create(): View
    {
        return view('users.form', [
            'record' => null,
            'companies' => Company::query()->orderBy('name')->pluck('name', 'id')->all(),
            'roles' => config('nota_toko.roles', []),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateRequest($request);
        $validated['company_id'] = $validated['company_id'] ?: $request->user()?->company_id;

        User::create($validated);

        return redirect()->route('users.index')->with('status', 'User berhasil disimpan.');
    }

    public function edit(User $user): View
    {
        return view('users.form', [
            'record' => $user,
            'companies' => Company::query()->orderBy('name')->pluck('name', 'id')->all(),
            'roles' => config('nota_toko.roles', []),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $this->validateRequest($request, $user->id);
        $validated['company_id'] = $validated['company_id'] ?: $request->user()?->company_id;

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $user->fill($validated)->save();

        return redirect()->route('users.index')->with('status', 'User berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()?->is($user)) {
            return redirect()->route('users.index')->with('error', 'Akun yang sedang login tidak bisa dihapus.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('status', 'User berhasil dihapus.');
    }

    protected function validateRequest(Request $request, ?int $ignoreId = null): array
    {
        $roles = config('nota_toko.roles', []);

        return $request->validate([
            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($ignoreId),
            ],
            'password' => [$ignoreId ? 'nullable' : 'required', 'string', 'min:6', 'confirmed'],
            'role' => ['required', Rule::in($roles)],
        ]);
    }
}
