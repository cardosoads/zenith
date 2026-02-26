<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\ProviderProfile;
use App\Models\ProviderSubscription;
use App\Models\User;
use App\ProviderStatus;
use App\SubscriptionStatus;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class RegisteredUserController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'cpf' => ['required', 'string', 'max:14', 'regex:/^\d{3}\.?\d{3}\.?\d{3}-?\d{2}$/', 'unique:users,cpf'],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone'],
            'seguimento' => ['required', 'string', 'max:100'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'name.required' => 'O nome é obrigatório.',
            'name.max' => 'O nome deve ter no máximo :max caracteres.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Informe um e-mail válido.',
            'email.unique' => 'Este e-mail já está em uso.',
            'cpf.required' => 'O CPF é obrigatório.',
            'cpf.max' => 'O CPF deve ter no máximo :max caracteres.',
            'cpf.regex' => 'Informe um CPF válido.',
            'cpf.unique' => 'Este CPF já está em uso.',
            'phone.required' => 'O telefone é obrigatório.',
            'phone.max' => 'O telefone deve ter no máximo :max caracteres.',
            'phone.unique' => 'Este telefone já está em uso.',
            'seguimento.required' => 'O seguimento é obrigatório.',
            'seguimento.max' => 'O seguimento deve ter no máximo :max caracteres.',
            'password.required' => 'A senha é obrigatória.',
            'password.confirmed' => 'A confirmação de senha não confere.',
        ]);

        $now = now();
        $trialEndsAt = $now->copy()->addDays(7);
        $starterPlan = Plan::query()->where('slug', 'starter')->firstOrFail();

        $user = User::query()->create([
            'name' => $request->string('name')->value(),
            'email' => $request->string('email')->value(),
            'cpf' => $request->string('cpf')->value(),
            'phone' => $request->string('phone')->value(),
            'seguimento' => $request->string('seguimento')->value(),
            'password' => Hash::make($request->string('password')->value()),
        ]);

        Role::findOrCreate('provider');

        $user->assignRole('provider');

        $profile = ProviderProfile::query()->create([
            'user_id' => $user->id,
            'slug' => Str::slug($user->name.'-'.Str::random(6)),
            'display_name' => $user->name,
            'timezone' => 'America/Sao_Paulo',
            'status' => ProviderStatus::Active,
            'billing_status' => 'trialing',
            'trial_ends_at' => $trialEndsAt,
        ]);

        ProviderSubscription::query()->create([
            'provider_profile_id' => $profile->id,
            'plan_id' => $starterPlan->id,
            'payment_provider' => 'trial',
            'status' => SubscriptionStatus::Trialing,
            'current_period_start' => $now,
            'current_period_end' => $trialEndsAt,
        ]);

        $profile->agendas()->firstOrCreate(
            ['slug' => 'agenda-principal'],
            [
                'name' => 'Agenda Principal',
                'description' => 'Agenda inicial para atendimento',
                'timezone' => $profile->timezone,
                'is_published' => false,
                'theme' => 'auto',
                'accent' => 'sky',
                'density' => 'medium',
                'preset' => 'clean',
                'embed_height' => 680,
                'customer_extra_fields' => [],
            ]
        );

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
