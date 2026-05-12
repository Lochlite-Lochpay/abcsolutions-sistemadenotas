<?php

/****** Another website produced by The Lochlite & Lochpay Company
___
|   |
|   |             _______         ______       __      __
|   |            /       \       /  ____|     |  |    |  |
|   |______     /         \     /  /          |  |----|  |
|          |    \         /     \  \____      |  |----|  |
|__________|     \_______/       \______|     |__|    |__| Lite ®


Long live Lochlite! ******/

namespace App\Http\Controllers;

use App\Models\Settings;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        /** Remove o redirecionamento ao painel admin */
        $request->session()->put('isLoggedAdmin', false);
        session()->forget('isLoggedAdmin');
        $request->session()->save();
        /*** Exibe os posts retornados pela API */
        $user = Auth::user();

        function disckUsage($diretorio = '/')
        {
            // Obtém o espaço total do disco em bytes
            $espacoTotal = disk_total_space($diretorio);

            // Obtém o espaço livre do disco em bytes
            $espacoLivre = disk_free_space($diretorio);

            // Calcula o espaço usado subtraindo o espaço livre do espaço total
            $espacoUsado = $espacoTotal - $espacoLivre;

            // Calcula a porcentagem de espaço usado
            $porcentagemUsado = ($espacoUsado / $espacoTotal) * 100;

            return $porcentagemUsado;
        }

        return inertia('Admin/Dashboard', ['user' => $user, 'diskusage' => disckUsage()]);
    }

    public function users(Request $request)
    {
        /** Remove o redirecionamento ao painel admin */
        $request->session()->put('isLoggedAdmin', false);
        session()->forget('isLoggedAdmin');
        $request->session()->save();
        /*** Exibe os posts retornados pela API */
        $user = Auth::user();
        $users = User::when($request->query('search'), function ($query) use ($request) {
            $query->where('name', 'like', '%'.$request->query('search').'%')
                ->orWhere('email', 'like', '%'.$request->query('search').'%');
        })->orderBy('created_at', 'desc')->paginate();

        return inertia('Admin/Users/Index', ['authid' => $user->id, 'users' => $users, 'search' => $request->query('search')]);
    }

    public function personalize()
    {
        $settings = Settings::first();

        return inertia('Admin/Personalize', ['settings' => $settings]);
    }

    public function updatePersonalize(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'night' => 'required|boolean',
            'auth_social_media' => 'required|boolean',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'auth_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'favicon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $settings = Settings::first();
        $settings->update($request->except(['_method', 'logo', 'auth_logo', 'favicon']));

        if ($request->hasFile('logo')) {
            $settings->update(['logo' => $request->file('logo')->store('logos', 'public')]);
        }

        if ($request->hasFile('auth_logo')) {
            $settings->update(['auth_logo' => $request->file('auth_logo')->store('authlogos', 'public')]);
        }

        if ($request->hasFile('favicon')) {
            $settings->update(['favicon' => $request->file('favicon')->store('favicons', 'public')]);
        }

        $settings->save();
        $request->session()->flash('flash.banner', 'Atualizado com sucesso.');
        $request->session()->flash('flash.bannerStyle', 'success');

        return redirect()->route('dashboard.admin.personalize');
    }

    public function create()
    {
        return inertia('Admin/Users/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email',
            'domain' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'domain' => $request->input('domain'),
            'password' => Hash::make($request->input('password')),
        ]);

        $request->session()->flash('flash.banner', 'Cadastrado com sucesso.');
        $request->session()->flash('flash.bannerStyle', 'success');

        return redirect()->route('dashboard.admin.users');
    }

    public function edit($id)
    {
        $user = User::find($id);

        if (! $user) {
            return back()->withErrors(['message' => 'Usuário não encontrado.'])->with('message', 'Usuário não encontrado.');
        }

        return inertia('Admin/Users/Edit', ['user' => $user, 'id' => $id]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'id' => 'required',
            'name' => 'required|string|max:255',
            'email' => 'required|email|string',
            'domain' => 'required|string',
        ]);

        $user = User::find($id);

        if ($user) {
            $user->update($request->except(['_method', 'password', 'password_confirmation']));
            if ($request->get('password')) {
                $user->update(['password' => Hash::make($request->get('password'))]);
            }
            $request->session()->flash('flash.banner', 'Atualizado com sucesso.');
            $request->session()->flash('flash.bannerStyle', 'success');

            return redirect()->route('dashboard.admin.users');
        } else {
            return back()->withErrors(['message' => 'Usuário não encontrado.'])->with('message', 'Usuário não encontrado.');
        }
    }

    public function destroy(Request $request, $id)
    {
        $thisuser = Auth::user();

        if ($thisuser->id == $id) {
            session()->flash('message', 'Você não pode excluir seu próprio usuário');

            return back()->with('message', 'Você não pode excluir seu próprio usuário');
        }

        $user = User::find($id);

        if ($user) {
            $user->delete();
            $request->session()->flash('flash.banner', 'Excluído com sucesso.');
            $request->session()->flash('flash.bannerStyle', 'success');

            return redirect()->route('dashboard.admin.users');
        } else {
            session()->flash('message', 'Usuário não encontrado.');

            return back()->withErrors(['message' => 'Usuário não encontrado.']);
        }
    }
}
