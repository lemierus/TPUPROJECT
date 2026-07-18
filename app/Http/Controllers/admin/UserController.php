<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        $users = User::when(request()->routeIs('kepala.*'), function ($query) {
            $query->where('role', User::ROLE_PETUGAS);
        })->when(request()->routeIs('kdlh.*'), function ($query) {
            $query->where('role', User::ROLE_KEPALA);
        })->when($search, function ($query) use ($search) {
            $query->where('name', 'like', "%$search%")
                ->orWhere('email', 'like', "%$search%")
                ->orWhere('role', 'like', "%$search%");
        })
            ->latest()
            // === PAGINATION: 10 data per halaman ===
            // withQueryString() supaya filter 'search' tidak hilang saat pindah halaman.
            ->paginate(10)
            ->withQueryString();

        return view('admin.pages.users.index', compact('users'));
    }

    public function create()
    {
        $this->ensureKepalaCanOnlyManagePetugas();

        return view('admin.pages.users.form', [
            'user' => new User(),
            'tpuOptions' => User::tpuOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $this->ensureKepalaCanOnlyManagePetugas();

        $data = $this->validatedData($request);
        $data['password'] = Hash::make($data['password']);

        User::create($data);

        return redirect()->route($this->routePrefix().'.users.index')
            ->with('success', 'User berhasil ditambahkan');
    }

    public function edit(User $user)
    {
        $this->ensureAccessibleUser($user);

        return view('admin.pages.users.form', [
            'user' => $user,
            'tpuOptions' => User::tpuOptions(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $this->ensureAccessibleUser($user);

        $data = $this->validatedData($request, $user->id, true);

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route($this->routePrefix().'.users.index')
            ->with('success', 'User berhasil diperbarui');
    }

    public function destroy(User $user)
    {
        $this->ensureAccessibleUser($user);

        if ($user->is(auth()->user())) {
            return back()->with('error', 'Akun yang sedang login tidak boleh dihapus.');
        }

        $user->delete();

        return redirect()->route($this->routePrefix().'.users.index')
            ->with('success', 'User berhasil dihapus');
    }

    private function validatedData(Request $request, ?int $ignoreId = null, bool $isUpdate = false): array
    {
        $isKepalaRoute = request()->routeIs('kepala.*');
        $isKdlhRoute = request()->routeIs('kdlh.*');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($ignoreId)],
            'nip' => ['nullable', 'string', 'max:30'],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'password' => [$isUpdate ? 'nullable' : 'required', 'string', 'min:6'],
            'role' => $isKepalaRoute
                ? ['required', Rule::in([User::ROLE_PETUGAS])]
                : ($isKdlhRoute
                    ? ['required', Rule::in([User::ROLE_KEPALA])]
                    : ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_KDLH, User::ROLE_PETUGAS, User::ROLE_KEPALA, User::ROLE_USER])]),
            'tpu' => ($isKepalaRoute)
                ? ['required', Rule::in(User::tpuOptions())]
                : ['nullable', Rule::in(User::tpuOptions())],
        ]);

        if ($isKepalaRoute) {
            $data['role'] = User::ROLE_PETUGAS;
        } elseif ($isKdlhRoute) {
            $data['role'] = User::ROLE_KEPALA;
        }

        return $data;
    }

    private function routePrefix(): string
    {
        if (request()->routeIs('kepala.*')) {
            return 'kepala';
        }

        if (request()->routeIs('kdlh.*')) {
            return 'kdlh';
        }

        return 'admin';
    }

    private function ensureKepalaCanOnlyManagePetugas(): void
    {
        if (! request()->routeIs('kepala.*') && ! request()->routeIs('kdlh.*')) {
            return;
        }

        if (request()->routeIs('kepala.*')) {
            abort_unless(auth()->user()?->isKepala(), 403);
            return;
        }

        abort_unless(auth()->user()?->isKdlh(), 403);
    }

    private function ensureAccessibleUser(User $user): void
    {
        if (! request()->routeIs('kepala.*') && ! request()->routeIs('kdlh.*')) {
            return;
        }

        if (request()->routeIs('kepala.*')) {
            abort_unless(auth()->user()?->isKepala() && $user->role === User::ROLE_PETUGAS, 403);
            return;
        }

        abort_unless(auth()->user()?->isKdlh() && $user->role === User::ROLE_KEPALA, 403);
    }
}